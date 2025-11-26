<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\UsesUuid;

class Payment extends Model
{
    use SoftDeletes, UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tenant_id','job_id','invoice_id','method','amount_pence','application_fee_pence','processor_fee_pence','stripe_charge_id','status','attempts','last_error'
    ];
}

