<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlintAuditLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'shop_id',
        'actor_type',
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'before_json',
        'after_json',
        'context_json',
    ];

    protected $casts = [
        'before_json' => 'array',
        'after_json' => 'array',
        'context_json' => 'array',
    ];
}
