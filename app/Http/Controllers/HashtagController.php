<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\UserPlatform;
use App\Models\PlatformPage;

class HashtagController extends Controller
{
    public function search(Request $request)
    {
        try {
            $request->validate([
                'hashtag' => 'required|string|min:2',
            ]);
            
            // First, find the user's Instagram platform connection
            $userPlatform = UserPlatform::where('user_id', Auth::id())
                ->where('platform_id', 2) // Instagram platform_id
                ->first();
                
            if (!$userPlatform || !$userPlatform->access_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid Instagram connection found. Please connect your Instagram account.'
                ], 400);
            }

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

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $userPlatform->access_token
                ])
                ->post('https://localhost:8443/instagram/hashtags', [
                    'hashtag' => $request->input('hashtag'),
                    'ig_user_id' => $instagramPage->page_id // Use the stored Instagram page ID
                ]);
                
            if ($response->failed()) {
                Log::error('Hashtag search failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to search hashtags'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Hashtag search failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to search hashtags: ' . $e->getMessage()
            ], 500);
        }
    }

    public function analyze(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|min:2',
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
            
            return response()->json([
                'success' => true,
                'data' => $response->json()
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

    public function trending(Request $request)
    {
        try {
            // Get trending hashtags from your Flask API
            $trendingResponse = Http::withoutVerifying()
                ->get('https://localhost:8443/hashtags/trending', [
                    'limit' => $request->input('limit', 10)
                ]);
                
            if ($trendingResponse->failed()) {
                Log::error('Trending hashtags fetch failed', [
                    'status' => $trendingResponse->status(),
                    'response' => $trendingResponse->body(),
                    'user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch trending hashtags'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'data' => $trendingResponse->json()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Trending hashtags fetch failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trending hashtags: ' . $e->getMessage()
            ], 500);
        }
    }
} 