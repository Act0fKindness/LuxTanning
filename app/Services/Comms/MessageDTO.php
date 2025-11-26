<?php

namespace App\Services\Comms;

class MessageDTO
{
    public function __construct(
        public string $channel,
        public string $to,
        public string $subject = '',
        public string $body = '',
        public ?string $templateKey = null,
        public array $data = [],
    ) {}
}

