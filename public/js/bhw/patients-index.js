/**
 * Patients Index Page JavaScript
 * Handles patient modal management, form validation, and AJAX operations
 */

// ============================================
// GLOBAL VARIABLES
// ============================================

// Store current patient data for modal transitions
let currentPatientData = null;

// ============================================
// PATIENT MODAL MANAGEMENT
// ============================================

/**
 * Open the Add Patient Modal
 */
function openPatientModal() {
    const modal = document.getElementById('patient-modal');
    if (!modal) return console.error('Patient modal not found');

    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus on name input after modal animation
    setTimeout(() => {
        const nameInput = modal.querySelector('input[name="name"]');
        if (nameInput) nameInput.focus();
    }, 300);
}

/**
 * Close the Add Patient Modal
 * @param {Event} e - Optional event object for click-outside-to-close
 */
function closePatientModal(e) {
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('patient-modal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Only reset form if there are no validation errors
        const form = modal.querySelector('form');
        if (form && !document.querySelector('.bg-red-100')) {
            form.reset();
        }
    }, 300);
}

/**
 * Open the View Patient Modal
 * @param {Object} patient - Patient data object
 */
function openViewPatientModal(patient) {
    if (!patient) return console.error('No patient data provided');

    // Store patient data globally for other functions
    currentPatientData = patient;

    // Populate modal fields
    document.getElementById('viewPatientName').textContent =
        patient.name || (patient.first_name + ' ' + patient.last_name) || 'N/A';
    document.getElementById('viewPatientId').textContent =
        patient.formatted_patient_id || 'N/A';
    document.getElementById('viewPatientAge').textContent =
        patient.age ? patient.age + ' years' : 'N/A';
    document.getElementById('viewPatientContact').textContent =
        patient.contact || 'N/A';
    document.getElementById('viewPatientEmergencyContact').textContent =
        patient.emergency_contact || 'N/A';
    document.getElementById('viewPatientAddress').textContent =
        patient.address || 'N/A';
    document.getElementById('viewPatientOccupation').textContent =
        patient.occupation || 'N/A';

    // Set status from prenatal record with appropriate styling
    const riskStatusElement = document.getElementById('viewPatientRiskStatus');
    let statusHtml = '';

    if (patient.active_prenatal_record && patient.active_prenatal_record.status) {
        const status = patient.active_prenatal_record.status;

        switch(status) {
            case 'normal':
                statusHtml = '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Normal</span>';
                break;
            case 'monitor':
                statusHtml = '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Monitor</span>';
                break;
            case 'high-risk':
                statusHtml = '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">High Risk</span>';
                break;
            case 'due':
                statusHtml = '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Due</span>';
                break;
            case 'completed':
                statusHtml = '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Completed</span>';
                break;
            default:
                statusHtml = '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Unknown</span>';
        }
    } else {
        statusHtml = '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">No Prenatal Record</span>';
    }

    riskStatusElement.innerHTML = statusHtml;

    // Set created date if available
    const createdAtElement = document.getElementById('viewPatientCreatedAt');
    if (patient.created_at) {
        const date = new Date(patient.created_at);
        createdAtElement.textContent = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } else {
        createdAtElement.textContent = 'N/A';
    }

    // Update prenatal records link (will be set by Blade)
    const prenatalLink = document.getElementById('viewPrenatalRecordsLink');
    if (prenatalLink && window.prenatalRecordIndexRoute) {
        prenatalLink.href = window.prenatalRecordIndexRoute +
            '?search=' + encodeURIComponent(
                patient.name || (patient.first_name + ' ' + patient.last_name) || patient.formatted_patient_id
            );
    }

    // Show modal
    const modal = document.getElementById('view-patient-modal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}

/**
 * Close the View Patient Modal
 * @param {Event} e - Optional event object for click-outside-to-close
 */
function closeViewPatientModal(e) {
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('view-patient-modal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentPatientData = null; // Clear stored data
    }, 300);
}

/**
 * Close View Modal and Open Edit Modal (transition)
 */
function closeViewPatientModalAndEdit() {
    if (!currentPatientData) return;

    closeViewPatientModal();
    // Wait for the view modal to close before opening edit modal
    setTimeout(() => {
        openEditPatientModal(currentPatientData);
    }, 350);
}

/**
 * Open the Edit Patient Modal
 * @param {Object} patient - Patient data object
 */
function openEditPatientModal(patient) {
    if (!patient) return console.error('No patient data provided');

    const modal = document.getElementById('edit-patient-modal');
    const form = document.getElementById('edit-patient-form');
    if (!modal || !form) return console.error('Edit modal elements not found');

    // Set form action (URL will be set from data attribute)
    if (form.dataset.updateUrl) {
        form.action = form.dataset.updateUrl.replace(':id', patient.id);
    }

    // Populate form fields
    const fields = {
        'edit-first-name': patient.first_name || (patient.name ? patient.name.split(' ')[0] : ''),
        'edit-last-name': patient.last_name || (patient.name ? patient.name.split(' ').slice(1).join(' ') : ''),
        'edit-age': patient.age || '',
        'edit-contact': patient.contact || '',
        'edit-emergency-contact': patient.emergency_contact || '',
        'edit-address': patient.address || '',
        'edit-occupation': patient.occupation || ''
    };

    Object.entries(fields).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    });

    // Show modal
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus on first input
    setTimeout(() => {
        const firstInput = document.getElementById('edit-name');
        if (firstInput) firstInput.focus();
    }, 100);
}

/**
 * Close the Edit Patient Modal
 * @param {Event} e - Optional event object for click-outside-to-close
 */
function closeEditPatientModal(e) {
    if (e && e.target !== e.currentTarget) return;

    const modal = document.getElementById('edit-patient-modal');
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// ============================================
// FORM VALIDATION AND SUBMISSION
// ============================================

/**
 * Validate and submit the Add Patient form
 */
function initializeAddPatientForm() {
    const addPatientForm = document.getElementById('patient-form');

    if (addPatientForm) {
        addPatientForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const firstNameInput = this.querySelector('input[name="first_name"]');
            const lastNameInput = this.querySelector('input[name="last_name"]');
            const ageInput = this.querySelector('input[name="age"]');

            // Client-side validation with SweetAlert
            if (!firstNameInput || !firstNameInput.value.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'First name is required.',
                    confirmButtonColor: '#D4A373'
                });
                if (firstNameInput) firstNameInput.focus();
                return;
            }

            if (!lastNameInput || !lastNameInput.value.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Last name is required.',
                    confirmButtonColor: '#D4A373'
                });
                if (lastNameInput) lastNameInput.focus();
                return;
            }

            if (!ageInput || !ageInput.value || ageInput.value < 15 || ageInput.value > 50) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Age must be between 15 and 50 years.',
                    confirmButtonColor: '#D4A373'
                });
                if (ageInput) ageInput.focus();
                return;
            }

            // Show loading state
            const submitBtn = this.querySelector('#add-submit-btn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Registering...';

            // Prepare form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (data.success) {
                    // Close modal first
                    closePatientModal();

                    // Then show success SweetAlert after a short delay
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Patient registered successfully!',
                            confirmButtonColor: '#D4A373',
                            confirmButtonText: 'Great!'
                        }).then((result) => {
                            // Reload page after user clicks Great!
                            window.location.reload();
                        });
                    }, 400); // Wait for modal close animation
                } else {
                    // Error SweetAlert
                    let errorMessage = data.message || 'An error occurred while registering the patient.';

                    // If there are validation errors, show them
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat();
                        errorMessage += '\n\n' + errorList.join('\n');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
                        text: errorMessage,
                        confirmButtonColor: '#D4A373'
                    });
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An unexpected error occurred. Please try again.',
                    confirmButtonColor: '#D4A373'
                });
            });
        });
    }
}

// ============================================
// ALERT AND MESSAGE HANDLING
// ============================================

/**
 * Auto-hide alert messages after delay
 */
function autoHideAlertMessages() {
    const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 2000);
    });
}

/**
 * Show flash messages using SweetAlert2
 * This function will be called from Blade with session data
 */
function showFlashMessages(successMessage, errorMessage) {
    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: successMessage,
            confirmButtonColor: '#D4A373',
            timer: 3000,
            timerProgressBar: true
        });
    }

    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: errorMessage,
            confirmButtonColor: '#D4A373'
        });
    }
}

// ============================================
// SEARCH ENHANCEMENT
// ============================================

/**
 * Initialize search form enhancements
 */
function initializeSearchEnhancements() {
    const searchForm = document.querySelector('form[method="GET"]');
    const searchInput = searchForm?.querySelector('input[name="search"]');

    if (searchInput) {
        // Clear search on double click
        searchInput.addEventListener('dblclick', function() {
            this.value = '';
            this.focus();
        });

        // Submit on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
    }
}

// ============================================
// KEYBOARD SHORTCUTS
// ============================================

/**
 * Setup keyboard shortcuts for modals
 */
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closePatientModal();
            closeViewPatientModal();
            closeEditPatientModal();
        }
    });
}

// ============================================
// INITIALIZATION
// ============================================

/**
 * Initialize all patient page functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form validation and submission
    initializeAddPatientForm();

    // Initialize search enhancements
    initializeSearchEnhancements();

    // Setup keyboard shortcuts
    setupKeyboardShortcuts();

    // Auto-hide alert messages
    autoHideAlertMessages();
});
