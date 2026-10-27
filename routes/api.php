<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SeoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MovieApiController;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\MovieView;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\MovieAnalyticsController;

/*
|--------------------------------------------------------------------------
| API Routes  —  prefix: /api
|--------------------------------------------------------------------------
*/

// ── Public routes ─────────────────────────────────────────────────────────────

// Projects (Blog)
Route::prefix('projects')->group(function () {
    Route::get('/',           [ProjectController::class, 'index']);
    Route::get('/featured',   [ProjectController::class, 'featured']);
    Route::get('/{slug}',     [ProjectController::class, 'show']);
});

// Shop — Products & Categories
Route::get('/products',            [ProductController::class, 'index']);
Route::get('/products/{slug}',     [ProductController::class, 'show']);
Route::get('/categories',          [CategoryController::class, 'index']);

// Coupons
Route::post('/coupons/validate',   [CouponController::class, 'validate']);

// Orders
Route::post('/orders',             [OrderController::class, 'store']);
Route::get('/orders/{id}',         [OrderController::class, 'show']);
Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice']);

// Movies
Route::get('/movie', [MovieApiController::class, 'getMovies']);
Route::get('/movie/{id}', [MovieApiController::class, 'getMoviesById']);
Route::get('/movies/tmdb/{tmdb_id}', [MovieApiController::class, 'showOrSync']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. Fix the Get/Sync Endpoint route structure
Route::get('/movies/{id}', function ($id) {
    // This calls your automatic TMDB logic method we added earlier
    $movie = Movie::syncWithTmdb((int)$id);

    if (!$movie) {
return response()->json(['error' => 'Movie sync failed'], 404);
    }

    return response()->json($movie);
});

// 2. Fix the Tracking Route endpoint structure
Route::post('/movies/{id}/track-view', function (Request $request, $id) {
    $trustedNextJsProxies = ['127.0.0.1', '::1'];
    $immediateSender = $request->server('REMOTE_ADDR');

    if (in_array($immediateSender, $trustedNextJsProxies) && $request->hasHeader('X-Client-IP')) {
        $ip = $request->header('X-Client-IP');
    } else {
        $ip = $request->ip(); // Standard fallback
    }

    $alreadyViewed = DB::table('movie_views')
        ->where('tmdb_id', $id)
        ->where('ip_address', $ip)
        ->where('created_at', '>=', now()->subHour())
        ->exists();

    if (!$alreadyViewed) {
        DB::table('movie_views')->insert([
            'tmdb_id' => $id,
            'ip_address' => $ip,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $totalViews = DB::table('movie_views')->where('tmdb_id', $id)->count();

    return response()->json([
        'success' => true,
        'total_views' => $totalViews
    ]);
});



// Put this alongside your movie routes
Route::get('/analytics/movie-views', [MovieAnalyticsController::class, 'getViewStats']);

// PayHere webhook (called server-to-server by PayHere — no CSRF, no auth)
Route::post('/payhere/notify',     [OrderController::class, 'payhereNotify'])
     ->withoutMiddleware(['auth:sanctum']);

// Contact
Route::post('/contact',            [ContactController::class, 'send']);

// SEO
Route::get('/seo/{page}',          [SeoController::class, 'show']);

// ── Protected routes (admin API — Sanctum token required) ────────────────────
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard',    [ReportController::class, 'dashboard']);
        Route::get('/sales',        [ReportController::class, 'sales']);
        Route::get('/top-products', [ReportController::class, 'topProducts']);
        Route::get('/stock',        [ReportController::class, 'stock']);
        Route::get('/coupons',      [ReportController::class, 'coupons']);
    });

});
