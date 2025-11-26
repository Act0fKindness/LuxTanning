<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\UsesUuid;

class Address extends Model
{
    use SoftDeletes, UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'tenant_id','user_id','line1','line2','city','county','postcode','lat','lng','access_notes','door_code'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
