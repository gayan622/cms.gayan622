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
