<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlatform extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform_id',
        'access_token',
        'connected_at'
    ];

    // cast the connected_at to a datetime
    protected $casts = [
        'connected_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function userPlatformPermissions()
    {
        return $this->hasMany(UserPlatformPermission::class);
    }
    
}
