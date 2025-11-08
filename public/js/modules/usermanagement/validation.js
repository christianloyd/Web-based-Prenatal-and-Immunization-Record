/**
 * Validation Module for User Management
 * Handles form field validation and phone number formatting
 */

import { getIsEditMode } from './state.js';

/**
 * Format phone number - Philippine format (9xxxxxxxxx)
 * @param {HTMLInputElement} input
 */
export function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');

    if (value.length > 0 && !value.startsWith('9')) {
        value = '';
    }

    if (value.length > 10) {
        value = value.substring(0, 10);
    }

    input.value = value;
}

/**
 * Setup phone number formatting handlers
 */
export function setupPhoneNumberFormatting() {
    const phoneInput = document.getElementById('contact_number');

    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            formatPhoneNumber(e.target);
        });

        phoneInput.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });

        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = paste.replace(/\D/g, '');

            let phoneNumber = cleaned;
            if (phoneNumber.startsWith('63')) {
                phoneNumber = phoneNumber.substring(2);
            }
            if (phoneNumber.startsWith('0')) {
                phoneNumber = phoneNumber.substring(1);
            }

            if (phoneNumber.startsWith('9') && phoneNumber.length <= 10) {
                phoneInput.value = phoneNumber;
                formatPhoneNumber(phoneInput);
            }
        });
    }
}

/**
 * Validate entire form
 * @returns {Object} { isValid: boolean, errors: string[] }
 */
export function validateForm() {
    const requiredFields = ['name', 'username', 'age', 'contact_number', 'role'];
    let isValid = true;
    const errors = [];

    clearValidationErrors();

    requiredFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input && !input.value.trim()) {
            input.classList.add('error-border');
            errors.push(`${getFieldLabel(fieldId)} is required.`);
            isValid = false;
        } else if (input) {
            input.classList.remove('error-border');
        }
    });

    // Check gender radio buttons
    const genderChecked = document.querySelector('input[name="gender"]:checked');
    if (!genderChecked) {
        errors.push('Gender is required.');
        isValid = false;
    }

    // Validate phone number format
    const phoneInput = document.getElementById('contact_number');
    if (phoneInput && phoneInput.value) {
        const phonePattern = /^9\d{9}$/;
        if (!phonePattern.test(phoneInput.value)) {
            phoneInput.classList.add('error-border');
            errors.push('Contact number must be a valid Philippine mobile number starting with 9.');
            isValid = false;
        }
    }

    // Validate age range
    const ageInput = document.getElementById('age');
    if (ageInput && ageInput.value) {
        const age = parseInt(ageInput.value);
        if (age < 18 || age > 100) {
            ageInput.classList.add('error-border');
            errors.push('Age must be between 18 and 100 years.');
            isValid = false;
        }
    }

    // Validate password
    const passwordInput = document.getElementById('password');
    if (passwordInput && passwordInput.value) {
        const password = passwordInput.value;

        // Check minimum length
        if (password.length < 8) {
            passwordInput.classList.add('error-border');
            errors.push('Password must be at least 8 characters long.');
            isValid = false;
        }

        // Check for lowercase letter
        if (!/[a-z]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one lowercase letter.');
            isValid = false;
        }

        // Check for uppercase letter
        if (!/[A-Z]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one uppercase letter.');
            isValid = false;
        }

        // Check for number
        if (!/[0-9]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one number.');
            isValid = false;
        }

        // Check for special character
        if (!/[@$!%*#?&]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one special character (@$!%*#?&).');
            isValid = false;
        }
    }

    // Password is required for new users
    if (!getIsEditMode() && passwordInput && !passwordInput.value.trim()) {
        passwordInput.classList.add('error-border');
        errors.push('Password is required.');
        isValid = false;
    }

    return { isValid, errors };
}

/**
 * Setup form validation handlers
 */
export function setupFormValidation() {
    const form = document.getElementById('userForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const validation = validateForm();
            if (!validation.isValid) {
                e.preventDefault();
                showValidationErrors(validation.errors);
                return false;
            }
        });
    }
}

/**
 * Show validation errors
 * @param {string[]} errors
 */
export function showValidationErrors(errors) {
    if (errors.length === 0) return;

    let errorContainer = document.querySelector('#userModal .validation-errors');
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.className = 'validation-errors bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';

        const modalHeader = document.querySelector('#userModal .flex.justify-between.items-center');
        if (modalHeader) {
            modalHeader.parentNode.insertBefore(errorContainer, modalHeader.nextSibling);
        }
    }

    errorContainer.innerHTML = `
        <div class="font-medium">Please correct the following errors:</div>
        <ul class="list-disc list-inside mt-2">
            ${errors.map(error => `<li class="text-sm">${error}</li>`).join('')}
        </ul>
    `;

    const modalContent = document.querySelector('#userModal .modal-content');
    if (modalContent) {
        modalContent.scrollTop = 0;
    }
}

/**
 * Clear validation errors
 */
export function clearValidationErrors() {
    const errorContainer = document.querySelector('#userModal .validation-errors');
    if (errorContainer) {
        errorContainer.remove();
    }

    // Remove error borders from all inputs
    const inputs = document.querySelectorAll('#userModal .error-border');
    inputs.forEach(input => input.classList.remove('error-border'));
}

/**
 * Get field label for error messages
 * @param {string} fieldId
 * @returns {string}
 */
function getFieldLabel(fieldId) {
    const labels = {
        'name': 'Full Name',
        'username': 'Username',
        'age': 'Age',
        'contact_number': 'Contact Number',
        'role': 'Role',
        'password': 'Password'
    };
    return labels[fieldId] || fieldId;
}
