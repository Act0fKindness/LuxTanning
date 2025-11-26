<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class AddressesController extends BaseApiController
{
    public function store(Request $request, string $id) { return $this->notImplemented('Add address'); }
    public function update(Request $request, string $id) { return $this->notImplemented('Update address'); }
}

