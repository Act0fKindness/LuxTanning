<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\JobsController as TenantJobsController;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class JobsController extends Controller
{
    public function create(Request $request)
    {
        $tenantId = $this->tenantIdOrFallback($request);
        if (!$tenantId) {
            abort(403, 'Tenant context missing');
        }

        $customers = Customer::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email', 'phone']);

        $customerAddresses = Address::whereIn('user_id', $customers->pluck('id'))
            ->get(['id', 'user_id', 'line1', 'line2', 'city', 'postcode', 'lat', 'lng'])
            ->groupBy('user_id');

        $customersPayload = $customers->map(function (Customer $customer) use ($customerAddresses) {
            $addresses = ($customerAddresses->get($customer->id) ?? collect())->map(function (Address $address) {
                return [
                    'id' => $address->id,
                    'line1' => $address->line1,
                    'line2' => $address->line2,
                    'city' => $address->city,
                    'postcode' => $address->postcode,
                    'lat' => $address->lat,
                    'lng' => $address->lng,
                ];
            })->values();

            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'addresses' => $addresses,
            ];
        })->values();

        $staff = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['cleaner', 'manager'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name ?? $user->email,
                'email' => $user->email,
                'role' => $user->role,
            ])
            ->values();

        $tenant = Tenant::find($tenantId);

        return Inertia::render('Owner/Jobs/New', [
            'customers' => $customersPayload,
            'staff' => $staff,
            'tenant' => $tenant ? ['id' => $tenant->id, 'name' => $tenant->name] : null,
        ]);
    }

    public function store(Request $request, TenantJobsController $tenantJobsController)
    {
        return $tenantJobsController->store($request);
    }

    protected function tenantIdOrFallback(Request $request): ?string
    {
        $user = $request->user();
        if ($user && $user->tenant_id) {
            return $user->tenant_id;
        }

        if ($user && Schema::hasTable('tenant_user')) {
            $tenantId = DB::table('tenant_user')->where('user_id', $user->id)->value('tenant_id');
            if ($tenantId) {
                return $tenantId;
            }
        }

        if ($user && $user->role === 'owner') {
            $id = DB::table('tenants')->where('slug', 'aok-world')->value('id');
            if ($id) {
                return $id;
            }
        }

        $defaultSlug = config('tenant.default_slug');
        if ($defaultSlug) {
            return DB::table('tenants')->where('slug', $defaultSlug)->value('id');
        }

        return null;
    }
}
