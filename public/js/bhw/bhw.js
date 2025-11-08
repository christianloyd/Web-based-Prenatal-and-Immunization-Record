/**
 * BHW Shared JavaScript Utilities
 * Common functions used across all BHW modules
 * Includes: modal management, alerts, date utilities, form validation
 */

// ============================================
// MODAL MANAGEMENT
// ============================================

/**
 * Open a modal by ID
 * @param {string} modalId - The ID of the modal element
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

/**
 * Close a modal by ID
 * @param {string} modalId - The ID of the modal element
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = ''; // Restore scrolling
        }, 200); // Wait for fade-out animation
    }
}

/**
 * Close modal when clicking outside the modal content
 * @param {Event} event - The click event
 * @param {string} modalId - The ID of the modal element
 */
function closeModalOnOutsideClick(event, modalId) {
    const modal = document.getElementById(modalId);
    if (event.target === modal) {
        closeModal(modalId);
    }
}

/**
 * Setup keyboard shortcuts for modals (ESC to close)
 * @param {string} modalId - The ID of the modal element
 */
function setupModalKeyboardShortcuts(modalId) {
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal(modalId);
        }
    });
}

// ============================================
// ALERT MANAGEMENT
// ============================================

/**
 * Auto-hide alert messages after a delay
 * @param {number} delay - Delay in milliseconds (default: 5000)
 */
function autoHideAlerts(delay = 5000) {
    const alerts = document.querySelectorAll('[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, delay);
    });
}

/**
 * Show SweetAlert2 success message
 * @param {string} title - Alert title
 * @param {string} text - Alert message
 */
function showSuccessAlert(title, text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            confirmButtonColor: '#243b55',
            timer: 3000,
            timerProgressBar: true
        });
    }
}

/**
 * Show SweetAlert2 error message
 * @param {string} title - Alert title
 * @param {string} text - Alert message
 */
function showErrorAlert(title, text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            confirmButtonColor: '#243b55'
        });
    }
}

/**
 * Show SweetAlert2 confirmation dialog
 * @param {string} title - Confirmation title
 * @param {string} text - Confirmation message
 * @param {function} onConfirm - Callback function when confirmed
 */
function showConfirmationDialog(title, text, onConfirm) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#243b55',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && typeof onConfirm === 'function') {
                onConfirm();
            }
        });
    }
}

// ============================================
// DATE UTILITIES
// ============================================

/**
 * Get today's date in YYYY-MM-DD format
 * @returns {string} Today's date
 */
function getTodayDate() {
    return new Date().toISOString().split('T')[0];
}

/**
 * Set max date attribute to today for date inputs
 * @param {string} inputSelector - CSS selector for the input element
 */
function setMaxDateToToday(inputSelector) {
    const input = document.querySelector(inputSelector);
    if (input) {
        input.setAttribute('max', getTodayDate());
    }
}

/**
 * Calculate date difference in days
 * @param {Date|string} date1 - First date
 * @param {Date|string} date2 - Second date
 * @returns {number} Difference in days
 */
function dateDifferenceInDays(date1, date2) {
    const d1 = new Date(date1);
    const d2 = new Date(date2);
    const diffTime = Math.abs(d2 - d1);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

/**
 * Add days to a date
 * @param {Date|string} date - Starting date
 * @param {number} days - Number of days to add
 * @returns {string} New date in YYYY-MM-DD format
 */
function addDaysToDate(date, days) {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result.toISOString().split('T')[0];
}

/**
 * Format date to readable format (e.g., "Jan 15, 2024")
 * @param {Date|string} date - Date to format
 * @returns {string} Formatted date
 */
function formatDate(date) {
    const d = new Date(date);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return d.toLocaleDateString('en-US', options);
}

/**
 * Validate if date is not in the future
 * @param {string} dateString - Date string to validate
 * @returns {boolean} True if valid (not in future)
 */
function isDateNotInFuture(dateString) {
    const selectedDate = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return selectedDate <= today;
}

// ============================================
// FORM UTILITIES
// ============================================

/**
 * Validate required fields in a form
 * @param {string} formId - The ID of the form element
 * @returns {boolean} True if all required fields are filled
 */
function validateRequiredFields(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

/**
 * Clear form fields
 * @param {string} formId - The ID of the form element
 */
function clearForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        // Remove error classes
        const errorFields = form.querySelectorAll('.error');
        errorFields.forEach(field => {
            field.classList.remove('error');
        });
    }
}

/**
 * Disable form submission button with loading state
 * @param {string} buttonId - The ID of the submit button
 * @param {string} loadingText - Text to display while loading (default: "Processing...")
 */
function setButtonLoading(buttonId, loadingText = 'Processing...') {
    const button = document.getElementById(buttonId);
    if (button) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`;
    }
}

/**
 * Reset form submission button to original state
 * @param {string} buttonId - The ID of the submit button
 */
function resetButton(buttonId) {
    const button = document.getElementById(buttonId);
    if (button && button.dataset.originalText) {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText;
    }
}

/**
 * Handle form submission with AJAX
 * @param {string} formId - The ID of the form
 * @param {string} url - The URL to submit to
 * @param {function} onSuccess - Callback on success
 * @param {function} onError - Callback on error
 */
function submitFormAjax(formId, url, onSuccess, onError) {
    const form = document.getElementById(formId);
    if (!form) return;

    const formData = new FormData(form);

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (typeof onSuccess === 'function') {
            onSuccess(data);
        }
    })
    .catch(error => {
        if (typeof onError === 'function') {
            onError(error);
        }
    });
}

// ============================================
// SEARCH UTILITIES
// ============================================

/**
 * Setup search input enhancements
 * - Double-click to clear
 * - Enter to submit
 * @param {string} searchInputId - The ID of the search input
 * @param {string} searchFormId - The ID of the search form
 */
function setupSearchEnhancements(searchInputId, searchFormId) {
    const searchInput = document.getElementById(searchInputId);
    const searchForm = document.getElementById(searchFormId);

    if (searchInput) {
        // Double-click to clear
        searchInput.addEventListener('dblclick', function() {
            this.value = '';
            this.focus();
        });

        // Enter to submit
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && searchForm) {
                e.preventDefault();
                searchForm.submit();
            }
        });
    }
}

/**
 * Debounce function for search inputs
 * @param {function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {function} Debounced function
 */
function debounce(func, delay = 300) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showSuccessAlert('Copied!', 'Text copied to clipboard');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}

/**
 * Scroll to element smoothly
 * @param {string} elementId - The ID of the element to scroll to
 */
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

/**
 * Toggle element visibility
 * @param {string} elementId - The ID of the element
 */
function toggleVisibility(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.toggle('hidden');
    }
}

/**
 * Format number with commas
 * @param {number} num - Number to format
 * @returns {string} Formatted number
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// ============================================
// INITIALIZATION
// ============================================

/**
 * Initialize common features when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    autoHideAlerts();

    // Setup ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close all active modals
            const activeModals = document.querySelectorAll('.modal-overlay.active');
            activeModals.forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
});

// Export functions for use in other modules (if using module system)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        openModal,
        closeModal,
        closeModalOnOutsideClick,
        setupModalKeyboardShortcuts,
        autoHideAlerts,
        showSuccessAlert,
        showErrorAlert,
        showConfirmationDialog,
        getTodayDate,
        setMaxDateToToday,
        dateDifferenceInDays,
        addDaysToDate,
        formatDate,
        isDateNotInFuture,
        validateRequiredFields,
        clearForm,
        setButtonLoading,
        resetButton,
        submitFormAjax,
        setupSearchEnhancements,
        debounce,
        copyToClipboard,
        scrollToElement,
        toggleVisibility,
        formatNumber
    };
}
