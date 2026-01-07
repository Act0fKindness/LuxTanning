<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Shop;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function __construct(private readonly AuditService $audit)
    {
        $this->middleware('guest');
    }

    public function show(Request $request): Response
    {
        return Inertia::render('Auth/RegisterWizard', [
            'defaults' => [
                'timezone' => 'Europe/London',
                'currency' => 'GBP',
                'kiosk' => [
                    'enable_kiosk' => true,
                    'allow_bed_selection' => false,
                    'require_staff_approval' => false,
                    'require_waiver_daily' => true,
                    'auto_reset_timeout' => 30,
                    'min_minutes' => 1,
                    'max_minutes' => 30,
                ],
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        $user = DB::transaction(function () use ($data) {
            $organisation = Organisation::create($this->organisationPayload($data));
            $shop = Shop::create($this->shopPayload($organisation, $data));

            $user = User::create([
                'tenant_id' => $organisation->getKey(),
                'name' => trim($data['account']['first_name'].' '.$data['account']['last_name']),
                'email' => strtolower($data['account']['email']),
                'password' => Hash::make($data['account']['password']),
                'role' => 'org_owner',
                'status' => 'active',
                'primary_shop_id' => $shop->getKey(),
                'shop_access_mode' => 'single',
            ]);

            $organisation->update([
                'onboarding_step' => 4,
                'onboarding_completed_at' => now(),
            ]);

            $this->audit->log('org.created', [
                'tenant_id' => $organisation->getKey(),
                'entity_type' => 'organisation',
                'entity_id' => $organisation->getKey(),
            ]);
            $this->audit->log('shop.created', [
                'tenant_id' => $organisation->getKey(),
                'entity_type' => 'shop',
                'entity_id' => $shop->getKey(),
            ]);
            $this->audit->log('user.created', [
                'tenant_id' => $organisation->getKey(),
                'entity_type' => 'user',
                'entity_id' => $user->getKey(),
            ]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    public function saveDraft(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'account.first_name' => ['required', 'string', 'min:2'],
            'account.last_name' => ['required', 'string', 'min:2'],
            'account.email' => ['required', 'email'],
            'account.password' => ['required', 'string', 'min:10', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
            'organisation.trading_name' => ['required', 'string', 'max:255'],
            'organisation.support_phone' => ['required', 'string', 'max:30'],
            'organisation.support_email' => ['nullable', 'email'],
            'organisation.timezone' => ['required', 'string'],
            'organisation.currency' => ['required', 'string', 'size:3'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $payload = $this->organisationPayload([
                'account' => $data['account'],
                'organisation' => $data['organisation'],
                'shop' => [
                    'name' => $data['organisation']['trading_name'].' – Main',
                    'address_line1' => 'Update me',
                    'city' => 'TBC',
                    'postcode' => 'TBC',
                    'phone' => $data['organisation']['support_phone'],
                    'opening_hours_preset' => 'standard',
                ],
                'kiosk' => [
                    'enable_kiosk' => true,
                    'allow_bed_selection' => false,
                    'require_staff_approval' => false,
                    'require_waiver_daily' => true,
                    'auto_reset_timeout' => 30,
                    'min_minutes' => 1,
                    'max_minutes' => 30,
                ],
            ]);

            $organisation = Organisation::create(array_merge($payload, [
                'onboarding_step' => 2,
                'onboarding_completed_at' => null,
            ]));

            $user = User::create([
                'tenant_id' => $organisation->getKey(),
                'name' => trim($data['account']['first_name'].' '.$data['account']['last_name']),
                'email' => strtolower($data['account']['email']),
                'password' => Hash::make($data['account']['password']),
                'role' => 'org_owner',
                'status' => 'invited',
            ]);

            $this->audit->log('org.draft_created', [
                'tenant_id' => $organisation->getKey(),
                'entity_id' => $organisation->getKey(),
                'entity_type' => 'organisation',
            ]);

            return $user;
        });

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Draft saved. Check your inbox to finish onboarding.');
    }

    private function rules(): array
    {
        return [
            'account.first_name' => ['required', 'string', 'min:2'],
            'account.last_name' => ['required', 'string', 'min:2'],
            'account.email' => ['required', 'email', 'unique:users,email'],
            'account.password' => ['required', 'string', 'min:10', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
            'account.password_confirmation' => ['required'],
            'organisation.trading_name' => ['required', 'string', 'max:255'],
            'organisation.support_email' => ['nullable', 'email'],
            'organisation.support_phone' => ['required', 'string', 'max:30'],
            'organisation.website_url' => ['nullable', 'url'],
            'organisation.timezone' => ['required', 'string'],
            'organisation.currency' => ['required', 'string', 'size:3'],
            'shop.name' => ['required', 'string', 'max:255'],
            'shop.address_line1' => ['required', 'string', 'max:255'],
            'shop.city' => ['required', 'string', 'max:255'],
            'shop.postcode' => ['required', 'string', 'max:20'],
            'shop.phone' => ['nullable', 'string', 'max:30'],
            'shop.opening_hours_preset' => ['required', 'string', 'in:standard,weekdays,custom'],
            'shop.opening_hours' => ['nullable', 'array'],
            'kiosk.enable_kiosk' => ['required', 'boolean'],
            'kiosk.allow_bed_selection' => ['required', 'boolean'],
            'kiosk.require_staff_approval' => ['required', 'boolean'],
            'kiosk.require_waiver_daily' => ['required', 'boolean'],
            'kiosk.auto_reset_timeout' => ['required', 'integer', 'min:10', 'max:120'],
            'kiosk.min_minutes' => ['required', 'integer', 'min:1', 'max:30'],
            'kiosk.max_minutes' => ['required', 'integer', 'min:1', 'max:30'],
        ];
    }

    private function organisationPayload(array $data): array
    {
        $accountEmail = strtolower($data['account']['email']);
        $supportEmail = $data['organisation']['support_email'] ?? $accountEmail;
        $supportPhone = $data['organisation']['support_phone'];
        $brand = [
            'kiosk_defaults' => Arr::only($data['kiosk'], [
                'enable_kiosk',
                'allow_bed_selection',
                'require_staff_approval',
                'require_waiver_daily',
                'auto_reset_timeout',
                'min_minutes',
                'max_minutes',
            ]),
        ];

        return [
            'name' => $data['organisation']['trading_name'],
            'slug' => $this->generateSlug($data['organisation']['trading_name']),
            'trading_name' => $data['organisation']['trading_name'],
            'support_email' => $supportEmail,
            'support_phone' => $supportPhone,
            'contact_email' => $accountEmail,
            'contact_phone' => $supportPhone,
            'website_url' => $data['organisation']['website_url'] ?? null,
            'default_timezone' => $data['organisation']['timezone'],
            'default_currency' => strtoupper($data['organisation']['currency']),
            'default_language' => 'en-GB',
            'brand_json' => $brand,
            'plan_key' => 'trial',
            'plan_status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'status' => 'active',
        ];
    }

    private function shopPayload(Organisation $organisation, array $data): array
    {
        return [
            'tenant_id' => $organisation->getKey(),
            'name' => $data['shop']['name'],
            'phone' => $data['shop']['phone'] ?? $organisation->support_phone,
            'email' => $organisation->support_email,
            'address_line1' => $data['shop']['address_line1'],
            'address_line2' => $data['shop']['address_line2'] ?? null,
            'city' => $data['shop']['city'],
            'county' => $data['shop']['county'] ?? null,
            'postcode' => $data['shop']['postcode'],
            'country_code' => 'GB',
            'timezone' => $organisation->default_timezone,
            'opening_hours_json' => $this->determineOpeningHours($data['shop']),
        ];
    }

    private function determineOpeningHours(array $shop): array
    {
        $preset = $shop['opening_hours_preset'];
        if ($preset === 'custom' && ! empty($shop['opening_hours'])) {
            return $shop['opening_hours'];
        }

        $standard = [
            'mon' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'tue' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'wed' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'thu' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'fri' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
            'sat' => ['open' => '10:00', 'close' => '16:00', 'closed' => false],
            'sun' => ['open' => '10:00', 'close' => '14:00', 'closed' => false],
        ];

        if ($preset === 'weekdays') {
            $standard['sat']['closed'] = true;
            $standard['sun']['closed'] = true;
        }

        return $standard;
    }

    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug ?: 'luma-org';
        $counter = 1;
        while (Organisation::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }
}
