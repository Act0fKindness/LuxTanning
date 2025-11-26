<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class ExportsController extends BaseApiController
{
    public function store(Request $request, string $type) { return $this->notImplemented('Create export: '.$type); }
    public function show(Request $request, string $id) { return $this->ok(['id' => $id]); }
}

