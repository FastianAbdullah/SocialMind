<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'post_type',
        'file_path',
        'alt_text',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    //Because a post can have multiple media attachments, we can get the media attachments from the post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

}
