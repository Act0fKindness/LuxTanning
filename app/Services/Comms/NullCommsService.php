<?php

namespace App\Services\Comms;

use Illuminate\Support\Facades\Log;

class NullCommsService implements CommsService
{
    public function sendEmail(MessageDTO $message): void
    {
        Log::info('[NullComms] Email queued', [
            'to' => $message->to,
            'subject' => $message->subject,
            'template' => $message->templateKey,
        ]);
    }

    public function sendSms(MessageDTO $message): void
    {
        Log::info('[NullComms] SMS queued', [
            'to' => $message->to,
            'body' => $message->body,
            'template' => $message->templateKey,
        ]);
    }
}

