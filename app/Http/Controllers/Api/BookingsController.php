<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class BookingsController extends BaseApiController
{
    public function store(Request $request) { return $this->notImplemented('Confirm booking'); }
    public function cancel(Request $request, string $id) { return $this->ok(); }
}

