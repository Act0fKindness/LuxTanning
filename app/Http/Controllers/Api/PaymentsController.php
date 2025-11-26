<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class PaymentsController extends BaseApiController
{
    public function intent(Request $request) { return $this->notImplemented('Create payment intent'); }
    public function index(Request $request) { return $this->ok(['items' => []]); }
    public function retry(Request $request, string $id) { return $this->ok(); }
    public function refund(Request $request, string $id) { return $this->ok(); }
}

