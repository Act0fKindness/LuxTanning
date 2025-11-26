<?php

namespace App\Http\Controllers\Api\Hub;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;

class TenantsController extends BaseApiController
{
    public function index(Request $request) { return $this->ok(['items' => []]); }
    public function store(Request $request) { return $this->notImplemented('Provision tenant'); }
    public function update(Request $request, string $id) { return $this->notImplemented('Update tenant'); }
    public function impersonate(Request $request, string $id) { return $this->notImplemented('Impersonate tenant'); }
}

