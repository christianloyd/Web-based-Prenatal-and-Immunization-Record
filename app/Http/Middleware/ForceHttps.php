<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Force HTTPS Middleware
 *
 * Redirects all HTTP requests to HTTPS in production environment.
 * Ensures secure communication for all application traffic.
 */
class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce HTTPS in production
        if (config('app.env') === 'production' && !$request->secure()) {
            // Preserve query string and path in redirect
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
