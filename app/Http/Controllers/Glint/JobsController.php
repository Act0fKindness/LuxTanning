<?php

namespace App\Http\Controllers\Glint;

use App\Models\Address;
use App\Models\Job;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CustomerProfileService;
use App\Support\QuoteCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class JobsController extends CompaniesController
{
    public function __construct(protected CustomerProfileService $customerProfiles)
    {
    }

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        $tenants = Tenant::orderBy('name')->get(['id', 'name', 'slug', 'status']);
        $selectedId = $request->query('tenant');
        if ($selectedId && !$tenants->firstWhere('id', $selectedId)) {
            $selectedId = null;
        }
        if (!$selectedId && $tenants->isNotEmpty()) {
            $selectedId = $tenants->first()->id;
        }

        $selectedTenant = $selectedId ? $tenants->firstWhere('id', $selectedId) : null;
        $jobs = ['upcoming' => [], 'recent' => []];
        $customers = [];
        $jobLeads = [];
        $staff = [];
        if ($selectedTenant) {
            $jobSummary = $this->loadJobsSummary($selectedTenant->id, 80);
            $jobs = [
                'upcoming' => $jobSummary['upcoming'] ?? [],
                'recent' => $jobSummary['recent'] ?? [],
            ];
            $customerRows = $this->loadCustomers($selectedTenant->id, $jobSummary['customerMap'] ?? [], true);
            $customers = array_values(array_filter($customerRows, function ($row) {
                return empty($row['is_lead']);
            }));
            $jobLeads = array_values(array_filter($customerRows, function ($row) {
                return !empty($row['is_lead']);
            }));
            $staff = $this->loadStaff($selectedTenant->id, $this->staffRolesList);
        }

        return Inertia::render('Glint/Jobs', [
            'tenants' => $tenants->map(fn (Tenant $tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'status' => ucfirst($tenant->status ?? 'active'),
            ]),
            'selectedTenant' => $selectedTenant ? [
                'id' => $selectedTenant->id,
                'name' => $selectedTenant->name,
                'slug' => $selectedTenant->slug,
                'status' => ucfirst($selectedTenant->status ?? 'active'),
            ] : null,
            'jobs' => $jobs,
            'customers' => $customers,
            'jobLeads' => $jobLeads,
            'staff' => $staff,
            'filters' => [
                'tenant' => $selectedId,
            ],
        ]);
    }

    public function store(Request $request, Tenant $tenant)
    {
        $this->ensureAdmin($request);

        if (!$request->input('customer_name') && !$request->input('customer_id')) {
            $fallbackName = $request->input('address_line1') ?: ($request->input('city') ?: 'Customer');
            $request->merge(['customer_name' => $fallbackName]);
        }

        $data = $request->validate([
            'customer_id' => ['nullable', 'uuid'],
            'customer_name' => ['required_without:customer_id', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'address_id' => ['nullable', 'uuid'],
            'address_line1' => ['required_without:address_id', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'postcode' => ['required_without:address_id', 'string', 'max:20'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'storeys' => ['required', 'integer', 'min:1', 'max:3'],
            'windows' => ['required', 'integer', 'min:0', 'max:400'],
            'frames' => ['nullable', 'boolean'],
            'sills' => ['nullable', 'boolean'],
            'gutters' => ['nullable', 'boolean'],
            'frequency' => ['required', 'in:one_off,four_week,six_week,eight_week'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'staff_user_id' => ['required', 'uuid'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = $this->resolveCustomer($tenant->id, $data);
        $address = $this->resolveAddress($tenant->id, $customer, $data);
        $staff = $this->resolveCleaner($tenant->id, $data['staff_user_id']);

        if (!$staff) {
            return redirect()->back()->with('error', 'Cleaner not found for this tenant.');
        }

        $quote = QuoteCalculator::windowCleaning(
            (int) $data['windows'],
            (int) $data['storeys'],
            (string) $data['frequency'],
            (bool) ($data['frames'] ?? false),
            (bool) ($data['sills'] ?? false),
            (bool) ($data['gutters'] ?? false),
        );

        $start = Carbon::parse($data['date'] . ' ' . $data['start_time']);
        $end = (clone $start)->addMinutes($quote['estimate_minutes']);
        $etaWindow = $start->format('H:i') . '-' . $end->format('H:i');

        Job::create([
            'tenant_id' => $tenant->id,
            'staff_user_id' => $staff->id,
            'date' => Carbon::parse($data['date']),
            'eta_window' => $etaWindow,
            'status' => 'scheduled',
            'checklist_json' => [
                'customer_id' => $customer->id,
                'address_id' => $address->id,
                'address_line1' => $address->line1,
                'address_line2' => $address->line2,
                'city' => $address->city,
                'postcode' => $address->postcode,
                'lat' => $address->lat,
                'lng' => $address->lng,
                'storeys' => (int) $data['storeys'],
                'windows' => (int) $data['windows'],
                'frames' => (bool) ($data['frames'] ?? false),
                'sills' => (bool) ($data['sills'] ?? false),
                'gutters' => (bool) ($data['gutters'] ?? false),
                'frequency' => (string) $data['frequency'],
                'price_pence' => $quote['total_pence'],
                'deposit_pence' => $quote['deposit_pence'],
                'estimate_minutes' => $quote['estimate_minutes'],
                'notes' => $data['notes'] ?? null,
            ],
        ]);

        return redirect()->back()->with('success', 'Job created.');
    }

    private function resolveCustomer(string $tenantId, array $data): User
    {
        $payload = [
            'name' => $data['customer_name'] ?? null,
            'email' => $data['customer_email'] ?? null,
            'phone' => $data['customer_phone'] ?? null,
            'address_line1' => $data['address_line1'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'] ?? null,
            'postcode' => $data['postcode'] ?? null,
        ];

        if (!empty($data['customer_id'])) {
            $customer = User::where('id', $data['customer_id'])->firstOrFail();
            if ($customer->tenant_id && $customer->tenant_id !== $tenantId) {
                abort(422, 'Customer belongs to another tenant.');
            }
            $customer->tenant_id = $customer->tenant_id ?: $tenantId;
            if (!empty($payload['name']) && !$customer->name) {
                $customer->name = $payload['name'];
            }
            if (!empty($payload['phone'])) {
                $customer->phone = $payload['phone'];
            }
            $customer->save();
            $this->customerProfiles->ensureMembership($tenantId, $customer);
            return $customer;
        }

        return $this->customerProfiles->findOrCreateCustomer($tenantId, $payload);
    }

    private function resolveAddress(string $tenantId, User $customer, array $data): Address
    {
        $address = null;
        $column = $this->addressUserColumn();
        if ($column && !empty($data['address_id'])) {
            $address = Address::where('id', $data['address_id'])
                ->where('tenant_id', $tenantId)
                ->first();
        }

        if ($address) {
            return $address;
        }

        $attributes = [
            'tenant_id' => $tenantId,
            'line1' => $data['address_line1'] ?? 'Address on file',
            'line2' => $data['address_line2'] ?? null,
            'city' => $data['city'] ?? null,
            'postcode' => strtoupper(trim($data['postcode'] ?? '')),
            'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
            'lng' => isset($data['lng']) ? (float) $data['lng'] : null,
        ];

        if ($column) {
            $attributes[$column] = $customer->id;
        }

        $address = Address::create($attributes);
        $customer->profile()->updateOrCreate([], ['default_address_id' => $address->id]);

        return $address;
    }

    private function resolveCleaner(string $tenantId, string $userId): ?User
    {
        if (Schema::hasTable('tenant_user')) {
            $isCleaner = DB::table('tenant_user')
                ->where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('role', 'cleaner')
                ->exists();
            if (!$isCleaner) {
                return null;
            }
        }

        $query = User::where('id', $userId)
            ->where('role', 'cleaner');

        if (Schema::hasColumn('users', 'tenant_id')) {
            $query->where(function ($builder) use ($tenantId) {
                $builder->whereNull('tenant_id')
                    ->orWhere('tenant_id', $tenantId);
            });
        }

        return $query->first();
    }
}
