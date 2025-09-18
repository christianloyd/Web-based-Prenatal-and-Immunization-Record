<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Display notifications for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread notifications count (cached for performance)
     */
    public function getUnreadCount()
    {
        $userId = Auth::id();
        
        $count = Cache::remember("unread_notifications_count_{$userId}", 30, function () {
            return Auth::user()->unreadNotifications()->count();
        });
        
        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown (cached for performance)
     */
    public function getRecent()
    {
        $userId = Auth::id();
        
        $notifications = Cache::remember("recent_notifications_{$userId}", 60, function () {
            return Auth::user()->notifications()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        });

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->markAsRead();
            
            $userId = Auth::id();
            Cache::forget("unread_notifications_count_{$userId}");
            Cache::forget("recent_notifications_{$userId}");
            
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        $userId = Auth::id();
        Cache::forget("unread_notifications_count_{$userId}");
        Cache::forget("recent_notifications_{$userId}");
        
        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification
     */
    public function delete($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        
        if ($notification) {
            $notification->delete();
            
            $userId = Auth::id();
            Cache::forget("unread_notifications_count_{$userId}");
            Cache::forget("recent_notifications_{$userId}");
            
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Send a test notification (for demo purposes)
     */
    public function sendTest()
    {
        $user = Auth::user();
        
        $user->notify(new HealthcareNotification(
            'Test Notification',
            'This is a test notification from the healthcare system.',
            'info',
            route('dashboard')
        ));

        // Clear notification cache for the recipient
        Cache::forget("unread_notifications_count_{$user->id}");
        Cache::forget("recent_notifications_{$user->id}");

        return response()->json(['success' => true, 'message' => 'Test notification sent!']);
    }

    /**
     * Send appointment reminder notification
     */
    public function sendAppointmentReminder($patientId)
    {
        try {
            $user = Auth::user();
            
            $user->notify(new HealthcareNotification(
                'Appointment Reminder',
                'You have an upcoming prenatal checkup appointment.',
                'warning',
                route('midwife.prenatalcheckup.index'),
                ['patient_id' => $patientId]
            ));

            // Clear notification cache for the recipient
            Cache::forget("unread_notifications_count_{$user->id}");
            Cache::forget("recent_notifications_{$user->id}");

            return response()->json(['success' => true, 'message' => 'Appointment reminder sent']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send reminder'], 500);
        }
    }

    /**
     * Send vaccination reminder notification
     */
    public function sendVaccinationReminder($childId)
    {
        try {
            $user = Auth::user();
            
            $user->notify(new HealthcareNotification(
                'Vaccination Due',
                'A child has an upcoming vaccination due.',
                'warning',
                route('midwife.childrecord.show', $childId),
                ['child_id' => $childId]
            ));

            // Clear notification cache for the recipient
            Cache::forget("unread_notifications_count_{$user->id}");
            Cache::forget("recent_notifications_{$user->id}");

            return response()->json(['success' => true, 'message' => 'Vaccination reminder sent']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to send reminder'], 500);
        }
    }

    /**
     * Manually trigger notification checks (for admin use)
     */
    public function triggerChecks()
    {
        try {
            \App\Services\NotificationService::checkUpcomingAppointments();
            \App\Services\NotificationService::checkVaccinationsDue();
            \App\Services\NotificationService::checkLowVaccineStock();
            
            return response()->json([
                'success' => true, 
                'message' => 'Notification checks completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to run notification checks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get new notifications since a specific timestamp for real-time polling
     */
    public function getNewNotifications(Request $request)
    {
        $lastCheck = $request->query('last_check');
        $user = Auth::user();

        $query = $user->notifications()->orderBy('created_at', 'desc');

        if ($lastCheck) {
            $query->where('created_at', '>', $lastCheck);
        } else {
            // If no last_check, get notifications from the last minute
            $query->where('created_at', '>', now()->subMinute());
        }

        $newNotifications = $query->get();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $newNotifications,
            'unread_count' => $unreadCount,
            'timestamp' => now()->toISOString()
        ]);
    }
}
