<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class PayoutsController extends BaseApiController
{
    public function index(Request $request) { return $this->ok(['items' => []]); }
}

