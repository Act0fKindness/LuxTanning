<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tenant;
use App\Models\Payout;

class PayoutsController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user())->role === 'platform_admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);
        $tenants = Tenant::get(['id','name'])->keyBy('id');
        $rows = Payout::orderBy('period_end','desc')->limit(200)->get(['id','tenant_id','period_start','period_end','amount_pence','fee_pence','status','created_at']);
        $items = $rows->map(function ($p) use ($tenants) {
            return [
                'id' => $p->id,
                'tenant_id' => $p->tenant_id,
                'tenant' => $tenants[$p->tenant_id]->name ?? 'Unknown',
                'period_start' => optional($p->period_start)->toDateTimeString(),
                'period_end' => optional($p->period_end)->toDateTimeString(),
                'amount_pence' => (int)$p->amount_pence,
                'fee_pence' => (int)$p->fee_pence,
                'status' => $p->status,
                'created_at' => optional($p->created_at)->toDateTimeString(),
            ];
        });
        return Inertia::render('Hub/Payouts', [ 'items' => $items ]);
    }
}

