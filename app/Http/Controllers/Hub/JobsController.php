<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Support\PlatformJobsFetcher;

class JobsController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user())->role === 'platform_admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $daysBack = (int) min(max((int) $request->query('days_back', 3), 0), 30);
        $daysAhead = (int) min(max((int) $request->query('days_ahead', 14), 0), 60);

        $today = Carbon::today();
        $windowStart = (clone $today)->subDays($daysBack);
        $windowEnd = (clone $today)->addDays($daysAhead);

        $jobs = PlatformJobsFetcher::fetch($windowStart, $windowEnd, 600);

        $nowMinutes = Carbon::now()->hour * 60 + Carbon::now()->minute;
        $activeNow = $jobs->filter(function (array $job) use ($nowMinutes) {
            $start = $job['start_minutes'];
            $end = $job['end_minutes'];
            if (!is_numeric($start)) {
                return false;
            }
            $effectiveEnd = is_numeric($end) ? $end : $start + 60;
            return $nowMinutes >= $start && $nowMinutes <= $effectiveEnd;
        })->count();

        $statusCounts = $jobs->groupBy(function ($job) {
            return $job['status'] ?? 'unscheduled';
        })->map->count()->mapWithKeys(function ($count, $status) {
            $label = $status ?? 'unscheduled';
            $label = $label === '' ? 'unscheduled' : $label;
            return [$label => $count];
        })->sortDesc()->toArray();

        $tenantCounts = $jobs->groupBy(function ($job) {
            return $job['tenant_name'] ?? 'Unknown company';
        })->map->count()->sortDesc()->toArray();

        return Inertia::render('Hub/Jobs', [
            'jobs' => $jobs->toArray(),
            'summary' => [
                'total' => $jobs->count(),
                'active_now' => $activeNow,
                'status_counts' => $statusCounts,
                'tenant_counts' => $tenantCounts,
                'window' => [
                    'start' => $windowStart->format('Y-m-d'),
                    'end' => $windowEnd->format('Y-m-d'),
                ],
            ],
            'filters' => [
                'days_back' => $daysBack,
                'days_ahead' => $daysAhead,
            ],
        ]);
    }
}
