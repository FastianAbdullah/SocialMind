<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use App\Models\UserPlatform;
use App\Models\PlatformPage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class PostManagerController extends Controller
{
    /**
     * Create a new post by analyzing content and generating optimized versions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                'content' => 'required|string|min:5',
            ]);

            $content = $request->input('content');
            
            // Step 1: Analyze the content to get purpose and hashtags
            $analysisResponse = Http::withoutVerifying()->timeout(80)
                ->post('https://localhost:8443/content/analyze', [
                    'text' => $content
                ]);
                
            if ($analysisResponse->failed()) {
                Log::error('Content analysis failed', [
                    'status' => $analysisResponse->status(),
                    'response' => $analysisResponse->body(),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to analyze content'
                ], 500);
            }
            
            $analysisData = $analysisResponse->json();
            $purpose = $analysisData['purpose'] ?? 'informational';
            $hashtags = $analysisData['hashtags'] ?? [];
            
            // Find admin user for Instagram API calls
            $adminUser = User::where('name', 'admin')->first();
            
            if (!$adminUser) {
                Log::error('Admin user not found', [
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Admin configuration not found'
                ], 500);
            }
            
            // Get admin's Instagram platform
            $instagramPlatform = UserPlatform::where('user_id', $adminUser->id)
                ->where('platform_id', 2) // Instagram platform_id
                ->first();
                
            if (!$instagramPlatform || !$instagramPlatform->access_token) {
                Log::error('Admin Instagram platform not found', [
                    'user_id' => Auth::id(),
                    'admin_id' => $adminUser->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Instagram configuration not found'
                ], 500);
            }
            
            // Get active Instagram page
            $instagramPage = PlatformPage::where('user_platform_id', $instagramPlatform->id)
                ->where('type', 'instagram_account')
                ->where('is_active', 1)
                ->first();
                
            if (!$instagramPage) {
                Log::error('Admin Instagram page not found', [
                    'user_id' => Auth::id(),
                    'admin_id' => $adminUser->id,
                    'platform_id' => $instagramPlatform->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Instagram page not found'
                ], 500);
            }
            
            // Get trending hashtags if we have at least one hashtag from analysis
            $trendingHashtags = [];
            if (!empty($hashtags)) {
                $seedHashtag = $hashtags[0]; // Use the first hashtag as seed
                
                $trendingResponse = Http::withoutVerifying()->timeout(80)
                    ->withHeaders([
                        'Authorization' => $instagramPlatform->access_token
                    ])
                    ->post('https://localhost:8443/instagram/hashtags', [
                        'hashtag' => $seedHashtag,
                        'ig_user_id' => $instagramPage->page_id
                    ]);
                    
                if (!$trendingResponse->failed()) {
                    $trendingData = $trendingResponse->json();
                    if (isset($trendingData['status']) && $trendingData['status'] === 'success') {
                        $trendingHashtags = array_column($trendingData['trending_hashtags'], 'hashtag');
                    }
                }
            }
            
            // Create three optimized post variations
            $posts = [];
            
            // Make three optimization requests with different seed texts
            for ($i = 0; $i < 3; $i++) {
                // Optimization using our enhanced API
                $optimizationResponse = Http::withoutVerifying()->timeout(90)
                    ->withHeaders([
                        'Authorization' => $instagramPlatform->access_token
                    ])
                    ->post('https://localhost:8443/content/generate-optimized', [
                        'text' => $content,
                        'ig_user_id' => $instagramPage->page_id
                    ]);
                    
                if ($optimizationResponse->failed()) {
                    Log::error('Content optimization failed', [
                        'status' => $optimizationResponse->status(),
                        'response' => $optimizationResponse->body(),
                        'user_id' => Auth::id(),
                        'attempt' => $i + 1
                    ]);
                    
                    continue; // Skip this iteration and try the next one
                }
                
                $optimizationData = $optimizationResponse->json();
                
                if (isset($optimizationData['status']) && $optimizationData['status'] === 'success') {
                    $optimizedContent = $optimizationData['optimized_content'] ?? '';
                    
                    // Extract hashtags from the optimized content
                    preg_match_all('/#(\w+)/', $optimizedContent, $matches);
                    $extractedHashtags = $matches[1] ?? []; // This gets the actual hashtags without the # symbol
                    
                    // If no hashtags found in content, use a subset of the analyzed hashtags as fallback
                    if (empty($extractedHashtags)) {
                        $extractedHashtags = array_slice($hashtags, 0, min(count($hashtags), $i == 0 ? count($hashtags) : ($i == 1 ? 5 : 3)));
                    }
                    
                    $posts[] = [
                        'content' => $optimizedContent,
                        'purpose' => $purpose,
                        'hashtags' => $extractedHashtags
                    ];
                }
            }
            
            // Fallback: If we couldn't get any optimized content, use the original
            if (empty($posts)) {
                $posts[] = [
                    'content' => $content,
                    'purpose' => $purpose,
                    'hashtags' => $hashtags
                ];
            }
            
            return response()->json([
                'success' => true,
                'posts' => $posts,
                'trending_hashtags' => $trendingHashtags
            ]);
            
        } catch (\Exception $e) {
            Log::error('Post creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save a selected post
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        try {
            $request->validate([
                'content' => 'required|string',
                'purpose' => 'required|string',
                'hashtags' => 'required|array',
            ]);
            
            $post = new Post();
            $post->user_id = Auth::id();
            $post->content = $request->input('content');
            $post->purpose = $request->input('purpose');
            $post->hashtags = json_encode($request->input('hashtags'));
            $post->status = 'draft';
            $post->save();
            
            return response()->json([
                'success' => true,
                'post' => $post
            ]);
            
        } catch (\Exception $e) {
            Log::error('Post saving failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save post: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate optimized content using AI analysis and top-performing posts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOptimizedContent(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|min:5',
            ]);

            // Find admin user instead of using authenticated user
            $adminUser = User::where('name', 'admin')->first();
            
            if (!$adminUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin user not found for Instagram integration'
                ], 400);
            }

            // Find the admin's Instagram platform connection
            $userPlatform = UserPlatform::where('user_id', $adminUser->id)
                ->where('platform_id', 2) // Instagram platform_id
                ->first();
            
            // Now find the active Instagram page using the user_platform_id
            $instagramPage = null;
            if ($userPlatform) {
                $instagramPage = PlatformPage::where('user_platform_id', $userPlatform->id)
                    ->where('type', 'instagram_account')
                    ->where('is_active', 1)
                    ->first();
            }

            if (!$instagramPage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active Instagram account found for admin user.'
                ], 400);
            }
            
            if (!$userPlatform || !$userPlatform->access_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid access token found for admin Instagram account.'
                ], 400);
            }

            // Make request to Flask API with proper authorization
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $userPlatform->access_token
                ])
                ->post('https://localhost:8443/content/generate-optimized', [
                    'text' => $request->input('text'),
                    'ig_user_id' => $instagramPage->page_id
                ]);

            if ($response->failed()) {
                Log::error('Content generation failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate optimized content'
                ], 500);
            }

            $data = $response->json();
            
            // Check if we have a valid response structure
            if (!isset($data['status']) || $data['status'] !== 'success') {
                throw new \Exception('Invalid response format from optimization service');
            }

            return response()->json([
                'success' => true,
                'optimized_content' => $data['optimized_content'] ?? '',
                'analysis' => [
                    'purpose' => $data['analysis']['purpose'] ?? '',
                    'suggested_hashtags' => $data['analysis']['suggested_hashtags'] ?? [],
                    'example_posts' => $data['analysis']['example_posts'] ?? [],
                    'common_phrases' => $data['analysis']['common_phrases'] ?? [],
                    'emoji_usage' => $data['analysis']['emoji_usage'] ?? [],
                    'structure_patterns' => $data['analysis']['structure_patterns'] ?? []
                ],
                'template' => $data['template'] ?? '',
                'metrics' => [
                    'avg_length' => $data['metrics']['avg_length'] ?? 0,
                    'engagement_patterns' => $data['metrics']['engagement_patterns'] ?? []
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Content generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze text content for purpose and hashtags
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzeContent(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|min:5',
            ]);

            $response = Http::withoutVerifying()
                ->post('https://localhost:8443/content/analyze', [
                    'text' => $request->input('text')
                ]);

            if ($response->failed()) {
                Log::error('Content analysis failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to analyze content'
                ], 500);
            }

            $data = $response->json();
            return response()->json([
                'success' => true,
                'purpose' => $data['purpose'] ?? '',
                'hashtags' => $data['hashtags'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Content analysis failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize content based on purpose and examples
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function optimizeContent(Request $request)
    {
        try {
            $request->validate([
                'purpose' => 'required|string',
                'descriptions' => 'sometimes|array'
            ]);

            $response = Http::withoutVerifying()
                ->post('https://localhost:8443/content/optimize', [
                    'purpose' => $request->input('purpose'),
                    'descriptions' => $request->input('descriptions', [])
                ]);

            if ($response->failed()) {
                Log::error('Content optimization failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to optimize content'
                ], 500);
            }

            $data = $response->json();
            return response()->json([
                'success' => true,
                'optimized_content' => $data['optimized_content'] ?? ''
            ]);

        } catch (\Exception $e) {
            Log::error('Content optimization failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comments for a post and analyze sentiment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyzePostSentiment(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|string',
            'platform' => 'required|string|in:instagram,facebook'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        // Get user information
        $user = Auth::user();
        
        // Get platform data
        $platform = $request->platform;
        $postId = $request->post_id;
        
        // Get access token
        $userPlatform = DB::table('user_platforms')
            ->where('user_id', $user->id)
            ->where('platform', $platform)
            ->first();
        
        if (!$userPlatform || !$userPlatform->access_token) {
            return response()->json([
                'status' => 'error',
                'message' => "No $platform account connected"
            ], 400);
        }

        $accessToken = $userPlatform->access_token;
        
        // Call Flask API endpoint
        try {
            $response = Http::withHeaders([
                'Authorization' => $accessToken
            ])->post('https://localhost:8443/post/sentiment-analysis', [
                'post_id' => $postId,
                'platform' => $platform
            ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Failed to analyze post sentiment: ' . $response->body());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to analyze post sentiment',
                    'details' => $response->json()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception analyzing post sentiment: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while analyzing post sentiment'
            ], 500);
        }
    }

    /**
     * Get comments for a post
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostComments(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|string',
            'platform' => 'required|string|in:instagram,facebook'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        // Get user information
        $user = Auth::user();
        
        // Get platform data
        $platform = $request->platform;
        $postId = $request->post_id;
        
        // Get access token
        $userPlatform = DB::table('user_platforms')
            ->where('user_id', $user->id)
            ->where('platform', $platform)
            ->first();
        
        if (!$userPlatform || !$userPlatform->access_token) {
            return response()->json([
                'status' => 'error',
                'message' => "No $platform account connected"
            ], 400);
        }

        $accessToken = $userPlatform->access_token;
        
        // Call Flask API endpoint
        try {
            $response = Http::withHeaders([
                'Authorization' => $accessToken
            ])->post('https://localhost:8443/post/comments', [
                'post_id' => $postId,
                'platform' => $platform
            ]);
            
            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Failed to fetch post comments: ' . $response->body());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to fetch post comments',
                    'details' => $response->json()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception fetching post comments: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching post comments'
            ], 500);
        }
    }

    /**
     * Publish posts to selected social media platforms
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'platforms' => 'required|json',
                'initial_post_description' => 'nullable|string',
            ]);
            
            // Decode platforms data and intial description
            $platformsData = json_decode($request->input('platforms'), true);
            $initialPostDescription = $request->input('initial_post_description');
            Log::info('Initial post description', ['initial_post_description' => $initialPostDescription]);
            if (!is_array($platformsData) || empty($platformsData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No platforms selected for publishing'
                ], 400);
            }
            
            $results = [];
            $userId = Auth::id();
            
            // Process each platform
            foreach ($platformsData as $platform) {
                $platformId = $platform['platform_id'] ?? null;
                $pageId = $platform['page_id'] ?? null;
                $content = $platform['content'] ?? '';
                
                if (!$platformId) {
                    continue;
                }
                
                // Get user platform data
                $userPlatform = $this->getUserPlatform($userId, $platformId);
                
                if (!$userPlatform) {
                    $results[$platformId] = [
                        'success' => false,
                        'message' => 'Platform not connected or missing access token'
                    ];
                    continue;
                }
                
                // For Facebook and Instagram, get the page
                $platformPage = null;
                if (in_array($platformId, [1, 2])) { // Facebook or Instagram
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
                
                $this->logMediaFileInfo($mediaFile, $platformId);
                $this->logPublishingData($userId, $platformId, $userPlatform, $platformPage, $content, $mediaFile);
                
                // Publish to appropriate platform
                if ($platformId == 3) { // LinkedIn
                    $results[$platformId] = $this->publishToLinkedIn($userPlatform, $content, $mediaFile, $userId, $initialPostDescription);
                } else if ($platformId == 1 && $platformPage && $mediaFile) { // Facebook with media
                    $results[$platformId] = $this->publishToFacebook($userPlatform, $platformPage, $content, $mediaFile, $userId, $initialPostDescription);
                } else if ($platformId == 2 && $platformPage && $mediaFile) { // Instagram with media
                    $results[$platformId] = $this->publishToInstagram($userPlatform, $platformPage, $content, $mediaFile, $userId, $initialPostDescription);
                } else {
                    // For now, just return success for other platforms
                    $results[$platformId] = [
                        'success' => true,
                        'message' => 'Post data logged successfully',
                        'platform_id' => $platformId,
                        'platform_name' => $this->getPlatformName($platformId)
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Post publishing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to publish posts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user platform data
     *
     * @param int $userId
     * @param int $platformId
     * @return UserPlatform|null
     */
    private function getUserPlatform($userId, $platformId)
    {
        $userPlatform = UserPlatform::where('user_id', $userId)
            ->where('platform_id', $platformId)
            ->first();
        
        if (!$userPlatform || !$userPlatform->access_token) {
            Log::info('No valid user platform found', [
                'user_id' => $userId,
                'platform_id' => $platformId
            ]);
            return null;
        }
        
        return $userPlatform;
    }

    /**
     * Get platform page
     *
     * @param int $userPlatformId
     * @return PlatformPage|null
     */
    private function getPlatformPage($userPlatformId)
    {
        $platformPage = PlatformPage::where('user_platform_id', $userPlatformId)
            ->first();
        
        if (!$platformPage) {
            Log::info('Platform page not found', [
                'user_platform_id' => $userPlatformId
            ]);
            return null;
        }
        
        return $platformPage;
    }

    /**
     * Log media file information
     *
     * @param mixed $mediaFile
     * @param int $platformId
     * @return void
     */
    private function logMediaFileInfo($mediaFile, $platformId)
    {
        if ($mediaFile) {
            Log::info('Media file received', [
                'platform_id' => $platformId,
                'file_name' => $mediaFile->getClientOriginalName(),
                'file_size' => $mediaFile->getSize(),
                'mime_type' => $mediaFile->getMimeType()
            ]);
        }
    }

    /**
     * Log publishing data
     *
     * @param int $userId
     * @param int $platformId
     * @param UserPlatform $userPlatform
     * @param PlatformPage|null $platformPage
     * @param string $content
     * @param mixed $mediaFile
     * @return void
     */
    private function logPublishingData($userId, $platformId, $userPlatform, $platformPage, $content, $mediaFile)
    {
        Log::info('Publishing post data', [
            'user_id' => $userId,
            'platform_id' => $platformId,
            'platform_name' => $this->getPlatformName($platformId),
            'access_token' => substr($userPlatform->access_token, 0, 10) . '...',
            'page_id' => $platformPage ? $platformPage->page_id : 'N/A',
            'page_name' => $platformPage ? $platformPage->name : 'N/A',
            'content_length' => strlen($content),
            'has_media' => $mediaFile ? 'yes' : 'no'
        ]);
    }

    /**
     * Get platform name by ID
     *
     * @param int $platformId
     * @return string
     */
    private function getPlatformName($platformId)
    {
        $platforms = [
            1 => 'Facebook',
            2 => 'Instagram',
            3 => 'LinkedIn'
        ];
        
        return $platforms[$platformId] ?? 'Unknown';
    }

    /**
     * Create a post record in the database
     *
     * @param int $userId
     * @param int $platformId
     * @param string $initialDescription
     * @param string $content
     * @param string $status
     * @param string|null $responsePostId
     * @param array $metadata
     * @return Post
     */
    private function createPostRecord($userId, $platformId, $initialDescription, $content, $status, $responsePostId, $metadata)
    {
        $post = new Post();
        $post->user_id = $userId;
        $post->platform_id = $platformId;
        $post->initial_description = $initialDescription;
        $post->AI_generated_description = $content;
        $post->status = $status;
        $post->response_post_id = $responsePostId;
        $post->metadata = json_encode($metadata);
        
        // Set the table name explicitly to 'post' instead of the default 'posts'
        $post->setTable('post');
        
        $post->save();
        
        return $post;
    }

    /**
     * Publish to LinkedIn
     *
     * @param UserPlatform $userPlatform
     * @param string $content
     * @param mixed $mediaFile
     * @param int $userId
     * @param string $initialPostDescription
     * @return array
     */
    private function publishToLinkedIn($userPlatform, $content, $mediaFile, $userId, $initialPostDescription)
    {
        try {
            // Prepare the request data
            $requestData = [
                'content' => $content
            ];
            
            // Handle media file if present
            if ($mediaFile) {
                // Convert the file to base64
                $base64Media = base64_encode(file_get_contents($mediaFile->getPathname()));
                $requestData['media_file'] = $base64Media;
                $requestData['media_type'] = 'image'; // Assuming it's an image, adjust if needed
            }
            
            // Make the API request with increased timeout
            $response = Http::withoutVerifying()
                ->timeout(60) // Increase timeout to 60 seconds
                ->withHeaders([
                    'Authorization' => $userPlatform->access_token,
                    'Content-Type' => 'application/json'
                ])
                ->post('https://localhost:8443/linkedin/post', $requestData);
            
            // Log the request details
            Log::info('LinkedIn API request', [
                'endpoint' => 'https://localhost:8443/linkedin/post',
                'has_media' => $mediaFile ? 'yes' : 'no',
                'content_length' => strlen($content)
            ]);
            
            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('LinkedIn post response', [
                    'response' => $responseData
                ]);

                // Create a new post record in the database with correct column names
                $metadata = [
                    'published_at' => now()->toDateTimeString(),
                    'has_media' => $mediaFile ? true : false,
                    'media_type' => $mediaFile ? $mediaFile->getMimeType() : null,
                    'media_name' => $mediaFile ? $mediaFile->getClientOriginalName() : null,
                ];
                
                // Create a new post record in the database with correct column names
                $post = $this->createPostRecord(
                    $userId, 
                    3, // LinkedIn platform_id
                    $initialPostDescription, 
                    $content, 
                    'published',
                    $responseData['post_id'] ?? null,
                    $metadata
                );
                

                return [
                    'success' => true,
                    'message' => 'Post published successfully to LinkedIn',
                    'platform_id' => 3,
                    'platform_name' => 'LinkedIn',
                    'post_id' => $responseData['post_id'] ?? null,
                    'database_id' => $post->id
                ];
            } else {
                Log::error('LinkedIn post failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => $userId
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to publish to LinkedIn: ' . ($response->json()['message'] ?? 'Unknown error'),
                    'platform_id' => 3,
                    'platform_name' => 'LinkedIn'
                ];
            }
        } catch (\Exception $e) {
            Log::error('LinkedIn post exception', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Error publishing to LinkedIn: ' . $e->getMessage(),
                'platform_id' => 3,
                'platform_name' => 'LinkedIn'
            ];
        }
    }

    /**
     * Publish to Facebook
     *
     * @param UserPlatform $userPlatform
     * @param PlatformPage $platformPage
     * @param string $content
     * @param mixed $mediaFile
     * @param int $userId
     * @param string $initialPostDescription
     * @return array
     */
    private function publishToFacebook($userPlatform, $platformPage, $content, $mediaFile, $userId, $initialPostDescription)
    {
        try {
            // Save the file to a location accessible by the Flask app
            $filename = time() . '_' . $mediaFile->getClientOriginalName();

            // Save mediafile Original Name and type for Metadata
            $mediaFileName = $mediaFile->getClientOriginalName();
            $mediaFileType = $mediaFile->getMimeType();

            $flaskAppPath = base_path('Post_Generation');
            $mediaFile->move($flaskAppPath, $filename);
            
            // Make a JSON request to the Facebook API
            $response = Http::withoutVerifying()
                ->post('https://localhost:8443/facebook/post', [
                    'page_id' => $platformPage->page_id,
                    'page_token' => $userPlatform->access_token,
                    'filename' => $filename,
                    'message' => $content
                ]);
            
            // Log the request details
            Log::info('Facebook API request', [
                'endpoint' => 'https://localhost:8443/facebook/post',
                'page_id' => $platformPage->page_id,
                'filename' => $filename
            ]);
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Create a new post record in the database
                $metadata = [
                    'published_at' => now()->toDateTimeString(),
                    'platform_page_id' => $platformPage->page_id,
                    'platform_page_name' => $platformPage->name,
                    'media_type' => $mediaFileType,
                    'media_name' => $mediaFileName,
                    'media_filename' => $filename
                ];
                
                $post = $this->createPostRecord(
                    $userId, 
                    1, // Facebook platform_id
                    $initialPostDescription, 
                    $content, 
                    'published', 
                    $responseData['post_id'] ?? null, 
                    $metadata
                );
                
                return [
                    'success' => true,
                    'message' => 'Post published successfully to Facebook',
                    'platform_id' => 1,
                    'platform_name' => 'Facebook',
                    'post_id' => $responseData['post_id'] ?? null,
                    'database_id' => $post->id
                ];
            } else {
                Log::error('Facebook post failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => $userId
                ]);
                                
                return [
                    'success' => false,
                    'message' => 'Failed to publish to Facebook: ' . ($response->json()['message'] ?? 'Unknown error'),
                    'platform_id' => 1,
                    'platform_name' => 'Facebook'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Facebook post exception', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Error publishing to Facebook: ' . $e->getMessage(),
                'platform_id' => 1,
                'platform_name' => 'Facebook'
            ];
        }
    }

    /**
     * Publish to Instagram
     *
     * @param UserPlatform $userPlatform
     * @param PlatformPage $platformPage
     * @param string $content
     * @param mixed $mediaFile
     * @param int $userId
     * @param string $initialPostDescription
     * @return array
     */
    private function publishToInstagram($userPlatform, $platformPage, $content, $mediaFile, $userId, $initialPostDescription)
    {
        try {
            // Save the file to a location accessible by the Flask app
            $filename = time() . '_' . $mediaFile->getClientOriginalName();
            $flaskAppPath = base_path('Post_Generation');

            // Save mediafile Original Name and type for Metadata
            $mediaFileName = $mediaFile->getClientOriginalName();
            $mediaFileType = $mediaFile->getMimeType();
            
            // Make sure the directory exists
            if (!file_exists($flaskAppPath)) {
                mkdir($flaskAppPath, 0755, true);
            }
            
            // Check if the file was successfully moved
            if (!$mediaFile->move($flaskAppPath, $filename)) {
                Log::error('Failed to move Instagram media file', [
                    'user_id' => $userId,
                    'file_name' => $mediaFile->getClientOriginalName(),
                    'destination' => $flaskAppPath . '/' . $filename
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Error saving media file for Instagram post',
                    'platform_id' => 2,
                    'platform_name' => 'Instagram'
                ];
            }
            
            // Verify the file exists after moving
            if (!file_exists($flaskAppPath . '/' . $filename)) {
                Log::error('Instagram media file not found after move', [
                    'user_id' => $userId,
                    'file_path' => $flaskAppPath . '/' . $filename
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Media file not found after upload for Instagram post',
                    'platform_id' => 2,
                    'platform_name' => 'Instagram'
                ];
            }
            
            // Make the API request
            $response = Http::withoutVerifying()
                ->timeout(60) // Increase timeout to 60 seconds
                ->withHeaders([
                    'Authorization' => $userPlatform->access_token,
                    'Content-Type' => 'application/json'
                ])
                ->post('https://localhost:8443/instagram/post', [
                    'ig_user_id' => $platformPage->page_id,
                    'filename' => $filename,
                    'caption' => $content
                ]);
            
            // Log the request details
            Log::info('Instagram API request', [
                'endpoint' => 'https://localhost:8443/instagram/post',
                'payload' => [
                    'ig_user_id' => $platformPage->page_id,
                    'filename' => $filename,
                    'caption_length' => strlen($content)
                ]
            ]);
            
            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('Instagram post Successfully published', [
                    'response' => $responseData
                ]);

                // Create a new post record in the database
                $metadata = [
                    'published_at' => now()->toDateTimeString(),
                    'platform_page_id' => $platformPage->page_id,
                    'platform_page_name' => $platformPage->name,
                    'media_type' => $mediaFileType,
                    'media_name' => $mediaFileName,
                    'media_filename' => $filename
                ];

                Log::info('MetaData Stored for Insta is ', [
                    'metadata' => $metadata
                ]);
                
                $post = $this->createPostRecord(
                    $userId,
                    2, // Instagram platform_id
                    $initialPostDescription,
                    $content,
                    'published',
                    $responseData['post_id'] ?? null,
                    $metadata
                );
                
                return [
                    'success' => true,
                    'message' => 'Post published successfully to Instagram',
                    'platform_id' => 2,
                    'platform_name' => 'Instagram',
                    'post_id' => $responseData['post_id'] ?? null,
                    'database_id' => $post->id
                ];
                
                // File will be cleaned up by the Flask app
            } else {
                Log::error('Instagram post failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => $userId
                ]);
                                
                return [
                    'success' => false,
                    'message' => 'Failed to publish to Instagram: ' . ($response->json()['message'] ?? 'Unknown error'),
                    'platform_id' => 2,
                    'platform_name' => 'Instagram'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Instagram post exception', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);
            
            return [
                'success' => false,
                'message' => 'Error publishing to Instagram: ' . $e->getMessage(),
                'platform_id' => 2,
                'platform_name' => 'Instagram'
            ];
        }
    }
}
