<?php

namespace App\Services\Tracking;

class TrackingService
{
    public function startSession(string $jobId): array
    {
        // TODO: Create tracking_session + share token
        return ['tracking_session_id' => null, 'share_token' => null];
    }

    public function recordPing(string $trackingSessionId, array $ping): void
    {
        // TODO: Persist ping and update ETA/stops ahead
    }
}

