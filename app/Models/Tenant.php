<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\UsesUuid;

class Tenant extends Model
{
    use SoftDeletes, UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'country',
        'status',
        'theme_json',
        'fee_tier',
        'vat_scheme',
        'service_area_label',
        'service_area_place_id',
        'service_area_center_lat',
        'service_area_center_lng',
        'service_area_radius_km',
    ];
    protected $casts = [
        'theme_json' => 'array',
        'service_area_center_lat' => 'float',
        'service_area_center_lng' => 'float',
        'service_area_radius_km' => 'float',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')->withPivot(['role','status'])->withTimestamps();
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
