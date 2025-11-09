/**
 * Application Routes Configuration
 * Centralized routing configuration for role-based access (BHW, Midwife, Admin)
 *
 * This eliminates duplicate route definitions across different role-specific files.
 * Each role has its own base path and route structure.
 *
 * @module shared/config/routes
 */

/**
 * Route configuration object indexed by role
 * @type {Object<string, Object>}
 */
export const routes = {
    /**
     * Barangay Health Worker (BHW) routes
     */
    bhw: {
        dashboard: '/bhw/dashboard',
        patients: {
            index: '/bhw/patients',
            create: '/bhw/patients/create',
            store: '/bhw/patients',
            show: (id) => `/bhw/patients/${id}`,
            edit: (id) => `/bhw/patients/${id}/edit`,
            update: (id) => `/bhw/patients/${id}`,
            destroy: (id) => `/bhw/patients/${id}`
        },
        prenatalRecords: {
            index: '/bhw/prenatalrecords',
            create: '/bhw/prenatalrecords/create',
            store: '/bhw/prenatalrecords',
            show: (id) => `/bhw/prenatalrecords/${id}`,
            edit: (id) => `/bhw/prenatalrecords/${id}/edit`,
            update: (id) => `/bhw/prenatalrecords/${id}`,
            destroy: (id) => `/bhw/prenatalrecords/${id}`
        },
        prenatalCheckups: {
            index: '/bhw/prenatalcheckups',
            create: '/bhw/prenatalcheckups/create',
            store: '/bhw/prenatalcheckups',
            show: (id) => `/bhw/prenatalcheckups/${id}`,
            edit: (id) => `/bhw/prenatalcheckups/${id}/edit`,
            update: (id) => `/bhw/prenatalcheckups/${id}`,
            destroy: (id) => `/bhw/prenatalcheckups/${id}`
        },
        childRecords: {
            index: '/bhw/childrecords',
            create: '/bhw/childrecords/create',
            store: '/bhw/childrecords',
            show: (id) => `/bhw/childrecords/${id}`,
            edit: (id) => `/bhw/childrecords/${id}/edit`,
            update: (id) => `/bhw/childrecords/${id}`,
            destroy: (id) => `/bhw/childrecords/${id}`
        },
        immunizations: {
            index: '/bhw/immunizations',
            create: '/bhw/immunizations/create',
            store: '/bhw/immunizations',
            show: (id) => `/bhw/immunizations/${id}`,
            edit: (id) => `/bhw/immunizations/${id}/edit`,
            update: (id) => `/bhw/immunizations/${id}`,
            destroy: (id) => `/bhw/immunizations/${id}`,
            reschedule: (id) => `/bhw/immunizations/${id}/reschedule`
        },
        appointments: {
            index: '/bhw/appointments',
            create: '/bhw/appointments/create',
            store: '/bhw/appointments',
            show: (id) => `/bhw/appointments/${id}`,
            update: (id) => `/bhw/appointments/${id}`,
            destroy: (id) => `/bhw/appointments/${id}`
        },
        reports: {
            index: '/bhw/reports',
            generate: '/bhw/reports/generate',
            export: '/bhw/reports/export'
        }
    },

    /**
     * Midwife routes
     */
    midwife: {
        dashboard: '/midwife/dashboard',
        patients: {
            index: '/midwife/patients',
            create: '/midwife/patients/create',
            store: '/midwife/patients',
            show: (id) => `/midwife/patients/${id}`,
            edit: (id) => `/midwife/patients/${id}/edit`,
            update: (id) => `/midwife/patients/${id}`,
            destroy: (id) => `/midwife/patients/${id}`
        },
        prenatalRecords: {
            index: '/midwife/prenatalrecords',
            create: '/midwife/prenatalrecords/create',
            store: '/midwife/prenatalrecords',
            show: (id) => `/midwife/prenatalrecords/${id}`,
            edit: (id) => `/midwife/prenatalrecords/${id}/edit`,
            update: (id) => `/midwife/prenatalrecords/${id}`,
            destroy: (id) => `/midwife/prenatalrecords/${id}`
        },
        prenatalCheckups: {
            index: '/midwife/prenatalcheckups',
            create: '/midwife/prenatalcheckups/create',
            store: '/midwife/prenatalcheckups',
            show: (id) => `/midwife/prenatalcheckups/${id}`,
            edit: (id) => `/midwife/prenatalcheckups/${id}/edit`,
            update: (id) => `/midwife/prenatalcheckups/${id}`,
            destroy: (id) => `/midwife/prenatalcheckups/${id}`
        },
        childRecords: {
            index: '/midwife/childrecords',
            create: '/midwife/childrecords/create',
            store: '/midwife/childrecords',
            show: (id) => `/midwife/childrecords/${id}`,
            edit: (id) => `/midwife/childrecords/${id}/edit`,
            update: (id) => `/midwife/childrecords/${id}`,
            destroy: (id) => `/midwife/childrecords/${id}`
        },
        immunizations: {
            index: '/midwife/immunizations',
            create: '/midwife/immunizations/create',
            store: '/midwife/immunizations',
            show: (id) => `/midwife/immunizations/${id}`,
            edit: (id) => `/midwife/immunizations/${id}/edit`,
            update: (id) => `/midwife/immunizations/${id}`,
            destroy: (id) => `/midwife/immunizations/${id}`,
            reschedule: (id) => `/midwife/immunizations/${id}/reschedule`
        },
        appointments: {
            index: '/midwife/appointments',
            create: '/midwife/appointments/create',
            store: '/midwife/appointments',
            show: (id) => `/midwife/appointments/${id}`,
            update: (id) => `/midwife/appointments/${id}`,
            destroy: (id) => `/midwife/appointments/${id}`
        },
        vaccines: {
            index: '/midwife/vaccines',
            create: '/midwife/vaccines/create',
            store: '/midwife/vaccines',
            show: (id) => `/midwife/vaccines/${id}`,
            edit: (id) => `/midwife/vaccines/${id}/edit`,
            update: (id) => `/midwife/vaccines/${id}`,
            destroy: (id) => `/midwife/vaccines/${id}`,
            stock: '/midwife/vaccines/stock'
        },
        reports: {
            index: '/midwife/reports',
            generate: '/midwife/reports/generate',
            export: '/midwife/reports/export'
        },
        cloudBackup: {
            index: '/midwife/cloudbackup',
            backup: '/midwife/cloudbackup/backup',
            restore: '/midwife/cloudbackup/restore',
            download: (id) => `/midwife/cloudbackup/${id}/download`,
            delete: (id) => `/midwife/cloudbackup/${id}`
        }
    },

    /**
     * Admin routes
     */
    admin: {
        dashboard: '/admin/dashboard',
        users: {
            index: '/admin/users',
            create: '/admin/users/create',
            store: '/admin/users',
            show: (id) => `/admin/users/${id}`,
            edit: (id) => `/admin/users/${id}/edit`,
            update: (id) => `/admin/users/${id}`,
            destroy: (id) => `/admin/users/${id}`,
            activate: (id) => `/admin/users/${id}/activate`,
            deactivate: (id) => `/admin/users/${id}/deactivate`
        },
        reports: {
            index: '/admin/reports',
            analytics: '/admin/reports/analytics',
            export: '/admin/reports/export'
        },
        settings: {
            index: '/admin/settings',
            update: '/admin/settings'
        }
    }
};

/**
 * Get routes for the current user role
 *
 * @param {string} role - User role (bhw, midwife, admin)
 * @returns {Object} Role-specific routes
 *
 * @example
 * const userRoutes = getRoutesForRole('midwife');
 * console.log(userRoutes.patients.index); // '/midwife/patients'
 */
export function getRoutesForRole(role) {
    if (!routes[role]) {
        console.warn(`[Routes] Role "${role}" not found in routes configuration`);
        return {};
    }
    return routes[role];
}

/**
 * Get the current user role from the page context
 * Attempts multiple methods to determine role
 *
 * @returns {string|null} Current user role or null if not found
 *
 * @example
 * const role = getCurrentRole();
 * const routes = getRoutesForRole(role);
 */
export function getCurrentRole() {
    // Method 1: Check window.userRole (if set by Blade template)
    if (window.userRole) {
        return window.userRole;
    }

    // Method 2: Check meta tag
    const metaRole = document.querySelector('meta[name="user-role"]');
    if (metaRole) {
        return metaRole.getAttribute('content');
    }

    // Method 3: Infer from URL path
    const path = window.location.pathname;
    if (path.startsWith('/bhw')) return 'bhw';
    if (path.startsWith('/midwife')) return 'midwife';
    if (path.startsWith('/admin')) return 'admin';

    console.warn('[Routes] Unable to determine current user role');
    return null;
}

/**
 * Get routes for the current authenticated user
 *
 * @returns {Object} Current user's routes
 *
 * @example
 * const routes = getCurrentRoutes();
 * fetch(routes.patients.index)
 *     .then(response => response.json())
 *     .then(data => console.log(data));
 */
export function getCurrentRoutes() {
    const role = getCurrentRole();
    return getRoutesForRole(role);
}

/**
 * Build URL with query parameters
 *
 * @param {string} url - Base URL
 * @param {Object} params - Query parameters
 * @returns {string} URL with query string
 *
 * @example
 * const url = buildUrl('/midwife/patients', { status: 'active', page: 2 });
 * // Returns: '/midwife/patients?status=active&page=2'
 */
export function buildUrl(url, params = {}) {
    const queryString = Object.entries(params)
        .filter(([_, value]) => value !== null && value !== undefined && value !== '')
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
        .join('&');

    return queryString ? `${url}?${queryString}` : url;
}

// Export as default
export default {
    routes,
    getRoutesForRole,
    getCurrentRole,
    getCurrentRoutes,
    buildUrl
};
