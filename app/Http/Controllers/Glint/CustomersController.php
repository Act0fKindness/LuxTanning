<?php

namespace App\Http\Controllers\Glint;

use App\Models\Job;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\CustomerProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class CustomersController extends CompaniesController
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

        $selectedCustomerId = $request->query('customer');

        $selectedTenant = $selectedId ? $tenants->firstWhere('id', $selectedId) : null;
        $customers = [];
        $staff = [];
        $jobs = ['upcoming' => [], 'recent' => []];
        if ($selectedTenant) {
            $jobsSummary = $this->loadJobsSummary($selectedTenant->id, 120);
            $customers = $this->loadCustomers($selectedTenant->id, $jobsSummary['customerMap'] ?? [], true);
            $jobs = [
                'upcoming' => $jobsSummary['upcoming'] ?? [],
                'recent' => $jobsSummary['recent'] ?? [],
            ];
            $staff = $this->loadStaff($selectedTenant->id, $this->staffRolesList);
        }

        $stats = [
            'customers' => count(array_filter($customers, fn ($row) => empty($row['is_lead']))),
            'leads' => count(array_filter($customers, fn ($row) => !empty($row['is_lead']))),
        ];

        return Inertia::render('Glint/Customers', [
            'tenants' => $tenants->map(fn (Tenant $tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'status' => ucfirst($tenant->status ?? 'active'),
            ]),
            'selectedTenant' => $selectedTenant ? [
                'id' => $selectedTenant->id,
                'name' => $selectedTenant->name,
                'status' => ucfirst($selectedTenant->status ?? 'active'),
                'slug' => $selectedTenant->slug,
            ] : null,
            'customers' => $customers,
            'jobs' => $jobs,
            'staff' => $staff,
            'stats' => $stats,
            'filters' => ['tenant' => $selectedId, 'customer' => $selectedCustomerId],
        ]);
    }

    public function update(Request $request, Tenant $tenant, User $customer)
    {
        $this->ensureAdmin($request);

        if ($customer->tenant_id && $customer->tenant_id !== $tenant->id) {
            abort(403, 'Customer belongs to another tenant.');
        }

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'merge_target_id' => ['nullable', 'uuid', 'different:' . $customer->id],
        ]);

        $addressPayload = array_filter([
            'address_line1' => $data['address_line1'] ?? null,
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'] ?? null,
            'postcode' => $data['postcode'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $profilePayload = [
            'name' => array_key_exists('name', $data) ? ($data['name'] ?? $customer->name) : $customer->name,
            'email' => array_key_exists('email', $data) ? ($data['email'] ?? null) : $customer->email,
            'phone' => array_key_exists('phone', $data) ? ($data['phone'] ?? null) : $customer->phone,
        ];

        $targetSelection = $customer->id;

        DB::transaction(function () use ($tenant, $customer, $data, $addressPayload, $profilePayload, &$targetSelection) {
            $customer->tenant_id = $customer->tenant_id ?: $tenant->id;

            if (!empty($data['merge_target_id'])) {
                $target = User::where('id', $data['merge_target_id'])->firstOrFail();
                if ($target->tenant_id && $target->tenant_id !== $tenant->id) {
                    abort(403, 'Merge target belongs to another tenant.');
                }

                $target->tenant_id = $target->tenant_id ?: $tenant->id;

                $payloadName = $profilePayload['name'];
                $payloadEmail = $profilePayload['email'];
                $payloadPhone = $profilePayload['phone'];

                if ($payloadEmail && $customer->email === $payloadEmail) {
                    $customer->email = sprintf('merge-%s@placeholder.invalid', $customer->id);
                }
                $customer->save();

                $target->name = $payloadName;
                if ($payloadEmail !== null) {
                    $target->email = $payloadEmail;
                }
                $target->phone = $payloadPhone;
                $target->save();

                if (!empty($addressPayload['address_line1'])) {
                    $this->customerProfiles->createAddressFromChecklist($tenant->id, $target, $addressPayload, true);
                }

                $this->customerProfiles->reassignAddresses($tenant->id, $customer, $target);

                TenantUser::where('tenant_id', $tenant->id)->where('user_id', $customer->id)->delete();
                $customer->delete();

                $targetSelection = $target->id;
                return;
            }

            if (array_key_exists('name', $data)) {
                $customer->name = $profilePayload['name'];
            }
            if (array_key_exists('email', $data)) {
                $customer->email = $profilePayload['email'];
            }
            if (array_key_exists('phone', $data)) {
                $customer->phone = $profilePayload['phone'];
            }
            $customer->save();

            if (!empty($addressPayload['address_line1'])) {
                $this->customerProfiles->createAddressFromChecklist($tenant->id, $customer, $addressPayload, true);
            }
        });

        return redirect()
            ->route('glint.customers', ['tenant' => $tenant->id, 'customer' => $targetSelection])
            ->with('success', 'Customer updated.');
    }

    public function convertJobLead(Request $request, Tenant $tenant)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'job_id' => ['required', 'uuid'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $job = Job::where('tenant_id', $tenant->id)->where('id', $data['job_id'])->firstOrFail();
        $checklist = is_array($job->checklist_json) ? $job->checklist_json : (json_decode($job->checklist_json ?? '[]', true) ?: []);
        if (!empty($checklist['customer_id'])) {
            return redirect()->back()->with('success', 'Job already linked to a customer.');
        }

        $name = $data['name'] ?? $checklist['customer_name'] ?? ($checklist['address_line1'] ?? 'Customer');
        $email = $data['email'] ?? $checklist['customer_email'] ?? null;
        $phone = $data['phone'] ?? $checklist['customer_phone'] ?? null;

        $customer = $this->customerProfiles->findOrCreateCustomer($tenant->id, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address_line1' => $checklist['address_line1'] ?? null,
            'address_line2' => $checklist['address_line2'] ?? null,
            'city' => $checklist['city'] ?? null,
            'postcode' => $checklist['postcode'] ?? null,
        ]);
        $address = $this->customerProfiles->createAddressFromChecklist($tenant->id, $customer, $checklist);

        $this->customerProfiles->attachJobToCustomer($job, $customer, $address, $checklist);

        return redirect()->back()->with('success', 'Customer profile created from job.');
    }

    public function mergeJobLead(Request $request, Tenant $tenant)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'job_id' => ['required', 'uuid'],
            'customer_id' => ['required', 'uuid'],
        ]);

        $job = Job::where('tenant_id', $tenant->id)->where('id', $data['job_id'])->firstOrFail();
        $customer = User::where('id', $data['customer_id'])->firstOrFail();

        if ($customer->tenant_id && $customer->tenant_id !== $tenant->id) {
            abort(422, 'Customer belongs to another tenant.');
        }

        $checklist = is_array($job->checklist_json) ? $job->checklist_json : (json_decode($job->checklist_json ?? '[]', true) ?: []);
        $this->customerProfiles->ensureMembership($tenant->id, $customer);
        $address = $this->customerProfiles->createAddressFromChecklist($tenant->id, $customer, $checklist, false);
        $this->customerProfiles->attachJobToCustomer($job, $customer, $address, $checklist);

        return redirect()->back()->with('success', 'Job details merged into customer.');
    }
}
