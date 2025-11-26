<?php

namespace App\Http\Controllers\Glint;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Job;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\JobPresenter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CompaniesController extends Controller
{
    protected array $staffRolesList = ['owner', 'manager', 'cleaner', 'accountant', 'platform_admin'];

    public function index(Request $request): Response
    {
        $this->ensureAdmin($request);

        $staffRoles = $this->staffRolesList;
        $staffCounts = collect();
        $customerCounts = collect();
        if (Schema::hasTable('tenant_user')) {
            $staffCounts = DB::table('tenant_user')
                ->join('users', 'users.id', '=', 'tenant_user.user_id')
                ->whereIn('users.role', $staffRoles)
                ->select('tenant_user.tenant_id', DB::raw('COUNT(*) as total'))
                ->groupBy('tenant_user.tenant_id')
                ->pluck('total', 'tenant_user.tenant_id');

            $customerCounts = DB::table('tenant_user')
                ->join('users', 'users.id', '=', 'tenant_user.user_id')
                ->where('users.role', 'customer')
                ->select('tenant_user.tenant_id', DB::raw('COUNT(*) as total'))
                ->groupBy('tenant_user.tenant_id')
                ->pluck('total', 'tenant_user.tenant_id');
        }

        $jobsPerTenant = $this->aggregateJobStats();

        $tenants = Tenant::orderBy('name')
            ->get(['id', 'name', 'slug', 'domain', 'status', 'fee_tier', 'country', 'created_at']);

        $companies = $tenants->map(function (Tenant $tenant) use ($staffCounts, $customerCounts, $jobsPerTenant, $staffRoles) {
            $staff = $staffCounts[$tenant->id] ?? User::where('tenant_id', $tenant->id)
                ->whereIn('role', $staffRoles)
                ->count();
            $customers = $customerCounts[$tenant->id] ?? User::where('tenant_id', $tenant->id)
                ->where('role', 'customer')
                ->count();
            $jobCustomers = $this->loadJobCustomers($tenant->id, [], false);
            $jobsRow = $jobsPerTenant[$tenant->id] ?? ['jobs' => 0, 'customers' => 0];
            $customers += $jobCustomers;
            $jobsThisWeek = (int) ($jobsRow['jobs'] ?? 0);

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'plan' => $tenant->fee_tier ? ucfirst($tenant->fee_tier) : 'Custom',
                'domain' => $tenant->domain,
                'status' => ucfirst($tenant->status ?? 'active'),
                'staff_count' => $staff,
                'customer_count' => $customers,
                'jobs_this_week' => $jobsThisWeek,
                'created_at' => optional($tenant->created_at)->toDateString(),
            ];
        })->all();

        $selectedId = $request->query('company');
        if ($selectedId && !collect($companies)->firstWhere('id', $selectedId)) {
            $selectedId = null;
        }
        if (!$selectedId && !empty($companies)) {
            $selectedId = $companies[0]['id'];
        }

        $selectedTenant = $selectedId ? $tenants->firstWhere('id', $selectedId) : null;
        $jobsSummary = ['upcoming' => [], 'recent' => [], 'customerMap' => []];
        $staff = [];
        $customers = [];
        if ($selectedTenant) {
            $jobsSummary = $this->loadJobsSummary($selectedTenant->id);
            $staff = $this->loadStaff($selectedTenant->id, $staffRoles);
            $customers = $this->loadCustomers($selectedTenant->id, $jobsSummary['customerMap'] ?? []);
        }

        if ($selectedTenant && !empty($companies)) {
            $selectedCustomerCount = count($customers);
            foreach ($companies as &$company) {
                if ($company['id'] === $selectedTenant->id) {
                    $company['customer_count'] = max($company['customer_count'], $selectedCustomerCount);
                    break;
                }
            }
            unset($company);
        }

        $stats = [
            'companies' => count($companies),
            'staff' => array_sum(array_column($companies, 'staff_count')),
            'customers' => array_sum(array_column($companies, 'customer_count')),
        ];

        return Inertia::render('Glint/Companies', [
            'companies' => $companies,
            'selected' => $selectedTenant ? [
                'id' => $selectedTenant->id,
                'name' => $selectedTenant->name,
                'plan' => $selectedTenant->fee_tier ? ucfirst($selectedTenant->fee_tier) : 'Custom',
                'domain' => $selectedTenant->domain,
                'status' => ucfirst($selectedTenant->status ?? 'active'),
                'country' => $selectedTenant->country,
                'created_at' => optional($selectedTenant->created_at)->toDateString(),
                'service_area' => [
                    'label' => $selectedTenant->service_area_label,
                    'place_id' => $selectedTenant->service_area_place_id,
                    'lat' => $selectedTenant->service_area_center_lat,
                    'lng' => $selectedTenant->service_area_center_lng,
                    'radius_km' => $selectedTenant->service_area_radius_km,
                ],
            ] : null,
            'staff' => $staff,
            'customers' => $customers,
            'jobs' => [
                'upcoming' => $jobsSummary['upcoming'] ?? [],
                'recent' => $jobsSummary['recent'] ?? [],
            ],
            'stats' => $stats,
            'filters' => ['company' => $selectedId],
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', 'unique:tenants,slug'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'country' => ['nullable', 'string', 'size:2'],
            'status' => ['nullable', 'in:active,paused,disabled'],
            'fee_tier' => ['nullable', 'string', 'max:50'],
            'vat_scheme' => ['nullable', 'in:standard,flat_rate,none'],
            'service_area_label' => ['required', 'string', 'max:255'],
            'service_area_place_id' => ['nullable', 'string', 'max:255'],
            'service_area_lat' => ['required', 'numeric', 'between:-90,90'],
            'service_area_lng' => ['required', 'numeric', 'between:-180,180'],
            'service_area_radius_km' => ['required', 'numeric', 'min:0.5', 'max:500'],
        ]);

        $name = trim($data['name']);
        $slugInput = $data['slug'] ?? Str::slug($name);
        $slugBase = Str::slug($slugInput ?: 'tenant');
        $slug = $this->uniqueSlug($slugBase ?: 'tenant');

        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'domain' => $data['domain'] ?? null,
            'country' => strtoupper($data['country'] ?? 'GB'),
            'status' => $data['status'] ?? 'active',
            'fee_tier' => $data['fee_tier'] ?? null,
            'vat_scheme' => $data['vat_scheme'] ?? 'standard',
            'service_area_label' => $data['service_area_label'] ?? null,
            'service_area_place_id' => $data['service_area_place_id'] ?? null,
            'service_area_center_lat' => isset($data['service_area_lat']) ? (float) $data['service_area_lat'] : null,
            'service_area_center_lng' => isset($data['service_area_lng']) ? (float) $data['service_area_lng'] : null,
            'service_area_radius_km' => isset($data['service_area_radius_km']) ? (float) $data['service_area_radius_km'] : null,
        ]);

        return redirect()->route('glint.companies', ['company' => $tenant->id])
            ->with('success', 'Company created.');
    }

    public function storeStaff(Request $request, Tenant $tenant)
    {
        $this->ensureAdmin($request);

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
        } else {
            $user->name = $data['name'];
            $user->phone = $data['phone'] ?? $user->phone;
            if ($user->role !== 'platform_admin') {
                $user->role = $data['role'];
            }
            $user->save();
        }

        TenantUser::updateOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ], [
            'role' => $data['role'],
            'status' => 'active',
        ]);

        return redirect()->route('glint.companies', ['company' => $tenant->id])
            ->with('success', 'Team member added.');
    }

    public function storeCustomer(Request $request, Tenant $tenant)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'property_type' => 'nullable|string|max:100',
            'storeys' => 'nullable|string|max:50',
            'frequency' => 'nullable|string|max:50',
            'access_notes' => 'nullable|string|max:500',
            'sms_opt_in' => 'nullable|boolean',
        ]);

        $data['sms_opt_in'] = filter_var($data['sms_opt_in'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $user = null;
        if (!empty($data['email'])) {
            $user = User::firstOrNew(['email' => $data['email']]);
        } else {
            $user = new User();
        }

        if (!$user->exists) {
            $user->name = $data['name'];
            if (!empty($data['email'])) {
                $user->email = $data['email'];
            }
            $user->phone = $data['phone'] ?? null;
            $user->role = 'customer';
            $user->password = bcrypt(Str::random(24));
            $user->save();
        } else {
            $user->name = $data['name'];
            $user->phone = $data['phone'] ?? $user->phone;
            $user->save();
        }

        TenantUser::firstOrCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
        ], [
            'role' => 'customer',
            'status' => 'active',
        ]);

        if (!empty($data['address_line1']) || !empty($data['postcode'])) {
            $propertyMeta = array_filter([
                $data['property_type'] ? 'Type: ' . $data['property_type'] : null,
                $data['storeys'] ? 'Storeys: ' . $data['storeys'] : null,
                $data['frequency'] ? 'Frequency: ' . $data['frequency'] : null,
            ]);
            $accessNotes = trim($data['access_notes'] ?? '');
            if (!empty($propertyMeta)) {
                $metaString = implode(' · ', $propertyMeta);
                $accessNotes = trim($accessNotes ? $accessNotes . "\n" . $metaString : $metaString);
            }

            $addressAttributes = [
                'tenant_id' => $tenant->id,
                'line1' => $data['address_line1'] ?? 'Address on file',
                'line2' => $data['address_line2'] ?? null,
                'city' => $data['city'] ?? null,
                'postcode' => $data['postcode'] ?? null,
                'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
                'lng' => isset($data['lng']) ? (float) $data['lng'] : null,
                'access_notes' => $accessNotes ?: null,
            ];

            if ($addressUserColumn = $this->addressUserColumn()) {
                $addressAttributes[$addressUserColumn] = $user->id;
            }

            Address::create($addressAttributes);
        }

        UserProfile::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'marketing_opt_in' => (bool) ($data['sms_opt_in'] ?? false),
        ]);

        return redirect()->route('glint.companies', ['company' => $tenant->id])
            ->with('success', 'Customer added.');
    }

    protected function loadJobsSummary(string $tenantId, int $limit = 40): array
    {
        if (!Schema::hasTable('jobs')) {
            return ['upcoming' => [], 'recent' => [], 'customerMap' => []];
        }

        $today = Carbon::today();
        $baseQuery = Job::with('staff:id,name')
            ->where('tenant_id', $tenantId);

        $upcomingRecords = (clone $baseQuery)
            ->whereDate('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('eta_window')
            ->limit($limit)
            ->get();

        $recentRecords = (clone $baseQuery)
            ->whereDate('date', '<', $today)
            ->orderByDesc('date')
            ->orderByDesc('eta_window')
            ->limit($limit)
            ->get();

        $customerIds = $upcomingRecords
            ->merge($recentRecords)
            ->map(function (Job $job) {
                $checklist = $job->checklist_json ?? [];
                return $checklist['customer_id'] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $customerMap = $customerIds->isEmpty()
            ? collect()
            : User::whereIn('id', $customerIds)->get(['id', 'name', 'email', 'phone'])->keyBy('id');

        $customerJobs = [];

        $formatJob = function (Job $job, string $bucket) use (&$customerJobs, $customerMap) {
            $summary = $this->presentJobSummary($job, $customerMap);
            $customerId = $summary['customer']['id'] ?? null;
            if ($customerId) {
                if (!isset($customerJobs[$customerId])) {
                    $customerJobs[$customerId] = ['upcoming' => [], 'recent' => []];
                }
                $customerJobs[$customerId][$bucket][] = $summary;
            }
            return $summary;
        };

        $upcoming = $upcomingRecords->map(fn (Job $job) => $formatJob($job, 'upcoming'))->values()->all();
        $recent = $recentRecords->map(fn (Job $job) => $formatJob($job, 'recent'))->values()->all();

        return [
            'upcoming' => $upcoming,
            'recent' => $recent,
            'customerMap' => $customerJobs,
        ];
    }

    protected function presentJobSummary(Job $job, $customerMap): array
    {
        $checklist = $job->checklist_json ?? [];
        $customerId = $checklist['customer_id'] ?? null;
        $customer = $customerId && $customerMap instanceof \Illuminate\Support\Collection ? $customerMap->get($customerId) : null;
        [$start, $end] = $this->parseEtaWindow($job->eta_window);

        return [
            'id' => $job->id,
            'tenant_id' => $job->tenant_id,
            'date' => optional($job->date)->toDateString(),
            'day_label' => optional($job->date)->format('D d M'),
            'eta_window' => $job->eta_window,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $job->status,
            'status_label' => JobPresenter::statusLabel($job->status ?? 'scheduled'),
            'status_badge' => JobPresenter::statusBadge($job->status ?? 'scheduled'),
            'price_pence' => $checklist['price_pence'] ?? null,
            'price_display' => $this->formatPrice($checklist['price_pence'] ?? null),
            'frequency' => $checklist['frequency'] ?? null,
            'customer' => $customer ? [
                'id' => $customer->id,
                'name' => $customer->name ?? $customer->email,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ] : null,
            'staff' => $job->relationLoaded('staff') && $job->staff ? [
                'id' => $job->staff->id,
                'name' => $job->staff->name,
            ] : null,
            'address' => [
                'line1' => $checklist['address_line1'] ?? null,
                'line2' => $checklist['address_line2'] ?? null,
                'city' => $checklist['city'] ?? null,
                'postcode' => $checklist['postcode'] ?? null,
                'lat' => isset($checklist['lat']) ? (float) $checklist['lat'] : (isset($checklist['latitude']) ? (float) $checklist['latitude'] : null),
                'lng' => isset($checklist['lng']) ? (float) $checklist['lng'] : (isset($checklist['longitude']) ? (float) $checklist['longitude'] : null),
            ],
        ];
    }

    protected function parseEtaWindow(?string $window): array
    {
        if (!$window) {
            return [null, null];
        }

        $parts = explode('-', $window);
        $start = trim($parts[0] ?? '') ?: null;
        $end = trim($parts[1] ?? '') ?: null;

        return [$start, $end];
    }

    protected function formatPrice(?int $pence): ?string
    {
        if ($pence === null) {
            return null;
        }

        return '£' . number_format($pence / 100, 2);
    }

    protected function loadStaff(string $tenantId, array $roles): array
    {
        if (Schema::hasTable('tenant_user')) {
            return User::select('users.id', 'users.name', 'users.email', 'users.phone', 'tenant_user.role')
                ->join('tenant_user', 'tenant_user.user_id', '=', 'users.id')
                ->where('tenant_user.tenant_id', $tenantId)
                ->whereIn('users.role', $roles)
                ->orderBy('users.name')
                ->get()
                ->map(function (User $user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name ?? $user->email,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'role' => ucfirst($user->role ?? 'staff'),
                        'role_key' => $user->role ?? null,
                    ];
                })
                ->all();
        }

        return User::where('tenant_id', $tenantId)
            ->whereIn('role', $roles)
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? $user->email,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => ucfirst($user->role ?? 'staff'),
                    'role_key' => $user->role ?? null,
                ];
            })
            ->all();
    }

    protected function loadCustomers(string $tenantId, array $jobMap = [], bool $includeJobLeads = false): array
    {
        $query = User::select('users.id', 'users.name', 'users.email', 'users.phone', 'users.created_at');

        $addressUserColumn = $this->addressUserColumn();
        if ($addressUserColumn && Schema::hasTable('addresses')) {
            $query->addSelect('addresses.line1');
            $query->leftJoin('addresses', function ($join) use ($tenantId, $addressUserColumn) {
                $join->on('addresses.' . $addressUserColumn, '=', 'users.id')
                    ->where('addresses.tenant_id', '=', $tenantId);
            });
        } else {
            $query->addSelect(DB::raw('NULL as line1'));
        }

        if (Schema::hasTable('tenant_user')) {
            $query->join('tenant_user', 'tenant_user.user_id', '=', 'users.id')
                ->where('tenant_user.tenant_id', $tenantId)
                ->where('tenant_user.role', 'customer');
        } else {
            $query->where('users.tenant_id', $tenantId)
                ->where('users.role', 'customer');
        }

        $baseCustomers = $query
            ->orderByDesc('users.created_at')
            ->limit(200)
            ->get()
            ->map(function (User $user) {
                $displayName = $user->name ?: ($user->line1 ?: $user->email);
                return [
                    'id' => $user->id,
                    'name' => $displayName,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'created_at' => optional($user->created_at)->toDateString(),
                    'address_line1' => $user->line1,
                    'type' => 'customer',
                    'is_lead' => false,
                ];
            })
            ->keyBy('id');

        $existingIds = $baseCustomers->keys()->filter()->map(fn ($id) => (string) $id)->all();
        $addresses = $this->loadCustomerAddresses($tenantId, $existingIds);

        $customers = $baseCustomers
            ->map(function (array $customer) use ($jobMap, $addresses) {
                $customer['jobs'] = $jobMap[$customer['id']] ?? ['upcoming' => [], 'recent' => []];
                $customer['addresses'] = $addresses[$customer['id']] ?? [];
                if (!$customer['address_line1'] && !empty($customer['addresses'])) {
                    $customer['address_line1'] = $customer['addresses'][0]['line1'];
                }
                return $customer;
            })
            ->values();

        if ($includeJobLeads) {
            $jobCustomers = $this->loadJobCustomers($tenantId, $existingIds);
            return $customers->merge($jobCustomers)->values()->all();
        }

        return $customers->values()->all();
    }

    protected function loadCustomerAddresses(string $tenantId, array $customerIds): array
    {
        $column = $this->addressUserColumn();
        if (!$column || empty($customerIds) || !Schema::hasTable('addresses')) {
            return [];
        }

        return Address::where('tenant_id', $tenantId)
            ->whereIn($column, $customerIds)
            ->orderByDesc('created_at')
            ->get(['id', 'line1', 'line2', 'city', 'postcode', 'lat', 'lng', $column])
            ->groupBy($column)
            ->map(function ($rows) {
                return $rows->map(function (Address $address) {
                    return [
                        'id' => $address->id,
                        'line1' => $address->line1,
                        'line2' => $address->line2,
                        'city' => $address->city,
                        'postcode' => $address->postcode,
                        'lat' => $address->lat !== null ? (float) $address->lat : null,
                        'lng' => $address->lng !== null ? (float) $address->lng : null,
                    ];
                })->values()->all();
            })
            ->all();
    }

    protected function aggregateJobStats(): array
    {
        if (!Schema::hasTable('jobs')) {
            return [];
        }

        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        $query = DB::table('jobs as j')
            ->whereBetween('j.date', [$start, $end]);

        $bookingUserColumn = $this->bookingUserColumn();
        if ($bookingUserColumn && Schema::hasTable('bookings')) {
            $query->leftJoin('bookings', 'bookings.id', '=', 'j.booking_id');
        }

        $query->select('j.tenant_id', DB::raw('COUNT(*) as total'));
        if ($bookingUserColumn && Schema::hasTable('bookings')) {
            $query->selectRaw('COUNT(DISTINCT bookings.' . $bookingUserColumn . ') as customers');
        } else {
            $query->selectRaw('0 as customers');
        }

        return $query
            ->groupBy('j.tenant_id')
            ->get()
            ->mapWithKeys(function ($row) {
                return [
                    $row->tenant_id => [
                        'jobs' => (int) $row->total,
                        'customers' => (int) ($row->customers ?? 0),
                    ],
                ];
            })
            ->all();
    }

    protected function loadJobCustomers(string $tenantId, array $existingUserIds = [], bool $returnRecords = true)
    {
        if (!Schema::hasTable('jobs')) {
            return $returnRecords ? [] : 0;
        }

        $bookingUserColumn = $this->bookingUserColumn();
        if (!$bookingUserColumn || !Schema::hasTable('bookings')) {
            return $returnRecords ? [] : 0;
        }

        $qualifiedBookingColumn = 'b.' . $bookingUserColumn;

        $query = DB::table('jobs as j')
            ->leftJoin('bookings as b', 'b.id', '=', 'j.booking_id')
            ->leftJoin('users as u', 'u.id', '=', $qualifiedBookingColumn)
            ->leftJoin('addresses as addr', 'addr.id', '=', 'b.address_id')
            ->where('j.tenant_id', $tenantId)
            ->when(!empty($existingUserIds), function ($builder) use ($existingUserIds, $qualifiedBookingColumn) {
                $builder->where(function ($inner) use ($existingUserIds, $qualifiedBookingColumn) {
                    $inner->whereNull($qualifiedBookingColumn)
                        ->orWhereNotIn($qualifiedBookingColumn, $existingUserIds);
                });
            })
            ->orderByDesc('j.date')
            ->limit(400)
            ->get([
                'j.id as job_id',
                'j.date',
                'j.status',
                'j.eta_window',
                'j.checklist_json',
                DB::raw($qualifiedBookingColumn . ' as booking_user_id'),
                'u.name',
                'u.email',
                'u.phone',
                'u.role as user_role',
                'addr.line1',
                'addr.line2',
                'addr.city',
                'addr.postcode',
            ]);

        $seen = [];
        $count = 0;
        $customers = [];

        foreach ($query as $row) {
            if ($row->booking_user_id && $row->user_role && in_array($row->user_role, $this->staffRolesList, true)) {
                continue;
            }
            if ($row->booking_user_id && $row->user_role === 'customer') {
                continue;
            }

            $key = $row->booking_user_id ?: ($row->line1 ? 'addr:' . $row->line1 . '|' . ($row->postcode ?? '') : 'job:' . $row->job_id);
            if (isset($seen[$key])) {
                continue;
            }

            $checklist = [];
            if (!empty($row->checklist_json)) {
                $decoded = is_string($row->checklist_json)
                    ? json_decode($row->checklist_json, true)
                    : (array) $row->checklist_json;
                if (is_array($decoded)) {
                    $checklist = $decoded;
                }
            }

            if (!empty($checklist['customer_id'])) {
                continue;
            }

            $seen[$key] = true;

            if (!$returnRecords) {
                $count++;
                continue;
            }

            $line1 = $row->line1 ?: ($checklist['address_line1'] ?? null);
            $city = $row->city ?: ($checklist['city'] ?? null);
            $postcode = $row->postcode ?: ($checklist['postcode'] ?? null);
            $name = $row->name ?: ($line1 ?: ($row->email ?: '—'));
            $date = $row->date ? Carbon::parse($row->date)->toDateString() : null;
            $jobDate = $row->date ? Carbon::parse($row->date) : null;
            $etaWindow = $row->eta_window ?? ($checklist['eta_window'] ?? null);
            [$start, $end] = $this->parseEtaWindow($etaWindow);
            $pricePence = $checklist['price_pence'] ?? null;

            $summary = [
                'id' => $row->job_id,
                'tenant_id' => $tenantId,
                'date' => $date,
                'day_label' => $jobDate ? $jobDate->format('D d M') : null,
                'eta_window' => $etaWindow,
                'start_time' => $start,
                'end_time' => $end,
                'status' => $row->status ?? 'scheduled',
                'status_label' => JobPresenter::statusLabel($row->status ?? 'scheduled'),
                'status_badge' => JobPresenter::statusBadge($row->status ?? 'scheduled'),
                'price_pence' => $pricePence,
                'price_display' => $this->formatPrice($pricePence),
                'frequency' => $checklist['frequency'] ?? null,
                'customer' => null,
                'staff' => null,
                'address' => [
                    'line1' => $line1,
                    'line2' => $row->line2 ?? ($checklist['address_line2'] ?? null),
                    'city' => $city,
                    'postcode' => $postcode,
                    'lat' => isset($checklist['lat']) ? (float) $checklist['lat'] : null,
                    'lng' => isset($checklist['lng']) ? (float) $checklist['lng'] : null,
                ],
            ];

            $bucket = $jobDate && $jobDate->isFuture() ? 'upcoming' : 'recent';
            $jobsBuckets = ['upcoming' => [], 'recent' => []];
            $jobsBuckets[$bucket][] = $summary;

            $customers[] = [
                'id' => $row->booking_user_id ?: $key,
                'name' => $name,
                'email' => $row->email,
                'phone' => $row->phone,
                'created_at' => $date,
                'address_line1' => $line1,
                'city' => $city,
                'postcode' => $postcode,
                'type' => 'lead',
                'is_lead' => true,
                'source_job_id' => $row->job_id,
                'addresses' => $line1 ? [[
                    'id' => null,
                    'line1' => $line1,
                    'line2' => $row->line2 ?? ($checklist['address_line2'] ?? null),
                    'city' => $city,
                    'postcode' => $postcode,
                    'lat' => isset($checklist['lat']) ? (float) $checklist['lat'] : null,
                    'lng' => isset($checklist['lng']) ? (float) $checklist['lng'] : null,
                ]] : [],
                'jobs' => $jobsBuckets,
                'last_job' => $summary,
            ];
        }

        return $returnRecords ? $customers : $count;
    }

    protected function ensureAdmin(Request $request): void
    {
        abort_unless(optional($request->user())->role === 'platform_admin', 403);
    }

    protected function addressUserColumn(): ?string
    {
        if (!Schema::hasTable('addresses')) {
            return null;
        }

        if (Schema::hasColumn('addresses', 'user_id')) {
            return 'user_id';
        }

        if (Schema::hasColumn('addresses', 'customer_id')) {
            return 'customer_id';
        }

        return null;
    }

    protected function bookingUserColumn(): ?string
    {
        if (!Schema::hasTable('bookings')) {
            return null;
        }

        if (Schema::hasColumn('bookings', 'user_id')) {
            return 'user_id';
        }

        if (Schema::hasColumn('bookings', 'customer_id')) {
            return 'customer_id';
        }

        return null;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $counter = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
