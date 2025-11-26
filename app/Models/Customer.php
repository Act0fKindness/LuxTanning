<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\UsesUuid;

class Customer extends Model
{
    use SoftDeletes, UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tenant_id','name','email','phone','marketing_opt_in','tags','notes','default_address_id'
    ];
    protected $casts = [
        'tags' => 'array',
        'marketing_opt_in' => 'boolean',
    ];
}

