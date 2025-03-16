<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedular;

class PostSchedulerController extends PostManagerController
{
    /**
     * Schedule posts for later publishing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function schedule(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'platforms' => 'required|json',
                'initial_post_description' => 'required|string',
                'scheduled_time' => 'required|string',
                'use_ai_suggestion' => 'sometimes|boolean',
            ]);
            
            // Decode platforms data
            $platformsData = json_decode($request->input('platforms'), true);
            $initialPostDescription = $request->input('initial_post_description');
            $scheduledTime = $request->input('scheduled_time');
            $useAiSuggestion = $request->input('use_ai_suggestion', false);
            
            if (!is_array($platformsData) || empty($platformsData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No platforms selected for scheduling'
                ], 400);
            }
            
            $userId = Auth::id();
            $results = [];
            
            // Process each platform
            foreach ($platformsData as $platform) {
                $platformId = $platform['platform_id'] ?? null;
                $platformContent = $platform['content'] ?? $initialPostDescription;
                
                if (!$platformId) {
                    continue;
                }
                
                // Get user platform data using parent method
                $userPlatform = $this->getUserPlatform($userId, $platformId);
                
                if (!$userPlatform) {
                    $results[$platformId] = [
                        'success' => false,
                        'message' => 'Platform not connected or missing access token'
                    ];
                    continue;
                }
                
                // For Facebook and Instagram, get the page using parent method
                $platformPage = null;
                if (in_array($platformId, [1, 2])) {
                    $platformPage = $this->getPlatformPage($userPlatform->id);
                    
                    if (!$platformPage) {
                        $results[$platformId] = [
                            'success' => false,
                            'message' => 'Selected page not found'
                        ];
                        continue;
                    }
                }
                
                // Check for media file
                $mediaFile = $request->file("media_{$platformId}");
                
                // Save platform-specific data in metadata
                $metadata = [
                    'platform_id' => $platformId,
                    'platform_name' => $this->getPlatformName($platformId),
                    'use_ai_suggestion' => $useAiSuggestion,
                    'original_scheduled_time' => $scheduledTime,
                    'user_platform_id' => $userPlatform->id,
                    'access_token' => $userPlatform->access_token
                ];
                
                // Add page info if available
                if ($platformPage) {
                    $metadata['platform_page_id'] = $platformPage->page_id;
                    $metadata['platform_page_name'] = $platformPage->name;
                }
                
                // Create a post record using parent method
                $post = $this->createPostRecord(
                    $userId,
                    $platformId,
                    $initialPostDescription,
                    $platformContent,
                    'scheduled',
                    null,
                    $metadata
                );
                
                // Save media if present
                if ($mediaFile) {
                    // Update post metadata with media info
                    $updatedMetadata = json_decode($post->metadata, true);
                    $post->metadata = json_encode($updatedMetadata);
                    $post->save();
                }
                
                // Create scheduler record
                $scheduler = new Schedular();
                $scheduler->post_id = $post->id;
                $scheduler->scheduled_at = $scheduledTime;
                $scheduler->status = 'pending';
                $scheduler->setTable('scheduler');
                $scheduler->save();
                
                // Add to results
                $results[$platformId] = [
                    'success' => true,
                    'message' => 'Post scheduled successfully',
                    'platform_id' => $platformId,
                    'platform_name' => $this->getPlatformName($platformId),
                    'scheduled_at' => $scheduledTime,
                    'post_id' => $post->id,
                    'scheduler_id' => $scheduler->id
                ];
                
                Log::info('Post scheduled', [
                    'user_id' => $userId,
                    'platform_id' => $platformId,
                    'post_id' => $post->id,
                    'scheduler_id' => $scheduler->id,
                    'scheduled_time' => $scheduledTime,
                    'has_media' => $mediaFile ? 'yes' : 'no'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Post scheduling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule posts: ' . $e->getMessage()
            ], 500);
        }
    }
} 