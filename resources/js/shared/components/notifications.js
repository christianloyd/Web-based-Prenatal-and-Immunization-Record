/**
 * Shared Notifications Component
 * Toast-style notifications for non-blocking user feedback
 *
 * This module provides a lightweight notification system as an alternative
 * to SweetAlert for less intrusive messages.
 *
 * @module shared/components/notifications
 */

import { createElement } from '../utils/dom.js';

/**
 * Notification configuration
 * @type {Object}
 */
const config = {
    duration: 3000, // Default duration in milliseconds
    position: 'top-right', // top-right, top-left, bottom-right, bottom-left, top-center, bottom-center
    maxNotifications: 5,
    animationDuration: 300
};

/**
 * Notification container element
 * @type {HTMLElement|null}
 */
let container = null;

/**
 * Active notifications
 * @type {Array}
 */
const activeNotifications = [];

/**
 * Get or create notification container
 * @private
 *
 * @returns {HTMLElement} Container element
 */
function getContainer() {
    if (!container) {
        container = createElement('div', {
            id: 'notification-container',
            class: `fixed ${getPositionClasses(config.position)} z-50 pointer-events-none`,
            style: 'max-width: 400px;'
        });
        document.body.appendChild(container);
    }
    return container;
}

/**
 * Get CSS classes for position
 * @private
 *
 * @param {string} position - Position key
 * @returns {string} CSS classes
 */
function getPositionClasses(position) {
    const positions = {
        'top-right': 'top-4 right-4',
        'top-left': 'top-4 left-4',
        'bottom-right': 'bottom-4 right-4',
        'bottom-left': 'bottom-4 left-4',
        'top-center': 'top-4 left-1/2 -translate-x-1/2',
        'bottom-center': 'bottom-4 left-1/2 -translate-x-1/2'
    };
    return positions[position] || positions['top-right'];
}

/**
 * Create notification element
 * @private
 *
 * @param {string} type - Notification type (success, error, warning, info)
 * @param {string} message - Notification message
 * @param {string} title - Optional title
 * @returns {HTMLElement} Notification element
 */
function createNotification(type, message, title = null) {
    const colors = {
        success: { bg: 'bg-green-50', border: 'border-green-500', text: 'text-green-800', icon: 'fa-check-circle', iconColor: 'text-green-500' },
        error: { bg: 'bg-red-50', border: 'border-red-500', text: 'text-red-800', icon: 'fa-times-circle', iconColor: 'text-red-500' },
        warning: { bg: 'bg-yellow-50', border: 'border-yellow-500', text: 'text-yellow-800', icon: 'fa-exclamation-triangle', iconColor: 'text-yellow-500' },
        info: { bg: 'bg-blue-50', border: 'border-blue-500', text: 'text-blue-800', icon: 'fa-info-circle', iconColor: 'text-blue-500' }
    };

    const style = colors[type] || colors.info;

    const iconEl = createElement('div', {
        class: `flex-shrink-0 ${style.iconColor}`
    }, createElement('i', { class: `fas ${style.icon} text-xl` }));

    const contentEl = createElement('div', { class: 'ml-3 flex-1' }, [
        title ? createElement('p', { class: 'text-sm font-semibold' }, title) : null,
        createElement('p', { class: 'text-sm' }, message)
    ].filter(Boolean));

    const closeBtn = createElement('button', {
        class: 'ml-4 flex-shrink-0 inline-flex text-gray-400 hover:text-gray-500 focus:outline-none',
        type: 'button'
    }, createElement('i', { class: 'fas fa-times' }));

    const notification = createElement('div', {
        class: `${style.bg} ${style.border} ${style.text} border-l-4 p-4 mb-4 rounded-lg shadow-lg pointer-events-auto transform transition-all duration-300 opacity-0 translate-x-full`,
        role: 'alert'
    }, [
        createElement('div', { class: 'flex items-start' }, [iconEl, contentEl, closeBtn])
    ]);

    return { element: notification, closeBtn };
}

/**
 * Show notification
 * @private
 *
 * @param {string} type - Notification type
 * @param {string} message - Notification message
 * @param {string} title - Optional title
 * @param {number} duration - Duration in milliseconds
 * @returns {void}
 */
function showNotification(type, message, title = null, duration = config.duration) {
    const containerEl = getContainer();

    // Limit number of notifications
    if (activeNotifications.length >= config.maxNotifications) {
        const oldest = activeNotifications.shift();
        removeNotification(oldest);
    }

    const { element, closeBtn } = createNotification(type, message, title);

    // Add to container
    containerEl.appendChild(element);
    activeNotifications.push(element);

    // Animate in
    requestAnimationFrame(() => {
        element.classList.remove('opacity-0', 'translate-x-full');
        element.classList.add('opacity-100', 'translate-x-0');
    });

    // Close button handler
    closeBtn.addEventListener('click', () => {
        removeNotification(element);
    });

    // Auto-remove after duration
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(element);
        }, duration);
    }
}

/**
 * Remove notification
 * @private
 *
 * @param {HTMLElement} element - Notification element
 * @returns {void}
 */
function removeNotification(element) {
    if (!element || !element.parentNode) return;

    // Animate out
    element.classList.remove('opacity-100', 'translate-x-0');
    element.classList.add('opacity-0', 'translate-x-full');

    // Remove from DOM after animation
    setTimeout(() => {
        if (element.parentNode) {
            element.parentNode.removeChild(element);
        }

        // Remove from active list
        const index = activeNotifications.indexOf(element);
        if (index > -1) {
            activeNotifications.splice(index, 1);
        }
    }, config.animationDuration);
}

/**
 * Show success notification
 *
 * @param {string} message - Success message
 * @param {string} title - Optional title (default: 'Success')
 * @param {number} duration - Duration in milliseconds
 * @returns {void}
 *
 * @example
 * success('Patient record saved successfully!');
 * success('Record saved', 'Success!', 5000);
 */
export function success(message, title = 'Success', duration = config.duration) {
    showNotification('success', message, title, duration);
}

/**
 * Show error notification
 *
 * @param {string} message - Error message
 * @param {string} title - Optional title (default: 'Error')
 * @param {number} duration - Duration in milliseconds
 * @returns {void}
 *
 * @example
 * error('Failed to save patient record');
 * error('Network error', 'Error!', 5000);
 */
export function error(message, title = 'Error', duration = config.duration) {
    showNotification('error', message, title, duration);
}

/**
 * Show warning notification
 *
 * @param {string} message - Warning message
 * @param {string} title - Optional title (default: 'Warning')
 * @param {number} duration - Duration in milliseconds
 * @returns {void}
 *
 * @example
 * warning('This action cannot be undone');
 * warning('Check your input', 'Warning!', 5000);
 */
export function warning(message, title = 'Warning', duration = config.duration) {
    showNotification('warning', message, title, duration);
}

/**
 * Show info notification
 *
 * @param {string} message - Info message
 * @param {string} title - Optional title (default: 'Info')
 * @param {number} duration - Duration in milliseconds
 * @returns {void}
 *
 * @example
 * info('New update available');
 * info('Processing your request', 'Please wait', 5000);
 */
export function info(message, title = 'Info', duration = config.duration) {
    showNotification('info', message, title, duration);
}

/**
 * Clear all notifications
 *
 * @returns {void}
 *
 * @example
 * clearAll();
 */
export function clearAll() {
    activeNotifications.forEach(notification => {
        removeNotification(notification);
    });
}

/**
 * Configure notification system
 *
 * @param {Object} options - Configuration options
 * @param {number} options.duration - Default duration
 * @param {string} options.position - Default position
 * @param {number} options.maxNotifications - Maximum notifications
 * @returns {void}
 *
 * @example
 * configure({ duration: 5000, position: 'bottom-right', maxNotifications: 3 });
 */
export function configure(options = {}) {
    Object.assign(config, options);

    // Update container position if it exists
    if (container) {
        const positionClasses = getPositionClasses(config.position);
        container.className = `fixed ${positionClasses} z-50 pointer-events-none`;
    }
}

/**
 * Initialize notifications from Laravel flash messages
 *
 * @returns {void}
 */
export function initializeFlashMessages() {
    const flashSuccess = document.querySelector('[data-toast-success]');
    const flashError = document.querySelector('[data-toast-error]');
    const flashWarning = document.querySelector('[data-toast-warning]');
    const flashInfo = document.querySelector('[data-toast-info]');

    if (flashSuccess) {
        success(flashSuccess.getAttribute('data-toast-success'));
    }

    if (flashError) {
        error(flashError.getAttribute('data-toast-error'));
    }

    if (flashWarning) {
        warning(flashWarning.getAttribute('data-toast-warning'));
    }

    if (flashInfo) {
        info(flashInfo.getAttribute('data-toast-info'));
    }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initializeFlashMessages);

// Export as default
export default {
    success,
    error,
    warning,
    info,
    clearAll,
    configure
};
