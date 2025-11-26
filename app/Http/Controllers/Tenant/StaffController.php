<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Schema;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;
        if (!$tenantId && Schema::hasTable('tenant_user')) {
            $tenantId = DB::table('tenant_user')->where('user_id', optional($request->user())->id)->value('tenant_id');
        }
        if (!$tenantId && optional($request->user())->role === 'owner') {
            $tenantId = DB::table('tenants')->where('slug','aok-world')->value('id');
        }
        $staff = [];
        if ($tenantId) {
            if (Schema::hasTable('tenant_user')) {
                $ids = DB::table('tenant_user')->where('tenant_id',$tenantId)->pluck('user_id');
                $staff = User::whereIn('id', $ids)
                    ->orderBy('name')
                    ->get(['id','name','email','phone','role'])
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'role' => $u->role,
                    ]);
            } else {
                $staff = User::where('tenant_id', $tenantId)
                    ->orderBy('name')
                    ->get(['id','name','email','phone','role'])
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'phone' => $u->phone,
                        'role' => $u->role,
                    ]);
            }
        }
        return Inertia::render('Tenant/Staff', [
            'staff' => $staff,
        ]);
    }

    public function invite(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:manager,cleaner,accountant',
        ]);
        $tenantId = optional($request->user())->tenant_id;
        if (!$tenantId && Schema::hasTable('tenant_user')) {
            $tenantId = DB::table('tenant_user')->where('user_id', optional($request->user())->id)->value('tenant_id');
        }
        if (!$tenantId && optional($request->user())->role === 'owner') {
            $tenantId = DB::table('tenants')->where('slug','aok-world')->value('id');
        }
        if (!$tenantId) return redirect()->back()->with('error', 'No tenant context');

        // Create user with a temporary password; invite email flow can be added later
        $tempPassword = Str::random(16);
        $user = User::firstOrNew(['email' => (string)$request->string('email')]);
        if (!$user->exists) {
            $user->name = (string)$request->string('name');
            $user->phone = (string)$request->string('phone');
            $user->role = (string)$request->string('role'); // keep for UI; membership is source of truth
            $user->password = bcrypt($tempPassword);
            $user->save();
        }
        // Attach membership
        TenantUser::firstOrCreate([
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
        ], [
            'role' => (string)$request->string('role'),
            'status' => 'invited',
        ]);
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => 'active',
            ]);
        }
        return redirect()->back()->with('success', 'Invitation created');
    }
}
