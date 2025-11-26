<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Support\CustomerProfileService;
use Illuminate\Database\Seeder;

class CreateCustomersFromJobsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(CustomerProfileService::class);
        $processed = 0;
        $linked = 0;
        $dryRun = $this->command ? (bool) $this->command->option('dry-run') : false;
        $tenantId = $this->command ? $this->command->option('tenant') : null;

        $builder = Job::query()->orderBy('id');
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }

        $builder->chunkById(200, function ($jobs) use ($service, &$processed, &$linked, $dryRun) {
            foreach ($jobs as $job) {
                $processed++;

                $checklist = is_array($job->checklist_json)
                    ? $job->checklist_json
                    : (json_decode($job->checklist_json ?? '[]', true) ?: []);

                $addressLine = $checklist['address_line1'] ?? null;
                $payload = [
                    'name' => $checklist['customer_name'] ?? null,
                    'email' => $checklist['customer_email'] ?? null,
                    'phone' => $checklist['customer_phone'] ?? null,
                    'address_line1' => $addressLine,
                    'address_line2' => $checklist['address_line2'] ?? null,
                    'city' => $checklist['city'] ?? null,
                    'postcode' => $checklist['postcode'] ?? null,
                ];

                // Require at least an address or email to build a customer profile
                if (empty($payload['email']) && empty($payload['address_line1'])) {
                    continue;
                }

                $customer = $service->findOrCreateCustomer($job->tenant_id, $payload);
                $address = $service->createAddressFromChecklist($job->tenant_id, $customer, $checklist, false);
                if (!$dryRun) {
                    $service->attachJobToCustomer($job, $customer, $address, $checklist);
                }
                $linked++;
            }
        });

        if ($this->command) {
            $scope = $tenantId ? " for tenant {$tenantId}" : '';
            $this->command->info(
                "Processed {$processed} jobs{$scope}; linked {$linked} jobs to customers" . ($dryRun ? ' (dry run).' : '.')
            );
        }
    }
}
