<!-- Universal Modal Form Reset System -->
<script>
/**
 * Universal Modal Form Reset System
 * Automatically clears forms when modals are closed via cancel buttons
 */

// Modal Form Reset Manager
class ModalFormResetManager {
    constructor() {
        this.init();
    }

    init() {
        // Wait for DOM to be fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupFormResets());
        } else {
            this.setupFormResets();
        }
    }

    setupFormResets() {
        // Find all modals and their forms
        const modals = document.querySelectorAll('[id*="modal"], .modal-overlay, [class*="modal"]');
        
        modals.forEach(modal => {
            this.setupModalFormReset(modal);
        });

        // Also setup for dynamically created modals
        this.observeNewModals();
    }

    setupModalFormReset(modal) {
        const forms = modal.querySelectorAll('form');
        const cancelButtons = modal.querySelectorAll(
            'button[onclick*="close"], button[onclick*="Cancel"], [onclick*="Modal()"]'
        );
        const closeButtons = modal.querySelectorAll(
            '.modal-close, [aria-label="Close"], [title="Close"]'
        );

        // Combine all close/cancel buttons
        const allCloseButtons = [...cancelButtons, ...closeButtons];

        allCloseButtons.forEach(button => {
            // Store original onclick handler
            const originalOnClick = button.onclick;
            
            // Create new click handler that resets forms
            button.onclick = (e) => {
                // Reset all forms in this modal
                forms.forEach(form => this.resetForm(form));
                
                // Call original handler if it exists
                if (originalOnClick) {
                    originalOnClick.call(button, e);
                }
            };

            // Also add event listener as backup
            button.addEventListener('click', () => {
                setTimeout(() => {
                    forms.forEach(form => this.resetForm(form));
                }, 100);
            });
        });

        // Reset form when modal overlay is clicked (if it closes the modal)
        if (modal.classList.contains('modal-overlay')) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    forms.forEach(form => this.resetForm(form));
                }
            });
        }
    }

    resetForm(form) {
        if (!form) return;

        try {
            // Reset the form using native method
            form.reset();

            // Additional cleanup for various input types
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                // Remove validation classes
                input.classList.remove('border-red-500', 'border-green-500', 'error-border', 'success-border');
                
                // Reset specific input types
                switch(input.type) {
                    case 'checkbox':
                    case 'radio':
                        input.checked = false;
                        break;
                    case 'file':
                        input.value = '';
                        break;
                    case 'select-one':
                    case 'select-multiple':
                        input.selectedIndex = 0;
                        break;
                    default:
                        if (input.tagName.toLowerCase() === 'textarea') {
                            input.value = '';
                        }
                }

                // Remove any custom data attributes
                input.removeAttribute('data-validation-error');
                
                // Trigger change event to update any listeners
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            // Clear any error messages
            const errorElements = form.querySelectorAll('.error-message, .text-red-500, .text-red-600');
            errorElements.forEach(el => {
                if (el.textContent.includes('required') || el.textContent.includes('error')) {
                    el.textContent = '';
                    el.style.display = 'none';
                }
            });

            // Reset any custom validation states
            form.classList.remove('was-validated');
            
            // Trigger custom reset event
            form.dispatchEvent(new CustomEvent('modal-form-reset', {
                bubbles: true,
                detail: { form: form }
            }));

            console.log('Form reset successfully:', form.id || form.className);

        } catch (error) {
            console.warn('Error resetting form:', error);
        }
    }

    observeNewModals() {
        // Observer for dynamically added modals
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Check if the added node is a modal or contains modals
                        if (node.matches && (node.matches('[id*="modal"]') || node.matches('.modal-overlay'))) {
                            this.setupModalFormReset(node);
                        }
                        
                        // Check for modals within the added node
                        const childModals = node.querySelectorAll ? node.querySelectorAll('[id*="modal"], .modal-overlay') : [];
                        childModals.forEach(modal => this.setupModalFormReset(modal));
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Manual reset method for specific forms
    resetFormById(formId) {
        const form = document.getElementById(formId);
        if (form) {
            this.resetForm(form);
        }
    }

    // Manual reset method for forms by selector
    resetFormsBySelector(selector) {
        const forms = document.querySelectorAll(selector);
        forms.forEach(form => this.resetForm(form));
    }
}

// Initialize the modal form reset manager
window.modalFormResetManager = new ModalFormResetManager();

// Global helper functions
window.resetModalForm = function(formId) {
    window.modalFormResetManager.resetFormById(formId);
};

window.resetAllModalForms = function() {
    const forms = document.querySelectorAll('.modal-overlay form, [id*="modal"] form');
    forms.forEach(form => window.modalFormResetManager.resetForm(form));
};

// Add keyboard shortcut (Escape) to reset and close modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal-overlay:not(.hidden)');
        openModals.forEach(modal => {
            const forms = modal.querySelectorAll('form');
            forms.forEach(form => window.modalFormResetManager.resetForm(form));
        });
    }
});

// Test function to verify form reset works
window.testFormReset = function() {
    console.log('🧪 Testing Modal Form Reset System...');
    
    // Fill some forms with test data
    const inputs = document.querySelectorAll('input[type="text"], input[type="email"], textarea, select');
    let testCount = 0;
    
    inputs.forEach(input => {
        if (testCount < 5) { // Only test first 5 inputs
            switch(input.type) {
                case 'text':
                case 'email':
                    input.value = `Test data ${testCount + 1}`;
                    break;
                case 'textarea':
                    input.value = `Test textarea content ${testCount + 1}`;
                    break;
                case 'select-one':
                    if (input.options.length > 1) {
                        input.selectedIndex = 1;
                    }
                    break;
            }
            testCount++;
        }
    });
    
    console.log(`✅ Filled ${testCount} form fields with test data`);
    console.log('💡 Now click any Cancel button to see forms reset!');
    
    // Auto-reset after 10 seconds for demo
    setTimeout(() => {
        console.log('🔄 Auto-resetting all forms...');
        window.resetAllModalForms();
        console.log('✨ All forms have been reset!');
    }, 10000);
};

// Debug function (remove in production)
window.debugModalReset = function() {
    console.log('Available modal forms:', document.querySelectorAll('.modal-overlay form, [id*="modal"] form'));
    console.log('Modal reset manager:', window.modalFormResetManager);
    
    // Show all available modals
    const modals = document.querySelectorAll('[id*="modal"], .modal-overlay');
    console.log('Found modals:', modals);
    
    modals.forEach(modal => {
        const forms = modal.querySelectorAll('form');
        const cancelButtons = modal.querySelectorAll('button[onclick*="close"], button[onclick*="Cancel"]');
        console.log(`Modal ${modal.id}: ${forms.length} forms, ${cancelButtons.length} cancel buttons`);
    });
};
</script>