/**
 * Midwife Patients Index Page JavaScript
 * Handles patient modals, form validation, and AJAX submissions
 */

/* ============================================
   PATIENT MODAL MANAGEMENT
   ============================================ */

/**
 * Opens the Add Patient modal
 */
function openPatientModal() {
    const modal = document.getElementById('patient-modal');
    if (!modal) return console.error('Patient modal not found');

    // Clear any previous error messages
    const errorElements = modal.querySelectorAll('.text-red-500');
    errorElements.forEach(el => el.remove());

    // Remove error border classes
    const errorInputs = modal.querySelectorAll('.error-border');
    errorInputs.forEach(input => input.classList.remove('error-border'));

    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        const firstNameInput = modal.querySelector('input[name="first_name"]');
        if (firstNameInput) firstNameInput.focus();
    }, 300);
}

/**
 * Closes the Add Patient modal
 */
function closePatientModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('patient-modal');
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
   VIEW PATIENT MODAL
   ============================================ */

// Global variable to store current patient data
let currentPatientData = null;

/**
 * Opens the View Patient modal with patient details
 * @param {Object} patient - Patient data object
 */
function openViewPatientModal(patient) {
    if (!patient) return console.error('No patient data provided');

    // Store patient data globally for other functions
    currentPatientData = patient;

    // Populate modal fields
    document.getElementById('viewPatientName').textContent = patient.name || (patient.first_name + ' ' + patient.last_name) || 'N/A';
    document.getElementById('viewPatientId').textContent = patient.formatted_patient_id || 'N/A';
    document.getElementById('viewPatientAge').textContent = patient.age ? patient.age + ' years' : 'N/A';
    document.getElementById('viewPatientContact').textContent = patient.contact || 'N/A';
    document.getElementById('viewPatientEmergencyContact').textContent = patient.emergency_contact || 'N/A';
    document.getElementById('viewPatientAddress').textContent = patient.address || 'N/A';
    document.getElementById('viewPatientOccupation').textContent = patient.occupation || 'N/A';

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

    // Update prenatal records link (uses configuration from page)
    const prenatalLink = document.getElementById('viewPrenatalRecordsLink');
    if (prenatalLink && window.MIDWIFE_PATIENTS_CONFIG && window.MIDWIFE_PATIENTS_CONFIG.prenatalRecordUrl) {
        const baseUrl = window.MIDWIFE_PATIENTS_CONFIG.prenatalRecordUrl;
        prenatalLink.href = `${baseUrl}?search=${encodeURIComponent(patient.name || (patient.first_name + ' ' + patient.last_name) || patient.formatted_patient_id)}`;
    }

    // Show modal
    const modal = document.getElementById('view-patient-modal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}

/**
 * Closes the View Patient modal
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
 * Closes view modal and opens edit modal
 */
function closeViewPatientModalAndEdit() {
    if (!currentPatientData) return;
    closeViewPatientModal();
    // Wait for the view modal to close before opening edit modal
    setTimeout(() => {
        openEditPatientModal(currentPatientData);
    }, 350);
}

/* ============================================
   EDIT PATIENT MODAL
   ============================================ */

/**
 * Opens the Edit Patient modal with patient data
 * @param {Object} patient - Patient data object
 */
function openEditPatientModal(patient) {
    if (!patient) return console.error('No patient data provided');

    const modal = document.getElementById('edit-patient-modal');
    const form = document.getElementById('edit-patient-form');
    if (!modal || !form) return console.error('Edit modal elements not found');

    // Set form action
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

    setTimeout(() => {
        const firstInput = document.getElementById('edit-name');
        if (firstInput) firstInput.focus();
    }, 100);
}

/**
 * Closes the Edit Patient modal
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

/* ============================================
   KEYBOARD SHORTCUTS
   ============================================ */

// Close modals on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closePatientModal();
        closeViewPatientModal();
        closeEditPatientModal();
    }
});

/* ============================================
   FORM VALIDATION & AJAX SUBMISSIONS
   ============================================ */

// Form validation and AJAX submission with SweetAlert
document.addEventListener('DOMContentLoaded', function() {
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
            const submitBtn = this.querySelector('button[type="submit"]');
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
                console.log('ADD Patient - Server response:', data); // Debug log
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
                    let errorHtml = '';

                    // If there are validation errors, show them as HTML list
                    if (data.errors && Object.keys(data.errors).length > 0) {
                        const errorList = Object.values(data.errors).flat();
                        errorHtml = '<div class="text-left mt-3">';
                        errorHtml += '<ul class="list-disc list-inside text-sm">';
                        errorList.forEach(error => {
                            errorHtml += `<li>${error}</li>`;
                        });
                        errorHtml += '</ul></div>';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Failed',
                        html: errorMessage + errorHtml,
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

    // Handle edit form if it exists
    const editPatientForm = document.getElementById('edit-patient-form');
    if (editPatientForm) {
        editPatientForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const firstNameInput = this.querySelector('input[name="first_name"]');
            const lastNameInput = this.querySelector('input[name="last_name"]');
            const ageInput = this.querySelector('input[name="age"]');

            // Client-side validation
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
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';

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
                    closeEditPatientModal();

                    // Then show success SweetAlert after a short delay
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Patient updated successfully!',
                            confirmButtonColor: '#D4A373',
                            confirmButtonText: 'Great!'
                        }).then((result) => {
                            // Reload page after user clicks Great!
                            window.location.reload();
                        });
                    }, 400); // Wait for modal close animation
                } else {
                    // Error SweetAlert
                    let errorMessage = data.message || 'An error occurred while updating the patient.';
                    let errorHtml = '';

                    // If there are validation errors, show them as HTML list
                    if (data.errors && Object.keys(data.errors).length > 0) {
                        const errorList = Object.values(data.errors).flat();
                        errorHtml = '<div class="text-left mt-3">';
                        errorHtml += '<ul class="list-disc list-inside text-sm">';
                        errorList.forEach(error => {
                            errorHtml += `<li>${error}</li>`;
                        });
                        errorHtml += '</ul></div>';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        html: errorMessage + errorHtml,
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
});

/* ============================================
   SEARCH FORM ENHANCEMENT
   ============================================ */

// Search form enhancement
document.addEventListener('DOMContentLoaded', function() {
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
});
