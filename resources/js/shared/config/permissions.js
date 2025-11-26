/**
 * Application Permissions Configuration
 * Centralized permissions configuration for role-based access control
 *
 * This eliminates duplicate permission checks across different role-specific files.
 * Defines what actions each role can perform on different resources.
 *
 * @module shared/config/permissions
 */

/**
 * Permission definitions for each role
 * @type {Object<string, Object>}
 */
export const permissions = {
    /**
     * Barangay Health Worker (BHW) permissions
     */
    bhw: {
        patients: {
            view: true,
            create: true,
            edit: true,
            delete: false,  // BHW cannot delete patients
            export: false
        },
        prenatalRecords: {
            view: true,
            create: true,
            edit: true,
            delete: false,
            export: false,
            complete: true
        },
        prenatalCheckups: {
            view: true,
            create: true,
            edit: true,
            delete: false,
            export: false
        },
        childRecords: {
            view: true,
            create: true,
            edit: true,
            delete: false,
            export: false
        },
        immunizations: {
            view: true,
            create: true,
            edit: true,
            delete: false,
            reschedule: true,
            export: false
        },
        appointments: {
            view: true,
            create: true,
            edit: true,
            delete: false,
            export: false
        },
        vaccines: {
            view: false,  // BHW cannot manage vaccines
            create: false,
            edit: false,
            delete: false,
            manageStock: false
        },
        reports: {
            view: true,
            generate: true,
            export: false  // BHW has limited export
        },
        users: {
            view: false,
            create: false,
            edit: false,
            delete: false,
            manage: false
        },
        cloudBackup: {
            view: false,
            create: false,
            restore: false,
            delete: false
        }
    },

    /**
     * Midwife permissions (full access to most features)
     */
    midwife: {
        patients: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        prenatalRecords: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true,
            complete: true
        },
        prenatalCheckups: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        childRecords: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        immunizations: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            reschedule: true,
            export: true
        },
        appointments: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        vaccines: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            manageStock: true
        },
        reports: {
            view: true,
            generate: true,
            export: true
        },
        users: {
            view: false,  // Midwife cannot manage users
            create: false,
            edit: false,
            delete: false,
            manage: false
        },
        cloudBackup: {
            view: true,
            create: true,
            restore: true,
            delete: true
        }
    },

    /**
     * Admin permissions (full system access)
     */
    admin: {
        patients: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        prenatalRecords: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        prenatalCheckups: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        childRecords: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        immunizations: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            reschedule: true,
            export: true
        },
        appointments: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            export: true
        },
        vaccines: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            manageStock: true
        },
        reports: {
            view: true,
            generate: true,
            export: true
        },
        users: {
            view: true,
            create: true,
            edit: true,
            delete: true,
            manage: true
        },
        cloudBackup: {
            view: true,
            create: true,
            restore: true,
            delete: true
        },
        settings: {
            view: true,
            edit: true
        }
    }
};

/**
 * Check if a user has permission to perform an action on a resource
 *
 * @param {string} role - User role (bhw, midwife, admin)
 * @param {string} resource - Resource name (e.g., 'patients', 'vaccines')
 * @param {string} action - Action name (e.g., 'view', 'create', 'edit', 'delete')
 * @returns {boolean} True if user has permission
 *
 * @example
 * if (hasPermission('bhw', 'patients', 'delete')) {
 *     showDeleteButton();
 * }
 */
export function hasPermission(role, resource, action) {
    if (!permissions[role]) {
        console.warn(`[Permissions] Role "${role}" not found`);
        return false;
    }

    if (!permissions[role][resource]) {
        console.warn(`[Permissions] Resource "${resource}" not found for role "${role}"`);
        return false;
    }

    return permissions[role][resource][action] === true;
}

/**
 * Get all permissions for a specific role
 *
 * @param {string} role - User role (bhw, midwife, admin)
 * @returns {Object} Role permissions object
 *
 * @example
 * const userPerms = getPermissionsForRole('midwife');
 * console.log(userPerms.vaccines.manageStock); // true
 */
export function getPermissionsForRole(role) {
    if (!permissions[role]) {
        console.warn(`[Permissions] Role "${role}" not found in permissions configuration`);
        return {};
    }
    return permissions[role];
}

/**
 * Get permissions for a specific resource and role
 *
 * @param {string} role - User role (bhw, midwife, admin)
 * @param {string} resource - Resource name
 * @returns {Object} Resource-specific permissions
 *
 * @example
 * const patientPerms = getResourcePermissions('bhw', 'patients');
 * if (patientPerms.edit) {
 *     enableEditMode();
 * }
 */
export function getResourcePermissions(role, resource) {
    const rolePerms = getPermissionsForRole(role);
    if (!rolePerms[resource]) {
        console.warn(`[Permissions] Resource "${resource}" not found for role "${role}"`);
        return {};
    }
    return rolePerms[resource];
}

/**
 * Check multiple permissions at once
 *
 * @param {string} role - User role
 * @param {Array<{resource: string, action: string}>} checks - Array of permission checks
 * @returns {boolean} True if user has ALL specified permissions
 *
 * @example
 * const canManagePatients = checkMultiplePermissions('midwife', [
 *     { resource: 'patients', action: 'view' },
 *     { resource: 'patients', action: 'edit' },
 *     { resource: 'patients', action: 'delete' }
 * ]);
 */
export function checkMultiplePermissions(role, checks) {
    return checks.every(({ resource, action }) => hasPermission(role, resource, action));
}

/**
 * Check if user has ANY of the specified permissions
 *
 * @param {string} role - User role
 * @param {Array<{resource: string, action: string}>} checks - Array of permission checks
 * @returns {boolean} True if user has AT LEAST ONE of the specified permissions
 *
 * @example
 * const canAccessReports = checkAnyPermission('bhw', [
 *     { resource: 'reports', action: 'generate' },
 *     { resource: 'reports', action: 'export' }
 * ]);
 */
export function checkAnyPermission(role, checks) {
    return checks.some(({ resource, action }) => hasPermission(role, resource, action));
}

/**
 * Get the current user role from the page context
 *
 * @returns {string|null} Current user role or null if not found
 */
export function getCurrentRole() {
    // Method 1: Check window.userRole
    if (window.userRole) {
        return window.userRole;
    }

    // Method 2: Check meta tag
    const metaRole = document.querySelector('meta[name="user-role"]');
    if (metaRole) {
        return metaRole.getAttribute('content');
    }

    // Method 3: Infer from URL
    const path = window.location.pathname;
    if (path.startsWith('/bhw')) return 'bhw';
    if (path.startsWith('/midwife')) return 'midwife';
    if (path.startsWith('/admin')) return 'admin';

    console.warn('[Permissions] Unable to determine current user role');
    return null;
}

/**
 * Check if current user has permission for a resource/action
 *
 * @param {string} resource - Resource name
 * @param {string} action - Action name
 * @returns {boolean} True if current user has permission
 *
 * @example
 * if (can('patients', 'delete')) {
 *     showDeleteButton();
 * } else {
 *     hideDeleteButton();
 * }
 */
export function can(resource, action) {
    const role = getCurrentRole();
    return hasPermission(role, resource, action);
}

/**
 * Check if current user CANNOT perform action (convenience method)
 *
 * @param {string} resource - Resource name
 * @param {string} action - Action name
 * @returns {boolean} True if current user CANNOT perform action
 *
 * @example
 * if (cannot('vaccines', 'manageStock')) {
 *     alert('You do not have permission to manage vaccine stock');
 * }
 */
export function cannot(resource, action) {
    return !can(resource, action);
}

// Export as default
export default {
    permissions,
    hasPermission,
    getPermissionsForRole,
    getResourcePermissions,
    checkMultiplePermissions,
    checkAnyPermission,
    getCurrentRole,
    can,
    cannot
};
