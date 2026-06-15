<?php

use App\Http\Middleware\HandleApiCors;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:     __DIR__ . '/../routes/web.php',
        api:     __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health:  '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Apply CORS to all API routes
        $middleware->api(prepend: [
            HandleApiCors::class,
        ]);

        // Trust Proxies (needed for PayHere notify URL behind a load balancer)
        $middleware->trustProxies(at: '*');

        // Exclude PayHere notify from CSRF (it's a server-to-server webhook)
        $middleware->validateCsrfTokens(except: [
            'api/payhere/notify',
        ]);

        // Sanctum stateful domains — enable if using SPA cookie auth
        // $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Return JSON for all API 404s
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found.',
                ], 404);
            }
        });

        // Return JSON for validation errors on API routes
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // Return JSON for auth errors on API routes
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })
    ->create();
