<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class TrackingController extends BaseApiController
{
    public function show(Request $request, string $share_token) { return $this->ok(['share_token' => $share_token]); }
}

