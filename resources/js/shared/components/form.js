/**
 * Shared Form Component
 * Reusable form handling and submission logic
 *
 * This module provides a standardized way to handle form validation,
 * submission, and error display across the application.
 *
 * @module shared/components/form
 */

import { validateForm, clearAllValidationErrors } from '../utils/validation.js';
import { post, put } from '../utils/api.js';
import { getFormData, resetForm as domResetForm, disable, enable } from '../utils/dom.js';
import { showSuccess, showError } from '../utils/sweetalert.js';

/**
 * Form handler class
 */
export class Form {
    /**
     * Create a Form instance
     *
     * @param {string|HTMLFormElement} formElement - Form element or selector
     * @param {Object} options - Form configuration options
     * @param {Object} options.validation - Validation rules
     * @param {function} options.onSuccess - Success callback
     * @param {function} options.onError - Error callback
     * @param {boolean} options.resetOnSuccess - Reset form after success (default: true)
     * @param {string} options.successMessage - Success message
     * @param {boolean} options.showSuccessAlert - Show success alert (default: true)
     * @param {boolean} options.showErrorAlert - Show error alert (default: true)
     */
    constructor(formElement, options = {}) {
        this.form = typeof formElement === 'string'
            ? document.querySelector(formElement)
            : formElement;

        if (!this.form) {
            console.error('[Form] Form element not found:', formElement);
            return;
        }

        this.options = {
            validation: {},
            onSuccess: null,
            onError: null,
            resetOnSuccess: true,
            successMessage: 'Form submitted successfully!',
            showSuccessAlert: true,
            showErrorAlert: true,
            ...options
        };

        this.submitButton = null;
        this.isSubmitting = false;

        this.initialize();
    }

    /**
     * Initialize form event listeners
     * @private
     */
    initialize() {
        // Find submit button
        this.submitButton = this.form.querySelector('[type="submit"]');

        // Prevent default form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submit();
        });

        // Clear validation errors on input
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                clearAllValidationErrors(this.form);
            });
        });

        console.log('[Form] Initialized:', this.form.id || 'form');
    }

    /**
     * Validate form
     *
     * @returns {boolean} True if valid
     *
     * @example
     * if (form.validate()) {
     *     // Form is valid
     * }
     */
    validate() {
        if (Object.keys(this.options.validation).length === 0) {
            return true;
        }

        return validateForm(this.form, this.options.validation);
    }

    /**
     * Get form data
     *
     * @returns {Object} Form data as key-value pairs
     *
     * @example
     * const data = form.getData();
     * console.log(data); // { name: 'John', phone: '09123456789' }
     */
    getData() {
        return getFormData(this.form);
    }

    /**
     * Set form data
     *
     * @param {Object} data - Data to populate
     * @returns {void}
     *
     * @example
     * form.setData({ name: 'John Doe', phone: '09123456789' });
     */
    setData(data) {
        for (const [key, value] of Object.entries(data)) {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = value;
                } else if (input.type === 'radio') {
                    const radio = this.form.querySelector(`[name="${key}"][value="${value}"]`);
                    if (radio) radio.checked = true;
                } else {
                    input.value = value || '';
                }
            }
        }
    }

    /**
     * Reset form
     *
     * @returns {void}
     */
    reset() {
        domResetForm(this.form);
    }

    /**
     * Disable form inputs
     * @private
     *
     * @param {string} loadingText - Loading text for submit button
     * @returns {void}
     */
    disableForm(loadingText = 'Submitting...') {
        const inputs = this.form.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            input.disabled = true;
        });

        if (this.submitButton) {
            disable(this.submitButton, loadingText);
        }

        this.isSubmitting = true;
    }

    /**
     * Enable form inputs
     * @private
     *
     * @returns {void}
     */
    enableForm() {
        const inputs = this.form.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            input.disabled = false;
        });

        if (this.submitButton) {
            enable(this.submitButton);
        }

        this.isSubmitting = false;
    }

    /**
     * Submit form
     *
     * @returns {Promise<void>}
     *
     * @example
     * form.submit();
     */
    async submit() {
        if (this.isSubmitting) return;

        // Validate form
        if (!this.validate()) {
            console.log('[Form] Validation failed');
            return;
        }

        // Disable form
        this.disableForm();

        try {
            const data = this.getData();
            const method = this.form.method?.toLowerCase() || 'post';
            const url = this.form.action;

            let response;
            if (method === 'put' || method === 'patch') {
                response = await put(url, data, { showError: false });
            } else {
                response = await post(url, data, { showError: false });
            }

            // Enable form
            this.enableForm();

            // Show success message
            if (this.options.showSuccessAlert) {
                showSuccess(response.message || this.options.successMessage, () => {
                    if (this.options.onSuccess) {
                        this.options.onSuccess(response, this);
                    }
                });
            } else if (this.options.onSuccess) {
                this.options.onSuccess(response, this);
            }

            // Reset form if configured
            if (this.options.resetOnSuccess) {
                this.reset();
            }

        } catch (error) {
            // Enable form
            this.enableForm();

            // Extract error message and validation errors
            const errorMessage = error.data?.message || 'An error occurred';
            const validationErrors = error.data?.errors ? Object.values(error.data.errors).flat() : null;

            // Show error message
            if (this.options.showErrorAlert) {
                showError(errorMessage, validationErrors);
            }

            // Call error callback
            if (this.options.onError) {
                this.options.onError(error, this);
            }
        }
    }
}

/**
 * Create a form handler
 *
 * @param {string} selector - Form selector
 * @param {Object} options - Form options
 * @returns {Form} Form instance
 *
 * @example
 * const patientForm = createForm('#patientForm', {
 *     validation: {
 *         name: { required: true, minLength: 2 },
 *         phone: { required: true, phone: true },
 *         email: { email: true }
 *     },
 *     successMessage: 'Patient saved successfully!',
 *     onSuccess: (response) => {
 *         window.location.reload();
 *     }
 * });
 */
export function createForm(selector, options = {}) {
    return new Form(selector, options);
}

/**
 * Handle form submission with AJAX
 *
 * @param {string|HTMLFormElement} form - Form element or selector
 * @param {Object} options - Submission options
 * @returns {Promise<void>}
 *
 * @example
 * await submitForm('#patientForm', {
 *     successMessage: 'Patient saved!',
 *     onSuccess: () => window.location.reload()
 * });
 */
export async function submitForm(form, options = {}) {
    const formEl = typeof form === 'string' ? document.querySelector(form) : form;
    if (!formEl) return;

    const formHandler = new Form(formEl, options);
    await formHandler.submit();
}

/**
 * Quick form submission without validation
 *
 * @param {string|HTMLFormElement} form - Form element or selector
 * @param {string} successMessage - Success message
 * @param {function} onSuccess - Success callback
 * @returns {Promise<void>}
 *
 * @example
 * await quickSubmit('#deleteForm', 'Record deleted!', () => {
 *     window.location.reload();
 * });
 */
export async function quickSubmit(form, successMessage, onSuccess = null) {
    await submitForm(form, {
        successMessage,
        onSuccess
    });
}

// Export as default
export default {
    Form,
    createForm,
    submitForm,
    quickSubmit
};
