<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseApiController
{
    public function login(Request $request)
    {
        return $this->notImplemented('JWT login to be implemented');
    }

    public function refresh(Request $request)
    {
        return $this->notImplemented('JWT refresh to be implemented');
    }

    public function logout(Request $request)
    {
        return $this->ok();
    }
}

