<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthLoginEvent extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'success',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];
}
