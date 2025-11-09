/**
 * Modal Management Module
 * Handles all modal interactions for immunization management
 *
 * @module midwife/immunization/modals
 */

import { clearValidationStates } from '@shared/utils/validation';
import { focusFirstInput } from '@shared/utils/dom';

/**
 * Opens the Add Immunization modal
 * Resets form and prepares UI for new entry
 *
 * @returns {void}
 */
export function openAddModal() {
    const modal = document.getElementById('immunizationModal');
    const form = document.getElementById('immunizationForm');

    if (!modal || !form) {
        console.error('[Immunization] Add modal elements not found');
        return;
    }

    // Reset form
    form.reset();
    clearValidationStates(form);

    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input after animation
    focusFirstInput(form, 300);

    console.log('[Immunization] Add modal opened');
}

/**
 * Closes the immunization modal
 *
 * @param {Event} [event] - Optional click event from modal overlay
 * @returns {void}
 */
export function closeModal(event) {
    // Only close if clicking overlay (not modal content)
    if (event && event.target !== event.currentTarget) {
        return;
    }

    const modal = document.getElementById('immunizationModal');
    if (!modal) {
        console.error('[Immunization] Modal element not found');
        return;
    }

    // Remove show class to trigger close animation
    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Reset form only if no validation errors exist
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('immunizationForm');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }
        }

        console.log('[Immunization] Modal closed');
    }, 300);
}

/**
 * Opens the View Immunization Details modal
 *
 * @param {number} immunizationId - ID of immunization to view
 * @param {Object} immunizationData - Immunization data object
 * @returns {void}
 */
export function openViewModal(immunizationId, immunizationData) {
    const modal = document.getElementById('viewModal');

    if (!modal) {
        console.error('[Immunization] View modal element not found');
        return;
    }

    // Populate modal with data
    populateViewModal(immunizationData);

    // Show modal
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    console.log(`[Immunization] View modal opened for ID: ${immunizationId}`);
}

/**
 * Closes the view modal
 *
 * @param {Event} [event] - Optional click event
 * @returns {void}
 */
export function closeViewModal(event) {
    if (event && event.target !== event.currentTarget) {
        return;
    }

    const modal = document.getElementById('viewModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        console.log('[Immunization] View modal closed');
    }, 300);
}

/**
 * Populates the view modal with immunization data
 *
 * @private
 * @param {Object} data - Immunization data
 * @param {string} data.child_name - Child's name
 * @param {string} data.vaccine_name - Vaccine name
 * @param {string} data.dose - Dose number
 * @param {string} data.schedule_date - Scheduled date
 * @param {string} data.schedule_time - Scheduled time
 * @param {string} data.status - Status
 * @param {string} [data.notes] - Optional notes
 * @returns {void}
 */
function populateViewModal(data) {
    const elements = {
        childName: document.getElementById('view_child_name'),
        vaccineName: document.getElementById('view_vaccine_name'),
        dose: document.getElementById('view_dose'),
        scheduleDate: document.getElementById('view_schedule_date'),
        scheduleTime: document.getElementById('view_schedule_time'),
        status: document.getElementById('view_status'),
        notes: document.getElementById('view_notes'),
    };

    // Populate fields
    if (elements.childName) elements.childName.textContent = data.child_name || 'N/A';
    if (elements.vaccineName) elements.vaccineName.textContent = data.vaccine_name || 'N/A';
    if (elements.dose) elements.dose.textContent = data.dose || 'N/A';
    if (elements.scheduleDate) elements.scheduleDate.textContent = data.schedule_date || 'N/A';
    if (elements.scheduleTime) elements.scheduleTime.textContent = data.schedule_time || 'N/A';
    if (elements.status) {
        elements.status.textContent = data.status || 'N/A';
        elements.status.className = getStatusClass(data.status);
    }
    if (elements.notes) elements.notes.textContent = data.notes || 'No notes';
}

/**
 * Gets appropriate CSS class for status badge
 *
 * @private
 * @param {string} status - Status value
 * @returns {string} CSS class names
 */
function getStatusClass(status) {
    const baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';

    switch (status) {
        case 'Done':
            return `${baseClasses} bg-green-100 text-green-800`;
        case 'Upcoming':
            return `${baseClasses} bg-blue-100 text-blue-800`;
        case 'Missed':
            return `${baseClasses} bg-red-100 text-red-800`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
}

/**
 * Opens the Reschedule modal
 *
 * @param {number} immunizationId - ID of immunization to reschedule
 * @returns {void}
 */
export function openRescheduleModal(immunizationId) {
    const modal = document.getElementById('rescheduleModal');
    const form = document.getElementById('rescheduleForm');

    if (!modal || !form) {
        console.error('[Immunization] Reschedule modal elements not found');
        return;
    }

    // Set immunization ID in hidden field
    const idInput = form.querySelector('input[name="immunization_id"]');
    if (idInput) {
        idInput.value = immunizationId;
    }

    // Reset form
    form.reset();
    clearValidationStates(form);

    // Show modal
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus date input
    setTimeout(() => {
        const dateInput = form.querySelector('input[name="new_date"]');
        if (dateInput) dateInput.focus();
    }, 300);

    console.log(`[Immunization] Reschedule modal opened for ID: ${immunizationId}`);
}

/**
 * Closes the reschedule modal
 *
 * @param {Event} [event] - Optional click event
 * @returns {void}
 */
export function closeRescheduleModal(event) {
    if (event && event.target !== event.currentTarget) {
        return;
    }

    const modal = document.getElementById('rescheduleModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        const form = document.getElementById('rescheduleForm');
        if (form) {
            form.reset();
            clearValidationStates(form);
        }

        console.log('[Immunization] Reschedule modal closed');
    }, 300);
}
