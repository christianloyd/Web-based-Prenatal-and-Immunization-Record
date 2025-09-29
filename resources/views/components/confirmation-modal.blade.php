{{-- Global Confirmation Modal Component --}}
<div id="global-confirmation-modal" tabindex="-1" class="modal-transition hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-[9999] flex justify-center items-center w-full h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="modal-content-transition relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Close button -->
            <button type="button" class="absolute top-3 end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" onclick="closeConfirmationModal()">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            
            <!-- Modal content -->
            <div class="p-4 md:p-5 text-center">
                <!-- Icon container - will be dynamically updated -->
                <div id="confirmation-icon-container" class="mx-auto mb-4 w-12 h-12">
                    <svg id="confirmation-icon" class="w-12 h-12 text-gray-400 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                
                <!-- Message text -->
                <h3 id="confirmation-message" class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">
                    Are you sure you want to perform this action?
                </h3>
                
                <!-- Action buttons -->
                <div class="flex justify-center space-x-3">
                    <button id="confirmation-confirm-btn" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center">
                        Yes, I'm sure
                    </button>
                    <button id="confirmation-cancel-btn" type="button" class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700" onclick="closeConfirmationModal()">
                        No, cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Global Confirmation Modal Styles --}}
<style>
/* Modal Animation Styles */
.modal-transition {
    transition: opacity 0.25s ease-in-out;
    background-color: rgba(0, 0, 0, 0);
}

.modal-transition:not(.hidden) {
    opacity: 1;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-transition.hidden {
    opacity: 0;
    background-color: rgba(0, 0, 0, 0);
}

.modal-content-transition {
    transition: transform 0.25s ease-out, opacity 0.25s ease-out;
    transform: translateY(-16px) scale(0.95);
    opacity: 0;
}

.modal-transition:not(.hidden) .modal-content-transition {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* Smooth modal entrance animation */
.modal-transition.show {
    animation: modalFadeIn 0.25s ease-out forwards;
}

.modal-transition.show .modal-content-transition {
    animation: modalSlideIn 0.25s ease-out forwards;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        background-color: rgba(0, 0, 0, 0);
    }
    to {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.5);
    }
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-16px) scale(0.95);
        opacity: 0;
    }
    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}
</style>

{{-- Global Confirmation Modal JavaScript --}}
<script>
/**
 * Global Confirmation Modal System
 * This provides a reusable confirmation modal for the entire application
 */

// Global variable to store the confirmation callback
let confirmationCallback = null;

/**
 * Show confirmation modal with custom options
 * @param {Object} options - Configuration object
 * @param {string} options.title - Modal title/message
 * @param {string} options.type - Type: 'danger', 'warning', 'info', 'success'
 * @param {string} options.confirmText - Confirm button text
 * @param {string} options.cancelText - Cancel button text  
 * @param {Function} options.onConfirm - Callback function when confirmed
 * @param {Function} options.onCancel - Callback function when cancelled
 */
function showConfirmationModal(options = {}) {
    const modal = document.getElementById('global-confirmation-modal');
    const icon = document.getElementById('confirmation-icon');
    const message = document.getElementById('confirmation-message');
    const confirmBtn = document.getElementById('confirmation-confirm-btn');
    const cancelBtn = document.getElementById('confirmation-cancel-btn');
    
    // Set default options
    const config = {
        title: 'Are you sure you want to perform this action?',
        type: 'warning',
        confirmText: "Yes, I'm sure",
        cancelText: 'No, cancel',
        onConfirm: null,
        onCancel: null,
        ...options
    };
    
    // Update message
    message.textContent = config.title;
    
    // Update icon and button styles based on type
    updateModalStyle(icon, confirmBtn, config.type);
    
    // Update button texts
    confirmBtn.textContent = config.confirmText;
    cancelBtn.textContent = config.cancelText;
    
    // Store callback for confirmation
    confirmationCallback = config.onConfirm;
    
    // Set up event listeners
    confirmBtn.onclick = function() {
        if (confirmationCallback && typeof confirmationCallback === 'function') {
            confirmationCallback();
        }
        if (config.onConfirm && typeof config.onConfirm === 'function') {
            config.onConfirm();
        }
        closeConfirmationModal();
    };
    
    if (config.onCancel) {
        cancelBtn.onclick = function() {
            config.onCancel();
            closeConfirmationModal();
        };
    }
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Add animation
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

/**
 * Update modal styling based on type
 */
function updateModalStyle(icon, confirmBtn, type) {
    // Reset classes
    icon.className = 'w-12 h-12';
    confirmBtn.className = 'font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center';
    
    switch (type) {
        case 'danger':
            icon.className += ' text-red-400 dark:text-red-200';
            confirmBtn.className += ' text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800';
            break;
        case 'warning':
            icon.className += ' text-yellow-400 dark:text-yellow-200';
            confirmBtn.className += ' text-white bg-yellow-600 hover:bg-yellow-800 focus:ring-4 focus:outline-none focus:ring-yellow-300 dark:focus:ring-yellow-800';
            break;
        case 'success':
            icon.className += ' text-green-400 dark:text-green-200';
            confirmBtn.className += ' text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800';
            break;
        case 'info':
            icon.className += ' text-blue-400 dark:text-blue-200';
            confirmBtn.className += ' text-white bg-primary hover:bg-secondary focus:ring-4 focus:outline-none focus:ring-blue-300 dark:focus:ring-blue-800';
            break;
        default:
            icon.className += ' text-gray-400 dark:text-gray-200';
            confirmBtn.className += ' text-white bg-gray-600 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 dark:focus:ring-gray-800';
    }
}

/**
 * Close confirmation modal
 */
function closeConfirmationModal() {
    const modal = document.getElementById('global-confirmation-modal');
    
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        confirmationCallback = null;
    }, 250);
}

/**
 * Convenience functions for common confirmation types
 */

// Delete confirmation
function confirmDelete(itemName, onConfirm, onCancel = null) {
    showConfirmationModal({
        title: `Are you sure you want to delete "${itemName}"? This action cannot be undone.`,
        type: 'danger',
        confirmText: 'Yes, delete it',
        cancelText: 'Cancel',
        onConfirm: onConfirm,
        onCancel: onCancel
    });
}

// Deactivate confirmation  
function confirmDeactivate(itemName, onConfirm, onCancel = null) {
    showConfirmationModal({
        title: `Are you sure you want to deactivate "${itemName}"? They will no longer be able to access the system.`,
        type: 'warning',
        confirmText: 'Yes, deactivate',
        cancelText: 'Cancel',
        onConfirm: onConfirm,
        onCancel: onCancel
    });
}

// Activate confirmation
function confirmActivate(itemName, onConfirm, onCancel = null) {
    showConfirmationModal({
        title: `Are you sure you want to activate "${itemName}"? They will regain access to the system.`,
        type: 'success',
        confirmText: 'Yes, activate',
        cancelText: 'Cancel',
        onConfirm: onConfirm,
        onCancel: onCancel
    });
}

// Submit confirmation
function confirmSubmit(message, onConfirm, onCancel = null) {
    showConfirmationModal({
        title: message || 'Are you sure you want to submit this form?',
        type: 'info',
        confirmText: 'Yes, submit',
        cancelText: 'Cancel',
        onConfirm: onConfirm,
        onCancel: onCancel
    });
}

// Save confirmation
function confirmSave(message, onConfirm, onCancel = null) {
    showConfirmationModal({
        title: message || 'Are you sure you want to save these changes?',
        type: 'info',
        confirmText: 'Yes, save',
        cancelText: 'Cancel',
        onConfirm: onConfirm,
        onCancel: onCancel
    });
}

// ESC key support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('global-confirmation-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeConfirmationModal();
        }
    }
});

// Click outside to close
document.addEventListener('click', function(e) {
    const modal = document.getElementById('global-confirmation-modal');
    if (e.target === modal && !modal.classList.contains('hidden')) {
        closeConfirmationModal();
    }
});
</script>