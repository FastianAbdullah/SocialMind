<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    // Because a platform can have many users, we can get the users from the platform
    public function users(){
        return $this->belongsToMany(User::class, 'user_platform');
    }

    // Because a platform can have many posts, we can get the posts from the platform
    public function posts(){
        return $this->hasMany(Post::class);
    }

    // Because a platform can have many platform permissions, we can get the platform permissions from the platform
    public function platformPermissions()
    {
        return $this->hasMany(PlatformPermission::class);
    }
    
}
