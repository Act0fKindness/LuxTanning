<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Job;
use App\Models\Payment;

class OverviewController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user())->role === 'platform_admin', 403);
    }

    public function show(Request $request)
    {
        $this->ensureAdmin($request);

        $today = Carbon::today();
        $d7 = (clone $today)->subDays(6);
        $d30 = (clone $today)->subDays(30);

        // Totals
        $totals = [
            'tenants' => Tenant::count(),
            'customers' => User::where('role', 'customer')->count(),
            'staff' => User::whereIn('role', ['owner','manager','cleaner','accountant'])->count(),
            'jobs_today' => Job::whereDate('date', $today)->count(),
            'jobs_7d_completed' => Job::whereBetween('date', [$d7, $today])->where('status','completed')->count(),
            'jobs_7d_total' => Job::whereBetween('date', [$d7, $today])->count(),
            'payments_30d' => 0,
            'platform_fees_30d' => 0,
        ];

        if (Schema::hasTable('payments')) {
            $payAgg = Payment::where('status','succeeded')
                ->where('created_at','>=',$d30)
                ->selectRaw('COALESCE(SUM(amount_pence),0) as amount, COALESCE(SUM(application_fee_pence),0) as app_fee')
                ->first();
            $totals['payments_30d'] = (int) ($payAgg->amount ?? 0);
            $totals['platform_fees_30d'] = (int) ($payAgg->app_fee ?? 0);
        }

        // Companies snapshot (top 50 by recent activity)
        $tenants = Tenant::orderBy('name')->get(['id','name','slug','status']);

        // Customers by tenant
        $customersByTenant = [];
        if (Schema::hasTable('tenant_user')) {
            $rows = DB::table('tenant_user')->select('tenant_id', DB::raw("SUM(CASE WHEN role='customer' THEN 1 ELSE 0 END) as c"))
                ->groupBy('tenant_id')->get();
            foreach ($rows as $r) { $customersByTenant[$r->tenant_id] = (int)$r->c; }
        } else {
            $rows = User::where('role','customer')->select('tenant_id', DB::raw('count(*) as c'))
                ->groupBy('tenant_id')->get();
            foreach ($rows as $r) { $customersByTenant[$r->tenant_id] = (int)$r->c; }
        }

        // Jobs today by tenant
        $jobsToday = Job::whereDate('date',$today)->select('tenant_id', DB::raw('count(*) as c'))
            ->groupBy('tenant_id')->get();
        $jobsTodayByTenant = [];
        foreach ($jobsToday as $r) { $jobsTodayByTenant[$r->tenant_id] = (int)$r->c; }

        // Payments & fees 30d by tenant
        $paymentsByTenant = [];
        $feesByTenant = [];
        if (Schema::hasTable('payments')) {
            $rows = Payment::where('status','succeeded')->where('created_at','>=',$d30)
                ->select('tenant_id', DB::raw('SUM(amount_pence) as amt'), DB::raw('SUM(application_fee_pence) as app_fee'))
                ->groupBy('tenant_id')->get();
            foreach ($rows as $r) { $paymentsByTenant[$r->tenant_id] = (int)$r->amt; $feesByTenant[$r->tenant_id] = (int)$r->app_fee; }
        }

        $tenantItems = $tenants->map(function ($t) use ($customersByTenant, $jobsTodayByTenant, $paymentsByTenant, $feesByTenant) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'status' => $t->status,
                'customers' => $customersByTenant[$t->id] ?? 0,
                'jobs_today' => $jobsTodayByTenant[$t->id] ?? 0,
                'payments_30d' => $paymentsByTenant[$t->id] ?? 0,
                'fees_30d' => $feesByTenant[$t->id] ?? 0,
            ];
        });

        // Upcoming jobs (next 10 by date/time)
        $upcomingJobs = Job::with('tenant')
            ->whereDate('date','>=',$today)
            ->orderBy('date')
            ->orderBy('eta_window')
            ->limit(10)
            ->get(['id','tenant_id','date','eta_window','status'])
            ->map(function ($j) {
                return [
                    'id' => $j->id,
                    'tenant' => $j->tenant?->name,
                    'date' => optional($j->date)->format('Y-m-d'),
                    'eta_window' => $j->eta_window,
                    'status' => $j->status,
                ];
            });

        return Inertia::render('Hub/Overview', [
            'totals' => $totals,
            'tenants' => $tenantItems,
            'upcoming_jobs' => $upcomingJobs,
        ]);
    }
}

