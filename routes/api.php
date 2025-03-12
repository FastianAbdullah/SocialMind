<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostManagerController;


// Handle CORS preflight requests
Route::options('{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Methods', 'POST, GET, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
})->where('any', '.*');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes - Remove auth:sanctum middleware from these routes

// Sentiment Analysis Routes
Route::middleware('auth:sanctum')->post('/post/comments', [PostManagerController::class, 'getPostComments']);
Route::middleware('auth:sanctum')->post('/post/sentiment-analysis', [PostManagerController::class, 'analyzePostSentiment']);




