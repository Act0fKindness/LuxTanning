<?php

namespace App\Http\Controllers\Cleaner;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Job;
use App\Support\JobPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('Cleaner/Dashboard', $this->buildPayload($request));
    }

    public function today(Request $request)
    {
        return Inertia::render('PWA/Today', $this->buildPayload($request));
    }

    protected function buildPayload(Request $request): array
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'cleaner', 403);

        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) {
            abort(403, 'No tenant context for cleaner');
        }

        $today = Carbon::today($user->timezone ?? config('app.timezone'));

        $baseQuery = Job::with('staff')
            ->where('tenant_id', $tenantId)
            ->where('staff_user_id', $user->id);

        $todayCollection = (clone $baseQuery)
            ->whereDate('date', $today)
            ->orderBy('date')
            ->orderBy('sequence')
            ->orderBy('eta_window')
            ->get();

        $upcomingCollection = (clone $baseQuery)
            ->whereDate('date', '>', $today)
            ->orderBy('date')
            ->orderBy('sequence')
            ->orderBy('eta_window')
            ->limit(10)
            ->get();

        $addressMap = $this->addressContextForJobs($todayCollection->merge($upcomingCollection));

        $todayJobs = $todayCollection
            ->map(fn (Job $job) => JobPresenter::make($job, [
                'address' => $this->addressForJob($job, $addressMap),
            ]))
            ->values();

        $upcomingJobs = $upcomingCollection
            ->map(fn (Job $job) => JobPresenter::make($job, [
                'address' => $this->addressForJob($job, $addressMap),
            ]))
            ->values();

        $active = $todayJobs->first(fn (array $job) => in_array($job['status'], ['started', 'arrived', 'en_route']));
        if (!$active) {
            $active = $todayJobs->first(fn (array $job) => $job['status'] === 'scheduled');
        }

        $completedCount = $todayJobs->where('status', 'completed')->count();
        $completedTotal = (clone $baseQuery)->where('status', 'completed')->count();
        $inProgressToday = $todayJobs->filter(fn (array $job) => in_array($job['status'], ['started', 'arrived', 'en_route']))->count();

        $todayPayload = $todayJobs->values()->all();
        $upcomingPayload = $upcomingJobs->values()->all();

        return [
            'today' => $todayPayload,
            'upcoming' => $upcomingPayload,
            'activeJobId' => $active['id'] ?? null,
            'stats' => [
                'totalToday' => $todayJobs->count(),
                'completedToday' => $completedCount,
                'completedTotal' => $completedTotal,
                'inProgressToday' => $inProgressToday,
            ],
            'serverTime' => Carbon::now()->toIso8601String(),
            'mapsKey' => config('services.google.maps_key'),
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Job> $jobs
     */
    protected function addressContextForJobs($jobs): array
    {
        $ids = $jobs
            ->pluck('checklist_json.address_id')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return Address::whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->map(fn (Address $address) => [
                'id' => $address->id,
                'line1' => $address->line1,
                'line2' => $address->line2,
                'city' => $address->city,
                'county' => $address->county,
                'postcode' => $address->postcode,
                'lat' => $address->lat !== null ? (float) $address->lat : null,
                'lng' => $address->lng !== null ? (float) $address->lng : null,
            ])
            ->all();
    }

    protected function addressForJob(Job $job, array $addressMap): ?array
    {
        $addressId = $job->checklist_json['address_id'] ?? null;
        if (!$addressId) {
            return null;
        }

        return $addressMap[$addressId] ?? null;
    }

    protected function tenantIdOrFallback(Request $request): ?string
    {
        $tenantId = optional($request->user())->tenant_id;
        if ($tenantId) {
            return $tenantId;
        }

        if (Schema::hasTable('tenant_user')) {
            $membership = DB::table('tenant_user')
                ->where('user_id', optional($request->user())->id)
                ->first();

            if ($membership && $membership->tenant_id) {
                return $membership->tenant_id;
            }
        }

        return null;
    }
}
