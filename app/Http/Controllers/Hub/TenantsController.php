<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\Address;
use App\Models\UserProfile;
use App\Models\CustomerRegistration;

class TenantsController extends Controller
{
    private function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user())->role === 'platform_admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);
        $tenants = Tenant::orderBy('name')->get(['id','name','slug','domain','status','created_at']);

        // Counts per tenant (guard if pivot not migrated yet)
        $byTenant = [];
        if (Schema::hasTable('tenant_user')) {
            $roles = DB::table('tenant_user')
                ->select('tenant_id', 'role', DB::raw('count(*) as c'))
                ->groupBy('tenant_id','role')->get();
            foreach ($roles as $r) {
                $byTenant[$r->tenant_id][$r->role] = (int)$r->c;
            }
        }
        $items = $tenants->map(function ($t) use ($byTenant) {
            // Resolve counts from pivot if present, else fallback to users.tenant_id
            $counts = $byTenant[$t->id] ?? [];
            if (empty($counts)) {
                $staff = User::where('tenant_id', $t->id)->whereIn('role', ['owner','manager','cleaner','accountant'])->count();
                $customers = User::where('tenant_id', $t->id)->where('role','customer')->count();
            } else {
                $staff = ($counts['owner'] ?? 0) + ($counts['manager'] ?? 0) + ($counts['cleaner'] ?? 0) + ($counts['accountant'] ?? 0);
                $customers = $counts['customer'] ?? 0;
            }
            return [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
                'domain' => $t->domain,
                'status' => $t->status,
                'staff_count' => $staff,
                'customer_count' => $customers,
                'created_at' => optional($t->created_at)->toDateTimeString(),
            ];
        });

        return Inertia::render('Hub/Tenants', [ 'items' => $items ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'country' => 'nullable|string|size:2',
            'owner_email' => 'nullable|email|max:255',
            'owner_name' => 'nullable|string|max:255',
        ]);
        $slug = Str::slug($data['name']);
        $tenant = Tenant::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $data['name'],
                'domain' => $data['domain'] ?? null,
                'country' => $data['country'] ?? 'GB',
                'status' => 'active',
            ]
        );
        // Optionally seed an owner
        if (!empty($data['owner_email'])) {
            $user = User::firstOrNew(['email' => $data['owner_email']]);
            if (!$user->exists) {
                $user->name = $data['owner_name'] ?? $data['owner_email'];
                $user->role = 'owner';
                $user->password = bcrypt(Str::random(24));
                $user->save();
            }
            TenantUser::firstOrCreate([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ], [ 'role' => 'owner', 'status' => 'active' ]);
        }
        return response()->json(['ok' => true, 'id' => $tenant->id]);
    }

    public function show(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $tenant = Tenant::findOrFail($id);
        // Staff via membership pivot (guard if pivot not present)
        $staff = collect();
        if (Schema::hasTable('tenant_user')) {
            $staffIds = DB::table('tenant_user')->where('tenant_id',$tenant->id)
                ->whereIn('role', ['owner','manager','cleaner','accountant'])->pluck('user_id');
            if ($staffIds->count()) {
                $staff = User::whereIn('id', $staffIds)->orderBy('name')->get(['id','name','email','phone','role']);
            }
        }
        // Fallback or supplement with users.tenant_id
        if ($staff->isEmpty()) {
            $staff = User::where('tenant_id', $tenant->id)->whereIn('role', ['owner','manager','cleaner','accountant'])->orderBy('name')->get(['id','name','email','phone','role']);
        }
        $staff = $staff->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'role' => $u->role,
        ]);
        // Customers via membership pivot
        $customers = collect();
        if (Schema::hasTable('tenant_user')) {
            $customerIds = DB::table('tenant_user')->where('tenant_id',$tenant->id)->where('role','customer')->orderByDesc('created_at')->limit(300)->pluck('user_id');
            if ($customerIds->count()) {
                $customers = User::whereIn('id', $customerIds)->get(['id','name','email','phone','created_at']);
            }
        }
        if ($customers->isEmpty()) {
            $customers = User::where('tenant_id', $tenant->id)->where('role','customer')->orderByDesc('created_at')->limit(300)->get(['id','name','email','phone','created_at']);
        }
        $customers = $customers->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'created_at' => $u->created_at?->toDateTimeString(),
        ]);
        return Inertia::render('Hub/Tenant', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'domain' => $tenant->domain,
                'status' => $tenant->status,
            ],
            'staff' => $staff,
            'customers' => $customers,
        ]);
    }

    public function addStaff(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $tenant = Tenant::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:owner,manager,cleaner,accountant',
        ]);
        $user = User::firstOrNew(['email' => $data['email']]);
        if (!$user->exists) {
            $user->name = $data['name'];
            $user->phone = $data['phone'] ?? null;
            $user->role = $data['role'];
            $user->password = bcrypt(Str::random(24));
            $user->save();
        }
        if (Schema::hasTable('tenant_user')) {
            TenantUser::updateOrCreate([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ], [ 'role' => $data['role'], 'status' => 'active' ]);
        } else {
            // Fallback for pre-migration environments
            $user->tenant_id = $tenant->id; $user->save();
        }
        return response()->json(['ok' => true, 'id' => $user->id]);
    }

    public function addCustomer(Request $request, string $id)
    {
        $this->ensureAdmin($request);
        $tenant = Tenant::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            // Address + property details (mirrors /customer/register)
            'address_source' => 'nullable|string|max:50',
            'place_id' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'mapbox_place' => 'nullable|string|max:1024',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'property_type' => 'nullable|string|max:50',
            'storeys' => 'nullable|string|max:20',
            'frequency' => 'nullable|string|max:50',
            'access_notes' => 'nullable|string',
            'sms_ok' => 'nullable|boolean',
        ]);
        $user = null;
        if (!empty($data['email'])) { $user = User::firstOrNew(['email' => $data['email']]); }
        else { $user = new User(); }
        if (!$user->exists) {
            $user->name = $data['name'];
            if (!empty($data['email'])) $user->email = $data['email'];
            $user->phone = $data['phone'] ?? null;
            $user->role = 'customer';
            $user->password = bcrypt(Str::random(24));
            $user->save();
        }
        if (Schema::hasTable('tenant_user')) {
            TenantUser::firstOrCreate([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ], [ 'role' => 'customer', 'status' => 'active' ]);
        } else {
            $user->tenant_id = $tenant->id; $user->save();
        }

        // Create address if provided
        if (!empty($data['address_line1']) || !empty($data['mapbox_place']) || !empty($data['postcode'])) {
            $addrData = [
                'tenant_id' => $tenant->id,
                'line1' => $data['address_line1'] ?? ($data['mapbox_place'] ?? null),
                'line2' => $data['address_line2'] ?? null,
                'city' => $data['city'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
                'lng' => isset($data['lng']) ? (float) $data['lng'] : null,
                'access_notes' => $data['access_notes'] ?? null,
            ];
            if (Schema::hasColumn('addresses', 'user_id')) {
                $addrData['user_id'] = $user->id;
            } elseif (Schema::hasColumn('addresses', 'customer_id')) {
                $addrData['customer_id'] = $user->id;
            }
            $addr = Address::create($addrData);
            $user->profile()->updateOrCreate([], [
                'default_address_id' => $addr->id,
                'marketing_opt_in' => isset($data['sms_ok']) ? (bool)$data['sms_ok'] : false,
            ]);
        }
        // Persist extended registration data for analytics/future use
        try {
            CustomerRegistration::create([
                'user_id' => $user->id,
                'address_source' => $data['address_source'] ?? null,
                'place_id' => $data['place_id'] ?? null,
                'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
                'lng' => isset($data['lng']) ? (float) $data['lng'] : null,
                'mapbox_place' => $data['mapbox_place'] ?? null,
                'address_line1' => $data['address_line1'] ?? null,
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'property_type' => $data['property_type'] ?? null,
                'storeys' => $data['storeys'] ?? null,
                'frequency' => $data['frequency'] ?? null,
                'access_notes' => $data['access_notes'] ?? null,
                'sms_ok' => isset($data['sms_ok']) ? (bool)$data['sms_ok'] : null,
            ]);
        } catch (\Throwable $e) {}
        return response()->json(['ok' => true, 'id' => $user->id]);
    }
}
