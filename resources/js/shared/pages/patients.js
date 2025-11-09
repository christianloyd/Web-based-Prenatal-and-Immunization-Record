/**
 * Unified Patients Page Module
 * Works for both BHW and Midwife roles using shared utilities
 *
 * This module consolidates duplicate patient management code,
 * eliminating ~400 lines of duplication between BHW and Midwife.
 *
 * @module shared/pages/patients
 */

import { Modal, ModalManager } from '../components/modal.js';
import { createForm } from '../components/form.js';
import { success } from '../components/notifications.js';
import { showSuccess, showError, showDeleteConfirmation } from '../utils/sweetalert.js';
import { getCurrentRoutes } from '../config/routes.js';
import { can } from '../config/permissions.js';
import { qs, on } from '../utils/dom.js';

/**
 * Patients page state
 */
let state = {
    currentPatientData: null,
    modals: null,
    forms: null,
    routes: null
};

/**
 * Initialize patients page
 *
 * @returns {void}
 */
export function initializePatientsPage() {
    console.log('[Patients] Initializing...');

    // Get current user routes
    state.routes = getCurrentRoutes();

    // Initialize modals
    initializeModals();

    // Initialize forms
    initializeForms();

    // Initialize table row actions
    initializeTableActions();

    // Initialize search form
    initializeSearchForm();

    console.log('[Patients] Initialization complete');
}

/**
 * Initialize modals
 * @private
 */
function initializeModals() {
    state.modals = new ModalManager();

    // Register Add Patient modal
    state.modals.register('add', '#patient-modal', {
        onClose: () => {
            const form = qs('#patient-modal form');
            if (form) form.reset();
        }
    });

    // Register View Patient modal
    state.modals.register('view', '#view-patient-modal', {
        closeOnBackdrop: true
    });

    // Register Edit Patient modal
    state.modals.register('edit', '#edit-patient-modal', {
        onClose: () => {
            const form = qs('#edit-patient-modal form');
            if (form) form.reset();
        }
    });

    // Expose global functions for backward compatibility with Blade templates
    window.openPatientModal = () => state.modals.open('add');
    window.closePatientModal = () => state.modals.close('add');
    window.openViewPatientModal = (patient) => openViewPatient(patient);
    window.closeViewPatientModal = () => state.modals.close('view');
    window.openEditPatientModal = (patient) => openEditPatient(patient);
    window.closeEditPatientModal = () => state.modals.close('edit');
}

/**
 * Initialize forms
 * @private
 */
function initializeForms() {
    state.forms = {};

    // Add Patient Form
    const addForm = qs('#patient-modal form');
    if (addForm) {
        state.forms.add = createForm(addForm, {
            validation: {
                first_name: { required: true, minLength: 2 },
                last_name: { required: true, minLength: 2 },
                contact: { required: true, phone: true },
                address: { required: true }
            },
            successMessage: 'Patient added successfully!',
            onSuccess: (response) => {
                state.modals.close('add');
                window.location.reload();
            }
        });
    }

    // Edit Patient Form
    const editForm = qs('#edit-patient-modal form');
    if (editForm) {
        state.forms.edit = createForm(editForm, {
            validation: {
                first_name: { required: true, minLength: 2 },
                last_name: { required: true, minLength: 2 },
                contact: { required: true, phone: true },
                address: { required: true }
            },
            successMessage: 'Patient updated successfully!',
            onSuccess: (response) => {
                state.modals.close('edit');
                window.location.reload();
            }
        });
    }
}

/**
 * Initialize table row actions (view, edit, delete)
 * @private
 */
function initializeTableActions() {
    const tableBody = qs('#patientsTable tbody');
    if (!tableBody) return;

    // View action
    on(tableBody, 'click', '.btn-view-patient', function(e) {
        e.preventDefault();
        const patientData = JSON.parse(this.getAttribute('data-patient'));
        openViewPatient(patientData);
    });

    // Edit action (if user has permission)
    if (can('patients', 'edit')) {
        on(tableBody, 'click', '.btn-edit-patient', function(e) {
            e.preventDefault();
            const patientData = JSON.parse(this.getAttribute('data-patient'));
            openEditPatient(patientData);
        });
    }

    // Delete action (if user has permission)
    if (can('patients', 'delete')) {
        on(tableBody, 'click', '.btn-delete-patient', function(e) {
            e.preventDefault();
            const patientId = this.getAttribute('data-id');
            const patientName = this.getAttribute('data-name');
            deletePatient(patientId, patientName);
        });
    }
}

/**
 * Initialize search form enhancement
 * @private
 */
function initializeSearchForm() {
    const searchForm = qs('form[role="search"]');
    if (!searchForm) return;

    // Auto-submit on Enter key
    const searchInput = searchForm.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
    }
}

/**
 * Open View Patient modal
 *
 * @param {Object} patient - Patient data
 * @returns {void}
 */
function openViewPatient(patient) {
    if (!patient) {
        console.error('[Patients] No patient data provided');
        return;
    }

    // Store current patient data
    state.currentPatientData = patient;

    // Populate modal fields
    populateViewModal(patient);

    // Open modal
    state.modals.open('view');
}

/**
 * Populate view patient modal
 * @private
 *
 * @param {Object} patient - Patient data
 * @returns {void}
 */
function populateViewModal(patient) {
    // Basic Info
    setText('#viewPatientName', patient.name || `${patient.first_name} ${patient.last_name}` || 'N/A');
    setText('#viewPatientId', patient.formatted_patient_id || 'N/A');
    setText('#viewPatientAge', patient.age ? `${patient.age} years` : 'N/A');
    setText('#viewPatientContact', patient.contact || 'N/A');
    setText('#viewPatientEmergencyContact', patient.emergency_contact || 'N/A');
    setText('#viewPatientAddress', patient.address || 'N/A');
    setText('#viewPatientOccupation', patient.occupation || 'N/A');

    // Risk Status
    const riskStatusElement = qs('#viewPatientRiskStatus');
    if (riskStatusElement && patient.active_prenatal_record) {
        const status = patient.active_prenatal_record.status;
        const statusBadges = {
            'normal': '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Normal</span>',
            'monitor': '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Monitor</span>',
            'high-risk': '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">High Risk</span>',
            'due': '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Due</span>'
        };
        riskStatusElement.innerHTML = statusBadges[status] || '<span class="text-gray-500">N/A</span>';
    }

    // EDD (Expected Due Date)
    setText('#viewPatientEDD',
        patient.active_prenatal_record?.edd ?
        new Date(patient.active_prenatal_record.edd).toLocaleDateString() :
        'N/A'
    );

    // Notes
    setText('#viewPatientNotes', patient.notes || 'No notes available');

    // Set profile link
    const viewProfileBtn = qs('#viewPatientProfileBtn');
    if (viewProfileBtn && state.routes.patients && patient.id) {
        viewProfileBtn.href = state.routes.patients.show(patient.id);
    }
}

/**
 * Open Edit Patient modal
 *
 * @param {Object} patient - Patient data
 * @returns {void}
 */
function openEditPatient(patient) {
    if (!patient) {
        console.error('[Patients] No patient data provided');
        return;
    }

    // Store current patient data
    state.currentPatientData = patient;

    // Populate edit form
    const editForm = qs('#edit-patient-modal form');
    if (editForm && state.forms.edit) {
        state.forms.edit.setData({
            first_name: patient.first_name || '',
            last_name: patient.last_name || '',
            middle_name: patient.middle_name || '',
            birthdate: patient.birthdate || '',
            contact: patient.contact || '',
            emergency_contact: patient.emergency_contact || '',
            address: patient.address || '',
            occupation: patient.occupation || '',
            notes: patient.notes || ''
        });

        // Update form action with patient ID
        if (state.routes.patients && patient.id) {
            editForm.action = state.routes.patients.update(patient.id);
        }
    }

    // Open modal
    state.modals.open('edit');
}

/**
 * Delete patient
 *
 * @param {number} patientId - Patient ID
 * @param {string} patientName - Patient name
 * @returns {void}
 */
function deletePatient(patientId, patientName) {
    showDeleteConfirmation(`Patient: ${patientName}`, () => {
        // Create and submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = state.routes.patients.destroy(patientId);

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken.content;
            form.appendChild(csrfInput);
        }

        // Add DELETE method
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    });
}

/**
 * Helper: Set element text content
 * @private
 *
 * @param {string} selector - Element selector
 * @param {string} text - Text content
 * @returns {void}
 */
function setText(selector, text) {
    const element = qs(selector);
    if (element) {
        element.textContent = text;
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', initializePatientsPage);

// Export for manual initialization
export default {
    initializePatientsPage,
    openViewPatient,
    openEditPatient,
    deletePatient
};
