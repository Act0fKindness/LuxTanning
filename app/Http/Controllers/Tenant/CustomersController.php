<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\TenantUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomersController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;
        $items = [];
        if ($tenantId) {
            if (Schema::hasTable('tenant_user')) {
                $ids = DB::table('tenant_user')->where('tenant_id',$tenantId)->where('role','customer')->orderByDesc('created_at')->limit(200)->pluck('user_id');
                $items = User::whereIn('id', $ids)
                    ->get(['id','name','email','phone','created_at'])
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'created_at' => $u->created_at?->toDateTimeString(),
                    ]);
            } else {
                $items = User::where('tenant_id', $tenantId)
                    ->where('role', 'customer')
                    ->latest()->take(200)
                    ->get(['id','name','email','phone','created_at'])
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'created_at' => $u->created_at?->toDateTimeString(),
                    ]);
            }
        }
        $view = $request->is('owner*') ? 'Owner/Customers' : 'Tenant/Customers';
        return Inertia::render($view, [
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);
        $tenantId = optional($request->user())->tenant_id;
        if (!$tenantId) return redirect()->back()->with('error', 'No tenant context');

        $email = $request->string('email');
        $user = $email ? User::firstOrNew(['email' => $email]) : new User();
        $user->name = (string)$request->string('name');
        if ($email) $user->email = (string)$email;
        $user->phone = (string)$request->string('phone');
        $user->role = 'customer';
        if (!$user->password) { $user->password = bcrypt(str()->random(32)); }
        $user->save();
        TenantUser::firstOrCreate([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
        ], [ 'role' => 'customer', 'status' => 'active' ]);

        return redirect()->back()->with('success', 'Customer created');
    }
}
