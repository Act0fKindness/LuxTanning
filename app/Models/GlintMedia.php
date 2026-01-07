<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlintMedia extends Model
{
    protected $table = 'glint_media';
    protected $fillable = [
        'tenant_id',
        'shop_id',
        'purpose',
        'disk',
        'storage_path',
        'original_filename',
        'mime',
        'size_bytes',
        'width',
        'height',
        'sha256',
        'created_by_user_id',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'tenant_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
