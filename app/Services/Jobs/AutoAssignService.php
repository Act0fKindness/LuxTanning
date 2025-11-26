<?php

namespace App\Services\Jobs;

class AutoAssignService
{
    // Assigns jobs to staff based on proximity and balancing counts.
    // Placeholder: wire real distance + DB models later.
    public function assignForDate(string $tenantId, string $date): array
    {
        return [
            'date' => $date,
            'tenant_id' => $tenantId,
            'jobs_assigned' => 0,
            'strategy' => 'nearest_with_balanced_counts',
        ];
    }
}

