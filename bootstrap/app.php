<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Define rate limiters
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling for secure error responses
        $exceptions->render(function (\Throwable $e, $request) {
            // Check if in production mode
            $isProduction = config('app.env') === 'production';

            // For API requests or AJAX requests, return JSON
            if ($request->expectsJson() || $request->ajax()) {
                // Default error response
                $response = [
                    'success' => false,
                    'message' => 'An error occurred while processing your request.',
                ];

                // Handle specific exception types
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $response['message'] = 'Authentication required. Please log in.';
                    return response()->json($response, 401);
                }

                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $response['message'] = 'You do not have permission to perform this action.';
                    return response()->json($response, 403);
                }

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $response['message'] = 'Validation failed. Please check your input.';
                    $response['errors'] = $e->errors();
                    return response()->json($response, 422);
                }

                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $response['message'] = 'The requested resource was not found.';
                    return response()->json($response, 404);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $response['message'] = 'The requested endpoint was not found.';
                    return response()->json($response, 404);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    $response['message'] = 'The HTTP method is not allowed for this endpoint.';
                    return response()->json($response, 405);
                }

                if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                    $response['message'] = 'Too many requests. Please try again later.';
                    return response()->json($response, 429);
                }

                // In development, include error details
                if (!$isProduction) {
                    $response['debug'] = [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ];
                }

                // Log the error
                \Log::error('API Exception: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                ]);

                return response()->json($response, 500);
            }

            // For web requests, let Laravel handle normally
            // (Laravel will show error pages for web routes)
            return null;
        });
    })->create();
