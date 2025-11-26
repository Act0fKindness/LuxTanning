<?php

namespace App\Support;

use App\Models\Address;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Job;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Support\JobPresenter;
use App\Support\PlatformJobsFetcher;
use App\Support\TenantBrandingResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageContextResolver
{
    public function __construct(protected Request $request)
    {
    }

    /**
     * Cached min/max house numbers per street for dispatch approximations.
     */
    protected array $streetNumberStats = [];

    /**
     * Cached pseudo orientation per street.
     */
    protected array $streetOrientationCache = [];

    public function resolve(string $pageKey, array $seed = []): array
    {
        $data = [];

        if (str_starts_with($pageKey, 'glint.')) {
            $data = $this->buildGlintContext();
        } elseif (str_starts_with($pageKey, 'cleaner.')) {
            $data = $this->buildCleanerContext();
        } elseif (str_starts_with($pageKey, 'owner.')) {
            $data = $this->buildOwnerContext($pageKey);
        }

        if (empty($data)) {
            return $seed;
        }

        return array_merge($seed, $data);
    }

    protected function buildGlintContext(): array
    {
        $user = $this->request->user();
        if (!$user || $user->role !== 'platform_admin') {
            return [];
        }

        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        $tenantsTotal = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $pendingTenants = max($tenantsTotal - $activeTenants, 0);

        $jobsToday = Job::whereDate('date', $today)->count();
        $lateJobs = Job::whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('date', '<', $today)
            ->count();

        $activeCleaners = User::where('role', 'cleaner')->count();
        $platformMau = User::where('updated_at', '>=', $now->copy()->subDays(30))
            ->distinct('id')
            ->count('id');

        $revenueMonthPence = Payment::where('status', 'succeeded')
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now])
            ->sum('amount_pence');

        $tenantsRows = Tenant::select(['id', 'name', 'fee_tier', 'status'])
            ->withCount(['jobs as jobs_this_week' => function ($query) use ($weekStart, $weekEnd) {
                $query->whereBetween('date', [$weekStart, $weekEnd]);
            }])
            ->orderBy('name')
            ->get()
            ->map(function (Tenant $tenant) {
                $jobsPerWeek = (int) ($tenant->jobs_this_week ?? 0);
                return [
                    'company' => $tenant->name,
                    'tenant' => $tenant->name,
                    'plan' => $tenant->fee_tier ? ucfirst($tenant->fee_tier) : 'Custom',
                    'status' => ucfirst($tenant->status ?? 'active'),
                    'usage' => $jobsPerWeek . ' jobs / wk',
                ];
            })
            ->all();

        $jobsWindow = PlatformJobsFetcher::fetch($today->copy()->subDays(1), $today->copy()->addDays(3), 200);

        $customerRows = Customer::select('customers.id', 'customers.name', 'customers.email', 'customers.phone', 'customers.tags', 'customers.created_at', 'tenants.name as tenant_name', 'customers.tenant_id')
            ->leftJoin('tenants', 'tenants.id', '=', 'customers.tenant_id')
            ->orderByDesc('customers.created_at')
            ->limit(100)
            ->get()
            ->map(function (Customer $customer) {
                return [
                    'customer' => $customer->name ?: 'Unnamed',
                    'email' => $customer->email,
                    'phone' => $customer->phone ?: '—',
                    'company' => $customer->tenant_name ?? '—',
                    'tags' => implode(', ', (array) $customer->tags) ?: '—',
                    'joined' => optional($customer->created_at)->format('d M Y') ?? '—',
                ];
            })
            ->all();

        $staffRows = User::select('users.id', 'users.name', 'users.email', 'users.role', 'users.deleted_at', 'users.updated_at', 'tenants.name as tenant_name')
            ->leftJoin('tenants', 'tenants.id', '=', 'users.tenant_id')
            ->whereIn('users.role', ['owner', 'manager', 'cleaner', 'platform_admin'])
            ->selectSub(function ($sub) use ($today) {
                $sub->selectRaw('COUNT(*)')
                    ->from('jobs')
                    ->whereColumn('jobs.staff_user_id', 'users.id')
                    ->whereDate('jobs.date', $today);
            }, 'jobs_today')
            ->orderByDesc('users.updated_at')
            ->limit(100)
            ->get()
            ->map(function (User $user) {
                $status = $user->deleted_at ? 'Deactivated' : 'Active';
                $role = match ($user->role) {
                    'platform_admin' => 'Platform Admin',
                    'owner' => 'Owner',
                    'manager' => 'Manager',
                    'cleaner' => 'Cleaner',
                    default => ucfirst($user->role ?? 'Staff'),
                };

                return [
                    'name' => $user->name ?? $user->email,
                    'email' => $user->email,
                    'role' => $role,
                    'company' => $user->tenant_name ?? '—',
                    'status' => $status,
                    'jobs' => $user->jobs_today ? $user->jobs_today . ' today' : '—',
                ];
            })
            ->all();

        $jobTableRows = $jobsWindow
            ->map(function (array $job) {
                $dateLabel = $job['date'] ? Carbon::parse($job['date'])->format('D d M') : 'TBC';
                $window = $job['eta_window'] ?? ($job['start_at'] ? Carbon::parse($job['start_at'])->format('H:i') : '—');

                return [
                    'job' => trim(($job['address']['line1'] ?? 'Job') . ' ' . ($job['address']['postcode'] ?? '')),
                    'tenant' => $job['tenant_name'] ?? 'Unknown',
                    'when' => trim($dateLabel . ' ' . $window),
                    'status' => JobPresenter::statusLabel($job['status'] ?? 'scheduled'),
                ];
            })
            ->take(12)
            ->values()
            ->all();

        $markers = $jobsWindow
            ->map(function (array $job) {
                $lat = $job['address']['lat'];
                $lng = $job['address']['lng'];
                if ($lat === null || $lng === null) {
                    return null;
                }

                return [
                    'id' => $job['id'],
                    'title' => trim(($job['tenant_name'] ?? 'Job') . ' · ' . ($job['address']['line1'] ?? '')),
                    'detail' => $job['eta_window'] ?: 'No ETA',
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'state' => match ($job['status'] ?? null) {
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'started', 'arrived', 'en_route' => 'info',
                        default => 'warning',
                    },
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'glint' => [
                'metrics' => [
                    'tenants_total' => (string) $tenantsTotal,
                    'active_tenants' => (string) $activeTenants,
                    'pending_tenants' => (string) $pendingTenants,
                    'jobs_today' => (string) $jobsToday,
                    'late_jobs' => (string) $lateJobs,
                    'active_cleaners' => (string) $activeCleaners,
                    'platform_mau' => (string) $platformMau,
                    'revenue_month' => '£' . number_format(($revenueMonthPence ?? 0) / 100, 2),
                ],
                'tenants' => [
                    'table' => $tenantsRows,
                ],
                'customers' => [
                    'table' => $customerRows,
                ],
                'staff' => [
                    'table' => $staffRows,
                ],
                'jobs' => [
                    'table' => $jobTableRows,
                    'markers' => $markers,
                ],
                'queues' => $this->buildQueuesSnapshot(),
            ],
        ];
    }

    protected function buildQueuesSnapshot(): array
    {
        $unassignedJobs = Job::whereNull('staff_user_id')->count();
        $pendingPayments = Payment::whereIn('status', ['pending', 'requires_action', 'failed'])->count();
        $queuedMessages = DB::table('messages')->where('status', 'queued')->count();
        $failedWebhooks = DB::table('webhook_events')->where('status', 'failed')->count();

        return [
            [
                'label' => 'Dispatch queue',
                'value' => $unassignedJobs . ' jobs',
                'hint' => $unassignedJobs ? 'Needs assignment' : 'All staffed',
                'state' => $unassignedJobs > 0 ? 'warning' : 'success',
            ],
            [
                'label' => 'Billing follow-ups',
                'value' => $pendingPayments . ' payments',
                'hint' => $pendingPayments ? 'Requires ops review' : 'All settled',
                'state' => $pendingPayments > 0 ? 'warning' : 'success',
            ],
            [
                'label' => 'Comms backlog',
                'value' => $queuedMessages . ' messages',
                'hint' => $queuedMessages ? 'Queued in outbox' : 'Queues clear',
                'state' => $queuedMessages > 0 ? 'info' : 'success',
            ],
            [
                'label' => 'Webhook retries',
                'value' => $failedWebhooks . ' events',
                'hint' => $failedWebhooks ? 'Investigate endpoints' : 'All healthy',
                'state' => $failedWebhooks > 0 ? 'danger' : 'success',
            ],
        ];
    }

    protected function buildCleanerContext(): array
    {
        $user = $this->request->user();
        if (!$user || $user->role !== 'cleaner') {
            return [];
        }

        $tenantId = $this->tenantIdOrFallback($user->id);
        if (!$tenantId) {
            return [];
        }

        $timezone = $user->timezone ?? config('app.timezone');
        $today = Carbon::today($timezone);

        $baseQuery = Job::with('staff')
            ->where('tenant_id', $tenantId)
            ->where('staff_user_id', $user->id);

        $todayJobs = (clone $baseQuery)
            ->whereDate('date', $today)
            ->orderBy('date')
            ->orderBy('sequence')
            ->orderBy('eta_window')
            ->get();

        $upcomingJobs = (clone $baseQuery)
            ->whereDate('date', '>', $today)
            ->orderBy('date')
            ->orderBy('sequence')
            ->orderBy('eta_window')
            ->limit(10)
            ->get();

        $historyJobs = (clone $baseQuery)
            ->whereDate('date', '<', $today)
            ->orderByDesc('date')
            ->orderBy('sequence')
            ->orderBy('eta_window')
            ->limit(30)
            ->get();

        $addressSource = $todayJobs
            ->concat($upcomingJobs)
            ->concat($historyJobs);

        $addressMap = $this->addressContextForJobs($addressSource);

        $presentedToday = $todayJobs->map(fn (Job $job) => JobPresenter::make($job, [
            'is_mine' => true,
            'address' => $this->addressForJob($job, $addressMap),
        ]));

        $presentedUpcoming = $upcomingJobs->map(fn (Job $job) => JobPresenter::make($job, [
            'is_mine' => true,
            'address' => $this->addressForJob($job, $addressMap),
        ]));

        $presentedHistory = $historyJobs->map(fn (Job $job) => JobPresenter::make($job, [
            'is_mine' => true,
            'address' => $this->addressForJob($job, $addressMap),
        ]));

        $activeJob = $presentedToday->first(fn (array $job) => in_array($job['status'], ['started', 'arrived', 'en_route'], true))
            ?? $presentedToday->first(fn (array $job) => $job['status'] === 'scheduled');

        $nextJob = $presentedToday->first(fn (array $job) => $job['status'] !== 'completed')
            ?? $presentedUpcoming->first();

        $nextJobCard = $this->buildNextJobCard($activeJob ?? $nextJob);

        $completedToday = $presentedToday->where('status', 'completed')->count();
        $inProgressToday = $presentedToday->filter(fn (array $job) => in_array($job['status'], ['started', 'arrived', 'en_route'], true))->count();
        $travelMinutes = $this->estimateTravelMinutes($presentedToday);
        $lateRiskLabel = $this->lateRiskLabel($presentedToday, $timezone);

        $historyTotal = $presentedHistory->count();
        $historyCompleted = $presentedHistory->where('status', 'completed')->count();
        $historyCancelled = $presentedHistory->where('status', 'cancelled')->count();

        $historyStreak = 0;
        foreach ($presentedHistory as $job) {
            if (($job['status'] ?? null) === 'completed') {
                $historyStreak++;
                continue;
            }

            break;
        }

        $latestHistoryJob = $presentedHistory->first();
        $latestHistoryStatus = $latestHistoryJob['status_label'] ?? 'No jobs yet';
        $latestHistoryLabel = $this->describeJob($latestHistoryJob ?? null);

        $jobsById = $presentedToday
            ->concat($presentedUpcoming)
            ->mapWithKeys(fn (array $job) => [$job['id'] => $job])
            ->all();

        return [
            'cleaner' => [
                'stats' => [
                    'total_today' => $presentedToday->count(),
                    'completed_today' => $completedToday,
                    'in_progress' => $inProgressToday,
                    'travel_minutes' => $travelMinutes,
                    'late_risk' => $lateRiskLabel,
                ],
                'timeline' => $this->buildTimeline($presentedToday),
                'tables' => [
                    'today' => $this->formatJobRows($presentedToday),
                    'upcoming' => $this->formatJobRows($presentedUpcoming),
                    'history' => $this->formatHistoryRows($presentedHistory, $timezone),
                ],
                'active_job' => $activeJob,
                'next_job' => $nextJob,
                'next_job_label' => $this->describeJob($nextJob),
                'next_job_card' => $nextJobCard,
                'jobs_by_id' => $jobsById,
                'history' => [
                    'stats' => [
                        'recent_total' => $historyTotal,
                        'completed' => $historyCompleted,
                        'cancelled' => $historyCancelled,
                        'completed_streak' => $historyStreak,
                        'latest_status' => $latestHistoryStatus,
                        'latest_job_label' => $latestHistoryLabel,
                    ],
                ],
            ],
        ];
    }

    protected function buildOwnerContext(string $pageKey): array
    {
        $tenantContext = app(TenantBrandingResolver::class)->resolve($this->request);
        if (empty($tenantContext)) {
            return [];
        }

        $data = [
            'company' => [
                'name' => $tenantContext['name'] ?? null,
                'slug' => $tenantContext['slug'] ?? null,
                'marketing_url' => $tenantContext['marketing_url'] ?? null,
                'portal_url' => $tenantContext['app_url'] ?? null,
                'profile' => Arr::get($tenantContext, 'branding.profile', []),
            ],
        ];

        $tenantId = $tenantContext['id'] ?? null;
        if ($tenantId) {
            $data['owner'] = $data['owner'] ?? [];

            if ($pageKey === 'owner.jobs') {
                $data['owner']['jobs'] = [
                    'table' => $this->buildOwnerJobsTable($tenantId),
                ];
            }

            if ($pageKey === 'owner.dispatch.board') {
                $timezone = Arr::get($tenantContext, 'branding.profile.timezone')
                    ?: Arr::get($tenantContext, 'company_profile.timezone')
                    ?: config('app.timezone');

                $data['owner']['dispatch_board'] = $this->buildOwnerDispatchBoardData($tenantId, $timezone);
            }
        }

        return $data;
    }

    protected function buildOwnerDispatchBoardData(string $tenantId, string $timezone): array
    {
        if (!Schema::hasTable('jobs')) {
            return [
                'date' => Carbon::now($timezone)->format('Y-m-d'),
                'jobs' => [],
                'stats' => $this->emptyDispatchStats(),
                'last_synced_at' => Carbon::now($timezone)->toIso8601String(),
                'timezone' => $timezone,
            ];
        }

        $selectedDate = $this->resolveDispatchBoardDate($timezone);

        $jobs = Job::with('staff:id,name')
            ->where('tenant_id', $tenantId)
            ->whereDate('date', $selectedDate)
            ->orderBy('eta_window')
            ->orderBy('sequence')
            ->orderBy('created_at')
            ->limit(400)
            ->get();

        if ($jobs->isEmpty()) {
            return [
                'date' => $selectedDate->format('Y-m-d'),
                'jobs' => [],
                'stats' => $this->emptyDispatchStats(),
                'last_synced_at' => Carbon::now($timezone)->toIso8601String(),
                'timezone' => $timezone,
            ];
        }

        $this->streetNumberStats = $this->buildStreetNumberStats($jobs);

        $customerIds = $jobs->map(function (Job $job) {
            return $job->checklist_json['customer_id'] ?? null;
        })->filter()->unique();

        $customers = $customerIds->isEmpty()
            ? collect()
            : User::whereIn('id', $customerIds)->get(['id', 'name', 'email']);
        $customerMap = $customers->mapWithKeys(fn (User $user) => [$user->id => $user])->all();

        $routeIds = $jobs->pluck('route_id')->filter()->unique();
        $routeMap = $routeIds->isEmpty()
            ? []
            : DB::table('routes')->whereIn('id', $routeIds)->pluck('name', 'id')->map(fn ($name) => $name ?: 'Route')->all();

        $jobIds = $jobs->pluck('id');
        $payments = $jobIds->isEmpty()
            ? []
            : Payment::whereIn('job_id', $jobIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('job_id')
                ->map(fn ($group) => $group->first())
                ->all();

        $addressIds = $jobs
            ->map(fn (Job $job) => $job->checklist_json['address_id'] ?? null)
            ->filter()
            ->unique();

        $addresses = $addressIds->isEmpty()
            ? collect()
            : Address::whereIn('id', $addressIds)
                ->get(['id', 'line1', 'postcode', 'lat', 'lng'])
                ->keyBy('id');

        $jobData = $jobs->map(function (Job $job) use ($customerMap, $routeMap, $payments, $addresses, $timezone) {
            $payload = $job->checklist_json ?? [];
            $windowParts = $this->splitWindowTimes($payload['eta_window'] ?? $job->eta_window);
            $customerName = $this->resolveDispatchCustomerName($payload, $customerMap);
            $addressLabel = $this->resolveDispatchAddressLabel($payload, $addresses);
            $routeLabel = $job->route_id && isset($routeMap[$job->route_id])
                ? $routeMap[$job->route_id]
                : 'Unassigned';
            $payment = $payments[$job->id] ?? null;
            $coords = $this->resolveDispatchCoordinates($payload, $addresses, $job->checklist_json['address_id'] ?? null);

            return [
                'id' => $job->id,
                'date' => optional($job->date)->format('Y-m-d'),
                'window_start' => $windowParts[0],
                'window_end' => $windowParts[1],
                'status' => (string) ($job->status ?? 'scheduled'),
                'status_label' => JobPresenter::statusLabel($job->status ?? 'scheduled'),
                'board_status' => $this->resolveDispatchBoardStatus($job),
                'customer' => $customerName,
                'address' => $addressLabel,
                'route' => [
                    'id' => $job->route_id,
                    'name' => $routeLabel,
                ],
                'cleaner' => $job->staff ? [
                    'id' => $job->staff->id,
                    'name' => $job->staff->name,
                ] : null,
                'type' => $this->formatOwnerJobType($job, $payload),
                'price' => $this->formatOwnerJobPrice($payload['price_pence'] ?? null),
                'price_pence' => isset($payload['price_pence']) ? (int) $payload['price_pence'] : null,
                'payment_status' => $this->resolveDispatchPaymentState($payment, $job),
                'payment_label' => $this->formatDispatchPaymentLabel($payment, $job),
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'at_risk' => $this->isJobAtRisk($job, $windowParts[1], $timezone),
                'sequence' => $job->sequence,
            ];
        })->values();

        $stats = [
            'scheduled' => $jobData->filter(fn ($job) => in_array($job['board_status'], ['scheduled', 'en_route', 'on_site'], true))->count(),
            'completed' => $jobData->filter(fn ($job) => $job['board_status'] === 'completed')->count(),
            'unassigned' => $jobData->filter(fn ($job) => $job['board_status'] === 'unassigned')->count(),
            'at_risk' => $jobData->filter(fn ($job) => $job['at_risk'])->count(),
            'failed_payments' => $jobData->filter(fn ($job) => $job['payment_status'] === 'failed')->count(),
        ];

        return [
            'date' => $selectedDate->format('Y-m-d'),
            'jobs' => $jobData->all(),
            'stats' => $stats,
            'last_synced_at' => Carbon::now($timezone)->toIso8601String(),
            'timezone' => $timezone,
        ];
    }

    protected function resolveDispatchBoardDate(string $timezone): Carbon
    {
        $input = trim((string) $this->request->query('date', ''));
        if ($input !== '') {
            try {
                return Carbon::parse($input, $timezone)->startOfDay();
            } catch (\Throwable $e) {
                // Fallback to today
            }
        }

        return Carbon::now($timezone)->startOfDay();
    }

    protected function emptyDispatchStats(): array
    {
        return [
            'scheduled' => 0,
            'completed' => 0,
            'unassigned' => 0,
            'at_risk' => 0,
            'failed_payments' => 0,
        ];
    }

    protected function splitWindowTimes(?string $window): array
    {
        if (!$window || !is_string($window)) {
            return [null, null];
        }

        $parts = array_map('trim', explode('-', $window, 2));
        if (count($parts) === 1) {
            return [$parts[0] ?: null, null];
        }

        return [
            $parts[0] ?: null,
            $parts[1] ?: null,
        ];
    }

    protected function resolveDispatchCustomerName(array $payload, array $customerMap): string
    {
        $customerId = $payload['customer_id'] ?? null;
        if ($customerId && isset($customerMap[$customerId])) {
            $customer = $customerMap[$customerId];
            if ($customer->name) {
                return $customer->name;
            }
            if ($customer->email) {
                return $customer->email;
            }
        }

        return $payload['customer_name'] ?? 'Customer';
    }

    protected function resolveDispatchAddressLabel(array $payload, $addresses): string
    {
        $addressId = $payload['address_id'] ?? null;
        $line = $payload['address_line1'] ?? null;
        $postcode = $payload['postcode'] ?? null;

        if ((!$line || !$postcode) && $addressId) {
            $address = $addresses->get($addressId);
            $line = $line ?: optional($address)->line1;
            $postcode = $postcode ?: optional($address)->postcode;
        }

        $parts = array_filter([$line, $postcode], fn ($value) => $value !== null && $value !== '');

        return $parts ? implode(', ', $parts) : 'Address pending';
    }

    protected function resolveDispatchCoordinates(array $payload, $addresses, $addressId): array
    {
        $lat = $payload['lat'] ?? $payload['latitude'] ?? null;
        $lng = $payload['lng'] ?? $payload['longitude'] ?? null;

        if (($lat === null || $lng === null) && $addressId) {
            $address = $addresses->get($addressId);
            if ($lat === null) {
                $lat = optional($address)->lat;
            }
            if ($lng === null) {
                $lng = optional($address)->lng;
            }
        }

        [$lat, $lng] = $this->applyStreetOffsets($lat, $lng, $payload['address_line1'] ?? null, $payload['postcode'] ?? null);

        return [
            'lat' => is_numeric($lat) ? (float) $lat : null,
            'lng' => is_numeric($lng) ? (float) $lng : null,
        ];
    }

    protected function buildStreetNumberStats($jobs): array
    {
        $stats = [];
        foreach ($jobs as $job) {
            $payload = $job->checklist_json ?? [];
            $line1 = $payload['address_line1'] ?? null;
            if (!$line1) {
                continue;
            }
            $street = $this->normalizeStreetName($line1);
            $number = $this->extractHouseNumber($line1);
            if (!$street || $number === null) {
                continue;
            }

            if (!isset($stats[$street])) {
                $stats[$street] = ['min' => $number, 'max' => $number];
            } else {
                $stats[$street]['min'] = min($stats[$street]['min'], $number);
                $stats[$street]['max'] = max($stats[$street]['max'], $number);
            }
        }

        return $stats;
    }

    protected function applyStreetOffsets($lat, $lng, ?string $addressLine, ?string $postcode): array
    {
        if (!is_numeric($lat) || !is_numeric($lng) || !$addressLine) {
            return [$lat, $lng];
        }

        $street = $this->normalizeStreetName($addressLine);
        $number = $this->extractHouseNumber($addressLine);
        if (!$street || $number === null) {
            return [$lat, $lng];
        }

        $stats = $this->streetNumberStats[$street] ?? ['min' => $number, 'max' => $number];
        $baseline = $stats['min'] ?? $number;
        $offsetUnits = $number - $baseline;
        if ($offsetUnits === 0) {
            return [$lat, $lng];
        }

        $angle = $this->streetOrientationCache[$street] ?? $this->computeStreetOrientation($street, $postcode);
        $this->streetOrientationCache[$street] = $angle;
        $step = 0.00002; // roughly 2m per house number
        $distance = $offsetUnits * $step;
        $adjustedLat = (float) $lat + cos($angle) * $distance;
        $adjustedLng = (float) $lng + sin($angle) * $distance;

        return [$adjustedLat, $adjustedLng];
    }

    protected function computeStreetOrientation(string $street, ?string $postcode): float
    {
        $seed = $street . '|' . ($postcode ?? '');
        $hash = crc32($seed);
        $degrees = ($hash % 60) - 30; // limit to -30..30 degrees to stay near east-west
        return deg2rad($degrees);
    }

    protected function normalizeStreetName(string $addressLine): ?string
    {
        $clean = preg_replace('/^\s*\d+[A-Z]?\s*/i', '', $addressLine);
        $clean = trim((string) $clean);
        return $clean !== '' ? mb_strtolower($clean) : null;
    }

    protected function extractHouseNumber(string $addressLine): ?int
    {
        if (preg_match('/(\d{1,4})/', $addressLine, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function resolveDispatchBoardStatus(Job $job): string
    {
        if (!$job->staff_user_id) {
            return 'unassigned';
        }

        return match ($job->status) {
            'completed' => 'completed',
            'cancelled', 'failed', 'no_access' => 'failed',
            'en_route' => 'en_route',
            'arrived', 'started' => 'on_site',
            default => 'scheduled',
        };
    }

    protected function resolveDispatchPaymentState(?Payment $payment, Job $job): string
    {
        if ($payment) {
            return match ($payment->status) {
                'succeeded' => 'paid',
                'failed', 'refunded', 'disputed' => 'failed',
                'pending' => 'pending',
                default => 'pending',
            };
        }

        if ($job->status === 'completed') {
            return 'pending';
        }

        return 'pending';
    }

    protected function formatDispatchPaymentLabel(?Payment $payment, Job $job): string
    {
        if ($payment) {
            $status = match ($payment->status) {
                'succeeded' => 'Paid',
                'failed' => 'Failed',
                'pending' => 'Pending',
                'refunded' => 'Refunded',
                'disputed' => 'Disputed',
                default => ucfirst((string) $payment->status),
            };

            $method = match ($payment->method) {
                'card' => 'Card',
                'bacs' => 'Bank transfer',
                'cash' => 'Cash',
                default => ucfirst((string) $payment->method),
            };

            return $status . ' (' . $method . ')';
        }

        if ($job->subscription_id) {
            return 'Subscription billing';
        }

        return 'Not collected';
    }

    protected function isJobAtRisk(Job $job, ?string $windowEnd, string $timezone): bool
    {
        if (!$windowEnd) {
            return false;
        }

        if (in_array($job->status, ['completed', 'cancelled'], true)) {
            return false;
        }

        $date = optional($job->date)?->format('Y-m-d');
        if (!$date) {
            return false;
        }

        try {
            $windowEndTime = Carbon::parse($date . ' ' . $windowEnd, $timezone);
        } catch (\Throwable $e) {
            return false;
        }

        return Carbon::now($timezone)->greaterThan($windowEndTime);
    }

    protected function buildOwnerJobsTable(string $tenantId): array
    {
        if (!Schema::hasTable('jobs')) {
            return [];
        }

        $jobs = Job::with('staff:id,name')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('date')
            ->orderBy('eta_window')
            ->limit(25)
            ->get();

        if ($jobs->isEmpty()) {
            return [];
        }

        $customerIds = $jobs->map(function (Job $job) {
            return $job->checklist_json['customer_id'] ?? null;
        })->filter()->unique();

        $customers = $customerIds->isEmpty()
            ? collect()
            : User::whereIn('id', $customerIds)->get(['id', 'name', 'email']);
        $customerMap = $customers->mapWithKeys(fn (User $user) => [$user->id => $user])->all();

        $bookingIds = $jobs->pluck('booking_id')->filter()->unique();
        $bookingMap = $bookingIds->isEmpty()
            ? []
            : Booking::whereIn('id', $bookingIds)
                ->get(['id', 'channel', 'source'])
                ->mapWithKeys(fn (Booking $booking) => [$booking->id => [
                    'channel' => $booking->channel,
                    'source' => $booking->source,
                ]])
                ->all();

        $jobIds = $jobs->pluck('id');
        $payments = $jobIds->isEmpty()
            ? []
            : Payment::whereIn('job_id', $jobIds)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('job_id')
                ->map(fn ($group) => $group->first())
                ->all();

        $routeIds = $jobs->pluck('route_id')->filter()->unique();
        $routeMap = $routeIds->isEmpty()
            ? []
            : DB::table('routes')
                ->whereIn('id', $routeIds)
                ->pluck('name', 'id')
                ->map(fn ($name) => $name ?: 'Route')
                ->all();

        return $jobs->map(function (Job $job) use ($customerMap, $bookingMap, $payments, $routeMap) {
            $payload = $job->checklist_json ?? [];
            $customerId = $payload['customer_id'] ?? null;
            $customer = $customerId && isset($customerMap[$customerId])
                ? ($customerMap[$customerId]->name ?: ($customerMap[$customerId]->email ?? 'Customer'))
                : ($payload['customer_name'] ?? 'Customer');

            $addressLine = trim((string) ($payload['address_line1'] ?? ''));
            $postcode = trim((string) ($payload['postcode'] ?? ''));
            $address = trim($addressLine . ($postcode ? ' ' . $postcode : '')) ?: 'Address pending';

            $when = $this->formatOwnerJobWhen($job);
            $status = JobPresenter::statusLabel($job->status ?? 'scheduled');
            $route = $job->route_id && isset($routeMap[$job->route_id]) ? $routeMap[$job->route_id] : 'Unassigned';
            $cleaner = optional($job->staff)->name ?: '—';
            $type = $this->formatOwnerJobType($job, $payload);
            $price = $this->formatOwnerJobPrice($payload['price_pence'] ?? null);
            $payment = $this->formatOwnerJobPayment($payments[$job->id] ?? null, $job);
            $source = $this->formatOwnerJobSource($bookingMap[$job->booking_id] ?? null, $payload);
            $updated = $this->formatOwnerJobUpdated($job->updated_at);

            return [
                'when' => $when,
                'status' => $status,
                'customer' => $customer,
                'address' => $address,
                'route' => $route,
                'cleaner' => $cleaner,
                'type' => $type,
                'price' => $price,
                'payment' => $payment,
                'source' => $source,
                'updated' => $updated,
            ];
        })->all();
    }

    protected function formatOwnerJobWhen(Job $job): string
    {
        $dateLabel = $job->date ? $job->date->format('D d M') : 'Date TBC';
        $window = $this->formatEtaWindowLabel($job->eta_window);

        return trim($dateLabel . ($window ? ' · ' . $window : ''));
    }

    protected function formatEtaWindowLabel(?string $etaWindow): ?string
    {
        if (!$etaWindow) {
            return null;
        }

        $parts = array_map('trim', explode('-', $etaWindow, 2));
        if (count($parts) === 1) {
            return $parts[0] ?: null;
        }

        [$start, $end] = $parts;
        if ($start && $end) {
            return $start . '-' . $end;
        }

        return $start ?: $end ?: null;
    }

    protected function formatOwnerJobType(Job $job, array $payload): string
    {
        if ($job->subscription_id) {
            return 'Subscription';
        }

        $frequency = (string) ($payload['frequency'] ?? '');
        return match ($frequency) {
            'four_week' => 'Subscription · 4-week',
            'six_week' => 'Subscription · 6-week',
            'eight_week' => 'Subscription · 8-week',
            'monthly' => 'Subscription · Monthly',
            default => 'One-off',
        };
    }

    protected function formatOwnerJobPrice($pence): string
    {
        if ($pence === null) {
            return '—';
        }

        $amount = (int) $pence / 100;
        $decimals = ((int) $pence % 100) === 0 ? 0 : 2;

        return '£' . number_format($amount, $decimals);
    }

    protected function formatOwnerJobPayment(?Payment $payment, Job $job): string
    {
        if ($payment) {
            $status = match ($payment->status) {
                'succeeded' => 'Paid',
                'pending' => 'Pending',
                'failed' => 'Failed',
                'refunded' => 'Refunded',
                'disputed' => 'Disputed',
                default => ucfirst((string) $payment->status),
            };

            $method = match ($payment->method) {
                'card' => 'Card',
                'bacs' => 'Bank transfer',
                'cash' => 'Cash',
                default => ucfirst((string) $payment->method),
            };

            $parts = [$status . ' (' . $method . ')'];
            if ($payment->stripe_charge_id) {
                $parts[] = $payment->stripe_charge_id;
            }

            return implode(' - ', $parts);
        }

        if ($job->subscription_id) {
            return 'Subscription billing';
        }

        return 'Payment not recorded';
    }

    protected function formatOwnerJobSource(?array $booking, array $payload): string
    {
        if ($booking) {
            $channel = match ($booking['channel'] ?? null) {
                'web' => 'Online booking',
                'admin' => 'Manual entry',
                default => $booking['channel'] ? ucfirst($booking['channel']) : null,
            };
            $parts = array_filter([$channel, $booking['source'] ?? null]);

            if (!empty($parts)) {
                return implode(' - ', $parts);
            }
        }

        if (!empty($payload['source'])) {
            return (string) $payload['source'];
        }

        return 'Manual';
    }

    protected function formatOwnerJobUpdated($timestamp): string
    {
        if (!$timestamp) {
            return '—';
        }

        try {
            return $timestamp->diffForHumans(null, ['parts' => 1, 'short' => true]);
        } catch (\Throwable $e) {
            return $timestamp->diffForHumans();
        }
    }

    protected function estimateTravelMinutes(Collection $jobs): int
    {
        $minutes = 0;
        $previousEnd = null;

        foreach ($jobs as $job) {
            $start = $this->minutesFromTime($job['start_time'] ?? null);
            $end = $this->minutesFromTime($job['end_time'] ?? null);

            if ($previousEnd !== null && $start !== null && $start > $previousEnd) {
                $minutes += max(0, $start - $previousEnd);
            }

            $previousEnd = $end ?? $start ?? $previousEnd;
        }

        return $minutes;
    }

    protected function lateRiskLabel(Collection $jobs, string $timezone): string
    {
        $now = Carbon::now($timezone);
        $nowMinutes = $now->hour * 60 + $now->minute;
        $lateCount = $jobs->filter(function (array $job) use ($nowMinutes) {
            if (in_array($job['status'], ['completed', 'cancelled'], true)) {
                return false;
            }
            $start = $this->minutesFromTime($job['start_time'] ?? null);
            return $start !== null && $start < $nowMinutes;
        })->count();

        if ($lateCount >= 2) {
            return 'High';
        }

        if ($lateCount === 1) {
            return 'Medium';
        }

        return 'Low';
    }

    protected function buildTimeline(Collection $jobs): array
    {
        return $jobs->map(function (array $job) {
            $state = match ($job['status']) {
                'completed' => 'success',
                'started', 'arrived', 'en_route' => 'info',
                'cancelled' => 'danger',
                default => 'warning',
            };

            $address = trim(($job['address_line1'] ?? 'Job') . ' ' . ($job['postcode'] ?? '')) ?: 'Job';

            return [
                'title' => $address,
                'time' => $job['start_time'] ?? ($job['eta_window'] ? explode('-', $job['eta_window'])[0] : '—'),
                'detail' => $job['status_label'],
                'state' => $state,
                'meta' => array_values(array_filter([
                    $job['eta_window'] ? 'Window ' . $job['eta_window'] : null,
                    $job['estimate_minutes'] ? $job['estimate_minutes'] . ' min' : null,
                ])),
            ];
        })->all();
    }

    protected function formatJobRows(Collection $jobs): array
    {
        return $jobs->map(function (array $job) {
            $slot = $job['eta_window'] ?? $job['start_time'] ?? '—';
            $address = trim(($job['address_line1'] ?? 'Job') . ' ' . ($job['postcode'] ?? ''));

            return [
                'slot' => $slot,
                'address' => $address,
                'status' => $job['status_label'],
            ];
        })->all();
    }

    protected function formatHistoryRows(Collection $jobs, string $timezone): array
    {
        return $jobs->map(function (array $job) use ($timezone) {
            $dateLabel = '—';
            if (!empty($job['date'])) {
                $dateLabel = Carbon::parse($job['date'], $timezone)->format('D d M');
            }

            $window = $job['eta_window'] ?? $job['start_time'] ?? '—';
            $address = trim(($job['address_line1'] ?? 'Job') . ' ' . ($job['postcode'] ?? ''));

            $duration = $job['actual_minutes'];
            if ($duration !== null) {
                $durationLabel = $duration . ' min';
            } elseif (!empty($job['estimate_minutes'])) {
                $durationLabel = $job['estimate_minutes'] . ' min est';
            } else {
                $durationLabel = '—';
            }

            return [
                'date' => $dateLabel,
                'window' => $window,
                'address' => $address,
                'status' => $job['status_label'],
                'duration' => $durationLabel,
            ];
        })->all();
    }

    protected function buildNextJobCard(?array $job): ?array
    {
        if (!$job || empty($job['id'])) {
            return null;
        }

        $address = trim(($job['address_line1'] ?? 'Job') . ' ' . ($job['postcode'] ?? '')) ?: 'Job';
        $window = $job['eta_window'] ?? $job['start_time'] ?? '—';

        return [
            'job_id' => $job['id'],
            'address' => $address,
            'window' => $window,
            'status' => $job['status_label'] ?? 'Scheduled',
            'status_badge' => $job['status_badge'] ?? 'secondary',
            'detail' => $this->describeJob($job),
            'meta' => array_values(array_filter([
                $job['estimate_minutes'] ? $job['estimate_minutes'] . ' min planned' : null,
                $job['actual_minutes'] ? ($job['actual_minutes'] . ' min actual') : null,
            ])),
            'actions' => $this->buildNextJobActions($job),
        ];
    }

    protected function buildNextJobActions(array $job): array
    {
        $jobId = $job['id'] ?? null;
        if (!$jobId) {
            return [];
        }

        $basePath = "/cleaner/jobs/{$jobId}";
        $status = $job['status'] ?? 'scheduled';

        $actions = [[
            'label' => 'Navigate',
            'href' => $basePath . '/navigate',
            'variant' => 'primary',
        ]];

        if (in_array($status, ['scheduled', 'en_route', 'arrived', 'paused'], true)) {
            $actions[] = [
                'label' => 'Start job',
                'variant' => 'success',
                'status_action' => 'start',
            ];
        }

        if (in_array($status, ['started'], true)) {
            $actions[] = [
                'label' => 'Finish job',
                'variant' => 'primary',
                'status_action' => 'finish',
            ];
        }

        if (!in_array($status, ['completed', 'cancelled'], true)) {
            $actions[] = [
                'label' => 'Cancel job',
                'variant' => 'danger',
                'status_action' => 'cancel',
                'confirm' => 'Cancel this job? This notifies the manager immediately.',
            ];
        } else {
            $actions[] = [
                'label' => 'View job',
                'href' => $basePath,
                'variant' => 'secondary',
            ];
        }

        return $actions;
    }

    protected function minutesFromTime(?string $value): ?int
    {
        if (!$value || !preg_match('/^(\d{1,2}):(\d{2})$/', $value, $matches)) {
            return null;
        }

        return ((int) $matches[1]) * 60 + (int) $matches[2];
    }

    /**
     * @param \Illuminate\Support\Collection<int, Job> $jobs
     */
    protected function addressContextForJobs(Collection $jobs): array
    {
        $ids = $jobs
            ->pluck('checklist_json.address_id')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return Address::whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->map(fn (Address $address) => $this->formatAddress($address))
            ->all();
    }

    protected function addressForJob(Job $job, array $addressMap): ?array
    {
        $addressId = $job->checklist_json['address_id'] ?? null;
        if (!$addressId) {
            return null;
        }

        return $addressMap[$addressId] ?? null;
    }

    protected function formatAddress(?Address $address): ?array
    {
        if (!$address) {
            return null;
        }

        return [
            'id' => $address->id,
            'line1' => $address->line1,
            'line2' => $address->line2,
            'city' => $address->city,
            'county' => $address->county,
            'postcode' => $address->postcode,
            'lat' => $address->lat !== null ? (float) $address->lat : null,
            'lng' => $address->lng !== null ? (float) $address->lng : null,
        ];
    }

    protected function describeJob(?array $job): string
    {
        if (!$job) {
            return 'All jobs complete';
        }

        $address = trim(($job['address_line1'] ?? 'Job') . ' ' . ($job['postcode'] ?? ''));
        $window = $job['eta_window'] ?? $job['start_time'] ?? null;

        return $window ? trim($address . ' · ' . $window) : $address;
    }

    protected function tenantIdOrFallback(?string $userId): ?string
    {
        $user = $this->request->user();
        if ($user && $user->tenant_id) {
            return $user->tenant_id;
        }

        if (!$userId) {
            return null;
        }

        if (Schema::hasTable('tenant_user')) {
            $membership = DB::table('tenant_user')
                ->where('user_id', $userId)
                ->first();

            if ($membership && $membership->tenant_id) {
                return $membership->tenant_id;
            }
        }

        return null;
    }
}
