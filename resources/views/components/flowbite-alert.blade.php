{{-- Centered Overlay Alert Component --}}
{{-- Debug: Check if session exists --}}
@if(config('app.debug'))
<!-- Debug: Success={{ session('success') ? 'YES' : 'NO' }}, Error={{ session('error') ? 'YES' : 'NO' }} -->
@endif

@if(session('success'))
<div class="healthcare-alert-overlay" id="alert-overlay-success">
    <div class="healthcare-alert healthcare-alert-success flex items-center justify-center p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert" data-alert-type="success">
        <svg class="healthcare-alert-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
        </svg>
        <span class="sr-only">Success</span>
        <div class="text-center flex-1">
            <span class="font-medium">Success!</span> {{ session('success') }}
        </div>
        <button type="button" class="healthcare-alert-close -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div class="healthcare-alert-overlay" id="alert-overlay-error">
    <div class="healthcare-alert healthcare-alert-error flex items-center justify-center p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert" data-alert-type="error">
        <svg class="healthcare-alert-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
        </svg>
        <span class="sr-only">Error</span>
        <div class="text-center flex-1">
            <span class="font-medium">Error!</span> {{ session('error') }}
        </div>
        <button type="button" class="healthcare-alert-close -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('warning'))
<div class="healthcare-alert-overlay" id="alert-overlay-warning">
    <div class="healthcare-alert healthcare-alert-warning flex items-center justify-center p-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 border border-yellow-200" role="alert" data-alert-type="warning">
        <svg class="healthcare-alert-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
        </svg>
        <span class="sr-only">Warning</span>
        <div class="text-center flex-1">
            <span class="font-medium">Warning!</span> {{ session('warning') }}
        </div>
        <button type="button" class="healthcare-alert-close -mx-1.5 -my-1.5 bg-yellow-50 text-yellow-500 rounded-lg focus:ring-2 focus:ring-yellow-400 p-1.5 hover:bg-yellow-200 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('info'))
<div class="healthcare-alert-overlay" id="alert-overlay-info">
    <div class="healthcare-alert healthcare-alert-info flex items-center justify-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 border border-blue-200" role="alert" data-alert-type="info">
        <svg class="healthcare-alert-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
        </svg>
        <span class="sr-only">Info</span>
        <div class="text-center flex-1">
            <span class="font-medium">Info!</span> {{ session('info') }}
        </div>
        <button type="button" class="healthcare-alert-close -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg focus:ring-2 focus:ring-blue-400 p-1.5 hover:bg-blue-200 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
</div>
@endif

<style>
/* Healthcare Alert Overlay Styles - Top Center Display */
.healthcare-alert-overlay {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 99999 !important;
    pointer-events: none;
}

.healthcare-alert {
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    transform: translateY(0);
    opacity: 1;
    box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1), 0 4px 8px -2px rgba(0, 0, 0, 0.06);
    width: fit-content;
    max-width: min(90vw, 600px);
    min-width: 200px;
    pointer-events: auto;
}

.healthcare-alert-icon {
    width: 1.75rem;
    height: 1.75rem;
    margin-right: 0.75rem;
    flex-shrink: 0;
    color: currentColor;
}

.healthcare-alert-success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-left: 4px solid #16a34a;
}

.healthcare-alert-error {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-left: 4px solid #dc2626;
}

.healthcare-alert-warning {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-left: 4px solid #d97706;
}

.healthcare-alert-info {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-left: 4px solid #2563eb;
}

.healthcare-alert.slide-in {
    animation: slideInFromTop 0.5s ease-out;
}

.healthcare-alert.slide-out {
    animation: slideOutToTop 0.4s ease-in;
}

@keyframes slideInFromTop {
    0% {
        transform: translateY(-100px);
        opacity: 0;
    }
    50% {
        transform: translateY(10px);
        opacity: 0.8;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideOutToTop {
    0% {
        transform: translateY(0);
        opacity: 1;
    }
    100% {
        transform: translateY(-100px);
        opacity: 0;
    }
}

.healthcare-alert-close {
    transition: all 0.2s ease;
}

.healthcare-alert-close:hover {
    transform: scale(1.1);
}
</style>

<script>
// Healthcare Alert Overlay System - Centered Screen Display
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all alert overlays
    const alertOverlays = document.querySelectorAll('.healthcare-alert-overlay');

    alertOverlays.forEach((overlay, index) => {
        const alert = overlay.querySelector('.healthcare-alert');

        // Auto-adjust width based on content length
        const textContent = alert.querySelector('div');
        if (textContent) {
            const textLength = textContent.textContent.length;
            if (textLength < 50) {
                alert.style.whiteSpace = 'nowrap';
                alert.style.width = 'fit-content';
            } else {
                alert.style.whiteSpace = 'normal';
                alert.style.maxWidth = 'min(90vw, 500px)';
            }
        }

        // Initially hide the overlay off-screen (top)
        overlay.style.transform = 'translate(-50%, -100px)';
        overlay.style.opacity = '0';

        // Add slide-in animation with staggered delay
        setTimeout(() => {
            overlay.style.transition = 'all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            overlay.style.transform = 'translateX(-50%)';
            overlay.style.opacity = '1';
            alert.classList.add('slide-in');
        }, index * 100);

        // Set up close button functionality
        const closeButton = alert.querySelector('.healthcare-alert-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                hideAlertOverlay(overlay);
            });
        }

        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (overlay && overlay.parentNode) {
                hideAlertOverlay(overlay);
            }
        }, 5000 + (index * 100));
    });

    function hideAlertOverlay(overlayElement) {
        if (!overlayElement || !overlayElement.parentNode) return;

        const alert = overlayElement.querySelector('.healthcare-alert');
        if (alert) {
            alert.classList.add('slide-out');
        }

        overlayElement.style.transform = 'translate(-50%, -100px)';
        overlayElement.style.opacity = '0';
        overlayElement.style.pointerEvents = 'none';

        setTimeout(() => {
            if (overlayElement.parentNode) {
                overlayElement.parentNode.removeChild(overlayElement);
            }
        }, 400);
    }
});

// Global healthcare alert system for dynamic alerts (matching immunization system)
window.healthcareAlert = {
    show: function(type, title, message) {
        // Remove any existing alerts first
        this.removeExisting();

        const alertHtml = this.createAlertHtml(type, title, message);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = alertHtml;
        const alertElement = tempDiv.firstElementChild;

        // Create overlay wrapper for centered positioning
        const overlay = document.createElement('div');
        overlay.className = 'healthcare-alert-overlay';
        overlay.setAttribute('data-dynamic-alert', 'true');

        // Position overlay at top center of screen
        overlay.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translate(-50%, -100px);
            z-index: 99999;
            pointer-events: none;
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        `;

        // Configure alert element
        alertElement.style.cssText = `
            max-width: min(90vw, 600px);
            min-width: 200px;
            width: auto;
            pointer-events: auto;
        `;

        // Auto-adjust width based on content
        const textContent = alertElement.querySelector('div');
        if (textContent) {
            const textLength = textContent.textContent.length;
            if (textLength < 50) {
                alertElement.style.whiteSpace = 'nowrap';
            } else {
                alertElement.style.whiteSpace = 'normal';
                alertElement.style.maxWidth = 'min(90vw, 500px)';
            }
        }

        overlay.appendChild(alertElement);
        document.body.appendChild(overlay);

        // Trigger slide-in animation
        setTimeout(() => {
            overlay.style.transform = 'translateX(-50%)';
            overlay.style.opacity = '1';
        }, 10);

        // Set up close button
        const closeButton = alertElement.querySelector('.healthcare-alert-close');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                this.hide(overlay);
            });
        }

        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.hide(overlay);
        }, 5000);

        return overlay;
    },

    createAlertHtml: function(type, title, message) {
        const alertConfigs = {
            success: {
                bgClass: 'healthcare-alert-success',
                textClass: 'text-green-800',
                closeClass: 'bg-green-50 text-green-500 hover:bg-green-200',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            },
            error: {
                bgClass: 'healthcare-alert-error',
                textClass: 'text-red-800',
                closeClass: 'bg-red-50 text-red-500 hover:bg-red-200',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            },
            warning: {
                bgClass: 'healthcare-alert-warning',
                textClass: 'text-yellow-800',
                closeClass: 'bg-yellow-50 text-yellow-500 hover:bg-yellow-200',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            },
            info: {
                bgClass: 'healthcare-alert-info',
                textClass: 'text-blue-800',
                closeClass: 'bg-blue-50 text-blue-500 hover:bg-blue-200',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            }
        };

        const config = alertConfigs[type] || alertConfigs.info;

        return `
            <div class="healthcare-alert ${config.bgClass} flex items-center justify-center p-4 text-sm ${config.textClass} rounded-lg border border-current/20" role="alert">
                <svg class="healthcare-alert-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="${config.iconPath}"/>
                </svg>
                <span class="sr-only">${type}</span>
                <div class="text-center flex-1">
                    <span class="font-medium">${title}</span> ${message}
                </div>
                <button type="button" class="healthcare-alert-close -mx-1.5 -my-1.5 ${config.closeClass} rounded-lg focus:ring-2 focus:ring-current/20 p-1.5 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        `;
    },

    hide: function(overlayElement) {
        if (!overlayElement || !overlayElement.parentNode) return;

        const alert = overlayElement.querySelector('.healthcare-alert');
        if (alert) {
            alert.classList.add('slide-out');
        }

        overlayElement.style.transform = 'translate(-50%, -100px)';
        overlayElement.style.opacity = '0';
        overlayElement.style.pointerEvents = 'none';

        setTimeout(() => {
            if (overlayElement.parentNode) {
                overlayElement.parentNode.removeChild(overlayElement);
            }
        }, 400);
    },

    removeExisting: function() {
        const existingAlerts = document.querySelectorAll('[data-dynamic-alert="true"]');
        existingAlerts.forEach(alert => {
            this.hide(alert);
        });
    },

    // Convenience methods
    success: function(message, title = 'Success!') {
        return this.show('success', title, message);
    },

    error: function(message, title = 'Error!') {
        return this.show('error', title, message);
    },

    warning: function(message, title = 'Warning!') {
        return this.show('warning', title, message);
    },

    info: function(message, title = 'Info!') {
        return this.show('info', title, message);
    }
};

// Test function for the new alert system
window.testHealthcareAlerts = function() {
    setTimeout(() => window.healthcareAlert.success('This is a success alert with enhanced design'), 500);
    setTimeout(() => window.healthcareAlert.error('This is an error alert with enhanced design'), 1000);
    setTimeout(() => window.healthcareAlert.warning('This is a warning alert with enhanced design'), 1500);
    setTimeout(() => window.healthcareAlert.info('This is an info alert with enhanced design'), 2000);
};
</script>