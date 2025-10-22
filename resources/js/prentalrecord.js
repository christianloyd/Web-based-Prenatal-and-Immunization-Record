function openPatientModal() {
    const modal = document.getElementById('patient-modal');
    if (!modal) {
        console.error('Patient modal not found');
        return;
    }
    
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        const nameInput = modal.querySelector('input[name="name"]');
        if (nameInput) nameInput.focus();
    }, 300);

    
}

function closePatientModal(e) {
    if (e && e.target !== e.currentTarget) return;
    
    const modal = document.getElementById('patient-modal');
    if (!modal) return;
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Only reset form if there are no validation errors
        if (!document.querySelector('.bg-red-100')) {
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
                // Remove validation classes
                form.querySelectorAll('.error-border, .success-border').forEach(input => {
                    input.classList.remove('error-border', 'success-border');
                });
            }
        }
    }, 300);
}

// View Patient Modal Functions
function openViewPatientModal(record) {
    if (!record) {
        console.error('No patient record provided');
        return;
    }
    
    try {
        // Populate modal fields
        const fieldMappings = [
            { id: 'modalName', value: record.name },
            { id: 'modalId', value: record.formatted_patient_id },
            { id: 'modalAge', value: record.age },
            { id: 'modalContact', value: record.contact },
            { id: 'modalEmergencyContact', value: record.emergency_contact },
            { id: 'modalLastVisit', value: record.last_visit },
            { id: 'modalNextAppointment', value: record.next_appointment },
            { id: 'modalStatus', value: record.status },
            { id: 'modalMedicalHistory', value: record.medical_history },
            { id: 'modalNotes', value: record.notes }
        ];
        
        fieldMappings.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.textContent = field.value || 'N/A';
            }
        });
        
        // Special handling for Gravida/Para
        const gravidaParaElement = document.getElementById('modalGravidaPara');
        if (gravidaParaElement) {
            const gravida = record.gravida || '-';
            const para = record.para || '-';
            gravidaParaElement.textContent = `G${gravida} / P${para}`;
        }
        
        // Show modal with animation
        const modal = document.getElementById('viewPatientModal');
        const content = document.getElementById('viewPatientModalContent');
        
        if (!modal || !content) {
            console.error('View modal elements not found');
            return;
        }
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Trigger animation
        requestAnimationFrame(() => {
            content.classList.remove('-translate-y-10', 'opacity-0');
            content.classList.add('translate-y-0', 'opacity-100');
        });
        
    } catch (error) {
        console.error('Error opening view modal:', error);
    }
}

function closeViewPatientModal() {
    const modal = document.getElementById('viewPatientModal');
    const content = document.getElementById('viewPatientModalContent');
    
    if (!modal || !content) return;
    
    content.classList.remove('translate-y-0', 'opacity-100');
    content.classList.add('-translate-y-10', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// Edit Patient Modal Functions
function openEditPatientModal(record) {
    if (!record) {
        console.error('No patient record provided');
        return;
    }

    const modal = document.getElementById('edit-patient-modal');
    if (!modal) {
        console.error('Edit modal element not found');
        return;
    }

    const form = document.getElementById('edit-patient-form');
    if (!form) {
        console.error('Edit form not found');
        return;
    }
    const originalAction = form.action;
    form.action = originalAction.replace(':id', record.id);

    
    // Format the date to "yyyy-MM-dd"
    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0]; // Extract "yyyy-MM-dd"
    };

    // Populate form fields
    const fieldMappings = [
        { id: 'edit-patient-id', value: record.id },
        { id: 'edit-name', value: record.name },
        { id: 'edit-age', value: record.age },
        { id: 'edit-contact', value: record.contact },
        { id: 'edit-emergency-contact', value: record.emergency_contact },
        { id: 'edit-address', value: record.address },
        { id: 'edit-lmp', value: formatDate(record.last_menstrual_period) }, // Format date
        { id: 'edit-due-date', value: formatDate(record.expected_due_date) }, // Format date
        { id: 'edit-status', value: record.status },
        { id: 'edit-gravida', value: record.gravida },
        { id: 'edit-para', value: record.para },
        { id: 'edit-medical-history', value: record.medical_history },
        { id: 'edit-notes', value: record.notes }
    ];

    fieldMappings.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.value = field.value || '';
            element.classList.remove('error-border', 'success-border');
        }
    });

    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        const nameInput = document.getElementById('edit-name');
        if (nameInput) nameInput.focus();
    }, 100);
}

function closeEditPatientModal(event) {
    if (event && event.target !== event.currentTarget) {
        return;
    }
    
    const modal = document.getElementById('edit-patient-modal');
    if (!modal) return;
    
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    // Reset form action back to placeholder
    const form = document.getElementById('edit-patient-form');
    if (form) {
        form.action = "{{ route('midwife.prenatalrecord.update', ':id') }}";
        form.reset();
        
        // Remove validation styling
        form.querySelectorAll('.error-border, .success-border').forEach(input => {
            input.classList.remove('error-border', 'success-border');
        });
    }
}

// Helper Functions
function setModalMode(mode) {
    const formElements = document.querySelectorAll('#edit-patient-form input, #edit-patient-form select, #edit-patient-form textarea');
    
    if (mode === 'view') {
        formElements.forEach(element => {
            element.readOnly = true;
            element.disabled = true;
            element.classList.add('bg-gray-100', 'cursor-not-allowed');
            element.classList.remove('focus:ring-2', 'focus:ring-primary');
        });
    } else if (mode === 'edit') {
        formElements.forEach(element => {
            element.readOnly = false;
            element.disabled = false;
            element.classList.remove('bg-gray-100', 'cursor-not-allowed');
            element.classList.add('focus:ring-2', 'focus:ring-primary');
        });
        
        // Keep hidden input enabled
        const hiddenInput = document.getElementById('edit-patient-id');
        if (hiddenInput) hiddenInput.disabled = false;
    }
}

function validateField() {
    const isValid = this.value.trim() !== '';
    this.classList.toggle('error-border', !isValid);
    this.classList.toggle('success-border', isValid);
}

// Function to calculate pregnancy status based on dates
function calculatePregnancyStatus(lmpDate, dueDateString) {
    if (!lmpDate) return 'normal';

    const today = new Date();
    const lmp = new Date(lmpDate);
    const dueDate = dueDateString ? new Date(dueDateString) : new Date(lmp.getTime() + (280 * 24 * 60 * 60 * 1000));

    // Calculate gestational age in weeks
    const gestationalAgeMs = today.getTime() - lmp.getTime();
    const gestationalAgeWeeks = Math.floor(gestationalAgeMs / (7 * 24 * 60 * 60 * 1000));

    // Calculate days until due date
    const daysUntilDue = Math.floor((dueDate.getTime() - today.getTime()) / (24 * 60 * 60 * 1000));

    // Determine status based on pregnancy timeline
    if (gestationalAgeWeeks < 0) {
        return 'normal'; // Future LMP date
    } else if (gestationalAgeWeeks >= 42) {
        return 'high-risk'; // Post-term pregnancy
    } else if (gestationalAgeWeeks >= 37 && daysUntilDue <= 14) {
        return 'due'; // Near term or overdue
    } else if (gestationalAgeWeeks >= 28) {
        return 'monitor'; // Third trimester - closer monitoring
    } else if (gestationalAgeWeeks >= 20) {
        return 'normal'; // Second trimester
    } else if (gestationalAgeWeeks < 12 || gestationalAgeWeeks > 40) {
        return 'monitor'; // Early pregnancy or approaching term
    } else {
        return 'normal'; // Normal progression
    }
}

// Function to update status automatically
function updatePregnancyStatus(lmpInput, dueDateInput, statusSelect) {
    if (!statusSelect) return; // Skip if no status field (for add patient modal)

    const lmpValue = lmpInput ? lmpInput.value : '';
    const dueDateValue = dueDateInput ? dueDateInput.value : '';

    if (lmpValue) {
        const newStatus = calculatePregnancyStatus(lmpValue, dueDateValue);
        statusSelect.value = newStatus;

        // Add visual feedback for status change
        statusSelect.classList.add('success-border');
        setTimeout(() => {
            statusSelect.classList.remove('success-border');
        }, 2000);
    }
}

// Auto-calculate due date and update status when LMP is entered
function setupLmpAndEddHandlers(form) {
    const lmpInput = form.querySelector('input[name="last_menstrual_period"]');
    const dueDateInput = form.querySelector('input[name="expected_due_date"]');
    const statusSelect = form.querySelector('select[name="status"]');

    if (lmpInput) {
        lmpInput.addEventListener('change', function () {
            if (this.value) {
                try {
                    const lmpDate = new Date(this.value);

                    // Auto-fill due date if not already set
                    if (dueDateInput && !dueDateInput.value) {
                        const dueDate = new Date(lmpDate.getTime() + (280 * 24 * 60 * 60 * 1000));
                        dueDateInput.value = dueDate.toISOString().split('T')[0];

                        // Add visual feedback for auto-calculated date
                        dueDateInput.classList.add('success-border');
                        setTimeout(() => {
                            dueDateInput.classList.remove('success-border');
                        }, 2000);
                    }

                    // Update status automatically
                    updatePregnancyStatus(lmpInput, dueDateInput, statusSelect);

                } catch (error) {
                    console.log('Error calculating due date:', error);
                }
            }
        });
    }

    // Update status when due date is manually changed
    if (dueDateInput) {
        dueDateInput.addEventListener('change', function () {
            updatePregnancyStatus(lmpInput, dueDateInput, statusSelect);
        });
    }
}

// Initialize the form handlers
document.addEventListener('DOMContentLoaded', function () {
    const addPatientForm = document.getElementById('patient-form');
    if (addPatientForm) {
        setupLmpAndEddHandlers(addPatientForm);
    }

    const editPatientForm = document.getElementById('edit-patient-form');
    if (editPatientForm) {
        setupLmpAndEddHandlers(editPatientForm);
    }
});

// Event Listeners and Initialization
document.addEventListener('DOMContentLoaded', function() {
    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closePatientModal();
            closeEditPatientModal();
            closeViewPatientModal();
        }
    });

    // Auto-open add patient modal if there are validation errors
    const addPatientModal = document.getElementById('patient-modal');
    const addPatientForm = document.getElementById('patient-form');
    if (addPatientModal && addPatientForm) {
        const hasErrors = addPatientForm.querySelector('.bg-red-100, .error-border, .text-red-500');
        if (hasErrors) {
            openPatientModal();
        }
    }

    // Auto-open edit patient modal if there are validation errors
    const editPatientModal = document.getElementById('edit-patient-modal');
    const editPatientForm = document.getElementById('edit-patient-form');
    if (editPatientModal && editPatientForm) {
        const hasErrors = editPatientForm.querySelector('.bg-red-100, .error-border, .text-red-500');
        if (hasErrors) {
            // Need to get patient data from old input to reopen modal
            const patientIdInput = editPatientForm.querySelector('#edit-patient-id');
            if (patientIdInput && patientIdInput.value) {
                editPatientModal.classList.remove('hidden');
                editPatientModal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }
    }

    // Setup form validation and submission handling
    if (addPatientForm) {
        setupFormHandling(addPatientForm, 'add-submit-btn', 'Saving...');
    }

    if (editPatientForm) {
        setupFormHandling(editPatientForm, 'edit-submit-btn', 'Updating...');
    }
});

function setupFormHandling(form, buttonId, loadingText) {
    // Add validation to required fields
    const requiredInputs = form.querySelectorAll('[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', validateField);
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById(buttonId);
        if (!submitBtn) return;
        
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText}
        `;
        
        // Re-enable button after 10 seconds as fallback
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }, 10000);
    });
    
    // Get form inputs
    const lmpInput = form.querySelector('input[name="last_menstrual_period"]');
    const dueDateInput = form.querySelector('input[name="expected_due_date"]');
    const statusSelect = form.querySelector('select[name="status"]');
    
    // Set date constraints
    if (lmpInput && dueDateInput) {
        const today = new Date().toISOString().split('T')[0];
        lmpInput.setAttribute('max', today);
        dueDateInput.setAttribute('min', today);
        
        // Set max due date to 1 year from today (reasonable limit)
        const maxDueDate = new Date();
        maxDueDate.setFullYear(maxDueDate.getFullYear() + 1);
        dueDateInput.setAttribute('max', maxDueDate.toISOString().split('T')[0]);
    }
    
}