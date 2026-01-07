<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Shop;
use App\Models\TenantUser;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserShop;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class CustomerSignupController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function create(Request $request): Response
    {
        $companies = Organisation::query()
            ->select(['id', 'name', 'trading_name', 'status'])
            ->where('status', 'active')
            ->with(['shops' => function ($query) {
                $query->select(['id', 'tenant_id', 'name', 'city', 'postcode'])->orderBy('name');
            }])
            ->orderByRaw('COALESCE(trading_name, name) asc')
            ->get()
            ->map(function (Organisation $organisation) {
                return [
                    'id' => $organisation->id,
                    'name' => $organisation->trading_name ?: $organisation->name,
                    'shops' => $organisation->shops->map(function (Shop $shop) {
                        return [
                            'id' => $shop->id,
                            'name' => $shop->name,
                            'city' => $shop->city,
                            'postcode' => $shop->postcode,
                        ];
                    })->values(),
                ];
            })
            ->values();

        $selectedCompany = $request->string('company')->value();
        $selectedShop = $request->string('shop')->value();

        if ($selectedShop && !$companies->flatMap(fn ($company) => $company['shops'])->firstWhere('id', $selectedShop)) {
            $selectedShop = null;
        }

        if ($selectedCompany && !$companies->firstWhere('id', $selectedCompany)) {
            $selectedCompany = null;
        }

        if (!$selectedCompany && $selectedShop) {
            $match = $companies->first(function ($company) use ($selectedShop) {
                return collect($company['shops'])->firstWhere('id', $selectedShop);
            });
            $selectedCompany = $match['id'] ?? null;
        }

        if (!$selectedCompany && $companies->isNotEmpty()) {
            $selectedCompany = $companies->first()['id'];
        }

        return Inertia::render('Auth/CustomerSignup', [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompany,
            'selectedShopId' => $selectedShop,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:10', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
            'shop_id' => ['required', 'uuid', 'exists:shops,id'],
            'marketing_opt_in' => ['sometimes', 'boolean'],
        ]);

        $shop = Shop::query()->where('id', $data['shop_id'])->firstOrFail();

        $user = DB::transaction(function () use ($data, $shop) {
            $user = User::create([
                'tenant_id' => $shop->tenant_id,
                'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                'email' => strtolower($data['email']),
                'phone' => $data['phone'] ?? null,
                'role' => 'customer',
                'status' => 'active',
                'password' => Hash::make($data['password']),
                'primary_shop_id' => $shop->id,
                'shop_access_mode' => 'single',
            ]);

            TenantUser::firstOrCreate(
                ['tenant_id' => $shop->tenant_id, 'user_id' => $user->id],
                ['role' => 'customer', 'status' => 'active']
            );

            UserShop::firstOrCreate([
                'user_id' => $user->id,
                'shop_id' => $shop->id,
            ]);

            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                ['marketing_opt_in' => (bool) ($data['marketing_opt_in'] ?? false)]
            );

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
