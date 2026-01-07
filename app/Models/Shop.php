<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'address_line1',
        'address_line2',
        'city',
        'county',
        'postcode',
        'country_code',
        'timezone',
        'opening_hours_json',
        'emergency_note',
        'shop_brand_override_json',
        'policy_override_json',
        'gallery_media_json',
    ];

    protected $casts = [
        'opening_hours_json' => 'array',
        'shop_brand_override_json' => 'array',
        'policy_override_json' => 'array',
        'gallery_media_json' => 'array',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'tenant_id');
    }

    public function staff()
    {
        return $this->belongsToMany(User::class, 'user_shops');
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
