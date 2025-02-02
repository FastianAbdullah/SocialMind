<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPage extends Model
{
    protected $fillable = [
        'user_platform_id',
        'page_id',
        'name',
        'type',
        'metadata',
        'is_active'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    public function userPlatform()
    {
        return $this->belongsTo(UserPlatform::class);
    }
} 