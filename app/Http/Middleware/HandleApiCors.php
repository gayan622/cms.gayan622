<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fine-grained CORS for the API.
 * Laravel 11 uses bootstrap/app.php instead of Kernel.php —
 * register this in the "api" middleware group there.
 *
 * For a quick alternative, install fruitcake/laravel-cors or use
 * the built-in HandleCors class with config/cors.php.
 */
class HandleApiCors
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedOrigins = array_filter([
            env('FRONTEND_URL', 'http://localhost:3000'),
            env('APP_URL'),
        ]);

        $origin = $request->header('Origin');

        // Preflight
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin',      in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0])
                ->header('Access-Control-Allow-Methods',     'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers',     'Content-Type, Authorization, X-Requested-With, Accept')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age',           '86400');
        }

        $response = $next($request);

        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin',      $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods',     'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers',     'Content-Type, Authorization, X-Requested-With, Accept');
        }

        return $response;
    }
}
