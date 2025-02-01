<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_id',
        'name',
        'description'
    ];
    
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function userPlatformPermissions()
    {
        return $this->hasMany(UserPlatformPermission::class);
    }
    
}
