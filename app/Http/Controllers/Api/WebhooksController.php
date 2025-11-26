<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class WebhooksController extends BaseApiController
{
    public function stripe(Request $request) { return $this->ok(); }
    public function bacs(Request $request) { return $this->ok(); }
}

