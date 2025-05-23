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
use App\Models\Schedular;
use App\Models\MediaAttachment;

class PostManagerController extends Controller
{
    protected $flaskApiUrl;

    public function __construct()
    {
        $this->flaskApiUrl = env('FLASK_API_URL', 'https://localhost:8443');
    }

    /**
     * Create a new post by analyzing content and generating optimized versions
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        set_time_limit(120);
        try {
            $request->validate([
                'content' => 'required|string|min:5',
            ]);

            $content = $request->input('content');
            
            // Step 1: Analyze the content to get purpose and hashtags
            $analysisResponse = Http::withoutVerifying()->timeout(80)
                ->post($this->flaskApiUrl . '/content/analyze', [
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
                    ->post($this->flaskApiUrl . '/instagram/hashtags', [
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
                    ->post($this->flaskApiUrl . '/content/generate-optimized', [
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
                ->timeout(120)
                ->withHeaders([
                    'Authorization' => $userPlatform->access_token
                ])
                ->post($this->flaskApiUrl . '/content/generate-optimized', [
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
                ->timeout(90)
                ->post($this->flaskApiUrl . '/content/analyze', [
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
                ->timeout(90)
                ->post($this->flaskApiUrl . '/content/optimize', [
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
        // Log the incoming request data for debugging
        Log::info('Sentiment analysis request received', [
            'data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        // Validate request
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|string',
            'platform' => 'required|string|in:instagram,facebook'
        ]);

        if ($validator->fails()) {
            Log::error('Sentiment analysis validation failed', [
                'errors' => $validator->errors()->toArray(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        // Get platform data
        $platform = $request->platform;
        $postId = $request->post_id;
        $userId = Auth::id();
        
        try {
            // Get platform ID based on platform name
            $platformId = $platform === 'instagram' ? 2 : 1; // Map platform name to ID
        
            
            // Get the user's own platform credentials
            $userPlatform = UserPlatform::where('user_id', $userId)
                ->where('platform_id', $platformId)
                ->first();
            
                
            if (!$userPlatform || !$userPlatform->access_token) {
                Log::error('User platform access token not found', [
                    'platform' => $platform,
                    'platform_id' => $platformId,
                    'user_id' => $userId
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your platform access token was not found. Please reconnect your account.'
                ], 401);
            }
            
            // Call the sentiment analysis endpoint in Flask with user's token
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'Authorization' => $userPlatform->access_token,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->flaskApiUrl . '/post/sentiment-analysis', [
                    'post_id' => $postId,
                    'platform' => $platform
                ]);
          
            
            // Debug response
            Log::info('Received response from sentiment analysis', [
                'status' => $response->status(),
                'body' => $response->body(),
                'content_type' => $response->header('Content-Type')
            ]);
            
            if ($response->successful()) {
                // Parse the JSON response
                $responseData = $response->json();
             
                // Check if response has expected structure
                if (!isset($responseData['analysis']) && isset($responseData['overall_sentiment'])) {
                    // Convert simple response format to expected structure
                    $responseData = [
                        'status' => 'success',
                        'analysis' => [
                            'overall_sentiment' => $responseData['overall_sentiment'] ?? 'neutral',
                            'sentiment_distribution' => [
                                'positive' => 0,
                                'neutral' => 0,
                                'negative' => 0
                            ],
                            'average_score' => 0,
                            'comment_sentiments' => [],
                            'common_positive_words' => [],
                            'common_negative_words' => []
                        ],
                        'comment_count' => $responseData['comment_count'] ?? 0,
                        'charts' => [
                            'sentiment_distribution' => null,
                            'score_distribution' => null
                        ]
                    ];
                }
                
                return response()->json($responseData);
            } else {
                Log::error('Failed to analyze post sentiment', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'post_id' => $postId,
                    'platform' => $platform,
                    'user_id' => $userId
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to analyze post sentiment',
                    'details' => $response->json()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Exception analyzing post sentiment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $postId,
                'platform' => $platform,
                'user_id' => $userId
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while analyzing post sentiment: ' . $e->getMessage()
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
            ])->post($this->flaskApiUrl . '/post/comments', [
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
     * Schedule posts for later publishing
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
    protected function getUserPlatform($userId, $platformId)
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
    protected function getPlatformPage($userPlatformId)
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
    protected function getPlatformName($platformId)
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
    protected function createPostRecord($userId, $platformId, $initialDescription, $content, $status, $responsePostId, $metadata)
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
                ->post($this->flaskApiUrl . '/linkedin/post', $requestData);
            
            // Log the request details
            Log::info('LinkedIn API request', [
                'endpoint' => $this->flaskApiUrl . '/linkedin/post',
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

                // Store media file if present
                if ($mediaFile) {
                    // Create directory for this post
                    $postDirectory = 'public/posts/' . $post->id;
                    $storagePath = storage_path('app/' . $postDirectory);
                    
                    if (!file_exists($storagePath)) {
                        mkdir($storagePath, 0755, true);
                    }
                    
                    // Generate a unique filename
                    $filename = time() . '_' . $mediaFile->getClientOriginalName();
                    
                    // Store the file in the post's directory
                    $mediaFile->storeAs($postDirectory, $filename);
                    
                    // Get the absolute path for database storage
                    $absolutePath = storage_path('app/' . $postDirectory . '/' . $filename);
                    
                    // Log the Saved Media File Data
                    Log::info('Saved Media File Data', [
                        'post_id' => $post->id,
                        'media_file' => $mediaFile,
                        'media_path' => $absolutePath,
                        'alt_text' => $initialPostDescription,
                        'metadata' => $metadata
                    ]);

                    // Save media attachment with absolute path in Database
                    $mediaAttachment = new MediaAttachment();
                    $mediaAttachment->post_id = $post->id;
                    $mediaAttachment->media_type = $mediaFile->getMimeType();
                    $mediaAttachment->file_path = $absolutePath;
                    $mediaAttachment->alt_text = $initialPostDescription;
                    $mediaAttachment->metadata = json_encode($metadata);
                    $mediaAttachment->setTable('media_attachment');
                    $mediaAttachment->save();
                }

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
            // Save mediafile Original Name and type for Metadata
            $mediaFileName = $mediaFile->getClientOriginalName();
            $mediaFileType = $mediaFile->getMimeType();
            
            // Create directory for this post in public storage
            $postDirectory = 'public/posts/' . time(); // Using timestamp for unique directory
            $storagePath = storage_path('app/' . $postDirectory);
            
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            // Generate a unique filename for storage
            $filename = time() . '_' . $mediaFileName;
            
            // Store the file in the public directory
            $mediaFile->storeAs($postDirectory, $filename);
            
            // Generate the public URL for the file
            $publicUrl = config('app.url') . '/storage/posts/' . time() . '/' . $filename;
            
            // Make a JSON request to the Facebook API
            $response = Http::withoutVerifying()
                ->timeout(120)
                ->post($this->flaskApiUrl . '/facebook/post', [
                    'page_id' => $platformPage->page_id,
                    'page_token' => $platformPage->metadata['access_token'],
                    'public_url' => $publicUrl, // Send the public URL instead of filename
                    'message' => $content
                ]);
            
            // Log the request details
            Log::info('Facebook API request', [
                'endpoint' => $this->flaskApiUrl . '/facebook/post',
                'page_id' => $platformPage->page_id,
                'public_url' => $publicUrl
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
                    'media_filename' => $filename,
                    'public_url' => $publicUrl
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

                // Save media attachment
                $mediaAttachment = new MediaAttachment();
                $mediaAttachment->post_id = $post->id;
                $mediaAttachment->media_type = $mediaFileType;
                $mediaAttachment->file_path = $storagePath . '/' . $filename;
                // $mediaAttachment->public_url = $publicUrl;
                $mediaAttachment->alt_text = $initialPostDescription;
                $mediaAttachment->metadata = json_encode($metadata);
                $mediaAttachment->setTable('media_attachment');
                $mediaAttachment->save();
                
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
        set_time_limit(120);
        try {
            // Save mediafile Original Name and type for Metadata
            $mediaFileName = $mediaFile->getClientOriginalName();
            $mediaFileType = $mediaFile->getMimeType();
            
            // Create directory for this post in public storage
            $postDirectory = 'public/posts/' . time(); // Using timestamp for unique directory
            $storagePath = storage_path('app/' . $postDirectory);
            
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0755, true);
            }
            
            // Generate a unique filename for storage
            $filename = time() . '_' . $mediaFileName;
            
            // Store the file in the public directory
            $mediaFile->storeAs($postDirectory, $filename);
            
            // Generate the public URL for the file
            $publicUrl = config('app.url') . '/storage/posts/' . time() . '/' . $filename;
            
            // Make the API request
            $response = Http::withoutVerifying()
                ->timeout(120)
                ->withHeaders([
                    'Authorization' => $userPlatform->access_token,
                    'Content-Type' => 'application/json'
                ])
                ->post($this->flaskApiUrl . '/instagram/post', [
                    'ig_user_id' => $platformPage->page_id,
                    'public_url' => $publicUrl,
                    'caption' => $content
                ]);
            
            // Log the request details
            Log::info('Instagram API request', [
                'endpoint' => $this->flaskApiUrl . '/instagram/post',
                'payload' => [
                    'ig_user_id' => $platformPage->page_id,
                    'public_url' => $publicUrl,
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
                    'media_filename' => $filename,
                    'public_url' => $publicUrl
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

                // Save media attachment
                $mediaAttachment = new MediaAttachment();
                $mediaAttachment->post_id = $post->id;
                $mediaAttachment->media_type = $mediaFileType;
                $mediaAttachment->file_path = $storagePath . '/' . $filename;
                // $mediaAttachment->public_url = $publicUrl;
                $mediaAttachment->alt_text = $initialPostDescription;
                $mediaAttachment->metadata = json_encode($metadata);
                $mediaAttachment->setTable('media_attachment');
                $mediaAttachment->save();
                
                return [
                    'success' => true,
                    'message' => 'Post published successfully to Instagram',
                    'platform_id' => 2,
                    'platform_name' => 'Instagram',
                    'post_id' => $responseData['post_id'] ?? null,
                    'database_id' => $post->id
                ];
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

    /**
     * Get all posts for the current user grouped by platform
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserPosts()
    {
        try {
            $userId = Auth::id();
            
            // Get all posts for the current user
            $posts = DB::table('post')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Group posts by platform
            $groupedPosts = [];
            $platformMap = [
                1 => 'facebook',
                2 => 'instagram',
                3 => 'linkedin'
            ];
            
            foreach ($posts as $post) {
                $platformId = $post->platform_id;
                $platformName = $platformMap[$platformId] ?? 'unknown';
                
                if (!isset($groupedPosts[$platformName])) {
                    $groupedPosts[$platformName] = [];
                }
                
                // Parse metadata if it exists
                $metadata = json_decode($post->metadata, true) ?? [];
                
                // Extract post URL or other relevant information
                $postUrl = '';
                if (isset($metadata['post_url'])) {
                    $postUrl = $metadata['post_url'];
                } elseif (isset($metadata['response_details']['post_url'])) {
                    $postUrl = $metadata['response_details']['post_url'];
                }
                
                // Extract published date
                $publishedAt = null;
                if (isset($metadata['published_at'])) {
                    $publishedAt = $metadata['published_at'];
                }
                
                // Add the post to the grouped posts
                $groupedPosts[$platformName][] = [
                    'id' => $post->id,
                    'platform_id' => $platformId,
                    'platform' => $platformName,
                    'response_post_id' => $post->response_post_id,
                    'initial_description' => $post->initial_description,
                    'AI_generated_description' => $post->AI_generated_description,
                    'status' => $post->status,
                    'published_at' => $publishedAt,
                    'created_at' => $post->created_at,
                    'post_url' => $postUrl
                ];
            }
            
            return response()->json([
                'success' => true,
                'posts' => $groupedPosts
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching user posts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch posts: ' . $e->getMessage()
            ], 500);
        }
    }

}
