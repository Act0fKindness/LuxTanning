<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LumaKioskSession extends Model
{
    protected $fillable = [
        'device_id',
        'tenant_id',
        'shop_id',
        'events_json',
        'duration_seconds',
    ];

    protected $casts = [
        'events_json' => 'array',
        'duration_seconds' => 'integer',
    ];
}
