<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedular;
use Illuminate\Support\Facades\Http;
use App\Models\MediaAttachment;

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
                    // Create directory for scheduled posts
                    $postDirectory = base_path('Post_Generation/scheduled/' . $post->id);
                    
                    if (!file_exists($postDirectory)) {
                        mkdir($postDirectory, 0755, true);
                    }
                    
                    // Generate a unique filename
                    $filename = time() . '_' . $mediaFile->getClientOriginalName();
                    $mediaFileType = $mediaFile->getMimeType();
                    $mediaFileSize = $mediaFile->getSize();
                    $mediaFileName = $mediaFile->getClientOriginalName();
                    
                    // Move the file to the post's directory
                    $mediaFile->move($postDirectory, $filename);
                    
                    // Get the absolute path for database storage
                    $absolutePath = $postDirectory . '/' . $filename;
                    
                    // Update post metadata with media info
                    $updatedMetadata = json_decode($post->metadata, true);
                    $updatedMetadata['has_media'] = true;
                    $updatedMetadata['media_type'] = $mediaFileType;
                    $updatedMetadata['media_name'] = $mediaFileName;
                    
                    $post->metadata = json_encode($updatedMetadata); //Update Metadata of Saved Post in Database.
                    $post->save();

                    // Save media attachment in Database
                    $mediaAttachment = new MediaAttachment();
                    $mediaAttachment->post_id = $post->id;
                    $mediaAttachment->media_type = $mediaFileType;
                    $mediaAttachment->file_path = $absolutePath;
                    $mediaAttachment->alt_text = $initialPostDescription;
                    $mediaAttachment->metadata = json_encode([
                        'original_name' => $mediaFileName,
                        'size' => $mediaFileSize,
                        'mime_type' => $mediaFileType
                    ]);
                    $mediaAttachment->setTable('media_attachment');
                    $mediaAttachment->save();

                    Log::info('Saved Scheduled Media File', [
                        'post_id' => $post->id,
                        'media_path' => $absolutePath,
                        'media_type' => $mediaFileType,
                        'original_name' => $mediaFileName
                    ]);
                }
                
                // Create scheduler record
                $scheduler = new Schedular();
                $scheduler->post_id = $post->id;
                $scheduler->scheduled_at = $scheduledTime;
                $scheduler->status = 'pending';
                $scheduler->setTable('scheduler');
                $scheduler->save();

                // Call Python scheduler API
                try {
                    // Get all necessary data for the post that Python needs
                    $completePostData = [
                        'AI_generated_description' => $post->AI_generated_description,
                        'access_token' => $userPlatform->access_token,
                        'media_type' => $mediaFile ? $mediaFileType : null,
                        'file_path' => $mediaFile ? $absolutePath : null,
                        'platform_page_id' => $platformPage ? $platformPage->page_id : null,
                        'platform_page_name' => $platformPage ? $platformPage->name : null,
                    ];
                    
                    // Get user's timezone
                    $userTimezone = Auth::user()->timezone ?? 'UTC';
                    
                    $response = Http::withoutVerifying()
                        ->timeout(60)
                        ->withHeaders([
                            'Content-Type' => 'application/json'
                        ])
                        ->post('https://localhost:8443/scheduler/schedule', [
                            'post_id' => $post->id,
                            'platform_id' => $platformId,
                            'scheduled_time' => $scheduledTime,
                            'user_id' => $userId,
                            'timezone' => $userTimezone,
                            'post_data' => $completePostData  // Send all post data here
                        ]);
                    
                    if (!$response->successful()) {
                        Log::error('Python scheduler API error', [
                            'response' => $response->json(),
                            'post_id' => $post->id
                        ]);
                    }

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

                } catch (\Exception $e) {
                    Log::error('Python scheduler API error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'post_id' => $post->id
                    ]);
                }
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

    public function getSchedulerStatus($postId)
    {
        try {
            $response = Http::get("https://localhost:8443/scheduler/status/{$postId}");
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get scheduler status'
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error connecting to scheduler service'
            ], 500);
        }
    }
} 