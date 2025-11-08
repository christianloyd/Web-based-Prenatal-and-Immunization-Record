/**
 * Prenatal Record Index - JavaScript Module
 *
 * This module handles all client-side functionality for the prenatal records index page.
 * It manages modal interactions, form validation, date calculations, and user interactions.
 *
 * @module prenatalrecord-index
 * @version 1.0.0
 * @author BHW System
 *
 * Main Features:
 * - Complete pregnancy modal management
 * - View prenatal record modal
 * - Edit prenatal record modal
 * - Date validation and EDD calculation from LMP
 * - Form validation
 * - Keyboard shortcuts (Escape key to close modals)
 * - Click-outside-to-close functionality
 *
 * Dependencies:
 * - Requires modal elements to be present in the DOM
 * - Uses Font Awesome icons
 * - Compatible with Tailwind CSS classes
 */

// =============================================================================
// COMPLETE PREGNANCY MODAL FUNCTIONS
// =============================================================================

/**
 * Opens the complete pregnancy confirmation modal
 *
 * This modal warns users that completing a pregnancy record is irreversible.
 * It displays patient information and requires confirmation before proceeding.
 *
 * @param {number} recordId - The ID of the prenatal record to complete
 * @param {string} patientName - The name of the patient for display
 *
 * @example
 * openCompletePregnancyModal(123, 'Jane Doe');
 */
function openCompletePregnancyModal(recordId, patientName) {
    console.log('Opening complete pregnancy modal for record:', recordId, 'patient:', patientName);

    // Set patient name in the modal
    const patientNameElement = document.getElementById('completePatientName');
    if (patientNameElement) {
        patientNameElement.textContent = patientName || 'Unknown Patient';
    } else {
        console.error('completePatientName element not found');
        return;
    }

    // Set form action URL dynamically
    // NOTE: This URL uses Laravel route format - may need adjustment if routes change
    const form = document.getElementById('completePregnancyForm');
    if (form) {
        form.action = `/bhw/prenatalrecord/${recordId}/complete`;
        console.log('Form action set to:', form.action);
    } else {
        console.error('completePregnancyForm not found');
        return;
    }

    // Show modal with smooth animation
    const modal = document.getElementById('completePregnancyModal');
    if (modal) {
        modal.classList.remove('hidden');

        // Use requestAnimationFrame to ensure DOM has updated before adding show class
        // This ensures the CSS transition animation plays correctly
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });

        // Prevent body scrolling when modal is open
        document.body.style.overflow = 'hidden';
        console.log('Modal should be visible now with animation');
    } else {
        console.error('completePregnancyModal not found');
    }
}

/**
 * Closes the complete pregnancy confirmation modal
 *
 * Handles modal closing with smooth animation and restores body scroll.
 * Prevents accidental closing when clicking inside the modal content.
 *
 * @param {Event} [event] - The click event (optional, for click-outside detection)
 *
 * @example
 * closeCompletePregnancyModal(); // Direct close
 * closeCompletePregnancyModal(event); // Close via click outside
 */
function closeCompletePregnancyModal(event) {
    // Prevent closing if clicking inside modal content
    if (event && event.target !== event.currentTarget && !event.currentTarget.id) {
        return;
    }

    const modal = document.getElementById('completePregnancyModal');
    if (!modal) return;

    // Remove show class first to trigger closing animation
    modal.classList.remove('show');

    // Wait for animation to complete (300ms) before hiding
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// =============================================================================
// VIEW PRENATAL RECORD MODAL FUNCTIONS
// =============================================================================

/**
 * Opens the view prenatal record modal with populated data
 *
 * Displays comprehensive prenatal record information in a read-only modal.
 * Safely handles missing data by displaying 'N/A' for null values.
 *
 * @param {Object} record - The prenatal record object
 * @param {Object} record.patient - Patient information object
 * @param {string} record.patient.name - Patient's full name
 * @param {string} record.patient.formatted_patient_id - Patient ID
 * @param {number} record.patient.age - Patient's age
 * @param {string} record.gestational_age - Current gestational age
 * @param {number} record.trimester - Current trimester (1, 2, or 3)
 * @param {string} record.last_menstrual_period - Last menstrual period date
 * @param {string} record.expected_due_date - Expected delivery date
 * @param {number} record.gravida - Number of pregnancies
 * @param {number} record.para - Number of births
 * @param {string} record.status - Record status
 * @param {string} record.status_text - Human-readable status
 * @param {string} record.blood_pressure - Blood pressure reading
 * @param {number} record.weight - Weight in kg
 * @param {number} record.height - Height in cm
 * @param {string} record.medical_history - Medical history notes
 * @param {string} record.notes - Additional notes
 * @param {string} record.last_visit - Last visit date
 * @param {string} record.next_appointment - Next appointment date
 *
 * @example
 * const record = { patient: { name: 'Jane Doe', age: 28 }, gravida: 2, para: 1 };
 * openViewPrenatalModal(record);
 */
function openViewPrenatalModal(record) {
    if (!record) {
        console.error('No prenatal record provided');
        return;
    }

    /**
     * Helper function to safely set text content
     * @private
     * @param {string} id - Element ID
     * @param {*} value - Value to set (will display 'N/A' if falsy)
     */
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || 'N/A';
        }
    };

    // Populate patient information
    setText('viewPatientName', record.patient?.name);
    setText('viewPatientId', record.patient?.formatted_patient_id);
    setText('viewPatientAge', record.patient?.age ? `${record.patient.age} years` : null);

    // Populate pregnancy details
    setText('viewGestationalAge', record.gestational_age);
    setText('viewTrimester', record.trimester ?
        `${record.trimester}${record.trimester == 1 ? 'st' : (record.trimester == 2 ? 'nd' : 'rd')} Trimester` : null);
    setText('viewLMP', record.last_menstrual_period);
    setText('viewEDD', record.expected_due_date);

    // Populate obstetric history (Gravida/Para)
    setText('viewGravida', record.gravida ? `G${record.gravida}` : null);
    setText('viewPara', record.para !== null ? `P${record.para}` : null);

    // Populate status and vitals
    setText('viewStatus', record.status_text || record.status);
    setText('viewBloodPressure', record.blood_pressure);
    setText('viewWeight', record.weight ? `${record.weight} kg` : null);
    setText('viewHeight', record.height ? `${record.height} cm` : null);

    // Populate notes and appointments
    setText('viewMedicalHistory', record.medical_history);
    setText('viewNotes', record.notes);
    setText('viewLastVisit', record.last_visit);
    setText('viewNextAppointment', record.next_appointment);

    // Show modal with animation
    const modal = document.getElementById('view-prenatal-modal');
    if (!modal) {
        console.error('View prenatal modal not found');
        return;
    }

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });
    document.body.style.overflow = 'hidden';
}

/**
 * Closes the view prenatal record modal
 *
 * @param {Event} [e] - The click event (optional, for click-outside detection)
 *
 * @example
 * closeViewPrenatalModal(); // Direct close
 * closeViewPrenatalModal(event); // Close via click outside
 */
function closeViewPrenatalModal(e) {
    // Only close if clicking on the overlay itself, not its children
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('view-prenatal-modal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// =============================================================================
// EDIT PRENATAL RECORD MODAL FUNCTIONS
// =============================================================================

/**
 * Opens the edit prenatal record modal with populated form fields
 *
 * Populates the edit form with current record data and sets up the form
 * action URL for submission. Handles date formatting for HTML input fields.
 *
 * @param {Object} record - The prenatal record object to edit
 * @param {number} record.id - Record ID
 * @param {number} record.patient_id - Patient ID
 * @param {Object} record.patient - Patient information
 * @param {string} record.last_menstrual_period - LMP date
 * @param {string} record.expected_due_date - EDD date
 * @param {number} record.gravida - Gravida value
 * @param {number} record.para - Para value
 * @param {string} record.status - Current status
 * @param {string} record.blood_pressure - Blood pressure
 * @param {number} record.weight - Weight in kg
 * @param {number} record.height - Height in cm
 * @param {string} record.medical_history - Medical history
 * @param {string} record.notes - Notes
 *
 * @example
 * const record = { id: 123, patient_id: 456, gravida: 2, para: 1, status: 'normal' };
 * openEditPrenatalModal(record);
 */
function openEditPrenatalModal(record) {
    if (!record) {
        console.error('No prenatal record provided');
        return;
    }

    const modal = document.getElementById('edit-prenatal-modal');
    const form = document.getElementById('edit-prenatal-form');

    if (!modal || !form) {
        console.error('Edit modal or form not found');
        return;
    }

    // Set form action URL
    // NOTE: This assumes a data-update-url attribute exists on the form
    // The URL template should contain ':id' which will be replaced with the actual record ID
    if (form.dataset.updateUrl) {
        form.action = form.dataset.updateUrl.replace(':id', record.id);
    }

    /**
     * Formats a date string for HTML date input fields (YYYY-MM-DD)
     * @private
     * @param {string} dateString - Date string to format
     * @returns {string} Formatted date string or empty string
     */
    const formatDate = (dateString) => {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        } catch (error) {
            return '';
        }
    };

    /**
     * Helper function to safely set form field values
     * @private
     * @param {string} id - Element ID
     * @param {*} value - Value to set
     */
    const setValue = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
            // Remove any previous validation classes
            element.classList.remove('error-border', 'success-border');
        }
    };

    /**
     * Helper function to set text content
     * @private
     * @param {string} id - Element ID
     * @param {*} value - Value to set
     */
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || 'N/A';
        }
    };

    // Populate patient information display (read-only fields)
    setText('edit-patient-name-display', record.patient?.name);
    setText('edit-patient-id-display', record.patient?.formatted_patient_id);
    setText('edit-patient-age-display', record.patient?.age ? `${record.patient.age} years` : null);

    // Set hidden patient_id field (required for form submission)
    setValue('edit-patient-id-hidden', record.patient_id || '');

    // Populate editable form fields
    setValue('edit-lmp', formatDate(record.last_menstrual_period));
    setValue('edit-due-date', formatDate(record.expected_due_date));
    setValue('edit-gravida', record.gravida || '');
    setValue('edit-para', record.para || '');
    setValue('edit-status', record.status || 'normal');
    setValue('edit-blood-pressure', record.blood_pressure || '');
    setValue('edit-weight', record.weight || '');
    setValue('edit-height', record.height || '');
    setValue('edit-medical-history', record.medical_history || '');
    setValue('edit-notes', record.notes || '');

    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });
    document.body.style.overflow = 'hidden';
}

/**
 * Closes the edit prenatal record modal
 *
 * @param {Event} [e] - The click event (optional, for click-outside detection)
 *
 * @example
 * closeEditPrenatalModal(); // Direct close
 * closeEditPrenatalModal(event); // Close via click outside
 */
function closeEditPrenatalModal(e) {
    // Only close if clicking on the overlay itself, not its children
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('edit-prenatal-modal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// =============================================================================
// DATE VALIDATION AND CALCULATION FUNCTIONS
// =============================================================================

/**
 * Calculates Expected Due Date (EDD) from Last Menstrual Period (LMP)
 *
 * Uses Naegele's Rule: EDD = LMP + 280 days (40 weeks)
 * This is the standard calculation used in obstetrics.
 *
 * @param {string} lmpDate - Last menstrual period date in ISO format (YYYY-MM-DD)
 * @returns {string} Expected due date in ISO format (YYYY-MM-DD) or empty string
 *
 * @example
 * const edd = calculateEDD('2024-01-15'); // Returns '2024-10-21'
 */
function calculateEDD(lmpDate) {
    if (!lmpDate) return '';

    const lmp = new Date(lmpDate);
    const edd = new Date(lmp);
    edd.setDate(edd.getDate() + 280); // Add 280 days (40 weeks)

    return edd.toISOString().split('T')[0];
}

/**
 * Sets up date validation for LMP and EDD fields
 *
 * - Restricts LMP to dates not in the future
 * - Auto-calculates EDD when LMP is entered
 * - Only calculates EDD if it's not already set (preserves manual edits)
 *
 * This function should be called on page load (DOMContentLoaded)
 *
 * @example
 * setupDateValidation(); // Called automatically on page load
 */
function setupDateValidation() {
    const today = new Date().toISOString().split('T')[0];

    // Setup for Edit Modal
    const editLmpInput = document.querySelector('#edit-prenatal-modal input[name="last_menstrual_period"]');
    const editEddInput = document.querySelector('#edit-prenatal-modal input[name="expected_due_date"]');

    if (editLmpInput) {
        // Set maximum date to today (can't select future dates)
        editLmpInput.setAttribute('max', today);

        if (editEddInput) {
            // Auto-calculate EDD when LMP changes
            editLmpInput.addEventListener('change', function() {
                // Only auto-calculate if EDD is empty (don't overwrite manual edits)
                if (this.value && !editEddInput.value) {
                    editEddInput.value = calculateEDD(this.value);
                }
            });
        }
    }
}

// =============================================================================
// FORM VALIDATION FUNCTIONS
// =============================================================================

/**
 * Validates a form by checking all required fields
 *
 * Adds visual feedback by applying red border to empty required fields.
 * Removes red border when field is valid.
 *
 * @param {string} formId - The ID of the form to validate
 * @returns {boolean} True if all required fields are filled, false otherwise
 *
 * @example
 * const isValid = validateForm('edit-prenatal-form');
 * if (!isValid) {
 *     alert('Please fill in all required fields.');
 * }
 */
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });

    return isValid;
}

// =============================================================================
// EVENT LISTENERS AND INITIALIZATION
// =============================================================================

/**
 * Initialize all event listeners and setup functions
 *
 * This runs when the DOM is fully loaded and sets up:
 * - Date validation
 * - Form validation
 * - Click-outside-to-close for modals
 * - Keyboard shortcuts (Escape key)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date validation
    setupDateValidation();

    // Form submission validation for edit form
    const editPrenatalForm = document.getElementById('edit-prenatal-form');
    if (editPrenatalForm) {
        editPrenatalForm.addEventListener('submit', function(e) {
            if (!validateForm('edit-prenatal-form')) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }

    // Setup click-outside-to-close for complete pregnancy modal
    const completeModal = document.getElementById('completePregnancyModal');
    if (completeModal) {
        completeModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCompletePregnancyModal(e);
            }
        });
    }
});

/**
 * Close modals when Escape key is pressed
 *
 * Listens for Escape key globally and closes any open modal.
 * Checks each modal individually to ensure it's visible before closing.
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close complete pregnancy modal if open
        const completeModal = document.getElementById('completePregnancyModal');
        if (completeModal && !completeModal.classList.contains('hidden')) {
            closeCompletePregnancyModal();
        }

        // Close view modal if open
        const viewModal = document.getElementById('view-prenatal-modal');
        if (viewModal && !viewModal.classList.contains('hidden')) {
            closeViewPrenatalModal();
        }

        // Close edit modal if open
        const editModal = document.getElementById('edit-prenatal-modal');
        if (editModal && !editModal.classList.contains('hidden')) {
            closeEditPrenatalModal();
        }
    }
});

/**
 * Prevent modal close when clicking inside modal content
 *
 * Handles click events on modal overlays and routes them to the appropriate
 * close function. Only closes if clicking directly on the overlay background.
 */
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        const modalId = e.target.id;
        switch (modalId) {
            case 'view-prenatal-modal':
                closeViewPrenatalModal(e);
                break;
            case 'edit-prenatal-modal':
                closeEditPrenatalModal(e);
                break;
            case 'completePregnancyModal':
                closeCompletePregnancyModal(e);
                break;
        }
    }
});
