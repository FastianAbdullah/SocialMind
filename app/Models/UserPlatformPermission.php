<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlatformPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_platform_id',
        'permission_id',
        'is_granted'
    ];

    protected $casts = [
        'is_granted' => 'boolean'
    ];
    
    public function userPlatform()
    {
        return $this->belongsTo(UserPlatform::class);
    }

    public function platformPermission()
    {
        return $this->belongsTo(PlatformPermission::class, 'permission_id');
    }
       
}
