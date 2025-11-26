<?php

namespace App\Services\Dunning;

class DunningWorker
{
    // Processes failed payments with retries at T+0, T+1, T+3
    public function run(): int
    {
        // TODO: Implement retry logic and pay-link generation
        return 0; // number processed
    }
}

