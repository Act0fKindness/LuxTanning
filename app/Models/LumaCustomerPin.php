<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LumaCustomerPin extends Model
{
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_id',
        'pin_hash',
    ];
}
