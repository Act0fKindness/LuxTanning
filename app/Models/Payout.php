<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesUuid;

class Payout extends Model
{
    use UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tenant_id','stripe_payout_id','period_start','period_end','amount_pence','fee_pence','status','report_url'
    ];
    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];
}

