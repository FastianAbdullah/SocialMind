<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedular extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'datetime',
        'status'
    ];

    protected $casts = [
        'datetime' => 'datetime'
    ];

    //Because a post can have one scheduler, we can get the scheduler from the post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    
}
