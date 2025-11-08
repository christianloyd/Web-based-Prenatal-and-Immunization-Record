/**
 * BHW Reports Print JavaScript
 * Auto-print functionality for reports
 */

// Auto-print if requested via URL parameter
if (window.location.search.includes('autoprint=1')) {
    window.onload = function() {
        setTimeout(() => {
            window.print();
        }, 500);
    };
}
