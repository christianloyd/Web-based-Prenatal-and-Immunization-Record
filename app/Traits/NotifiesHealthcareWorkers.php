<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait NotifiesHealthcareWorkers
{
    /**
     * Enhanced method to notify healthcare workers with role-specific notifications
     * Specifically designed for BHW-to-Midwife cross-role notifications
     */
    private function notifyHealthcareWorkers($title, $message, $type = 'info', $actionUrl = null, $data = [])
    {
        $currentUser = Auth::user();
        $currentUserRole = $currentUser->role;
        $currentUserName = $currentUser->name;

        // Get all healthcare workers (midwives and BHWs)
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])
            ->where('id', '!=', Auth::id()) // Exclude the current user
            ->get();

        foreach ($healthcareWorkers as $worker) {
            // Enhance notification data with role information
            $notificationData = array_merge($data, [
                'notified_by' => $currentUserName,
                'notified_by_role' => $currentUserRole,
                'recipient_role' => $worker->role,
                'is_cross_role' => $currentUserRole !== $worker->role,
                'action_source' => $currentUserRole === 'bhw' ? 'BHW Data Entry' : 'Midwife Action',
                'timestamp' => now()->toISOString()
            ]);

            // Customize notification for midwives receiving BHW notifications
            if ($currentUserRole === 'bhw' && $worker->role === 'midwife') {
                $enhancedTitle = "🏥 BHW Alert: " . $title;
                $enhancedMessage = "BHW {$currentUserName} " . strtolower(substr($message, 0, 1)) . substr($message, 1);

                $worker->notify(new HealthcareNotification(
                    $enhancedTitle,
                    $enhancedMessage,
                    $type,
                    $actionUrl,
                    array_merge($notificationData, [
                        'priority' => 'high',
                        'notification_category' => 'bhw_to_midwife',
                        'requires_attention' => true,
                        'toast_priority' => 'urgent'
                    ])
                ));
            }
            // Customize notification for BHWs receiving midwife notifications
            elseif ($currentUserRole === 'midwife' && $worker->role === 'bhw') {
                $enhancedTitle = "👩‍⚕️ Midwife Update: " . $title;
                $enhancedMessage = "Midwife {$currentUserName} " . strtolower(substr($message, 0, 1)) . substr($message, 1);

                $worker->notify(new HealthcareNotification(
                    $enhancedTitle,
                    $enhancedMessage,
                    $type,
                    $actionUrl,
                    array_merge($notificationData, [
                        'priority' => 'normal',
                        'notification_category' => 'midwife_to_bhw',
                        'requires_attention' => false,
                        'toast_priority' => 'normal'
                    ])
                ));
            }
            // Same role notifications
            else {
                $worker->notify(new HealthcareNotification(
                    $title,
                    $message,
                    $type,
                    $actionUrl,
                    array_merge($notificationData, [
                        'priority' => 'normal',
                        'notification_category' => 'same_role',
                        'requires_attention' => false,
                        'toast_priority' => 'normal'
                    ])
                ));
            }

            // Clear notification cache for the recipient
            Cache::forget("unread_notifications_count_{$worker->id}");
            Cache::forget("recent_notifications_{$worker->id}");
        }
    }

    /**
     * Specifically notify midwives about BHW actions with enhanced priority
     */
    private function notifyMidwivesOfBHWAction($title, $message, $type = 'info', $actionUrl = null, $data = [])
    {
        $currentUser = Auth::user();

        // Only proceed if current user is BHW
        if ($currentUser->role !== 'bhw') {
            return;
        }

        $currentUserName = $currentUser->name;

        // Get all midwives
        $midwives = User::where('role', 'midwife')->get();

        foreach ($midwives as $midwife) {
            $notificationData = array_merge($data, [
                'notified_by' => $currentUserName,
                'notified_by_role' => 'bhw',
                'recipient_role' => 'midwife',
                'is_cross_role' => true,
                'action_source' => 'BHW Data Entry',
                'priority' => 'urgent',
                'notification_category' => 'bhw_to_midwife_priority',
                'requires_attention' => true,
                'toast_priority' => 'urgent',
                'bhw_name' => $currentUserName,
                'timestamp' => now()->toISOString()
            ]);

            $enhancedTitle = "🚨 BHW Data Entry: " . $title;
            $enhancedMessage = "BHW {$currentUserName} has " . strtolower(substr($message, 0, 1)) . substr($message, 1) . " Please review this entry.";

            $midwife->notify(new HealthcareNotification(
                $enhancedTitle,
                $enhancedMessage,
                $type,
                $actionUrl,
                $notificationData
            ));

            // Clear notification cache for the recipient
            Cache::forget("unread_notifications_count_{$midwife->id}");
            Cache::forget("recent_notifications_{$midwife->id}");
        }
    }
}