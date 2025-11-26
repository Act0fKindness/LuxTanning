<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationPing extends Model
{
    use UsesUuid;

    protected $fillable = [
        'tracking_session_id',
        'lat',
        'lng',
        'accuracy',
        'ts',
    ];

    protected $casts = [
        'ts' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TrackingSession::class, 'tracking_session_id');
    }
}
