<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class JobsBoardController extends BaseApiController
{
    public function board(Request $request) { return $this->ok(['items' => []]); }
    public function claim(Request $request, string $id) { return $this->ok(['claimed' => true]); }
}

