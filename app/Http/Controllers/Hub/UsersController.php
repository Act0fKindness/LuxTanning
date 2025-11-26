<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\CustomerRegistration;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user())->role === 'platform_admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $glintTenant = $this->ensureGlintTenant();

        // Ensure all platform admins are linked to Glint Labs
        User::where('role', 'platform_admin')
            ->where(function ($q) use ($glintTenant) {
                $q->whereNull('tenant_id')->orWhere('tenant_id', '!=', $glintTenant->id);
            })
            ->update(['tenant_id' => $glintTenant->id]);

        $users = User::withTrashed()->with('tenant')->orderBy('created_at', 'desc')->take(300)->get();
        $membershipMap = $this->loadMemberships($users->pluck('id')->all());

        $items = $users->map(function ($user) use ($membershipMap) {
            return $this->serializeUser($user, $membershipMap);
        });

        $tenantOptions = Tenant::orderBy('name')
            ->where('id', '!=', $glintTenant->id)
            ->get(['id', 'name']);

        return Inertia::render('Hub/Users', [
            'items' => $items,
            'tenants' => $tenantOptions,
            'glintTenantId' => $glintTenant->id,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $glintTenant = $this->ensureGlintTenant();

        $baseRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:platform_admin,owner,manager,cleaner,accountant,customer',
            'tenant_id' => 'nullable|uuid',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $customerRules = [
            'customer.address_line1' => 'required_if:role,customer|string|max:255',
            'customer.address_line2' => 'nullable|string|max:255',
            'customer.city' => 'nullable|string|max:120',
            'customer.postcode' => 'required_if:role,customer|string|max:20',
            'customer.property_type' => 'nullable|string|max:120',
            'customer.storeys' => 'nullable|string|max:50',
            'customer.frequency' => 'nullable|string|max:50',
            'customer.access_notes' => 'nullable|string|max:500',
            'customer.lat' => 'nullable|numeric',
            'customer.lng' => 'nullable|numeric',
            'customer.sms_ok' => 'nullable|boolean',
        ];

        $data = $request->validate(array_merge($baseRules, $customerRules));
        $customerData = $request->input('customer', []);

        $user = User::firstOrNew(['email' => $data['email']]);
        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        $user->role = $data['role'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->must_change_password = true;
        } elseif (!$user->exists && !$user->password) {
            $user->password = bcrypt(Str::random(24));
            $user->must_change_password = false;
        }

        if ($data['role'] === 'platform_admin') {
            $user->tenant_id = $glintTenant->id;
        } else {
            $user->tenant_id = $data['tenant_id'] ?? null;
        }

        $user->save();

        if ($data['role'] === 'platform_admin') {
            if (Schema::hasTable('tenant_user')) {
                TenantUser::where('user_id', $user->id)->delete();
            }
        } elseif (!empty($data['tenant_id']) && Schema::hasTable('tenant_user')) {
            TenantUser::updateOrCreate([
                'tenant_id' => $data['tenant_id'],
                'user_id' => $user->id,
            ], [
                'role' => $data['role'] === 'customer' ? 'customer' : $data['role'],
                'status' => 'active',
            ]);
        }

        if ($data['role'] === 'customer') {
            $this->syncCustomerDetails($user, $customerData, $data['tenant_id'] ?? null);
        }

        $fresh = User::withTrashed()->with('tenant')->findOrFail($user->id);
        return response()->json($this->serializeUser($fresh));
    }

    public function show(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $user = User::withTrashed()->findOrFail($id);

        return response()->json([
            'form' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
                'customer' => $this->customerForm($user),
            ],
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $user = User::withTrashed()->findOrFail($id);
        $glintTenant = $this->ensureGlintTenant();

        $baseRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:platform_admin,owner,manager,cleaner,accountant,customer',
            'tenant_id' => 'nullable|uuid',
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $customerRules = [
            'customer.address_line1' => 'required_if:role,customer|string|max:255',
            'customer.address_line2' => 'nullable|string|max:255',
            'customer.city' => 'nullable|string|max:120',
            'customer.postcode' => 'required_if:role,customer|string|max:20',
            'customer.property_type' => 'nullable|string|max:120',
            'customer.storeys' => 'nullable|string|max:50',
            'customer.frequency' => 'nullable|string|max:50',
            'customer.access_notes' => 'nullable|string|max:500',
            'customer.lat' => 'nullable|numeric',
            'customer.lng' => 'nullable|numeric',
            'customer.sms_ok' => 'nullable|boolean',
        ];

        $data = $request->validate(array_merge($baseRules, $customerRules));
        $customerData = $request->input('customer', []);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->role = $data['role'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->must_change_password = true;
        }

        if ($data['role'] === 'platform_admin') {
            $user->tenant_id = $glintTenant->id;
            if (Schema::hasTable('tenant_user')) {
                TenantUser::where('user_id', $user->id)->delete();
            }
        } else {
            $user->tenant_id = $data['tenant_id'] ?? null;
            if (!empty($data['tenant_id']) && Schema::hasTable('tenant_user')) {
                TenantUser::updateOrCreate([
                    'tenant_id' => $data['tenant_id'],
                    'user_id' => $user->id,
                ], [
                    'role' => $data['role'] === 'customer' ? 'customer' : $data['role'],
                    'status' => 'active',
                ]);
            } elseif (Schema::hasTable('tenant_user')) {
                TenantUser::where('user_id', $user->id)->delete();
            }
        }

        $user->save();

        if ($data['role'] === 'customer') {
            $this->syncCustomerDetails($user, $customerData, $user->tenant_id);
        }

        $fresh = User::withTrashed()->with('tenant')->findOrFail($user->id);
        return response()->json($this->serializeUser($fresh));
    }

    public function deactivate(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $user = User::findOrFail($id);
        $user->delete();
        $user = User::withTrashed()->findOrFail($id);
        return response()->json($this->serializeUser($user->loadMissing('tenant')));
    }

    public function activate(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return response()->json($this->serializeUser($user->fresh()->loadMissing('tenant')));
    }

    public function destroy(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $user = User::withTrashed()->findOrFail($id);
        if (Schema::hasTable('tenant_user')) {
            TenantUser::where('user_id', $user->id)->delete();
        }
        CustomerRegistration::where('user_id', $user->id)->delete();
        Address::where('user_id', $user->id)->delete();
        $user->forceDelete();
        return response()->json(['status' => 'deleted']);
    }

    private function syncCustomerDetails(User $user, array $data, ?string $tenantId = null): void
    {
        $payload = [
            'user_id' => $user->id,
            'address_source' => $data['address_source'] ?? 'manual',
            'place_id' => $data['place_id'] ?? null,
            'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
            'lng' => isset($data['lng']) ? (float) $data['lng'] : null,
            'mapbox_place' => $data['mapbox_place'] ?? null,
            'address_line1' => $data['address_line1'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'] ?? null,
            'postcode' => isset($data['postcode']) ? strtoupper(trim($data['postcode'])) : null,
            'property_type' => $data['property_type'] ?? null,
            'storeys' => $data['storeys'] ?? null,
            'frequency' => $data['frequency'] ?? null,
            'access_notes' => $data['access_notes'] ?? null,
            'sms_ok' => isset($data['sms_ok']) ? (bool) $data['sms_ok'] : null,
        ];

        CustomerRegistration::updateOrCreate(
            ['user_id' => $user->id],
            $payload
        );

        $address = Address::firstOrNew([
            'id' => optional($user->profile)->default_address_id,
        ]);
        $address->tenant_id = $tenantId;
        $address->user_id = $user->id;
        $address->line1 = $payload['address_line1'];
        $address->line2 = $payload['address_line2'];
        $address->city = $payload['city'];
        $address->postcode = $payload['postcode'];
        $address->lat = $payload['lat'];
        $address->lng = $payload['lng'];
        $address->access_notes = $payload['access_notes'];
        $address->save();

        $user->profile()->updateOrCreate([], [
            'default_address_id' => $address->id,
            'marketing_opt_in' => isset($data['sms_ok']) ? (bool) $data['sms_ok'] : false,
        ]);
    }

    private function customerForm(User $user): array
    {
        $defaults = $this->defaultCustomerForm();
        if ($user->role !== 'customer') {
            return $defaults;
        }

        $address = Address::where('user_id', $user->id)->latest('updated_at')->first();
        $registration = CustomerRegistration::where('user_id', $user->id)->latest('created_at')->first();

        $defaults['address_line1'] = $address->line1 ?? $registration->address_line1 ?? '';
        $defaults['address_line2'] = $address->line2 ?? $registration->address_line2 ?? '';
        $defaults['city'] = $address->city ?? $registration->city ?? '';
        $defaults['postcode'] = $address->postcode ?? $registration->postcode ?? '';
        $defaults['property_type'] = $registration->property_type ?? '';
        $defaults['storeys'] = $registration->storeys ?? '';
        $defaults['frequency'] = $registration->frequency ?? '';
        $defaults['access_notes'] = $address->access_notes ?? $registration->access_notes ?? '';
        $defaults['lat'] = $address->lat ?? $registration->lat ?? null;
        $defaults['lng'] = $address->lng ?? $registration->lng ?? null;
        $defaults['sms_ok'] = (bool) ($registration->sms_ok ?? optional($user->profile)->marketing_opt_in ?? false);

        return $defaults;
    }

    private function ensureGlintTenant(): Tenant
    {
        return Tenant::firstOrCreate(
            ['slug' => 'glint-labs'],
            ['name' => 'Glint Labs', 'status' => 'active', 'country' => 'GB']
        );
    }

    private function loadMemberships(array $userIds): array
    {
        if (!Schema::hasTable('tenant_user') || empty($userIds)) {
            return [];
        }

        $rows = DB::table('tenant_user')
            ->join('tenants', 'tenant_user.tenant_id', '=', 'tenants.id')
            ->whereIn('tenant_user.user_id', $userIds)
            ->select('tenant_user.user_id', 'tenant_user.tenant_id', 'tenant_user.role', 'tenants.name')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->user_id][] = [
                'id' => $row->tenant_id,
                'name' => $row->name,
                'role' => $row->role,
            ];
        }
        return $map;
    }

    private function serializeUser(User $user, array $membershipMap = null): array
    {
        $user->loadMissing('tenant');
        $memberships = [];
        if ($membershipMap !== null && array_key_exists($user->id, $membershipMap)) {
            $memberships = $membershipMap[$user->id];
        } else {
            $memberships = $this->loadMemberships([$user->id])[$user->id] ?? [];
        }

        $hasMembershipForTenant = false;
        foreach ($memberships as $m) {
            if (($m['id'] ?? null) === $user->tenant_id) {
                $hasMembershipForTenant = true;
                break;
            }
        }

        if ($user->role === 'platform_admin') {
            $memberships = array_merge([
                [
                    'id' => $user->tenant_id,
                    'name' => 'Glint Labs',
                    'role' => 'platform_admin',
                ],
            ], $memberships);
        } elseif ($user->tenant_id && !$hasMembershipForTenant) {
            $tenantName = $user->tenant?->name ?? Tenant::where('id', $user->tenant_id)->value('name');
            if ($tenantName) {
                $memberships = array_merge($memberships, [[
                    'id' => $user->tenant_id,
                    'name' => $tenantName,
                    'role' => $user->role,
                ]]);
            }
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'tenant_id' => $user->tenant_id,
            'created_at' => optional($user->created_at)->toDateTimeString(),
            'deleted_at' => optional($user->deleted_at)->toDateTimeString(),
            'status' => $user->trashed() ? 'inactive' : 'active',
            'memberships' => $memberships,
        ];
    }
    private function defaultCustomerForm(): array
    {
        return [
            'address_line1' => '',
            'address_line2' => '',
            'city' => '',
            'postcode' => '',
            'property_type' => '',
            'storeys' => '',
            'frequency' => '',
            'access_notes' => '',
            'lat' => null,
            'lng' => null,
            'sms_ok' => false,
            'address_source' => 'manual',
            'place_id' => '',
            'mapbox_place' => '',
        ];
    }
}
