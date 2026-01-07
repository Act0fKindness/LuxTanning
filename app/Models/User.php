<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'role',
        'status',
        'password',
        'must_change_password',
        'primary_shop_id',
        'shop_access_mode',
        'invited_by_user_id',
        'invite_token_hash',
        'invite_expires_at',
        'invite_accepted_at',
        'last_login_at',
        'last_login_ip',
        'mfa_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'must_change_password' => 'boolean',
        'invite_expires_at' => 'datetime',
        'invite_accepted_at' => 'datetime',
        'last_login_at' => 'datetime',
        'mfa_enabled' => 'boolean',
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

    // Relationships
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'tenant_id');
    }

    public function shops()
    {
        return $this->belongsToMany(Shop::class, 'user_shops')->withTimestamps();
    }

    public function primaryShop()
    {
        return $this->belongsTo(Shop::class, 'primary_shop_id');
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function scopeForOrganisation($query, string $organisationId)
    {
        return $query->where('tenant_id', $organisationId);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canAccessShop(?string $shopId): bool
    {
        if (! $shopId) {
            return true;
        }

        if ($this->shop_access_mode === 'all') {
            return true;
        }

        if ($this->shop_access_mode === 'single') {
            return (string) $this->primary_shop_id === (string) $shopId;
        }

        return $this->shops()->where('shops.id', $shopId)->exists();
    }
}
