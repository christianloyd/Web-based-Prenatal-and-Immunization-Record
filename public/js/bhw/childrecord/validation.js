/**
 * Validation Module
 * Handles form field validation and formatting
 */

/**
 * Format and validate Philippine phone numbers
 * Supports formats: 09xxxxxxxxx, +639xxxxxxxxx, 639xxxxxxxxx, 9xxxxxxxxx
 * @param {HTMLInputElement} input - Phone input field
 * @returns {boolean} - True if valid
 */
export function formatPhoneNumber(input) {
    // Skip formatting if field is readonly (pre-filled from mother data)
    if (input.readOnly) {
        return true;
    }

    // Get the original value without changing it first
    let originalValue = input.value;

    // Remove all non-digits and special characters
    let digitsOnly = originalValue.replace(/\D/g, '');

    // Handle different input formats and validate
    let isValid = false;
    let formattedValue = originalValue;

    if (digitsOnly.startsWith('63') && digitsOnly.length === 12) {
        // 639xxxxxxxxx format - convert to +639xxxxxxxxx
        formattedValue = '+' + digitsOnly;
        isValid = /^\+639\d{9}$/.test(formattedValue);
    } else if (digitsOnly.startsWith('09') && digitsOnly.length === 11) {
        // 09xxxxxxxxx format - keep as is
        formattedValue = digitsOnly;
        isValid = /^09\d{9}$/.test(formattedValue);
    } else if (digitsOnly.startsWith('9') && digitsOnly.length === 10) {
        // 9xxxxxxxxx format - convert to 09xxxxxxxxx
        formattedValue = '0' + digitsOnly;
        isValid = /^09\d{9}$/.test(formattedValue);
    } else if (originalValue.startsWith('+63') && /^\+639\d{9}$/.test(originalValue)) {
        // Already in +639xxxxxxxxx format
        formattedValue = originalValue;
        isValid = true;
    } else if (digitsOnly.length === 0) {
        // Empty field
        formattedValue = '';
        isValid = false;
    } else {
        // Invalid format - keep original value but mark as invalid
        formattedValue = originalValue;
        isValid = false;
    }

    // Only update the input value if it changed
    if (formattedValue !== originalValue) {
        input.value = formattedValue;
    }

    // Apply validation styling
    input.classList.toggle('error-border', !isValid && input.value.length > 0);
    input.classList.toggle('success-border', isValid);

    return isValid;
}

/**
 * Validate individual form field
 * @param {Event} event - Blur/input event
 */
export function validateField(event) {
    const field = event.target;
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    let isValid = true;

    // Clear previous validation styles
    field.classList.remove('error-border', 'success-border');

    if (isRequired && !value) {
        isValid = false;
    } else if (value) {
        // Field-specific validation
        switch (field.name) {
            case 'full_name':
            case 'mother_name':
                isValid = value.length >= 2;
                break;

            case 'phone_number':
            case 'mother_contact':
                return formatPhoneNumber(field);

            case 'birthdate':
                const selectedDate = new Date(value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                isValid = selectedDate <= today;
                break;

            case 'birth_height':
                const height = parseFloat(value);
                isValid = !isNaN(height) && height >= 0 && height <= 999.99;
                break;

            case 'birth_weight':
                const weight = parseFloat(value);
                isValid = !isNaN(weight) && weight >= 0 && weight <= 99.999;
                break;

            case 'mother_age':
                const age = parseInt(value);
                isValid = !isNaN(age) && age >= 15 && age <= 50;
                break;

            default:
                isValid = value.length > 0;
        }
    }

    // Apply validation styling
    if (value) {
        field.classList.toggle('error-border', !isValid);
        field.classList.toggle('success-border', isValid);
    }

    return isValid;
}

/**
 * Clear validation states from all form fields
 * @param {HTMLFormElement} form - Form element
 */
export function clearValidationStates(form) {
    if (!form) return;

    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.classList.remove('error-border', 'success-border');
    });
}

/**
 * Validate entire form
 * @param {HTMLFormElement} form - Form element
 * @returns {boolean} - True if all fields valid
 */
export function validateForm(form) {
    if (!form) return false;

    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

    inputs.forEach(input => {
        const event = { target: input };
        if (!validateField(event)) {
            isValid = false;
        }
    });

    return isValid;
}
