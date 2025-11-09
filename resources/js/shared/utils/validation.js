/**
 * Shared Validation Utilities
 * Reusable form validation functions for all application pages
 *
 * This module provides client-side validation helpers that match
 * the Laravel backend validation rules.
 *
 * @module shared/utils/validation
 */

/**
 * Validate Philippine phone number format
 * Accepts formats: 09123456789, +639123456789, 639123456789
 *
 * @param {string} phone - Phone number to validate
 * @returns {boolean} True if valid Philippine phone number
 *
 * @example
 * isValidPhoneNumber('09123456789'); // true
 * isValidPhoneNumber('+639123456789'); // true
 * isValidPhoneNumber('12345'); // false
 */
export function isValidPhoneNumber(phone) {
    if (!phone || typeof phone !== 'string') return false;

    // Remove spaces and dashes
    const cleaned = phone.replace(/[\s-]/g, '');

    // Philippine phone number patterns
    const patterns = [
        /^09\d{9}$/,           // 09XXXXXXXXX
        /^\+639\d{9}$/,        // +639XXXXXXXXX
        /^639\d{9}$/           // 639XXXXXXXXX
    ];

    return patterns.some(pattern => pattern.test(cleaned));
}

/**
 * Format phone number to standard format (09XXXXXXXXX)
 *
 * @param {string} phone - Phone number to format
 * @returns {string} Formatted phone number
 *
 * @example
 * formatPhoneNumber('+639123456789'); // '09123456789'
 * formatPhoneNumber('639123456789'); // '09123456789'
 */
export function formatPhoneNumber(phone) {
    if (!phone) return '';

    const cleaned = phone.replace(/[\s-]/g, '');

    // Convert +639XXXXXXXXX or 639XXXXXXXXX to 09XXXXXXXXX
    if (cleaned.startsWith('+639')) {
        return '0' + cleaned.substring(3);
    } else if (cleaned.startsWith('639')) {
        return '0' + cleaned.substring(2);
    }

    return cleaned;
}

/**
 * Validate email address format
 *
 * @param {string} email - Email address to validate
 * @returns {boolean} True if valid email format
 *
 * @example
 * isValidEmail('user@example.com'); // true
 * isValidEmail('invalid-email'); // false
 */
export function isValidEmail(email) {
    if (!email || typeof email !== 'string') return false;

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(email);
}

/**
 * Validate maternal age (should be between 15-49 years)
 *
 * @param {number|string} age - Age to validate
 * @returns {boolean} True if age is within safe maternal range
 *
 * @example
 * isValidMaternalAge(25); // true
 * isValidMaternalAge(12); // false
 * isValidMaternalAge(52); // false
 */
export function isValidMaternalAge(age) {
    const numAge = parseInt(age, 10);
    return !isNaN(numAge) && numAge >= 15 && numAge <= 49;
}

/**
 * Check if maternal age is high risk (< 18 or > 35)
 *
 * @param {number|string} age - Age to check
 * @returns {boolean} True if high risk age
 *
 * @example
 * isHighRiskAge(17); // true
 * isHighRiskAge(36); // true
 * isHighRiskAge(25); // false
 */
export function isHighRiskAge(age) {
    const numAge = parseInt(age, 10);
    return !isNaN(numAge) && (numAge < 18 || numAge > 35);
}

/**
 * Validate required field
 *
 * @param {any} value - Value to validate
 * @returns {boolean} True if value is not empty
 *
 * @example
 * isRequired('John'); // true
 * isRequired(''); // false
 * isRequired(null); // false
 */
export function isRequired(value) {
    if (value === null || value === undefined) return false;
    if (typeof value === 'string') return value.trim().length > 0;
    if (Array.isArray(value)) return value.length > 0;
    return true;
}

/**
 * Validate minimum length
 *
 * @param {string} value - String to validate
 * @param {number} min - Minimum length
 * @returns {boolean} True if string meets minimum length
 *
 * @example
 * minLength('password123', 8); // true
 * minLength('pass', 8); // false
 */
export function minLength(value, min) {
    if (!value || typeof value !== 'string') return false;
    return value.length >= min;
}

/**
 * Validate maximum length
 *
 * @param {string} value - String to validate
 * @param {number} max - Maximum length
 * @returns {boolean} True if string is within maximum length
 *
 * @example
 * maxLength('John', 50); // true
 * maxLength('A very long name...', 10); // false
 */
export function maxLength(value, max) {
    if (!value || typeof value !== 'string') return false;
    return value.length <= max;
}

/**
 * Validate numeric value
 *
 * @param {any} value - Value to validate
 * @returns {boolean} True if value is numeric
 *
 * @example
 * isNumeric(123); // true
 * isNumeric('456'); // true
 * isNumeric('abc'); // false
 */
export function isNumeric(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
}

/**
 * Validate date format (YYYY-MM-DD)
 *
 * @param {string} date - Date string to validate
 * @returns {boolean} True if valid date format
 *
 * @example
 * isValidDate('2025-11-09'); // true
 * isValidDate('11/09/2025'); // false
 * isValidDate('invalid'); // false
 */
export function isValidDate(date) {
    if (!date || typeof date !== 'string') return false;

    const datePattern = /^\d{4}-\d{2}-\d{2}$/;
    if (!datePattern.test(date)) return false;

    const dateObj = new Date(date);
    return dateObj instanceof Date && !isNaN(dateObj.getTime());
}

/**
 * Validate date is not in the future
 *
 * @param {string} date - Date string to validate (YYYY-MM-DD)
 * @returns {boolean} True if date is today or in the past
 *
 * @example
 * isNotFutureDate('2020-01-01'); // true
 * isNotFutureDate('2099-12-31'); // false
 */
export function isNotFutureDate(date) {
    if (!isValidDate(date)) return false;

    const inputDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return inputDate <= today;
}

/**
 * Validate date is not in the past
 *
 * @param {string} date - Date string to validate (YYYY-MM-DD)
 * @returns {boolean} True if date is today or in the future
 *
 * @example
 * isNotPastDate('2099-12-31'); // true
 * isNotPastDate('2020-01-01'); // false
 */
export function isNotPastDate(date) {
    if (!isValidDate(date)) return false;

    const inputDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return inputDate >= today;
}

/**
 * Show validation error on input field
 *
 * @param {HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement} input - Form input element
 * @param {string} message - Error message to display
 * @returns {void}
 *
 * @example
 * const input = document.getElementById('phone');
 * if (!isValidPhoneNumber(input.value)) {
 *     showValidationError(input, 'Please enter a valid phone number');
 * }
 */
export function showValidationError(input, message) {
    if (!input) return;

    // Add error class to input
    input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    input.classList.remove('border-gray-300');

    // Remove any existing error message
    clearValidationError(input);

    // Create and insert error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'validation-error text-red-500 text-sm mt-1';
    errorDiv.textContent = message;
    errorDiv.setAttribute('data-validation-error', 'true');

    input.parentElement.appendChild(errorDiv);
}

/**
 * Clear validation error from input field
 *
 * @param {HTMLInputElement|HTMLSelectElement|HTMLTextAreaElement} input - Form input element
 * @returns {void}
 *
 * @example
 * const input = document.getElementById('phone');
 * clearValidationError(input);
 */
export function clearValidationError(input) {
    if (!input) return;

    // Remove error classes
    input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    input.classList.add('border-gray-300');

    // Remove error message
    const errorElement = input.parentElement.querySelector('[data-validation-error="true"]');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Clear all validation errors from a form
 *
 * @param {HTMLFormElement} form - Form element
 * @returns {void}
 *
 * @example
 * const form = document.getElementById('patientForm');
 * clearAllValidationErrors(form);
 */
export function clearAllValidationErrors(form) {
    if (!form) return;

    // Clear all inputs
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => clearValidationError(input));

    // Remove all error messages
    const errorMessages = form.querySelectorAll('[data-validation-error="true"]');
    errorMessages.forEach(msg => msg.remove());
}

/**
 * Validate form and show errors
 *
 * @param {HTMLFormElement} form - Form element to validate
 * @param {Object} rules - Validation rules object
 * @returns {boolean} True if form is valid
 *
 * @example
 * const form = document.getElementById('patientForm');
 * const valid = validateForm(form, {
 *     name: { required: true, minLength: 2 },
 *     phone: { required: true, phone: true },
 *     email: { email: true }
 * });
 */
export function validateForm(form, rules) {
    clearAllValidationErrors(form);

    let isValid = true;

    for (const [fieldName, fieldRules] of Object.entries(rules)) {
        const input = form.querySelector(`[name="${fieldName}"]`);
        if (!input) continue;

        const value = input.value;

        // Required validation
        if (fieldRules.required && !isRequired(value)) {
            showValidationError(input, `${fieldName} is required`);
            isValid = false;
            continue;
        }

        // Skip other validations if field is empty and not required
        if (!isRequired(value) && !fieldRules.required) continue;

        // Min length validation
        if (fieldRules.minLength && !minLength(value, fieldRules.minLength)) {
            showValidationError(input, `${fieldName} must be at least ${fieldRules.minLength} characters`);
            isValid = false;
            continue;
        }

        // Max length validation
        if (fieldRules.maxLength && !maxLength(value, fieldRules.maxLength)) {
            showValidationError(input, `${fieldName} must not exceed ${fieldRules.maxLength} characters`);
            isValid = false;
            continue;
        }

        // Email validation
        if (fieldRules.email && !isValidEmail(value)) {
            showValidationError(input, 'Please enter a valid email address');
            isValid = false;
            continue;
        }

        // Phone validation
        if (fieldRules.phone && !isValidPhoneNumber(value)) {
            showValidationError(input, 'Please enter a valid Philippine phone number');
            isValid = false;
            continue;
        }

        // Numeric validation
        if (fieldRules.numeric && !isNumeric(value)) {
            showValidationError(input, `${fieldName} must be a number`);
            isValid = false;
            continue;
        }

        // Min numeric value validation
        if (fieldRules.min !== undefined && isNumeric(value)) {
            const numValue = parseFloat(value);
            if (numValue < fieldRules.min) {
                showValidationError(input, `${fieldName} must be at least ${fieldRules.min}`);
                isValid = false;
                continue;
            }
        }

        // Max numeric value validation
        if (fieldRules.max !== undefined && isNumeric(value)) {
            const numValue = parseFloat(value);
            if (numValue > fieldRules.max) {
                showValidationError(input, `${fieldName} cannot exceed ${fieldRules.max}`);
                isValid = false;
                continue;
            }
        }

        // Date validation
        if (fieldRules.date && !isValidDate(value)) {
            showValidationError(input, 'Please enter a valid date (YYYY-MM-DD)');
            isValid = false;
            continue;
        }

        // Not future date validation
        if (fieldRules.notFuture && !isNotFutureDate(value)) {
            showValidationError(input, 'Date cannot be in the future');
            isValid = false;
            continue;
        }

        // Not past date validation
        if (fieldRules.notPast && !isNotPastDate(value)) {
            showValidationError(input, 'Date cannot be in the past');
            isValid = false;
            continue;
        }
    }

    return isValid;
}

// Export all functions as default
export default {
    isValidPhoneNumber,
    formatPhoneNumber,
    isValidEmail,
    isValidMaternalAge,
    isHighRiskAge,
    isRequired,
    minLength,
    maxLength,
    isNumeric,
    isValidDate,
    isNotFutureDate,
    isNotPastDate,
    showValidationError,
    clearValidationError,
    clearAllValidationErrors,
    validateForm
};
