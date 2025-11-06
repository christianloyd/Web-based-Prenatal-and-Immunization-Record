<?php

namespace App\Enums;

/**
 * User Role Enum
 *
 * Defines the possible roles for system users
 */
enum UserRole: string
{
    case MIDWIFE = 'midwife';
    case BHW = 'bhw';
    case ADMIN = 'admin';

    /**
     * Get all role values as array
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all role names as array
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Get role label for display
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::MIDWIFE => 'Midwife',
            self::BHW => 'Barangay Health Worker',
            self::ADMIN => 'Administrator',
        };
    }

    /**
     * Get role badge class for UI
     *
     * @return string
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::MIDWIFE => 'bg-purple-100 text-purple-800',
            self::BHW => 'bg-blue-100 text-blue-800',
            self::ADMIN => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Get role icon
     *
     * @return string
     */
    public function icon(): string
    {
        return match($this) {
            self::MIDWIFE => 'fa-user-nurse',
            self::BHW => 'fa-user-md',
            self::ADMIN => 'fa-user-shield',
        };
    }

    /**
     * Check if role is midwife
     *
     * @return bool
     */
    public function isMidwife(): bool
    {
        return $this === self::MIDWIFE;
    }

    /**
     * Check if role is BHW
     *
     * @return bool
     */
    public function isBhw(): bool
    {
        return $this === self::BHW;
    }

    /**
     * Check if role is admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Check if role has permission for a specific action
     *
     * @param string $permission
     * @return bool
     */
    public function can(string $permission): bool
    {
        return match($permission) {
            'manage_users' => $this === self::MIDWIFE || $this === self::ADMIN,
            'manage_vaccines' => $this === self::MIDWIFE || $this === self::ADMIN,
            'cloud_backup' => $this === self::MIDWIFE || $this === self::ADMIN,
            'view_reports' => true, // All roles can view reports
            'create_patients' => true, // All roles can create patients
            'manage_immunizations' => true, // All roles can manage immunizations
            default => false,
        };
    }
}
