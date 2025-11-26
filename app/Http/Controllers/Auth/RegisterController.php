<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Address;
use App\Models\UserProfile;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomerRegistration;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['owner','manager','cleaner','accountant','customer'])],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'service_area_label' => ['nullable', 'string', 'max:255'],
            'service_area_place_id' => ['nullable', 'string', 'max:255'],
            'service_area_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'service_area_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'service_area_radius_km' => ['nullable', 'numeric', 'min:0.1', 'max:500'],
        ]);

        $validator->sometimes('tenant_id', ['required'], function ($input) {
            return $input->role && $input->role !== 'owner' && $input->role !== 'customer';
        });

        $validator->sometimes('company_name', ['required'], function ($input) {
            return $input->role === 'owner';
        });

        $validator->sometimes(['service_area_label', 'service_area_lat', 'service_area_lng', 'service_area_radius_km'], ['required'], function ($input) {
            return $input->role === 'owner';
        });

        return $validator;
    }

    public function showRegistrationForm()
    {
        $tenants = Tenant::orderBy('name')->get(['id','name']);
        return view('auth.register', ['tenants' => $tenants]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $role = $data['role'] ?? 'customer';
        $tenantId = $data['tenant_id'] ?? null;

        if ($role === 'owner') {
            $companyName = trim($data['company_name'] ?? '');
            $slugBase = Str::slug($companyName) ?: 'tenant';
            $slug = $slugBase;
            $counter = 1;
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = $slugBase.'-'.$counter++;
            }
            $tenant = Tenant::create([
                'name' => $companyName,
                'slug' => $slug,
                'country' => 'GB',
                'status' => 'active',
                'vat_scheme' => 'standard',
                'service_area_label' => $data['service_area_label'] ?? ($data['mapbox_place'] ?? null),
                'service_area_place_id' => $data['service_area_place_id'] ?? null,
                'service_area_center_lat' => isset($data['service_area_lat']) ? (float) $data['service_area_lat'] : null,
                'service_area_center_lng' => isset($data['service_area_lng']) ? (float) $data['service_area_lng'] : null,
                'service_area_radius_km' => isset($data['service_area_radius_km']) ? (float) $data['service_area_radius_km'] : null,
            ]);
            $tenantId = $tenant->id;
        }

        $user = User::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'password' => Hash::make($data['password']),
        ]);

        if ($role !== 'customer' && $tenantId) {
            TenantUser::create([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'role' => $role,
                'status' => 'active',
            ]);
            return $user;
        }
        // Persist extended registration details (non-blocking)
        try {
            // Persist extended registration details (for analytics & later enrichment)
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
                'sms_ok' => isset($data['sms_ok']) ? (bool) $data['sms_ok'] : null,
            ]);
            // Create address + set as default on profile when present
            if (!empty($data['address_line1']) || !empty($data['mapbox_place']) || !empty($data['postcode'])) {
                $addr = Address::create([
                    'tenant_id' => $user->tenant_id, // null-safe; can be filled later when tenant context is known
                    'user_id' => $user->id,
                    'line1' => $data['address_line1'] ?? ($data['mapbox_place'] ?? null),
                    'line2' => $data['address_line2'] ?? null,
                    'city' => $data['city'] ?? null,
                    'postcode' => $data['postcode'] ?? null,
                    'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
                    'lng' => isset($data['lng']) ? (float) $data['lng'] : null,
                    'access_notes' => $data['access_notes'] ?? null,
                ]);
                $user->profile()->updateOrCreate([], [
                    'default_address_id' => $addr->id,
                    'marketing_opt_in' => isset($data['sms_ok']) ? (bool) $data['sms_ok'] : false,
                ]);
            }
        } catch (\Throwable $e) {
            // swallow to avoid blocking registration on optional extras
        }
        return $user;
    }
}
