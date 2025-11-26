<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class CustomersController extends BaseApiController
{
    public function index(Request $request) { return $this->ok(['items' => []]); }
    public function store(Request $request) { return $this->notImplemented('Create customer'); }
    public function update(Request $request, string $id) { return $this->notImplemented('Update customer'); }
    public function destroy(Request $request, string $id) { return $this->ok(); }
}

