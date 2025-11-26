<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class RoutesController extends BaseApiController
{
    public function index(Request $request) { return $this->ok(['items' => []]); }
    public function optimise(Request $request) { return $this->notImplemented('Optimise routes'); }
}

