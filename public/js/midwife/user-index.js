/* ========================================
   User Management Index Module JavaScript
   ======================================== */

// Laravel Routes Configuration for JavaScript
// Note: Routes are set via inline script in the Blade template
// This ensures window.userManagementRoutes is available before modules load
if (!window.userManagementRoutes) {
    console.warn('[User Management] Routes not found. Ensure they are defined in the Blade template.');
    window.userManagementRoutes = {
        store: '',
        update: '',
        deactivate: '',
        activate: ''
    };
}
