<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Platform;
use App\Models\UserPlatform;

class LinkedinController extends Controller
{
    public function generateAuthUrl()
    {
        try {
            $response = Http::withoutVerifying()
                ->get('https://localhost:8443/auth/linkedin');
            
            if ($response->failed()) {
                throw new \Exception('Failed to generate LinkedIn auth URL');
            }

            return response()->json([
                'auth_url' => $response->json()['auth_url']
            ]);

        } catch (\Exception $e) {
            Log::error('Linkedin auth URL generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'error' => 'Failed to initialize Linkedin connection'
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        try {
            // dd($request->all());
            if (!$request->has('token')) {
                return redirect()->to('/dashboard?status=error&message=No access token provided');
            }
    
            DB::beginTransaction();
            
            // Get or create LinkedIn platform
            $platform = Platform::where('name', 'linkedin')->first();
    
            // Store user platform connection
            $userPlatform = UserPlatform::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'platform_id' => $platform->id
                ],
                [
                    'access_token' => $request->token,
                    'connected_at' => now()
                ]
            );
        
            DB::commit();
    
            // Redirect to social links page with success message
            return redirect()->to('/dashboard?status=success&message=Linkedin account connected successfully&platform=linkedin');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Linkedin callback failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
    
            return redirect()->to('/dashboard?status=error&message=' . urlencode($e->getMessage()));
        }
    }
    public function disconnect()
    {
        try {
            DB::beginTransaction();

            $platform = Platform::where('name', 'linkedin')->first();
            
            if ($platform) {
                UserPlatform::where([
                    'user_id' => Auth::id(),
                    'platform_id' => $platform->id
                ])->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Linkedin account disconnected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Linkedin disconnect failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Failed to disconnect Linkedin account'
            ], 500);
        }
    }
    public function checkConnection()
    {
        try {
            $platform = Platform::where('name', 'linkedin')->first();
            
            if (!$platform) {
                return response()->json(['connected' => false]);
            }

            $userPlatform = UserPlatform::where([
                'user_id' => Auth::id(),
                'platform_id' => $platform->id
            ])->first();

            if (!$userPlatform) {
                return response()->json(['connected' => false]);
            }

            return response()->json([
                'connected' => true,
                'platform_id' => $platform->id,
                'user_platform_id'=> $userPlatform->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check Linkedin connection', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'connected' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
