/* ========================================
   User Management Index Module JavaScript
   ======================================== */

// Laravel Routes Configuration for JavaScript
window.userManagementRoutes = {
    store: document.querySelector('meta[data-route-store]')?.getAttribute('data-route-store'),
    update: document.querySelector('meta[data-route-update]')?.getAttribute('data-route-update'),
    deactivate: document.querySelector('meta[data-route-deactivate]')?.getAttribute('data-route-deactivate'),
    activate: document.querySelector('meta[data-route-activate]')?.getAttribute('data-route-activate')
};

// Initialize routes from blade template data if available
// These will be populated from the blade template via meta tags or inline script
