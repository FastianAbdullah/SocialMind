<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPlatform;
use App\Models\Platform;
use App\Models\PlatformPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    public function generateAuthUrl()
    {
        try {
            $response = Http::withoutVerifying()
                ->get('https://localhost:8443/auth/facebook');
            
            if ($response->failed()) {
                throw new \Exception('Failed to generate Facebook auth URL');
            }

            return response()->json([
                'auth_url' => $response->json()['auth_url']
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook auth URL generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'error' => 'Failed to initialize Facebook connection'
            ], 500);
        }
    }

    //Handle The Callback by Flask Backend.
    public function handleCallback(Request $request)
    {
        try {
            // dd($request->all());
            if (!$request->has('token')) {
                return redirect()->to('/dashboard?status=error&message=No access token provided');
            }
    
            DB::beginTransaction();
            
            // Get or create Facebook platform
            $platform = Platform::where('name', 'facebook')->first();
    
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
    
            // Get user data from Facebook using Flask backend
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $request->token
                ])
                ->get('https://localhost:8443/facebook/pages');
    
            if ($response->failed()) {
                throw new \Exception('Failed to fetch Facebook pages');
            }
    
            $facebookData = $response->json();
            
            // Store Facebook pages
            if (isset($facebookData['pages']) && is_array($facebookData['pages'])) {
                foreach ($facebookData['pages'] as $page) {
                    PlatformPage::updateOrCreate(
                        [
                            'user_platform_id' => $userPlatform->id,
                            'page_id' => $page['id']
                        ],
                        [
                            'name' => $page['name'],
                            'type' => 'facebook_page',
                            'metadata' => [
                                'access_token' => trim($page['access_token']),
                                'category' => $page['category'] ?? null,
                                'followers_count' => $page['followers_count'] ?? 0
                            ],
                            'is_active' => true
                        ]
                    );
                }
            }
    
            DB::commit();
    
            // Redirect to social links page with success message
            return redirect()->to('/dashboard?status=success&message=Facebook account connected successfully&platform=facebook');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Facebook callback failed', [
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

            $platform = Platform::where('name', 'facebook')->first();
            
            if ($platform) {
                UserPlatform::where([
                    'user_id' => Auth::id(),
                    'platform_id' => $platform->id
                ])->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Facebook account disconnected successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Facebook disconnect failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => 'Failed to disconnect Facebook account'
            ], 500);
        }
    }

    public function getPages()
    {
        try {
            $platform = Platform::where('name', 'facebook')->first();
            if (!$platform) {
                throw new \Exception('Facebook platform not found');
            }

            $userPlatform = UserPlatform::where([
                'user_id' => Auth::id(),
                'platform_id' => $platform->id
            ])->first();

            if (!$userPlatform) {
                throw new \Exception('No Facebook connection found');
            }

            $pages = PlatformPage::where('user_platform_id', $userPlatform->id)
                ->where('is_active', true)
                ->get()
                ->map(function ($page) {
                    return [
                        'id' => $page->page_id,
                        'name' => $page->name,
                        'access_token' => $page->metadata['access_token'],
                        'category' => $page->metadata['category'] ?? null,
                        'followers_count' => $page->metadata['followers_count'] ?? 0
                    ];
                });

            return response()->json(['pages' => $pages]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch Facebook pages', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkConnection()
    {
        try {
            $platform = Platform::where('name', 'facebook')->first();
            
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

            // Get pages to verify connection is still valid
            $pages = PlatformPage::where('user_platform_id', $userPlatform->id)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'connected' => true,
                'platform_id' => $platform->id,
                'pages' => $pages->map(function ($page) {
                    return [
                        'id' => $page->page_id,
                        'name' => $page->name,
                        'access_token' => $page->metadata['access_token'] ?? null
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check Facebook connection', [
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
