<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Support\CustomerProfileService;
use Illuminate\Console\Command;

class BackfillJobCustomers extends Command
{
    protected $signature = 'glint:backfill-job-customers {--tenant=} {--dry-run}';

    protected $description = 'Create customer records for historical jobs that do not yet have a linked customer.';

    public function __construct(protected CustomerProfileService $customerProfiles)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = Job::query()->orderBy('date');

        if ($tenant = $this->option('tenant')) {
            $query->where('tenant_id', $tenant);
        }

        $updated = 0;
        $processed = 0;

        $dryRun = (bool) $this->option('dry-run');
        $query->chunk(200, function ($jobs) use (&$updated, &$processed, $dryRun) {
            foreach ($jobs as $job) {
                $processed++;
                $checklist = is_array($job->checklist_json)
                    ? $job->checklist_json
                    : (json_decode($job->checklist_json ?? '[]', true) ?: []);

                $payload = [
                    'name' => $checklist['customer_name'] ?? null,
                    'email' => $checklist['customer_email'] ?? null,
                    'phone' => $checklist['customer_phone'] ?? null,
                    'address_line1' => $checklist['address_line1'] ?? null,
                    'address_line2' => $checklist['address_line2'] ?? null,
                    'city' => $checklist['city'] ?? null,
                    'postcode' => $checklist['postcode'] ?? null,
                ];

                if (empty($payload['email']) && empty($payload['address_line1'])) {
                    continue;
                }

                $customer = $this->customerProfiles->findOrCreateCustomer($job->tenant_id, $payload);
                $address = $this->customerProfiles->createAddressFromChecklist($job->tenant_id, $customer, $checklist, false);

                if (!$dryRun) {
                    $this->customerProfiles->attachJobToCustomer($job, $customer, $address, $checklist);
                }

                $updated++;
            }
        });

        $this->info("Processed {$processed} jobs; linked {$updated} records" . ($dryRun ? ' (dry run).' : '.'));

        return Command::SUCCESS;
    }
}
