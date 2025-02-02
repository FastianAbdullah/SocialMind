<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    protected $fillable = [
        'name',
        'seed_hashtag',
        'platform_id',
        'count',
        'is_seed',
        'last_fetched_at'
    ];

    protected $casts = [
        'is_seed' => 'boolean',
        'last_fetched_at' => 'datetime',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    // Get formatted hashtag with # prefix
    public function getFormattedNameAttribute()
    {
        return "#{$this->name}";
    }

    // Scope to get related hashtags for a seed
    public function scopeRelatedTo($query, $seedHashtag)
    {
        return $query->where('seed_hashtag', $seedHashtag)
                    ->orderBy('count', 'desc');
    }

    // Store trending hashtags from API response
    public static function storeTrendingHashtags($platformId, $response)
    {
        $seedHashtag = $response['seed_hashtag'];
        $now = now();

        // Store or update seed hashtag
        self::updateOrCreate(
            ['name' => $seedHashtag, 'platform_id' => $platformId],
            [
                'is_seed' => true,
                'last_fetched_at' => $now
            ]
        );

        // Store trending hashtags
        foreach ($response['trending_hashtags'] as $tag) {
            $hashtagName = ltrim($tag['hashtag'], '#'); // Remove # prefix
            
            self::updateOrCreate(
                [
                    'name' => $hashtagName,
                    'platform_id' => $platformId,
                    'seed_hashtag' => $seedHashtag
                ],
                [
                    'count' => $tag['count'],
                    'last_fetched_at' => $now
                ]
            );
        }
    }
} 