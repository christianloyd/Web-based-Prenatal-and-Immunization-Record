/**
 * Immunization Management - Main Controller
 *
 * @module midwife/immunization
 * @requires @shared/utils/api
 * @requires @shared/utils/sweetalert
 */

import { ImmunizationState } from './state';
import { initializeFilters } from './filters';
import { initializeTable, refreshTable } from './table';
import { initializeFormHandlers } from './forms';
import {
    openAddModal,
    closeModal,
    openViewModal,
    closeViewModal,
    openRescheduleModal,
    closeRescheduleModal,
} from './modals';

/**
 * Application state instance
 * @type {ImmunizationState}
 */
let appState;

/**
 * Initializes the immunization management page
 * Called when DOM is fully loaded
 *
 * @returns {void}
 */
function initialize() {
    console.log('[Immunization] Initializing...');

    // Create state instance
    appState = new ImmunizationState();

    // Initialize all modules
    initializeFilters(appState);
    initializeTable(appState);
    initializeFormHandlers(appState);

    // Initialize modals
    initializeModalEventListeners();

    // Expose functions to global scope for onclick handlers
    // TODO: Refactor HTML to use event listeners instead of onclick
    exposeGlobalFunctions();

    console.log('[Immunization] Initialization complete');
}

/**
 * Initializes modal close button event listeners
 *
 * @private
 * @returns {void}
 */
function initializeModalEventListeners() {
    // Find all modal close buttons
    const closeButtons = document.querySelectorAll('[data-modal-close]');

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modalClose;

            switch (modalId) {
                case 'immunizationModal':
                    closeModal();
                    break;
                case 'viewModal':
                    closeViewModal();
                    break;
                case 'rescheduleModal':
                    closeRescheduleModal();
                    break;
                default:
                    console.warn(`[Immunization] Unknown modal ID: ${modalId}`);
            }
        });
    });
}

/**
 * Exposes functions to global scope for backward compatibility
 * This is temporary until all HTML onclick handlers are refactored
 *
 * @private
 * @returns {void}
 */
function exposeGlobalFunctions() {
    window.openAddModal = openAddModal;
    window.closeModal = closeModal;
    window.openViewModal = openViewModal;
    window.closeViewModal = closeViewModal;
    window.openRescheduleModal = openRescheduleModal;
    window.closeRescheduleModal = closeRescheduleModal;
    window.refreshImmunizationTable = () => refreshTable(appState);

    console.log('[Immunization] Global functions exposed (temporary)');
}

/**
 * Gets the current application state
 * Useful for debugging and external access
 *
 * @returns {ImmunizationState} Current state
 */
export function getState() {
    return appState;
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    // DOM already loaded
    initialize();
}

// Export for ES6 module usage
export {
    initialize,
    openAddModal,
    closeModal,
    openViewModal,
    closeViewModal,
    openRescheduleModal,
    closeRescheduleModal,
};
