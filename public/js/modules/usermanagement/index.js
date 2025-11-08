/**
 * User Management Main Coordinator
 * Imports all modules and initializes user management functionality
 */

// Import state management
import { getCurrentViewUser, setCurrentViewUser, getIsEditMode, setIsEditMode } from './state.js';

// Import validation
import { formatPhoneNumber, setupPhoneNumberFormatting, validateForm, setupFormValidation, showValidationErrors, clearValidationErrors } from './validation.js';

// Import modals
import { showModal, hideModal, closeModal, closeViewUserModal, openAddModal, openEditUserModal, openViewUserModal, deactivateUser, activateUser } from './modals.js';

// Import forms
import { resetForm, populateEditForm, populateViewModal, addMethodOverride, removeMethodOverride } from './forms.js';

/**
 * Setup modal event listeners
 */
function setupModalEventListeners() {
    // Close modal when clicking outside
    const userModal = document.getElementById('userModal');
    if (userModal) {
        userModal.addEventListener('click', function(e) {
            if (e.target === userModal) {
                closeModal();
            }
        });
    }

    const viewUserModal = document.getElementById('viewUserModal');
    if (viewUserModal) {
        viewUserModal.addEventListener('click', function(e) {
            if (e.target === viewUserModal) {
                closeViewUserModal();
            }
        });
    }

    // ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!document.getElementById('userModal').classList.contains('hidden')) {
                closeModal();
            }
            if (!document.getElementById('viewUserModal').classList.contains('hidden')) {
                closeViewUserModal();
            }
        }
    });
}

/**
 * Initialize user management
 */
function initializeUserManagement() {
    setupPhoneNumberFormatting();
    setupFormValidation();
    setupModalEventListeners();

    // Check for server-side validation errors and show modal if needed
    if (document.querySelectorAll('.error-border').length > 0 ||
        document.querySelector('#userForm .text-red-500')) {
        const userIdInput = document.getElementById('userId');
        if (userIdInput && userIdInput.value) {
            setIsEditMode(true);
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-[#68727A] mr-2"></i>Edit User';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save mr-2"></i>Update User';
            const userId = userIdInput.value;
            document.getElementById('userForm').action = window.userManagementRoutes.update.replace(':id', userId);
            addMethodOverride('PUT');
        } else {
            setIsEditMode(false);
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus text-[#68727A] mr-2"></i>Add User';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save mr-2"></i>Save User';
            document.getElementById('userForm').action = window.userManagementRoutes.store;
        }
        showModal('userModal');
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initializeUserManagement);

// Expose functions to window object for backwards compatibility
window.openAddModal = openAddModal;
window.openEditUserModal = openEditUserModal;
window.openViewUserModal = openViewUserModal;
window.closeModal = closeModal;
window.closeViewUserModal = closeViewUserModal;
window.deactivateUser = deactivateUser;
window.activateUser = activateUser;
window.showModal = showModal;
window.hideModal = hideModal;
