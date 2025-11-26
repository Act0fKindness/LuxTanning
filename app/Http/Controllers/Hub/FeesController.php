<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Payment;

class FeesController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user())->role === 'platform_admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);
        $period = $request->query('period', '30d');
        $since = Carbon::today()->subDays($period === '7d' ? 7 : 30);

        $tenants = Tenant::get(['id','name'])->keyBy('id');
        $rows = Payment::where('status','succeeded')->where('created_at','>=',$since)
            ->select('tenant_id',
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(amount_pence),0) as amount'),
                DB::raw('COALESCE(SUM(application_fee_pence),0) as app_fee'),
                DB::raw('COALESCE(SUM(processor_fee_pence),0) as proc_fee')
            )
            ->groupBy('tenant_id')
            ->orderByDesc(DB::raw('SUM(amount_pence)'))
            ->get();

        $items = $rows->map(function ($r) use ($tenants) {
            $amount = (int)$r->amount;
            $appFee = (int)$r->app_fee;
            $procFee = (int)$r->proc_fee;
            $net = $amount - $appFee - $procFee;
            return [
                'tenant_id' => $r->tenant_id,
                'tenant' => $tenants[$r->tenant_id]->name ?? 'Unknown',
                'count' => (int)$r->count,
                'amount_pence' => $amount,
                'application_fee_pence' => $appFee,
                'processor_fee_pence' => $procFee,
                'net_pence' => $net,
            ];
        });

        return Inertia::render('Hub/Fees', [
            'period' => $period,
            'items' => $items,
        ]);
    }
}

