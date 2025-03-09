<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\LinkedinController;
use App\Http\Controllers\InstagramController;
use Illuminate\Http\Request;
use App\Http\Controllers\PostManagerController;
use App\Http\Controllers\HashtagController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('logout');

// Facebook Routes
Route::prefix('facebook')->middleware('auth:sanctum')->group(function () {
    Route::get('/auth', [FacebookController::class, 'generateAuthUrl']);
    Route::get('/callback', [FacebookController::class, 'handleCallback'])->name('facebook.callback');
    Route::post('/disconnect', [FacebookController::class, 'disconnect']);
    Route::get('/pages', [FacebookController::class, 'getPages']);
    Route::get('/check-connection', [FacebookController::class, 'checkConnection']);
});

// LinkedIn Routes
Route::prefix('linkedin')->middleware('auth:sanctum')->group(function() {
    Route::get('/auth', [LinkedinController::class, 'generateAuthUrl']);
    Route::get('/callback', [LinkedinController::class, 'handleCallback'])->name('linkedin.callback');
    Route::post('/disconnect', [LinkedinController::class, 'disconnect']);
    Route::get('/check-connection', [LinkedinController::class, 'checkConnection']);
});

// Instagram Routes
Route::prefix('instagram')->middleware('auth:sanctum')->group(function () {
    Route::get('/auth', [InstagramController::class, 'generateAuthUrl']);
    Route::get('/callback', [InstagramController::class, 'handleCallback'])->name('instagram.callback');
    Route::post('/disconnect', [InstagramController::class, 'disconnect']);
    Route::get('/check-connection', [InstagramController::class, 'checkConnection']);
});

// User Route
// Return the user object by calling the api/user endpoint
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Post management routes
Route::middleware(['auth'])->group(function () {
    Route::post('/posts/create', [PostManagerController::class, 'create']);
    Route::post('/posts/save', [PostManagerController::class, 'save']);
    Route::post('/hashtags/search', [HashtagController::class, 'search']);
    Route::post('/hashtags/analyze', [HashtagController::class, 'analyze']);
    Route::post('/content/generate-optimized', [PostManagerController::class, 'generateOptimizedContent']);
    Route::post('/content/analyze', [PostManagerController::class, 'analyzeContent']);
    Route::post('/content/optimize', [PostManagerController::class, 'optimizeContent']);
    Route::post('/post/comments', [PostManagerController::class, 'getPostComments'])->name('post.comments');
    Route::post('/post/sentiment-analysis', [PostManagerController::class, 'analyzePostSentiment'])->name('post.sentiment-analysis');
});

// Catch-all route for Vue frontend
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');

// Add route for the sentiment analysis frontend page
Route::get('/sentiment-analysis', function () {
    return view('home');
})->middleware(['auth'])->name('sentiment-analysis');
