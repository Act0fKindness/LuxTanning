<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use SoftDeletes, UsesUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'address_id',
        'status',
        'channel',
        'source',
        'quote_json',
        'deposit_pence',
        'tcs_accepted_at',
        'booking_number',
    ];

    protected $casts = [
        'quote_json' => 'array',
        'tcs_accepted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (!$booking->booking_number) {
                $booking->booking_number = static::generateBookingNumber($booking->tenant_id);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    protected static function generateBookingNumber(?string $context): string
    {
        $prefix = $context ? strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $context), 0, 4)) : 'GLNT';
        if ($prefix === '') {
            $prefix = 'GLNT';
        } elseif (strlen($prefix) < 4) {
            $prefix = str_pad($prefix, 4, 'X');
        }

        do {
            $candidate = sprintf('%s-%s', $prefix, strtoupper(Str::random(6)));
        } while (static::where('booking_number', $candidate)->exists());

        return $candidate;
    }
}

