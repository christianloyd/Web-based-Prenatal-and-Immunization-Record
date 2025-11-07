/**
 * Child Record Index Module
 * Main coordinator that initializes and exposes all child record functionality
 */

// Import all modules
import { initializeForms } from './forms.js';
import {
    openViewRecordModal,
    closeViewChildModal,
    openEditRecordModal,
    closeEditChildModal,
    openAddModal,
    closeModal
} from './modals.js';
import {
    showMotherForm,
    changeMotherType,
    goBackToConfirmation,
    setupMotherSelection,
    setupFormSubmission
} from './mother-selection.js';

/**
 * Initialize all child record functionality
 * Called on DOMContentLoaded
 */
function initialize() {
    console.log('Initializing Child Record modules...');

    // Initialize form validation and constraints
    initializeForms();

    // Setup mother selection handlers
    setupMotherSelection();
    setupFormSubmission();

    console.log('Child Record modules initialized successfully');
}

// Initialize on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    // DOM already loaded
    initialize();
}

// Expose functions to window for inline event handlers
window.childRecord = {
    // Modal functions
    openViewRecordModal,
    closeViewChildModal,
    openEditRecordModal,
    closeEditChildModal,
    openAddModal,
    closeModal,

    // Mother selection functions
    showMotherForm,
    changeMotherType,
    goBackToConfirmation
};

// Also expose individual functions for backwards compatibility
window.openViewRecordModal = openViewRecordModal;
window.closeViewChildModal = closeViewChildModal;
window.openEditRecordModal = openEditRecordModal;
window.closeEditChildModal = closeEditChildModal;
window.openAddModal = openAddModal;
window.closeModal = closeModal;
window.showMotherForm = showMotherForm;
window.changeMotherType = changeMotherType;
window.goBackToConfirmation = goBackToConfirmation;
