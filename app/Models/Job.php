<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\UsesUuid;

class Job extends Model
{
    use SoftDeletes, UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tenant_id','booking_id','subscription_id','route_id','staff_user_id','date','eta_window','status','sequence','checklist_json','required_photos','no_access_fee_pence','started_at','completed_at','cancelled_at','actual_minutes','last_lat','last_lng','last_location_at'
    ];
    protected $casts = [
        'date' => 'date',
        'checklist_json' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_location_at' => 'datetime',
        'last_lat' => 'float',
        'last_lng' => 'float',
        'actual_minutes' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }
}
