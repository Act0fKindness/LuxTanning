<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class SubscriptionsController extends BaseApiController
{
    public function store(Request $request) { return $this->notImplemented('Create subscription'); }
    public function update(Request $request, string $id) { return $this->notImplemented('Update subscription'); }
    public function destroy(Request $request, string $id) { return $this->ok(); }
}

