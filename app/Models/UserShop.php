<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserShop extends Model
{
    protected $fillable = [
        'user_id',
        'shop_id',
    ];
}
