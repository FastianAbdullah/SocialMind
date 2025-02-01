<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform_id',
        'initial_description',
        'AI_generated_description',
        'status',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    //Because a post can have one sentimental analysis, we can get the sentimental analysis from the post
    public function sentimentalAnalysis()
    {
        return $this->hasOne(SentimentalAnalysis::class);
    }

    //Because a post can have many media attachment, we can get the media attachment from the post
    public function mediaAttachments(){
        return $this->hasMany(MediaAttachment::class);
    }

    //Because a post can have one scheduler, we can get the scheduler from the post
    public function scheduler()
    {
        return $this->hasOne(Schedular::class);
    }

}
