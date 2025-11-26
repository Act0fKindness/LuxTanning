<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UsesUuid;

class TenantUser extends Model
{
    use UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'tenant_user';

    protected $fillable = [
        'tenant_id','user_id','role','status'
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

