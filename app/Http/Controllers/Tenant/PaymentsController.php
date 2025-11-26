<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Payment;
use Illuminate\Support\Facades\Schema;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;
        if (!$tenantId && Schema::hasTable('tenant_user')) {
            $tenantId = \Illuminate\Support\Facades\DB::table('tenant_user')->where('user_id', optional($request->user())->id)->value('tenant_id');
        }
        $items = [];
        if ($tenantId) {
            $items = Payment::where('tenant_id', $tenantId)
                ->latest()->take(200)
                ->get(['id','method','amount_pence','status','created_at'])
                ->map(fn($p) => [
                    'id' => $p->id,
                    'method' => $p->method,
                    'amount_pence' => $p->amount_pence,
                    'status' => $p->status,
                    'created_at' => $p->created_at?->toDateTimeString(),
                ]);
        }
        $view = $request->is('owner*') ? 'Owner/Payments' : 'Tenant/Payments';
        return Inertia::render($view, [
            'items' => $items,
        ]);
    }
}
