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

class InstagramController extends Controller
{
    public function generateAuthUrl()
    {

        try {
            $response = Http::withoutVerifying()
                ->get('https://localhost:8443/auth/instagram');
            
            if ($response->failed()) {
                throw new \Exception('Failed to generate Instagram auth URL');
            }

            return response()->json([
                'auth_url' => $response->json()['auth_url']
            ]);

        } catch (\Exception $e) {
            Log::error('Instagram auth URL generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'error' => 'Failed to initialize Instagram connection'
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
       
        try {
            if (!$request->has('token')) {
                return redirect()->to('/dashboard?status=error&message=No access token provided');
            }

            DB::beginTransaction();
            
            $platform = Platform::where('name', 'instagram')->first();

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

            // Get Instagram accounts
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $request->token
                ])
                ->get('https://localhost:8443/instagram/accounts');

            if ($response->failed()) {
                throw new \Exception('Failed to fetch Instagram accounts');
            }

            $instagramData = $response->json();
            
            if (isset($instagramData['accounts']) && is_array($instagramData['accounts'])) {
                foreach ($instagramData['accounts'] as $account) {
                    PlatformPage::updateOrCreate(
                        [
                            'user_platform_id' => $userPlatform->id,
                            'page_id' => $account['instagram_account_id']
                        ],
                        [
                            'name' => $account['username'] ?? 'Instagram Account',
                            'type' => 'instagram_account',
                            'metadata' => [
                                'account_type' => $account['account_type'] ?? 'business',
                                'followers_count' => $account['followers_count'] ?? 0
                            ],
                            'is_active' => true
                        ]
                    );
                }
            }

            DB::commit();

            return redirect()->to('/dashboard?status=success&message=Instagram account connected successfully&platform=instagram');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Instagram callback failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->to('/dashboard?status=error&message=' . urlencode($e->getMessage()));
        }
    }

    public function checkConnection()
    {
        try {
            $platform = Platform::where('name', 'instagram')->first();
            
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

            $accounts = PlatformPage::where('user_platform_id', $userPlatform->id)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'connected' => true,
                'platform_id' => $platform->id,
                'accounts' => $accounts->map(function ($account) {
                    return [
                        'id' => $account->page_id,
                        'name' => $account->name,
                        'metadata' => $account->metadata
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check Instagram connection', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'connected' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function disconnect()
    {
        try {
            $platform = Platform::where('name', 'instagram')->first();
            
            if (!$platform) {
                return response()->json(['success' => true]);
            }

            DB::beginTransaction();

            // Delete platform pages
            PlatformPage::whereHas('userPlatform', function ($query) use ($platform) {
                $query->where('user_id', Auth::id())
                    ->where('platform_id', $platform->id);
            })->delete();

            // Delete user platform connection
            UserPlatform::where([
                'user_id' => Auth::id(),
                'platform_id' => $platform->id
            ])->delete();

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Instagram disconnect failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}