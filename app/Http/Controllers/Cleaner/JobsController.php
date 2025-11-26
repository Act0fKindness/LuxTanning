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

class JobsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'cleaner', 403);

        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) {
            abort(403, 'No tenant context for cleaner');
        }

        $timezone = $user->timezone ?? config('app.timezone');
        $today = Carbon::today($timezone);

        $mineOnly = in_array($request->query('mine'), [1, '1', 'true', 'on'], true);
        $statusParam = $request->query('status');
        if (is_string($statusParam)) {
            $statusFilter = array_filter(array_map('trim', explode(',', $statusParam)));
        } elseif (is_array($statusParam)) {
            $statusFilter = array_filter(array_map('trim', $statusParam));
        } else {
            $statusFilter = [];
        }
        $statusFilter = array_values(array_unique($statusFilter));

        $windowStart = (clone $today)->subDays(3);
        $windowEnd = (clone $today)->addDays(14);

        if ($mineOnly && in_array('completed', $statusFilter, true)) {
            $windowStart = (clone $today)->subDays(120);
            $windowEnd = (clone $today)->endOfDay();
        }

        $jobsQuery = Job::with('staff')
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [(clone $windowStart)->startOfDay(), (clone $windowEnd)->endOfDay()]);

        if ($mineOnly) {
            $jobsQuery->where('staff_user_id', $user->id);
        }

        if (!empty($statusFilter)) {
            $jobsQuery->whereIn('status', $statusFilter);
        }

        $jobsCollection = $jobsQuery
            ->orderBy('date')
            ->orderBy('sequence')
            ->orderBy('eta_window')
            ->limit(250)
            ->get();

        $addressMap = $this->addressContextForJobs($jobsCollection);

        $jobs = $jobsCollection
            ->map(fn (Job $job) => JobPresenter::make($job, [
                'is_mine' => $job->staff_user_id === $user->id,
                'address' => $this->addressForJob($job, $addressMap),
            ]))
            ->values();

        return Inertia::render('Cleaner/Jobs', [
            'jobs' => $jobs,
            'window' => [
                'from' => $windowStart->toDateString(),
                'to' => $windowEnd->toDateString(),
            ],
            'mapsKey' => config('services.google.maps_key'),
            'filters' => [
                'mine' => $mineOnly,
                'status' => $statusFilter,
            ],
        ]);
    }

    public function show(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'cleaner', 403);

        $this->ensureJobForCleaner($job, $request);

        $job->load('staff');

        $presentedJob = $this->presentJob($job);

        $sameDayJobs = collect();
        if ($job->date) {
            $sameDayJobs = Job::with('staff')
                ->where('tenant_id', $job->tenant_id)
                ->where('staff_user_id', $user->id)
                ->whereDate('date', $job->date)
                ->orderBy('eta_window')
                ->orderBy('sequence')
                ->get()
                ->map(fn (Job $item) => JobPresenter::make($item, [
                    'is_mine' => $item->id === $job->id,
                ]))
                ->values();
        }

        return Inertia::render('Cleaner/Job', [
            'job' => $presentedJob,
            'sameDay' => $sameDayJobs,
            'mapsKey' => config('services.google.maps_key'),
        ]);
    }

    public function updateStatus(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'cleaner', 403);
        $this->ensureJobForCleaner($job, $request);

        $data = $request->validate([
            'action' => 'required|string|in:start,en_route,arrive,finish,cancel,resume',
        ]);

        $action = $data['action'];
        $now = Carbon::now();

        $previousStatus = $job->status;

        switch ($action) {
            case 'start':
                $job->status = 'started';
                $job->started_at = $job->started_at ?: $now;
                $job->completed_at = null;
                $job->actual_minutes = null;
                $job->cancelled_at = null;
                break;
            case 'en_route':
                $job->status = 'en_route';
                if ($previousStatus !== 'started') {
                    $job->started_at = null;
                }
                $job->completed_at = null;
                $job->actual_minutes = null;
                $job->cancelled_at = null;
                break;
            case 'arrive':
                $job->status = 'arrived';
                $job->started_at = $job->started_at ?: $now;
                $job->cancelled_at = null;
                break;
            case 'finish':
                if (!$job->started_at) {
                    $job->started_at = $now;
                }
                $job->status = 'completed';
                $job->completed_at = $now;
                $job->actual_minutes = $job->started_at ? $job->started_at->diffInMinutes($now) : null;
                $job->cancelled_at = null;
                break;
            case 'resume':
                $job->status = 'started';
                $job->started_at = $job->started_at ?: $now;
                $job->completed_at = null;
                $job->actual_minutes = null;
                $job->cancelled_at = null;
                break;
            case 'cancel':
                $job->status = 'cancelled';
                $job->cancelled_at = $now;
                break;
        }

        $job->save();

        return response()->json([
            'job' => $this->presentJob($job->fresh('staff')),
        ]);
    }

    public function updateLocation(Request $request, Job $job)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'cleaner', 403);
        $this->ensureJobForCleaner($job, $request);

        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
        ]);

        $now = Carbon::now();

        $job->forceFill([
            'last_lat' => $data['lat'],
            'last_lng' => $data['lng'],
            'last_location_at' => $now,
        ])->save();

        return response()->json([
            'job' => $this->presentJob($job->fresh('staff')),
        ]);
    }

    protected function ensureJobForCleaner(Job $job, Request $request): void
    {
        $user = $request->user();
        $tenantId = $this->tenantIdOrFallback($request);

        if (!$tenantId || $job->tenant_id !== $tenantId || $job->staff_user_id !== $user->id) {
            abort(403, 'Cannot modify this job');
        }
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
            ->map(fn (Address $address) => $this->formatAddress($address))
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

    protected function formatAddress(?Address $address): ?array
    {
        if (!$address) {
            return null;
        }

        return [
            'id' => $address->id,
            'line1' => $address->line1,
            'line2' => $address->line2,
            'city' => $address->city,
            'county' => $address->county,
            'postcode' => $address->postcode,
            'lat' => $address->lat !== null ? (float) $address->lat : null,
            'lng' => $address->lng !== null ? (float) $address->lng : null,
        ];
    }

    protected function presentJob(Job $job, array $context = []): array
    {
        $addressId = $job->checklist_json['address_id'] ?? null;
        $address = $addressId ? Address::find($addressId) : null;

        return JobPresenter::make($job, array_merge($context, [
            'is_mine' => true,
            'address' => $this->formatAddress($address),
        ]));
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
