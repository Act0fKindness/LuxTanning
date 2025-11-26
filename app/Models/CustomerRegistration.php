<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CustomerRegistration extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'address_source', 'place_id', 'lat', 'lng', 'mapbox_place',
        'address_line1', 'address_line2', 'city', 'postcode',
        'property_type', 'storeys', 'frequency', 'access_notes', 'sms_ok',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'sms_ok' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}

