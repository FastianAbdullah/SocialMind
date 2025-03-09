<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Post;
use App\Models\UserPlatform;
use App\Models\PlatformPage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            $analysisResponse = Http::withoutVerifying()
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
            
            // Step 2: Generate optimized content based on purpose
            $optimizationResponse = Http::withoutVerifying()
                ->post('https://localhost:8443/content/optimize', [
                    'text' => $content,
                    'purpose' => $purpose
                ]);
                
            if ($optimizationResponse->failed()) {
                Log::error('Content optimization failed', [
                    'status' => $optimizationResponse->status(),
                    'response' => $optimizationResponse->body(),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to optimize content'
                ], 500);
            }
            
            $optimizationData = $optimizationResponse->json();
            $optimizedContent = $optimizationData['optimized_content'] ?? [];
            
            // Create three post variations
            $posts = [];
            
            // If we have optimized content, use it for the first post
            if (!empty($optimizedContent)) {
                $posts[] = [
                    'content' => $optimizedContent,
                    'purpose' => $purpose,
                    'hashtags' => $hashtags
                ];
            }
            
            // Generate two more variations with slight differences
            // In a real implementation, you might want to make additional API calls
            // or use different optimization parameters
            
            // For now, we'll create simple variations
            $posts[] = [
                'content' => $this->createVariation($content, $optimizedContent ?? $content),
                'purpose' => $purpose,
                'hashtags' => array_slice($hashtags, 0, min(5, count($hashtags)))
            ];
            
            $posts[] = [
                'content' => $this->createVariation($content, $optimizedContent ?? $content, true),
                'purpose' => $purpose,
                'hashtags' => array_slice($hashtags, 0, min(3, count($hashtags)))
            ];
            
            return response()->json([
                'success' => true,
                'posts' => $posts
            ]);
            
        } catch (\Exception $e) {
            Log::error('Post creation failed', [
                'error' => $e->getMessage(),
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
     * Create a variation of the content
     *
     * @param string $originalContent
     * @param string $baseContent
     * @param bool $shorterVersion
     * @return string
     */
    private function createVariation($originalContent, $baseContent, $shorterVersion = false)
    {
        // In a real implementation, you would use more sophisticated methods
        // to create variations, possibly with AI assistance
        
        // For this example, we'll just make some simple modifications
        $sentences = preg_split('/(?<=[.!?])\s+/', $baseContent, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($sentences)) {
            return $baseContent;
        }
        
        if ($shorterVersion && count($sentences) > 2) {
            // Create a shorter version by removing some sentences
            $sentencesToKeep = max(1, floor(count($sentences) * 0.7));
            $sentences = array_slice($sentences, 0, $sentencesToKeep);
        }
        
        // Shuffle the order of some sentences (except the first one)
        if (count($sentences) > 2) {
            $firstSentence = array_shift($sentences);
            shuffle($sentences);
            array_unshift($sentences, $firstSentence);
        }
        
        // Add some filler words or phrases to make it different
        $fillerPhrases = [
            "Interestingly, ",
            "It's worth noting that ",
            "As many experts suggest, ",
            "According to recent trends, ",
            "Surprisingly, "
        ];
        
        if (!empty($sentences) && !$shorterVersion) {
            $randomIndex = array_rand($sentences);
            $randomFiller = $fillerPhrases[array_rand($fillerPhrases)];
            $sentences[$randomIndex] = $randomFiller . lcfirst($sentences[$randomIndex]);
        }
        
        return implode(' ', $sentences);
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

        // First, find the user's Instagram platform connection
        $userPlatform = UserPlatform::where('user_id', Auth::id())
            ->where('platform_id', 2) // Instagram platform_id
            ->first();
        
        // Now find the active Instagram page using the user_platform_id
        $instagramPage = PlatformPage::where('user_platform_id', $userPlatform->id)
            ->where('type', 'instagram_account')
            ->where('is_active', 1)
            ->first();

        if (!$instagramPage) {
            return response()->json([
                'success' => false,
                'message' => 'No active Instagram account found. Please connect your Instagram account first.'
            ], 400);
        }

        
        if (!$userPlatform || !$userPlatform->access_token) {
            return response()->json([
                'success' => false,
                'message' => 'No valid access token found. Please reconnect your Instagram account.'
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
}
