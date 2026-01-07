<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use SoftDeletes, UsesUuid;

    protected $table = 'tenants';
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
        'trading_name',
        'legal_name',
        'company_reg_number',
        'vat_number',
        'hq_address_line1',
        'hq_address_line2',
        'hq_city',
        'hq_county',
        'hq_postcode',
        'hq_country_code',
        'contact_email',
        'contact_phone',
        'support_email',
        'support_phone',
        'website_url',
        'social_links_json',
        'default_currency',
        'default_timezone',
        'default_language',
        'brand_json',
        'logo_media_id',
        'favicon_media_id',
        'receipt_logo_media_id',
        'plan_key',
        'plan_status',
        'trial_ends_at',
        'billing_provider',
        'billing_customer_id',
        'onboarding_step',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'theme_json' => 'array',
        'brand_json' => 'array',
        'social_links_json' => 'array',
        'service_area_center_lat' => 'float',
        'service_area_center_lng' => 'float',
        'service_area_radius_km' => 'float',
        'trial_ends_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function shops()
    {
        return $this->hasMany(Shop::class, 'tenant_id');
    }

    public function logo()
    {
        return $this->belongsTo(GlintMedia::class, 'logo_media_id');
    }

    public function favicon()
    {
        return $this->belongsTo(GlintMedia::class, 'favicon_media_id');
    }

    public function receiptLogo()
    {
        return $this->belongsTo(GlintMedia::class, 'receipt_logo_media_id');
    }
}
