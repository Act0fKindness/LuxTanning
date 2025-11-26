<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Comms\CommsService;
use App\Services\Comms\MessageDTO;

class MessagesController extends BaseApiController
{
    public function __construct(private CommsService $comms) {}

    public function email(Request $request)
    {
        $dto = new MessageDTO('email', $request->string('to'), $request->string('subject'), $request->string('body'));
        $this->comms->sendEmail($dto);
        return $this->ok();
    }

    public function sms(Request $request)
    {
        $dto = new MessageDTO('sms', $request->string('to'), body: $request->string('body'));
        $this->comms->sendSms($dto);
        return $this->ok();
    }
}

