/**
 * Forms Module
 * Handles form initialization, constraints, and validation setup
 */

import { validateField } from './validation.js';

/**
 * Set date input constraints (min/max)
 * Prevents selecting future dates or dates more than 100 years ago
 */
export function setDateConstraints() {
    const birthdateFields = [
        document.getElementById('birthdate'),
        document.getElementById('edit-birthdate')
    ];

    const today = new Date();
    const maxDate = today.toISOString().split('T')[0];

    // Set minimum to 100 years ago
    const minDate = new Date(today.getFullYear() - 100, today.getMonth(), today.getDate())
        .toISOString().split('T')[0];

    birthdateFields.forEach(field => {
        if (field) {
            field.setAttribute('max', maxDate);
            field.setAttribute('min', minDate);

            // Validate on change and input
            field.addEventListener('change', function(e) {
                const selectedDate = new Date(this.value);
                const todayDate = new Date();
                todayDate.setHours(0, 0, 0, 0);

                if (selectedDate > todayDate) {
                    this.value = maxDate;
                    alert('Birthdate cannot be in the future. Setting to today\'s date.');
                }

                validateField(e);
            });

            field.addEventListener('input', function(e) {
                const selectedDate = new Date(this.value);
                const todayDate = new Date();
                todayDate.setHours(0, 0, 0, 0);

                if (selectedDate > todayDate) {
                    this.classList.add('error-border');
                    this.classList.remove('success-border');
                } else {
                    this.classList.remove('error-border');
                    if (this.value) {
                        this.classList.add('success-border');
                    }
                }
            });
        }
    });
}

/**
 * Setup form validation handlers
 * Attaches blur and input event listeners to all form fields
 */
export function setupFormHandling() {
    const forms = ['recordForm', 'edit-child-form'];

    forms.forEach(formId => {
        const form = document.getElementById(formId);
        if (!form) return;

        const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
        inputs.forEach(input => {
            // Validate on blur (when user leaves field)
            input.addEventListener('blur', validateField);

            // Real-time validation for certain fields
            if (['text', 'email', 'tel', 'date', 'number'].includes(input.type)) {
                input.addEventListener('input', validateField);
            }
        });
    });
}

/**
 * Initialize all form-related functionality
 */
export function initializeForms() {
    setDateConstraints();
    setupFormHandling();
}
