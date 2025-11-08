/**
 * Modals Module for User Management
 * Handles all modal operations
 */

import { setCurrentViewUser, setIsEditMode } from './state.js';
import { clearValidationErrors } from './validation.js';

/**
 * Show modal
 * @param {string} modalId
 */
export function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with id '${modalId}' not found`);
        return;
    }

    modal.classList.remove('hidden');
    modal.offsetHeight; // Force reflow

    setTimeout(() => {
        modal.classList.add('show');
        if (modalId === 'viewUserModal') {
            const content = document.getElementById('viewUserModalContent');
            if (content) {
                content.classList.remove('-translate-y-10', 'opacity-0');
            }
        }
    }, 10);
    document.body.style.overflow = 'hidden';
}

/**
 * Hide modal
 * @param {string} modalId
 */
export function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('show');
    if (modalId === 'viewUserModal') {
        const content = document.getElementById('viewUserModalContent');
        if (content) {
            content.classList.add('-translate-y-10', 'opacity-0');
        }
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

/**
 * Close modal (from overlay click)
 * @param {Event} event
 */
export function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    hideModal('userModal');
}

/**
 * Close view user modal
 */
export function closeViewUserModal() {
    hideModal('viewUserModal');
    setCurrentViewUser(null);
}

/**
 * Open Add User Modal
 */
export function openAddModal() {
    import('./forms.js').then(forms => {
        forms.resetForm();
    });

    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus text-[#68727A] mr-2"></i>Add User';
    document.getElementById('userForm').action = window.userManagementRoutes.store;
    document.getElementById('userId').value = '';

    removeMethodOverride();

    // Show password section for new users
    const passwordSection = document.getElementById('passwordSection');
    const passwordInput = document.getElementById('password');
    if (passwordSection && passwordInput) {
        passwordSection.style.display = 'block';
        passwordInput.required = true;
        passwordInput.placeholder = 'Enter password';
        const passwordLabel = passwordSection.querySelector('label');
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password *';
        }
    }

    // Update submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save User';
    }

    setIsEditMode(false);
    showModal('userModal');
}

/**
 * Open Edit User Modal
 * @param {Object} user
 */
export function openEditUserModal(user) {
    import('./forms.js').then(forms => {
        forms.resetForm();
        forms.populateEditForm(user);
    });

    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-[#68727A] mr-2"></i>Edit User';
    document.getElementById('userForm').action = window.userManagementRoutes.update.replace(':id', user.id);

    addMethodOverride('PUT');

    // Show password section for edit but make it optional
    const passwordSection = document.getElementById('passwordSection');
    const passwordInput = document.getElementById('password');
    if (passwordSection && passwordInput) {
        passwordSection.style.display = 'block';
        passwordInput.required = false;
        passwordInput.placeholder = 'Leave blank to keep current password';
        const passwordLabel = passwordSection.querySelector('label');
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password';
        }
    }

    // Update submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update User';
    }

    setIsEditMode(true);
    showModal('userModal');
}

/**
 * Open View User Modal
 * @param {Object} user
 */
export function openViewUserModal(user) {
    setCurrentViewUser(user);
    import('./forms.js').then(forms => {
        forms.populateViewModal(user);
    });
    showModal('viewUserModal');
}

/**
 * Deactivate user
 * @param {number} userId
 */
export function deactivateUser(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.userManagementRoutes.deactivate.replace(':id', userId);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();
}

/**
 * Activate user
 * @param {number} userId
 */
export function activateUser(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.userManagementRoutes.activate.replace(':id', userId);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();
}

/**
 * Add method override for PUT/PATCH
 * @param {string} method
 */
function addMethodOverride(method) {
    removeMethodOverride();

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = method;
    methodInput.id = 'methodOverride';

    const form = document.getElementById('userForm');
    if (form) {
        form.appendChild(methodInput);
    }
}

/**
 * Remove method override
 */
function removeMethodOverride() {
    const methodOverride = document.getElementById('methodOverride');
    if (methodOverride) {
        methodOverride.remove();
    }
}
