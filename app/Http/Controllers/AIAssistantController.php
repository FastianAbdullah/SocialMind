<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\UserPlatform;
use App\Models\PlatformPage;
use Illuminate\Support\Facades\Auth;

class AIAssistantController extends Controller
{
    protected $flaskBaseUrl;

    public function __construct()
    {
        $this->flaskBaseUrl = env('FLASK_API_URL', 'https://localhost:8443');
        
        // Add fallback URL configuration and logging
        try {
            // Test if the primary Flask API is available using root endpoint instead of non-existent /health
            $testResponse = Http::withoutVerifying()
                ->timeout(5)
                ->get($this->flaskBaseUrl . '/');
                
            if (!$testResponse->successful()) {
                Log::warning('Primary Flask API not responding, checking fallback', [
                    'primary_url' => $this->flaskBaseUrl,
                    'response_status' => $testResponse->status()
                ]);
                
                // Try fallback URL if configured
                $fallbackUrl = env('FLASK_API_URL_FALLBACK');
                if ($fallbackUrl) {
                    $fallbackResponse = Http::withoutVerifying()
                        ->timeout(5)
                        ->get($fallbackUrl . '/');
                        
                    if ($fallbackResponse->successful()) {
                        Log::info('Switching to fallback Flask API URL', [
                            'fallback_url' => $fallbackUrl
                        ]);
                        $this->flaskBaseUrl = $fallbackUrl;
                    }
                }
            }
        } catch (\Exception $e) {
            // Log exception but don't fail constructor
            Log::warning('Error checking Flask API availability', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get appropriate access token for API calls
     *
     * @return array ['access_token' => string, 'platform_id' => int|null, 'user_platform_id' => int|null, 'page_id' => string|null]
     */
    private function getAccessToken()
    {
        $userId = Auth::id();
        $accessToken = null;
        $platformId = null;
        $userPlatformId = null;
        $pageId = null;
        
        // Check if user has Instagram connection first (platform_id = 2)
        $userPlatform = UserPlatform::where('user_id', $userId)
            ->where('platform_id', 2) // Instagram platform_id
            ->first();
            
        if ($userPlatform && $userPlatform->access_token) {
            Log::info('Using user\'s Instagram token for AI assistant', [
                'user_id' => $userId
            ]);
            $accessToken = $userPlatform->access_token;
            $platformId = 2;
            $userPlatformId = $userPlatform->id;
            
            // Get page ID if available
            $page = PlatformPage::where('user_platform_id', $userPlatform->id)
                ->where('is_active', 1)
                ->first();
                
            if ($page) {
                $pageId = $page->page_id;
            }
            
            return [
                'access_token' => $accessToken,
                'platform_id' => $platformId,
                'user_platform_id' => $userPlatformId,
                'page_id' => $pageId
            ];
        }
        
        // Try Facebook if Instagram is not available (platform_id = 1)
        $userPlatform = UserPlatform::where('user_id', $userId)
            ->where('platform_id', 1) // Facebook platform_id
            ->first();
            
        if ($userPlatform && $userPlatform->access_token) {
            Log::info('Using user\'s Facebook token for AI assistant', [
                'user_id' => $userId
            ]);
            $accessToken = $userPlatform->access_token;
            $platformId = 1;
            $userPlatformId = $userPlatform->id;
            
            // Get page ID if available
            $page = PlatformPage::where('user_platform_id', $userPlatform->id)
                ->where('is_active', 1)
                ->first();
                
            if ($page) {
                $pageId = $page->page_id;
            }
            
            return [
                'access_token' => $accessToken,
                'platform_id' => $platformId,
                'user_platform_id' => $userPlatformId,
                'page_id' => $pageId
            ];
        }
        
        // Try LinkedIn if others are not available (platform_id = 3)
        $userPlatform = UserPlatform::where('user_id', $userId)
            ->where('platform_id', 3) // LinkedIn platform_id
            ->first();
            
        if ($userPlatform && $userPlatform->access_token) {
            Log::info('Using user\'s LinkedIn token for AI assistant', [
                'user_id' => $userId
            ]);
            $accessToken = $userPlatform->access_token;
            $platformId = 3;
            $userPlatformId = $userPlatform->id;
            
            return [
                'access_token' => $accessToken,
                'platform_id' => $platformId,
                'user_platform_id' => $userPlatformId,
                'page_id' => null // LinkedIn doesn't use page_id
            ];
        }
        
        // Fallback to admin user only if no user token is available
        Log::info('No user tokens available, falling back to admin for AI assistant', [
            'user_id' => $userId
        ]);
        
        $adminUser = User::where('name', 'admin')->first();
        
        if (!$adminUser) {
            Log::error('Admin user not found for AI Assistant', [
                'user_id' => $userId
            ]);
            
            return [
                'access_token' => null,
                'platform_id' => null,
                'user_platform_id' => null,
                'page_id' => null,
                'error' => 'You need to connect at least one social media account to use this feature.'
            ];
        }
        
        // Get admin's Instagram platform for authorization
        $instagramPlatform = UserPlatform::where('user_id', $adminUser->id)
            ->where('platform_id', 2) // Instagram platform_id
            ->first();
            
        if (!$instagramPlatform || !$instagramPlatform->access_token) {
            Log::error('Admin Instagram platform not found', [
                'user_id' => $userId,
                'admin_id' => $adminUser->id
            ]);
            
            return [
                'access_token' => null,
                'platform_id' => null,
                'user_platform_id' => null,
                'page_id' => null,
                'error' => 'Please connect at least one social media account to use this feature.'
            ];
        }
        
        // Also get page ID if available
        $page = PlatformPage::where('user_platform_id', $instagramPlatform->id)
            ->where('is_active', 1)
            ->first();
            
        if ($page) {
            $pageId = $page->page_id;
        }
        
        return [
            'access_token' => $instagramPlatform->access_token,
            'platform_id' => 2,
            'user_platform_id' => $instagramPlatform->id,
            'page_id' => $pageId
        ];
    }

    /**
     * Process a query through the AI Assistant
     */
    public function processQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'context' => 'nullable|array'
        ]);

        try {
            // Get appropriate access token
            $tokenData = $this->getAccessToken();
            
            if (!$tokenData['access_token']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $tokenData['error'],
                    'intent' => 'error'
                ], 400);
            }
            
            // Now make the API request with the selected token
            $response = Http::withoutVerifying() // For development with self-signed cert
                ->withHeaders([
                    'Authorization' => $tokenData['access_token'],
                    'Content-Type' => 'application/json'
                ])
                ->timeout(60) // Add longer timeout
                ->post($this->flaskBaseUrl . '/agent/query', [
                    'query' => $request->input('query'),
                    'context' => $request->input('context', []),
                    'autonomous_mode' => true // Enable autonomous mode
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Error from Python agent', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error from Python agent: ' . $response->body(),
                    'intent' => 'error'
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error processing query', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing query: ' . $e->getMessage(),
                'intent' => 'error'
            ], 500);
        }
    }

    /**
     * Generate content using the AI Assistant
     */
    public function generateContent(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|min:2',
            'platform' => 'nullable|string',
            'context' => 'nullable|array'
        ]);

        try {
            // Get appropriate access token
            $tokenData = $this->getAccessToken();
            
            if (!$tokenData['access_token']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $tokenData['error'],
                    'intent' => 'error'
                ], 400);
            }
            
            // Make request to Flask API with proper authorization
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $tokenData['access_token'],
                    'Content-Type' => 'application/json'
                ])
                ->timeout(60) // Add longer timeout
                ->post($this->flaskBaseUrl . '/content/generate-optimized', [
                    'text' => $request->input('topic'),
                    'platform' => $request->input('platform'),
                    'context' => $request->input('context', []),
                    'ig_user_id' => $tokenData['page_id'], // Use page ID if available
                    'autonomous_mode' => true
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Error from content generation API', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error from content generation API: ' . $response->body(),
                    'intent' => 'error'
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error generating content', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating content: ' . $e->getMessage(),
                'intent' => 'error'
            ], 500);
        }
    }

    /**
     * Post content to selected platforms
     */
    public function postContent(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:2',
            'platforms' => 'required|array|min:1',
            'schedule_time' => 'nullable|string',
            'context' => 'nullable|array'
        ]);

        try {
            // Get appropriate access token
            $tokenData = $this->getAccessToken();
            
            if (!$tokenData['access_token']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $tokenData['error'],
                    'intent' => 'error'
                ], 400);
            }

            // Get list of connected platforms for this user
            $userId = Auth::id();
            $connectedPlatforms = UserPlatform::where('user_id', $userId)
                ->where('access_token', '!=', null)
                ->pluck('platform_id')
                ->toArray();
            
            Log::info('Connected platforms for user', [
                'user_id' => $userId,
                'connected_platforms' => $connectedPlatforms
            ]);
            
            // Filter out platforms that aren't connected
            $platforms = $request->input('platforms');
            $validPlatforms = [];
            
            foreach ($platforms as $platform) {
                // Ensure platform data is an array with required fields
                if (!is_array($platform) || !isset($platform['platform_id'])) {
                    Log::error('Invalid platform data structure', [
                        'platform' => $platform,
                        'user_id' => Auth::id()
                    ]);
                    continue;
                }
                
                $platformId = (int)$platform['platform_id'];
                
                // Only include platforms that are connected
                if (in_array($platformId, $connectedPlatforms)) {
                    $validPlatforms[] = $platform;
                } else {
                    Log::warning('Skipping disconnected platform', [
                        'platform_id' => $platformId,
                        'user_id' => $userId
                    ]);
                }
            }
            
            if (empty($validPlatforms)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'None of the selected platforms are connected. Please connect at least one platform.',
                    'intent' => 'error'
                ], 400);
            }

            // Map platform IDs to platform names for the API
            $platformMapping = [
                1 => 'facebook',
                2 => 'instagram',
                3 => 'linkedin'
                // Twitter would be here if implemented
            ];
            
            // Reverse mapping for looking up platform IDs from names
            $platformNameToId = array_flip($platformMapping);
            
            // Convert our platform IDs to platform names for the Flask API
            $platformNames = [];
            $platformIdMap = []; // Map to track which platform ID corresponds to which name
            
            foreach ($validPlatforms as $platform) {
                $platformId = (int)$platform['platform_id'];
                if (isset($platformMapping[$platformId])) {
                    $platformName = $platformMapping[$platformId];
                    $platformNames[] = $platformName;
                    $platformIdMap[$platformName] = $platformId;
                }
            }
            
            Log::info('Sending platforms to Flask API', [
                'platform_names' => $platformNames,
                'platform_id_map' => $platformIdMap
            ]);

            // Create initial post records for each platform
            $postRecords = [];
            
            foreach ($validPlatforms as $platform) {
                $platformId = (int)$platform['platform_id'];
                
                // Create initial post record
                $post = new \App\Models\Post();
                $post->user_id = Auth::id();
                $post->platform_id = $platformId;
                
                // Get the original description from context if available
                $context = $request->input('context', []);
                $originalDescription = $this->extractOriginalPromptFromContext($context);
                
                // If we couldn't find an original description, use a placeholder
                if (empty($originalDescription)) {
                    $originalDescription = 'User requested content generation';
                }
                
                // Store the original description and AI-generated content in appropriate columns
                $post->initial_description = substr($originalDescription, 0, 250) . (strlen($originalDescription) > 250 ? '...' : '');
                $post->AI_generated_description = $request->input('content'); // Full AI-generated content
                
                $post->status = $request->input('schedule_time') ? 'scheduled' : 'pending';
                $post->metadata = json_encode([
                    'context' => $request->input('context', []),
                    'schedule_time' => $request->input('schedule_time'),
                    'platform_data' => $platform
                ]);
                $post->setTable('post');
                $post->save();
                
                // Store by platform name for easier lookup when processing response
                $platformName = $platformMapping[$platformId] ?? null;
                if ($platformName) {
                    $postRecords[$platformName] = $post;
                }

                // If post is scheduled, create scheduler record
                if ($request->input('schedule_time')) {
                    $scheduler = new \App\Models\Schedular();
                    $scheduler->post_id = $post->id;
                    $scheduler->scheduled_time = $request->input('schedule_time');
                    $scheduler->status = 'pending';
                    $scheduler->save();
                }
                
                Log::info('Created post record', [
                    'post_id' => $post->id,
                    'platform_id' => $platformId,
                    'platform_name' => $platformName,
                    'user_id' => Auth::id()
                ]);
            }

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $tokenData['access_token'],
                    'Content-Type' => 'application/json'
                ])
                ->timeout(60)
                ->post($this->flaskBaseUrl . '/agent/post-content', [
                    'content' => $request->input('content'),
                    'platforms' => $platformNames, // Send platform names instead of the original array
                    'schedule_time' => $request->input('schedule_time'),
                    'context' => $request->input('context', []),
                    'autonomous_mode' => true
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Response from Flask API', [
                    'response' => $responseData
                ]);
                
                // Update post records with response data
                if (isset($responseData['results']) && is_array($responseData['results'])) {
                    foreach ($responseData['results'] as $platformName => $details) {
                        // Check if we have a post record for this platform
                        if (isset($postRecords[$platformName])) {
                            $post = $postRecords[$platformName];
                            
                            if ($details['status'] === 'success') {
                                $post->response_post_id = $details['post_id'] ?? null;
                                // Don't overwrite the AI_generated_description as we've already set it
                                // $post->AI_generated_description = $request->input('content');
                                $post->status = 'published';
                                
                                // Update metadata with response details
                                $metadata = json_decode($post->metadata, true) ?? [];
                                $metadata['published_at'] = now()->toDateTimeString();
                                $metadata['response_details'] = $details;
                                $post->metadata = json_encode($metadata);
                                
                                $post->save();
                                
                                Log::info('Post published successfully', [
                                    'post_id' => $post->id,
                                    'platform_name' => $platformName,
                                    'response_post_id' => $post->response_post_id
                                ]);
                            } else {
                                // Mark as failed if the API call succeeded but posting failed
                                $post->status = 'failed';
                                
                                // Update metadata with error details
                                $metadata = json_decode($post->metadata, true) ?? [];
                                $metadata['error_details'] = $details['message'] ?? 'Unknown error';
                                $post->metadata = json_encode($metadata);
                                
                                $post->save();
                                
                                Log::error('Post failed', [
                                    'post_id' => $post->id,
                                    'platform_name' => $platformName,
                                    'error' => $details['message'] ?? 'Unknown error'
                                ]);
                            }
                        } else {
                            Log::warning('Received response for platform with no post record', [
                                'platform_name' => $platformName
                            ]);
                        }
                    }
                } else {
                    Log::warning('No results in response', [
                        'response_data' => $responseData
                    ]);
                    
                    // Mark all posts as failed if no results were returned
                    foreach ($postRecords as $post) {
                        $post->status = 'failed';
                        $post->save();
                    }
                }
                
                return response()->json($responseData);
            } else {
                // Mark posts as failed
                foreach ($postRecords as $post) {
                    $post->status = 'failed';
                    $post->save();
                }

                Log::error('Error from posting API', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error from posting API: ' . $response->body(),
                    'intent' => 'error'
                ], $response->status());
            }
        } catch (\Exception $e) {
            // Mark posts as failed if they exist
            if (isset($postRecords)) {
                foreach ($postRecords as $post) {
                    $post->status = 'failed';
                    $post->save();
                }
            }

            Log::error('Error posting content', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error posting content: ' . $e->getMessage(),
                'intent' => 'error'
            ], 500);
        }
    }

    /**
     * Get posting time suggestions
     */
    public function suggestPostingTimes($platform)
    {
        try {
            // Get appropriate access token
            $tokenData = $this->getAccessToken();
            
            if (!$tokenData['access_token']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $tokenData['error'],
                    'intent' => 'error'
                ], 400);
            }

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $tokenData['access_token'],
                    'Content-Type' => 'application/json'
                ])
                ->timeout(60) // Add longer timeout
                ->get($this->flaskBaseUrl . '/agent/suggest-times/' . $platform, [
                    'autonomous_mode' => true
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Error from timing API', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'platform' => $platform,
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error from timing API: ' . $response->body(),
                    'intent' => 'error'
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error getting posting times', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'platform' => $platform,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error getting posting times: ' . $e->getMessage(),
                'intent' => 'error'
            ], 500);
        }
    }

    /**
     * Analyze post performance
     */
    public function analyzePerformance(Request $request)
    {
        $request->validate([
            'post_id' => 'required|string',
            'platform' => 'required|string'
        ]);

        try {
            // Get appropriate access token
            $tokenData = $this->getAccessToken();
            
            if (!$tokenData['access_token']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $tokenData['error'],
                    'intent' => 'error'
                ], 400);
            }

            // Call the sentiment analysis endpoint in Flask with admin token
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'Authorization' => $tokenData['access_token']
                ])
                ->post($this->flaskBaseUrl . '/post/sentiment-analysis', [
                    'post_id' => $request->input('post_id'),
                    'platform' => $request->input('platform')
                ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Error analyzing performance', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'platform' => $request->input('platform'),
                    'post_id' => $request->input('post_id'),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error analyzing performance: ' . $response->body(),
                    'intent' => 'error'
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Error analyzing performance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'platform' => $request->input('platform'),
                'post_id' => $request->input('post_id'),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error analyzing performance: ' . $e->getMessage(),
                'intent' => 'error'
            ], 500);
        }
    }

    /**
     * Extract the original prompt/topic from the context
     * 
     * @param array $context The context array from the request
     * @return string The original prompt or an empty string if not found
     */
    private function extractOriginalPromptFromContext($context)
    {
        // Look in different possible locations for the original prompt
        
        // Check for originalPrompt directly in context
        if (isset($context['originalPrompt']) && !empty($context['originalPrompt'])) {
            return $context['originalPrompt'];
        }
        
        // Check in currentTask
        if (isset($context['currentTask'])) {
            // Check for originalPrompt in currentTask
            if (isset($context['currentTask']['originalPrompt']) && !empty($context['currentTask']['originalPrompt'])) {
                return $context['currentTask']['originalPrompt'];
            }
            
            // Check for topic in currentTask
            if (isset($context['currentTask']['topic']) && !empty($context['currentTask']['topic'])) {
                return $context['currentTask']['topic'];
            }
            
            // Check for initialPrompt in currentTask
            if (isset($context['currentTask']['initialPrompt']) && !empty($context['currentTask']['initialPrompt'])) {
                return $context['currentTask']['initialPrompt'];
            }
        }
        
        // Check for query in context
        if (isset($context['query']) && !empty($context['query'])) {
            return $context['query'];
        }
        
        // Check if the context has any non-empty string keys at root level
        foreach ($context as $key => $value) {
            if (is_string($value) && !empty($value) && strlen($value) < 500) {
                return $value;
            }
        }
        
        // If nothing found, return empty string
        return '';
    }
} 