/**
 * Unified Prenatal Records Page Module
 * Works for both BHW and Midwife roles using shared utilities
 *
 * This module consolidates duplicate prenatal record management code,
 * eliminating ~200 lines of duplication between BHW and Midwife.
 *
 * @module shared/pages/prenatalrecords
 */

import { Modal, ModalManager } from '../components/modal.js';
import { createForm } from '../components/form.js';
import { showSuccess, showError, showConfirmation } from '../utils/sweetalert.js';
import { getCurrentRoutes } from '../config/routes.js';
import { can } from '../config/permissions.js';
import { qs, on } from '../utils/dom.js';

/**
 * Prenatal records page state
 */
let state = {
    currentRecord: null,
    modals: null,
    forms: null,
    routes: null
};

/**
 * Initialize prenatal records page
 *
 * @returns {void}
 */
export function initializePrenatalRecordsPage() {
    console.log('[PrenatalRecords] Initializing...');

    // Get current user routes
    state.routes = getCurrentRoutes();

    // Initialize modals
    initializeModals();

    // Initialize forms
    initializeForms();

    // Initialize table actions
    initializeTableActions();

    console.log('[PrenatalRecords] Initialization complete');
}

/**
 * Initialize modals
 * @private
 */
function initializeModals() {
    state.modals = new ModalManager();

    // View Record modal
    state.modals.register('view', '#view-prenatal-modal');

    // Edit Record modal
    state.modals.register('edit', '#edit-prenatal-modal');

    // Complete Pregnancy modal (BHW specific)
    if (can('prenatalRecords', 'complete')) {
        state.modals.register('complete', '#completePregnancyModal');
    }

    // Expose global functions for backward compatibility
    window.openViewPrenatalModal = (record) => openViewRecord(record);
    window.closeViewPrenatalModal = () => state.modals.close('view');
    window.openEditPrenatalModal = (record) => openEditRecord(record);
    window.closeEditPrenatalModal = () => state.modals.close('edit');
    window.openCompletePregnancyModal = (recordId, patientName) => openCompletePregnancy(recordId, patientName);
    window.closeCompletePregnancyModal = () => state.modals.close('complete');
}

/**
 * Initialize forms
 * @private
 */
function initializeForms() {
    state.forms = {};

    // Edit Prenatal Record Form
    const editForm = qs('#edit-prenatal-form');
    if (editForm) {
        state.forms.edit = createForm(editForm, {
            validation: {
                last_menstrual_period: { required: true, date: true, notFuture: true },
                blood_pressure: { required: false },
                weight: { numeric: true },
                height: { numeric: true }
            },
            successMessage: 'Prenatal record updated successfully!',
            onSuccess: () => {
                state.modals.close('edit');
                window.location.reload();
            }
        });
    }

    // Complete Pregnancy Form (BHW)
    const completeForm = qs('#completePregnancyForm');
    if (completeForm && can('prenatalRecords', 'complete')) {
        state.forms.complete = createForm(completeForm, {
            successMessage: 'Pregnancy completed successfully!',
            onSuccess: () => {
                state.modals.close('complete');
                window.location.reload();
            }
        });
    }
}

/**
 * Initialize table row actions
 * @private
 */
function initializeTableActions() {
    const tableBody = qs('table tbody');
    if (!tableBody) return;

    // View action
    on(tableBody, 'click', '.btn-view-prenatal, [onclick*="openViewPrenatalModal"]', function(e) {
        if (this.dataset.record) {
            e.preventDefault();
            const recordData = JSON.parse(this.dataset.record);
            openViewRecord(recordData);
        }
    });

    // Edit action
    if (can('prenatalRecords', 'edit')) {
        on(tableBody, 'click', '.btn-edit-prenatal, [onclick*="openEditPrenatalModal"]', function(e) {
            if (this.dataset.record) {
                e.preventDefault();
                const recordData = JSON.parse(this.dataset.record);
                openEditRecord(recordData);
            }
        });
    }

    // Complete pregnancy action (BHW)
    if (can('prenatalRecords', 'complete')) {
        on(tableBody, 'click', '.btn-complete-pregnancy', function(e) {
            e.preventDefault();
            const recordId = this.dataset.id;
            const patientName = this.dataset.name;
            openCompletePregnancy(recordId, patientName);
        });
    }
}

/**
 * Open View Record modal
 *
 * @param {Object} record - Prenatal record data
 * @returns {void}
 */
function openViewRecord(record) {
    if (!record) {
        console.error('[PrenatalRecords] No record data provided');
        return;
    }

    state.currentRecord = record;

    // Helper function to safely set text content
    const setText = (id, value) => {
        const element = qs(`#${id}`);
        if (element) {
            element.textContent = value || 'N/A';
        }
    };

    // Populate modal fields
    setText('viewPatientName', record.patient?.name);
    setText('viewPatientId', record.patient?.formatted_patient_id);
    setText('viewPatientAge', record.patient?.age ? `${record.patient.age} years` : null);
    setText('viewGestationalAge', record.gestational_age);
    setText('viewTrimester', formatTrimester(record.trimester));
    setText('viewLMP', record.last_menstrual_period);
    setText('viewEDD', record.expected_due_date);
    setText('viewGravida', record.gravida ? `G${record.gravida}` : null);
    setText('viewPara', record.para !== null ? `P${record.para}` : null);
    setText('viewStatus', record.status_text || record.status);
    setText('viewBloodPressure', record.blood_pressure);
    setText('viewWeight', record.weight ? `${record.weight} kg` : null);
    setText('viewHeight', record.height ? `${record.height} cm` : null);
    setText('viewMedicalHistory', record.medical_history);
    setText('viewNotes', record.notes);
    setText('viewLastVisit', record.last_visit);
    setText('viewNextAppointment', record.next_appointment);

    state.modals.open('view');
}

/**
 * Open Edit Record modal
 *
 * @param {Object} record - Prenatal record data
 * @returns {void}
 */
function openEditRecord(record) {
    if (!record) {
        console.error('[PrenatalRecords] No record data provided');
        return;
    }

    state.currentRecord = record;

    const editForm = qs('#edit-prenatal-form');
    if (editForm && state.forms.edit) {
        // Populate form
        state.forms.edit.setData({
            last_menstrual_period: formatDateForInput(record.last_menstrual_period),
            gravida: record.gravida || '',
            para: record.para || '',
            blood_pressure: record.blood_pressure || '',
            weight: record.weight || '',
            height: record.height || '',
            medical_history: record.medical_history || '',
            notes: record.notes || ''
        });

        // Set form action URL
        if (state.routes.prenatalRecords && record.id) {
            editForm.action = state.routes.prenatalRecords.update(record.id);
        }
    }

    state.modals.open('edit');
}

/**
 * Open Complete Pregnancy modal (BHW only)
 *
 * @param {number} recordId - Record ID
 * @param {string} patientName - Patient name
 * @returns {void}
 */
function openCompletePregnancy(recordId, patientName) {
    if (!can('prenatalRecords', 'complete')) {
        showError('You do not have permission to complete pregnancies');
        return;
    }

    // Set patient name
    const nameElement = qs('#completePatientName');
    if (nameElement) {
        nameElement.textContent = patientName || 'Unknown Patient';
    }

    // Set form action
    const completeForm = qs('#completePregnancyForm');
    if (completeForm && state.routes.prenatalRecords) {
        completeForm.action = `${state.routes.prenatalRecords.show(recordId).replace(/\/\d+$/, '')}/${recordId}/complete`;
    }

    state.modals.open('complete');
}

/**
 * Helper: Format trimester text
 * @private
 *
 * @param {number} trimester - Trimester number
 * @returns {string} Formatted trimester text
 */
function formatTrimester(trimester) {
    if (!trimester) return 'N/A';
    const suffix = trimester == 1 ? 'st' : (trimester == 2 ? 'nd' : 'rd');
    return `${trimester}${suffix} Trimester`;
}

/**
 * Helper: Format date for input field
 * @private
 *
 * @param {string} dateString - Date string
 * @returns {string} Formatted date (YYYY-MM-DD)
 */
function formatDateForInput(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    } catch (error) {
        return '';
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', initializePrenatalRecordsPage);

// Export for manual initialization
export default {
    initializePrenatalRecordsPage,
    openViewRecord,
    openEditRecord,
    openCompletePregnancy
};
