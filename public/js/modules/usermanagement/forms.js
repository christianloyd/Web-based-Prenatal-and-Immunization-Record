/**
 * Forms Module for User Management
 * Handles form reset, population, and helper functions
 */

import { setIsEditMode } from './state.js';
import { clearValidationErrors } from './validation.js';

/**
 * Reset form to initial state
 */
export function resetForm() {
    const form = document.getElementById('userForm');
    if (form) {
        form.reset();
        clearValidationErrors();
    }

    // Reset hidden fields
    const userIdInput = document.getElementById('userId');
    if (userIdInput) {
        userIdInput.value = '';
    }

    // Remove method override
    removeMethodOverride();
}

/**
 * Populate edit form with user data
 * @param {Object} user
 */
export function populateEditForm(user) {
    const fields = {
        'userId': user.id,
        'name': user.name || '',
        'username': user.username || '',
        'age': user.age || '',
        'contact_number': user.contact_number || '',
        'address': user.address || '',
        'role': user.role || ''
    };

    // Populate text inputs
    Object.keys(fields).forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.value = fields[fieldId];
        }
    });

    // Set gender radio button
    setGenderRadio(user.gender);
}

/**
 * Populate view modal with user data
 * @param {Object} user
 */
export function populateViewModal(user) {
    const viewFields = {
        'modalFullName': user.name || 'N/A',
        'modalGender': user.gender || 'N/A',
        'modalAge': user.age || 'N/A',
        'modalRole': user.role || 'N/A',
        'modalUsername': user.username || 'N/A',
        'modalContactNumber': user.contact_number ? '+63' + user.contact_number : 'N/A',
        'modalUserAddress': user.address || 'N/A',
        'modalStatus': user.is_active ? 'Active' : 'Inactive'
    };

    // Populate view fields
    Object.keys(viewFields).forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.textContent = viewFields[fieldId];
        }
    });

    // Update status styling in modal
    const statusElement = document.getElementById('modalStatus');
    if (statusElement) {
        statusElement.className = `text-lg font-semibold mt-1 ${user.is_active ? 'text-green-600' : 'text-red-600'}`;
    }

    // Set dates and role information
    setModalDates(user);
    setRoleInformation(user.role);
}

/**
 * Set gender radio button
 * @param {string} gender
 */
function setGenderRadio(gender) {
    const maleRadio = document.querySelector('input[name="gender"][value="Male"]');
    const femaleRadio = document.querySelector('input[name="gender"][value="Female"]');

    if (maleRadio) maleRadio.checked = gender === 'Male';
    if (femaleRadio) femaleRadio.checked = gender === 'Female';
}

/**
 * Set modal dates (created_at)
 * @param {Object} user
 */
function setModalDates(user) {
    const createdAtElement = document.getElementById('modalCreatedAt');

    if (user.created_at && createdAtElement) {
        const formattedDate = new Date(user.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        createdAtElement.textContent = formattedDate;
    }
}

/**
 * Set role information in view modal
 * @param {string} role
 */
function setRoleInformation(role) {
    const roleDescriptions = {
        'Midwife': 'Healthcare professional responsible for prenatal care, delivery assistance, and maternal health services. Has full system access including user management.',
        'BHW': 'Community health worker providing basic healthcare services and health education at the barangay level. Has limited system access focused on patient records.'
    };

    const accessLevels = {
        'Midwife': 'Full System Access',
        'BHW': 'Limited Access'
    };

    const descElement = document.getElementById('modalRoleDescription');
    const accessElement = document.getElementById('modalAccessLevel');

    if (descElement) {
        descElement.textContent = roleDescriptions[role] || 'No description available';
    }

    if (accessElement) {
        accessElement.textContent = accessLevels[role] || 'N/A';
    }
}

/**
 * Add method override for PUT/PATCH/DELETE
 * @param {string} method
 */
export function addMethodOverride(method) {
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
 * Remove method override input
 */
export function removeMethodOverride() {
    const methodOverride = document.getElementById('methodOverride');
    if (methodOverride) {
        methodOverride.remove();
    }
}
