<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle login authentication
     */
    public function authenticate(Request $request)
    {
        // Validate login form input
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Rate limiting key (IP + username combination)
        $throttleKey = Str::lower($credentials['username']) . '|' . $request->ip();

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            // Log the blocked attempt for security monitoring
            \Log::warning('Login rate limit exceeded', [
                'username' => $credentials['username'],
                'ip' => $request->ip(),
                'remaining_seconds' => $seconds,
                'attempts' => RateLimiter::attempts($throttleKey)
            ]);

            return back()->withErrors([
                'username' => "Too many login attempts. Please try again in {$minutes} minute(s).",
            ])->onlyInput('username');
        }

        // Include is_active check in login attempt
        $loginData = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'is_active' => true, // Only active users can log in
        ];

        if (Auth::attempt($loginData, $request->filled('remember'))) {
            // Clear rate limiter on successful login
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();
            $user = Auth::user();

            // Double-check role validity
            if (!in_array($user->role, ['midwife', 'bhw', 'admin'])) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', 'Invalid user role. Please contact administrator.');
            }

            // Log successful login
            \Log::info('Successful login', [
                'username' => $user->username,
                'role' => $user->role,
                'ip' => $request->ip()
            ]);

            // Redirect based on role
            return redirect()->route('dashboard')
                ->with('success', 'Welcome back, ' . ucfirst($user->role) . '!');
        }

        // Increment failed attempts (lock for 5 minutes = 300 seconds)
        RateLimiter::hit($throttleKey, 300);

        // Get current attempt count for logging
        $attempts = RateLimiter::attempts($throttleKey);

        // Log failed attempt
        \Log::info('Failed login attempt', [
            'username' => $credentials['username'],
            'ip' => $request->ip(),
            'attempts' => $attempts,
            'remaining_attempts' => max(0, 5 - $attempts)
        ]);

        // Failed login - could be wrong credentials or deactivated account
        return back()->withErrors([
            'username' => 'Invalid credentials or your account has been deactivated. Please contact administrator.',
        ])->onlyInput('username');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
