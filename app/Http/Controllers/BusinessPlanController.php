<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusinessPlanController extends Controller
{
    protected $flaskApiUrl;

    public function __construct()
    {
        $this->flaskApiUrl = env('FLASK_API_URL', 'https://localhost:8443');
    }

    /**
     * Generate business plan/strategy
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateStrategy(Request $request)
    {
        // Increase PHP execution time limit for this request
        set_time_limit(180); // 3 minutes
        
        $validator = Validator::make($request->all(), [
            'business_type' => 'required|string',
            'target_demographics' => 'required|string',
            'platform' => 'required|string',
            'business_goals' => 'required|string',
            'content_preferences' => 'nullable|string',
            'budget' => 'nullable|string',
            'timeframe' => 'nullable|string',
            'current_challenges' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get user information
            $user = Auth::user();

            // Call Flask API endpoint with increased timeout
            $response = Http::timeout(120) // 2 minutes timeout
                ->withoutVerifying()
                ->post($this->flaskApiUrl . '/generate-strategy', [
                    'business_type' => $request->business_type,
                    'target_demographics' => $request->target_demographics,
                    'platform' => $request->platform,
                    'business_goals' => $request->business_goals,
                    'content_preferences' => $request->content_preferences,
                    'budget' => $request->budget,
                    'timeframe' => $request->timeframe,
                    'current_challenges' => $request->current_challenges,
                    'user_id' => $user->id
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log success
                Log::info('Business plan generated successfully', [
                    'user_id' => $user->id,
                    'business_type' => $request->business_type
                ]);
                
                return response()->json([
                    'success' => true,
                    'strategy' => $data['strategy'] ?? '',
                    'message' => 'Business plan generated successfully'
                ]);
            } else {
                // Log failure
                Log::error('Business plan generation failed', [
                    'error' => $response->body(),
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate business plan: ' . ($response->json()['message'] ?? 'Unknown error')
                ], 500);
            }
        } catch (\Exception $e) {
            // Log exception
            Log::error('Exception during business plan generation', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
} 