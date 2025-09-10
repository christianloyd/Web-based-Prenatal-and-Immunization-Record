<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleDriveService;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        try {
            // Use static method to avoid service initialization issues
            $authUrl = GoogleDriveService::getAuthUrl();
            return redirect($authUrl);
            
        } catch (Exception $e) {
            \Log::error('Google Auth redirect failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to initialize Google authentication: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleCallback(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');
        
        // Determine redirect route based on user authentication and role
        $redirectRoute = $this->getRedirectRoute();
        
        if ($error) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Google authentication cancelled: ' . $error);
        }
        
        if (!$code) {
            return redirect()->route($redirectRoute)
                ->with('error', 'No authorization code received from Google');
        }
        
        try {
            $success = GoogleDriveService::handleCallback($code);
            
            if ($success) {
                return redirect()->route($redirectRoute)
                    ->with('success', 'Google Drive connected successfully! You can now create cloud backups.');
            } else {
                return redirect()->route($redirectRoute)
                    ->with('error', 'Failed to authenticate with Google Drive');
            }
            
        } catch (Exception $e) {
            return redirect()->route($redirectRoute)
                ->with('error', 'Authentication error: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect Google Drive
     */
    public function disconnect()
    {
        try {
            $googleDriveService = app(GoogleDriveService::class);
            
            if ($googleDriveService) {
                $googleDriveService->revokeAuthentication();
            }
            
            return redirect()->back()->with('success', 'Google Drive disconnected successfully');
            
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to disconnect: ' . $e->getMessage());
        }
    }

    /**
     * Get the appropriate redirect route based on authentication status
     */
    private function getRedirectRoute(): string
    {
        // Check if user is authenticated
        if (auth()->check()) {
            // Redirect based on user role
            return match (auth()->user()->role) {
                'midwife' => 'midwife.cloudbackup.index',
                'bhw' => 'bhw.dashboard', // or appropriate BHW route
                default => 'login'
            };
        }
        
        // Not authenticated, redirect to login
        return 'login';
    }
}
