<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class StaffController extends BaseApiController
{
    public function invite(Request $request) { return $this->notImplemented('Invite staff'); }
    public function accept(Request $request) { return $this->ok(); }
}

