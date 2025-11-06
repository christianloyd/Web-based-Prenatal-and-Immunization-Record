<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Vaccine;
use App\Models\User;

/**
 * Cache Service
 *
 * Centralized caching for frequently accessed data
 */
class CacheService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;

    /**
     * Short cache duration (5 minutes) for frequently changing data
     */
    const SHORT_CACHE = 300;

    /**
     * Get all active vaccines (cached)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveVaccines()
    {
        return Cache::remember('active_vaccines', self::CACHE_DURATION, function () {
            return Vaccine::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get vaccine by ID (cached)
     *
     * @param int $id
     * @return Vaccine|null
     */
    public static function getVaccine(int $id)
    {
        return Cache::remember("vaccine_{$id}", self::CACHE_DURATION, function () use ($id) {
            return Vaccine::find($id);
        });
    }

    /**
     * Clear vaccine cache
     *
     * @param int|null $id Specific vaccine ID or null for all
     */
    public static function clearVaccineCache(?int $id = null): void
    {
        if ($id) {
            Cache::forget("vaccine_{$id}");
        } else {
            Cache::forget('active_vaccines');
            // Clear all vaccine caches
            Vaccine::all()->each(function ($vaccine) {
                Cache::forget("vaccine_{$vaccine->id}");
            });
        }
    }

    /**
     * Get all active users (cached)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveUsers()
    {
        return Cache::remember('active_users', self::CACHE_DURATION, function () {
            return User::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get users by role (cached)
     *
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUsersByRole(string $role)
    {
        return Cache::remember("users_role_{$role}", self::CACHE_DURATION, function () use ($role) {
            return User::where('role', $role)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear user cache
     *
     * @param int|null $id Specific user ID or null for all
     */
    public static function clearUserCache(?int $id = null): void
    {
        Cache::forget('active_users');
        Cache::forget('users_role_midwife');
        Cache::forget('users_role_bhw');
        Cache::forget('users_role_admin');

        if ($id) {
            Cache::forget("user_{$id}");
        }
    }

    /**
     * Get dashboard statistics (cached for 5 minutes)
     *
     * @param string $role
     * @return array
     */
    public static function getDashboardStats(string $role): array
    {
        return Cache::remember("dashboard_stats_{$role}", self::SHORT_CACHE, function () use ($role) {
            return [
                'total_patients' => \App\Models\Patient::count(),
                'active_prenatal' => \App\Models\PrenatalRecord::where('is_active', true)->count(),
                'upcoming_immunizations' => \App\Models\Immunization::where('status', 'Upcoming')->count(),
                'low_stock_vaccines' => \App\Models\Vaccine::where('current_stock', '<=', 10)->count(),
            ];
        });
    }

    /**
     * Clear dashboard statistics cache
     */
    public static function clearDashboardCache(): void
    {
        Cache::forget('dashboard_stats_midwife');
        Cache::forget('dashboard_stats_bhw');
        Cache::forget('dashboard_stats_admin');
    }

    /**
     * Get notification count for user (cached briefly)
     *
     * @param int $userId
     * @return int
     */
    public static function getUnreadNotificationCount(int $userId): int
    {
        return Cache::remember("unread_notifications_count_{$userId}", self::SHORT_CACHE, function () use ($userId) {
            return \App\Models\User::find($userId)->unreadNotifications()->count();
        });
    }

    /**
     * Clear notification cache for user
     *
     * @param int $userId
     */
    public static function clearNotificationCache(int $userId): void
    {
        Cache::forget("unread_notifications_count_{$userId}");
        Cache::forget("recent_notifications_{$userId}");
    }

    /**
     * Clear all application caches
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public static function warmUp(): void
    {
        self::getActiveVaccines();
        self::getActiveUsers();
        self::getUsersByRole('midwife');
        self::getUsersByRole('bhw');
    }
}
