/**
 * Midwife Prenatal Record Create Page JavaScript
 * Handles patient search and prenatal record creation
 *
 * Configuration:
 * This script expects window.PRENATAL_CREATE_CONFIG with the following properties:
 * - searchUrl: API endpoint for searching patients
 * - oldPatientId: (optional) Patient ID to restore after validation error
 */

/* ============================================
   PATIENT SEARCH FUNCTIONALITY
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    // Patient search functionality
    const searchInput = document.getElementById('patient-search');
    const searchDropdown = document.getElementById('search-dropdown');
    const selectedPatientId = document.getElementById('selected-patient-id');
    const selectedPatientDisplay = document.getElementById('selected-patient-display');
    const selectedPatientName = document.getElementById('selected-patient-name');
    const selectedPatientDetails = document.getElementById('selected-patient-details');
    const clearSearchBtn = document.getElementById('clear-search');
    const removeSelectionBtn = document.getElementById('remove-selection');
    const searchLoading = document.getElementById('search-loading');

    let searchTimeout;
    let patients = [];

    // Fetch all patients on page load
    fetchPatients();

    function fetchPatients() {
        // Use configuration object to get the search URL
        const searchUrl = window.PRENATAL_CREATE_CONFIG && window.PRENATAL_CREATE_CONFIG.searchUrl
            ? window.PRENATAL_CREATE_CONFIG.searchUrl
            : '/api/midwife/patients/search';

        fetch(searchUrl)
            .then(response => response.json())
            .then(data => {
                // Handle Laravel Resource Collection structure
                patients = data.data || data; // Laravel resources wrap data in 'data' property
                console.log('Loaded patients:', patients.length);
            })
            .catch(error => {
                console.error('Error fetching patients:', error);
            });
    }

    // Search input handler
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            hideDropdown();
            return;
        }

        searchTimeout = setTimeout(() => {
            searchPatients(query);
        }, 300);
    });

    function searchPatients(query) {
        showLoading();

        const filteredPatients = patients.filter(patient => {
            const name = patient.name ? patient.name.toLowerCase() : '';
            const firstName = patient.first_name ? patient.first_name.toLowerCase() : '';
            const lastName = patient.last_name ? patient.last_name.toLowerCase() : '';
            const fullName = (firstName + ' ' + lastName).trim().toLowerCase();
            const id = patient.formatted_patient_id ? patient.formatted_patient_id.toLowerCase() : '';
            const contact = patient.contact ? patient.contact.toLowerCase() : '';

            return name.includes(query) || firstName.includes(query) || lastName.includes(query) || fullName.includes(query) || id.includes(query) || contact.includes(query);
        });

        displaySearchResults(filteredPatients);
        hideLoading();
    }

    function displaySearchResults(results) {
        searchDropdown.innerHTML = '';

        if (results.length === 0) {
            searchDropdown.innerHTML = '<div class="search-option" style="color: #374151;">No patients found</div>';
        } else {
            results.forEach(patient => {
                const option = document.createElement('div');
                option.className = 'search-option';
                option.innerHTML = `
                    <div class="patient-info">
                        <div class="patient-name">${patient.name || (patient.first_name + ' ' + patient.last_name)}</div>
                        <div class="patient-details">
                            ${patient.formatted_patient_id || 'P-' + String(patient.id).padStart(3, '0')} •
                            Age: ${patient.age || 'N/A'} •
                            Contact: ${patient.contact || 'N/A'}
                        </div>
                    </div>
                `;

                option.addEventListener('click', () => selectPatient(patient));
                searchDropdown.appendChild(option);
            });
        }

        showDropdown();
    }

    function selectPatient(patient) {
        selectedPatientId.value = patient.id;
        selectedPatientName.textContent = patient.name || (patient.first_name + ' ' + patient.last_name);
        selectedPatientDetails.textContent = `${patient.formatted_patient_id || 'P-' + String(patient.id).padStart(3, '0')} • Age: ${patient.age || 'N/A'} • Contact: ${patient.contact || 'N/A'}`;

        searchInput.value = patient.name || (patient.first_name + ' ' + patient.last_name);
        selectedPatientDisplay.classList.remove('hidden');
        hideDropdown();
        showClearButton();

        // Remove error styling if present
        searchInput.classList.remove('error');
    }

    function clearSelection() {
        selectedPatientId.value = '';
        searchInput.value = '';
        selectedPatientDisplay.classList.add('hidden');
        hideDropdown();
        hideClearButton();
        searchInput.focus();
    }

    function showDropdown() {
        searchDropdown.classList.add('show');
    }

    function hideDropdown() {
        searchDropdown.classList.remove('show');
    }

    function showLoading() {
        searchLoading.classList.remove('hidden');
    }

    function hideLoading() {
        searchLoading.classList.add('hidden');
    }

    function showClearButton() {
        clearSearchBtn.classList.remove('hidden');
    }

    function hideClearButton() {
        clearSearchBtn.classList.add('hidden');
    }

    // Event listeners
    clearSearchBtn.addEventListener('click', clearSelection);
    removeSelectionBtn.addEventListener('click', clearSelection);

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            hideDropdown();
        }
    });

    /* ============================================
       DATE VALIDATION AND AUTO-CALCULATION
       ============================================ */

    // LMP and EDD date calculation
    const lmpInput = document.getElementById('lmp-input');
    const eddInput = document.getElementById('edd-input');

    if (lmpInput && eddInput) {
        // Set max date for LMP to today
        const today = new Date().toISOString().split('T')[0];
        lmpInput.setAttribute('max', today);

        lmpInput.addEventListener('change', function() {
            if (this.value && !eddInput.value) {
                const lmp = new Date(this.value);
                const edd = new Date(lmp);
                edd.setDate(edd.getDate() + 280); // Add 280 days (40 weeks)
                eddInput.value = edd.toISOString().split('T')[0];
            }
        });
    }

    /* ============================================
       FORM HANDLING
       ============================================ */

    // Form validation
    const prenatalForm = document.getElementById('prenatal-form');
    if (prenatalForm) {
        prenatalForm.addEventListener('submit', function(e) {
            let isValid = true;

            // Validate patient selection
            if (!selectedPatientId.value) {
                searchInput.classList.add('error');
                isValid = false;

                // Show error message
                let errorMsg = searchInput.parentNode.querySelector('.error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'text-red-500 text-sm mt-1 error-message';
                    errorMsg.textContent = 'Please select a patient';
                    searchInput.parentNode.appendChild(errorMsg);
                }
            }

            // Validate LMP
            if (!lmpInput.value) {
                lmpInput.classList.add('error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();

                // Scroll to first error
                const firstError = document.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }

                // Show alert
                alert('Please fill in all required fields.');
            }
        });
    }

    // Clear error styling on input
    searchInput.addEventListener('input', function() {
        this.classList.remove('error');
        const errorMsg = this.parentNode.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    });

    lmpInput.addEventListener('input', function() {
        this.classList.remove('error');
    });

    /* ============================================
       INITIALIZATION
       ============================================ */

    // Restore selected patient if there's an old value (after validation error)
    // This will be handled by the Blade template passing oldPatientId via window.PRENATAL_CREATE_CONFIG
    if (window.PRENATAL_CREATE_CONFIG && window.PRENATAL_CREATE_CONFIG.oldPatientId) {
        const oldPatientId = window.PRENATAL_CREATE_CONFIG.oldPatientId;
        if (oldPatientId && patients.length > 0) {
            const patient = patients.find(p => p.id == oldPatientId);
            if (patient) {
                selectPatient(patient);
            }
        }
    }
});
