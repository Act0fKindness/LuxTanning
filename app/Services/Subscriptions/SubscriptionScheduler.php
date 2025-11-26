<?php

namespace App\Services\Subscriptions;

class SubscriptionScheduler
{
    // Nightly scheduler builds future jobs by cadence
    public function buildDueJobs(): int
    {
        // TODO: Query active subscriptions and enqueue/create jobs
        return 0; // number of jobs created
    }
}

