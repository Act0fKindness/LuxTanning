<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'shop_id',
        'name',
        'identifier',
        'status',
        'pairing_code',
        'pairing_code_expires_at',
        'paired_at',
        'revoked_at',
        'last_seen_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'pairing_code_expires_at' => 'datetime',
        'paired_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'tenant_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
