/**
 * Midwife SweetAlert Handler
 * Reusable SweetAlert functions for all Midwife pages
 */

// SweetAlert Configuration
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
 * @param {string} message - Success message
 * @param {function} callback - Optional callback after clicking OK
 */
function showSuccess(message, callback = null) {
    Swal.fire({
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
 * @param {string} message - Error message
 * @param {array} errors - Optional array of error details
 */
function showError(message, errors = null) {
    let html = message;

    if (errors && Array.isArray(errors) && errors.length > 0) {
        html += '<br><br><ul class="text-left list-disc list-inside text-sm">';
        errors.forEach(error => {
            html += `<li>${error}</li>`;
        });
        html += '</ul>';
    }

    Swal.fire({
        icon: 'error',
        title: 'Error!',
        html: html,
        confirmButtonColor: SwalConfig.confirmButtonColor,
        confirmButtonText: 'OK'
    });
}

/**
 * Show confirmation dialog
 * @param {string} title - Dialog title
 * @param {string} message - Dialog message
 * @param {function} onConfirm - Callback when confirmed
 * @param {function} onCancel - Optional callback when cancelled
 */
function showConfirmation(title, message, onConfirm, onCancel = null) {
    Swal.fire({
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
 * Show delete confirmation
 * @param {string} itemName - Name of item to delete
 * @param {function} onConfirm - Callback when confirmed
 */
function showDeleteConfirmation(itemName, onConfirm) {
    Swal.fire({
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
 * Show loading state
 * @param {string} message - Loading message
 */
function showLoading(message = 'Processing...') {
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
 * Close any open SweetAlert
 */
function closeAlert() {
    Swal.close();
}

/**
 * Handle AJAX form submission with SweetAlert
 * @param {HTMLFormElement} form - The form element
 * @param {string} successMessage - Success message
 * @param {function} onSuccess - Callback on success
 * @param {string} loadingMessage - Loading message (optional)
 */
function handleAjaxSubmit(form, successMessage, onSuccess, loadingMessage = 'Submitting...') {
    const formData = new FormData(form);

    // Show loading
    const submitBtn = form.querySelector('[type="submit"]');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>' + loadingMessage;
    }

    // Send AJAX request
    fetch(form.action, {
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
        // Restore button
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
        // Restore button
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }

        console.error('Error:', error);
        showError('An unexpected error occurred. Please try again.');
    });
}

/**
 * Initialize SweetAlert for flash messages on page load
 */
document.addEventListener('DOMContentLoaded', function() {
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
});

// Make functions globally available
window.showSuccess = showSuccess;
window.showError = showError;
window.showConfirmation = showConfirmation;
window.showDeleteConfirmation = showDeleteConfirmation;
window.showLoading = showLoading;
window.closeAlert = closeAlert;
window.handleAjaxSubmit = handleAjaxSubmit;
