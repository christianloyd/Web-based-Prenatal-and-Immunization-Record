/**
 * Midwife Prenatal Record Index Page JavaScript
 * Handles prenatal record modals and interactions
 */

/* ============================================
   VIEW PRENATAL RECORD MODAL
   ============================================ */

function openViewPrenatalModal(record) {
    if (!record) {
        console.error('No prenatal record provided');
        return;
    }

    // Helper function to safely set text content
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || 'N/A';
        }
    };

    // Populate modal fields
    setText('viewPatientName', record.patient?.name);
    setText('viewPatientId', record.patient?.formatted_patient_id);
    setText('viewPatientAge', record.patient?.age ? `${record.patient.age} years` : null);
    setText('viewGestationalAge', record.gestational_age);
    setText('viewTrimester', record.trimester ?
        `${record.trimester}${record.trimester == 1 ? 'st' : (record.trimester == 2 ? 'nd' : 'rd')} Trimester` : null);
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

    // Show modal
    const modal = document.getElementById('view-prenatal-modal');
    if (!modal) {
        console.error('View prenatal modal not found');
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeViewPrenatalModal(e) {
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('view-prenatal-modal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

/* ============================================
   EDIT PRENATAL RECORD MODAL
   ============================================ */

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
    if (form.dataset.updateUrl) {
        form.action = form.dataset.updateUrl.replace(':id', record.id);
    }

    // Helper function to format dates for input fields
    const formatDate = (dateString) => {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        } catch (error) {
            return '';
        }
    };

    // Helper function to safely set form values
    const setValue = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
            // Remove any validation styling
            element.classList.remove('error-border', 'success-border');
        }
    };

    // Helper function to set text content
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || 'N/A';
        }
    };

    // Populate patient information display (read-only)
    setText('edit-patient-name-display', record.patient?.name);
    setText('edit-patient-id-display', record.patient?.formatted_patient_id);
    setText('edit-patient-age-display', record.patient?.age ? `${record.patient.age} years` : null);

    // Set hidden patient_id field
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

    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeEditPrenatalModal(e) {
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('edit-prenatal-modal');
    if (!modal) return;

    // Reset form using the universal reset system
    const form = modal.querySelector('form');
    if (form && window.modalFormResetManager) {
        window.modalFormResetManager.resetForm(form);
    }

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

/* ============================================
   DATE VALIDATION AND AUTO-CALCULATION
   ============================================ */

function calculateEDD(lmpDate) {
    if (!lmpDate) return '';

    const lmp = new Date(lmpDate);
    const edd = new Date(lmp);
    edd.setDate(edd.getDate() + 280); // Add 280 days (40 weeks)

    return edd.toISOString().split('T')[0];
}

function setupDateValidation() {
    const today = new Date().toISOString().split('T')[0];

    // Setup for Add Modal
    const addLmpInput = document.querySelector('#prenatal-modal input[name="last_menstrual_period"]');
    const addEddInput = document.querySelector('#prenatal-modal input[name="expected_due_date"]');

    if (addLmpInput) {
        addLmpInput.setAttribute('max', today);

        if (addEddInput) {
            addLmpInput.addEventListener('change', function() {
                if (this.value && !addEddInput.value) {
                    addEddInput.value = calculateEDD(this.value);
                }
            });
        }
    }

    // Setup for Edit Modal
    const editLmpInput = document.querySelector('#edit-prenatal-modal input[name="last_menstrual_period"]');
    const editEddInput = document.querySelector('#edit-prenatal-modal input[name="expected_due_date"]');

    if (editLmpInput) {
        editLmpInput.setAttribute('max', today);

        if (editEddInput) {
            editLmpInput.addEventListener('change', function() {
                if (this.value && !editEddInput.value) {
                    editEddInput.value = calculateEDD(this.value);
                }
            });
        }
    }
}

/* ============================================
   FORM VALIDATION
   ============================================ */

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

/* ============================================
   EVENT LISTENERS
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    setupDateValidation();

    // Form submission validation with SweetAlert
    const prenatalForm = document.getElementById('prenatal-form');
    if (prenatalForm) {
        prenatalForm.addEventListener('submit', function(e) {
            if (!validateForm('prenatal-form')) {
                e.preventDefault();
                showError('Please fill in all required fields.');
            }
        });
    }

    const editPrenatalForm = document.getElementById('edit-prenatal-form');
    if (editPrenatalForm) {
        editPrenatalForm.addEventListener('submit', function(e) {
            if (!validateForm('edit-prenatal-form')) {
                e.preventDefault();
                showError('Please fill in all required fields.');
            }
        });
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeViewPrenatalModal();
        closeEditPrenatalModal();
    }
});

// Prevent modal close when clicking inside modal content
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
        }
    }
});
