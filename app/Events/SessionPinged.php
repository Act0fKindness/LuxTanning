<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionPinged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $jobId;
    public array $payload;

    public function __construct(string $jobId, array $payload)
    {
        $this->jobId = $jobId;
        $this->payload = $payload;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('job.' . $this->jobId);
    }

    public function broadcastAs(): string
    {
        return 'SessionPinged';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
