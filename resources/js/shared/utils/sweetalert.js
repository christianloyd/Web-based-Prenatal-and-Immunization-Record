/**
 * Shared SweetAlert Handler
 * Reusable SweetAlert functions for all application pages (BHW, Midwife, Admin)
 *
 * This module consolidates duplicate sweetalert-handler.js files to reduce code duplication.
 * Previously: 223 lines × 2 roles = 446 lines
 * Now: 235 lines × 1 file = 235 lines (211 lines saved)
 *
 * @module shared/utils/sweetalert
 */

/**
 * SweetAlert configuration object
 * @type {Object}
 */
const SwalConfig = {
    confirmButtonColor: '#D4A373',
    cancelButtonColor: '#6B7280',
    showClass: {
        popup: 'animate__animated animate__fadeInDown animate__faster'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOutUp animate__faster'
    }
};

/**
 * Show success message
 *
 * @param {string} message - Success message to display
 * @param {function|null} callback - Optional callback function after clicking OK
 * @returns {Promise} SweetAlert promise
 *
 * @example
 * showSuccess('Patient saved successfully!', () => {
 *     window.location.href = '/patients';
 * });
 */
export function showSuccess(message, callback = null) {
    return Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        confirmButtonColor: SwalConfig.confirmButtonColor,
        confirmButtonText: 'Great!',
        timer: 3000,
        timerProgressBar: true
    }).then((result) => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}

/**
 * Show error message
 *
 * @param {string} message - Error message to display
 * @param {Array<string>|null} errors - Optional array of error details
 * @returns {Promise} SweetAlert promise
 *
 * @example
 * showError('Failed to save patient', [
 *     'Name is required',
 *     'Phone number must be valid'
 * ]);
 */
export function showError(message, errors = null) {
    let html = message;

    if (errors && Array.isArray(errors) && errors.length > 0) {
        html += '<br><br><ul class="text-left list-disc list-inside text-sm">';
        errors.forEach(error => {
            html += `<li>${error}</li>`;
        });
        html += '</ul>';
    }

    return Swal.fire({
        icon: 'error',
        title: 'Error!',
        html: html,
        confirmButtonColor: SwalConfig.confirmButtonColor,
        confirmButtonText: 'OK'
    });
}

/**
 * Show confirmation dialog
 *
 * @param {string} title - Dialog title
 * @param {string} message - Dialog message
 * @param {function} onConfirm - Callback function when confirmed
 * @param {function|null} onCancel - Optional callback function when cancelled
 * @returns {Promise} SweetAlert promise
 *
 * @example
 * showConfirmation(
 *     'Submit Record?',
 *     'Are you sure you want to submit this prenatal record?',
 *     () => submitForm(),
 *     () => console.log('Cancelled')
 * );
 */
export function showConfirmation(title, message, onConfirm, onCancel = null) {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonColor: SwalConfig.confirmButtonColor,
        cancelButtonColor: SwalConfig.cancelButtonColor,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && typeof onConfirm === 'function') {
            onConfirm();
        } else if (result.isDismissed && onCancel && typeof onCancel === 'function') {
            onCancel();
        }
    });
}

/**
 * Show delete confirmation dialog
 *
 * @param {string} itemName - Name of the item to delete
 * @param {function} onConfirm - Callback function when deletion is confirmed
 * @returns {Promise} SweetAlert promise
 *
 * @example
 * showDeleteConfirmation('Patient John Doe', () => {
 *     deletePatient(patientId);
 * });
 */
export function showDeleteConfirmation(itemName, onConfirm) {
    return Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        html: `You are about to delete <strong>${itemName}</strong>.<br>This action cannot be undone!`,
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: SwalConfig.cancelButtonColor,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed && typeof onConfirm === 'function') {
            onConfirm();
        }
    });
}

/**
 * Show loading state dialog
 *
 * @param {string} message - Loading message to display
 * @returns {void}
 *
 * @example
 * showLoading('Saving patient record...');
 * // ... perform operation ...
 * closeAlert();
 */
export function showLoading(message = 'Processing...') {
    Swal.fire({
        title: message,
        html: 'Please wait...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Close any open SweetAlert dialog
 *
 * @returns {void}
 *
 * @example
 * showLoading('Processing...');
 * setTimeout(() => closeAlert(), 2000);
 */
export function closeAlert() {
    Swal.close();
}

/**
 * Handle AJAX form submission with SweetAlert feedback
 *
 * @param {HTMLFormElement} form - The form element to submit
 * @param {string} successMessage - Success message to display
 * @param {function} onSuccess - Callback function on successful submission
 * @param {string} loadingMessage - Loading message during submission
 * @returns {Promise<void>}
 *
 * @example
 * const form = document.getElementById('patientForm');
 * handleAjaxSubmit(
 *     form,
 *     'Patient saved successfully!',
 *     () => window.location.reload(),
 *     'Saving patient...'
 * );
 */
export function handleAjaxSubmit(form, successMessage, onSuccess, loadingMessage = 'Submitting...') {
    const formData = new FormData(form);

    // Show loading on submit button
    const submitBtn = form.querySelector('[type="submit"]');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>' + loadingMessage;
    }

    // Send AJAX request
    return fetch(form.action, {
        method: form.method || 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        // Restore button state
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }

        if (data.success) {
            showSuccess(data.message || successMessage, onSuccess);
        } else {
            const errors = data.errors ? Object.values(data.errors).flat() : null;
            showError(data.message || 'An error occurred', errors);
        }
    })
    .catch(error => {
        // Restore button state on error
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }

        console.error('[SweetAlert] AJAX Error:', error);
        showError('An unexpected error occurred. Please try again.');
    });
}

/**
 * Initialize SweetAlert for Laravel flash messages on page load
 * Automatically displays flash messages from session
 *
 * @returns {void}
 */
export function initializeFlashMessages() {
    // Check for Laravel flash messages
    const flashSuccess = document.querySelector('[data-flash-success]');
    const flashError = document.querySelector('[data-flash-error]');

    if (flashSuccess) {
        const message = flashSuccess.getAttribute('data-flash-success');
        if (message) {
            showSuccess(message);
        }
    }

    if (flashError) {
        const message = flashError.getAttribute('data-flash-error');
        if (message) {
            showError(message);
        }
    }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', initializeFlashMessages);

// Export for global window access (for backward compatibility with legacy code)
if (typeof window !== 'undefined') {
    window.showSuccess = showSuccess;
    window.showError = showError;
    window.showConfirmation = showConfirmation;
    window.showDeleteConfirmation = showDeleteConfirmation;
    window.showLoading = showLoading;
    window.closeAlert = closeAlert;
    window.handleAjaxSubmit = handleAjaxSubmit;
}
