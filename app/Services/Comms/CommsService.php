<?php

namespace App\Services\Comms;

interface CommsService
{
    public function sendEmail(MessageDTO $message): void;
    public function sendSms(MessageDTO $message): void;
}

