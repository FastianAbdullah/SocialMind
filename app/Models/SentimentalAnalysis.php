<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentimentalAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'score',
        'desc'
    ];

    //Because a post can have single sentimental analysis, we can get the sentimental analysis from the post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

}
