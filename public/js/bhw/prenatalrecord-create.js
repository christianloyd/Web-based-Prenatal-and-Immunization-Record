/**
 * Prenatal Record Creation - JavaScript Module
 *
 * This module handles all client-side functionality for creating prenatal records,
 * including patient search, selection, date calculations, and form validation.
 *
 * @module prenatalrecord-create
 * @requires AJAX/Fetch API
 * @requires DOM API
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ============================================================================
    // DOM ELEMENT REFERENCES
    // ============================================================================

    const searchInput = document.getElementById('patient-search');
    const searchDropdown = document.getElementById('search-dropdown');
    const selectedPatientId = document.getElementById('selected-patient-id');
    const selectedPatientDisplay = document.getElementById('selected-patient-display');
    const selectedPatientName = document.getElementById('selected-patient-name');
    const selectedPatientDetails = document.getElementById('selected-patient-details');
    const clearSearchBtn = document.getElementById('clear-search');
    const removeSelectionBtn = document.getElementById('remove-selection');
    const searchLoading = document.getElementById('search-loading');
    const lmpInput = document.getElementById('lmp-input');
    const eddInput = document.getElementById('edd-input');
    const prenatalForm = document.getElementById('prenatal-form');

    // ============================================================================
    // STATE VARIABLES
    // ============================================================================

    let searchTimeout;
    let patients = [];

    // ============================================================================
    // INITIALIZATION
    // ============================================================================

    /**
     * Initialize the patient search functionality by fetching available patients
     */
    function init() {
        fetchPatients();
        setupEventListeners();
        setupDateValidation();
    }

    // ============================================================================
    // PATIENT SEARCH & SELECTION
    // ============================================================================

    /**
     * Fetches patients without active prenatal records from the server
     * Uses AJAX to load patient data on page load
     *
     * @async
     * @function fetchPatients
     * @returns {Promise<void>}
     *
     * NOTE: This function contains a Blade directive for the route URL:
     * {{ route("bhw.patients.search") }}
     * This needs to be replaced with the actual URL when used outside Blade templates
     */
    function fetchPatients() {
        // BLADE DIRECTIVE: {{ route("bhw.patients.search") }}
        // Replace with actual URL: '/bhw/patients/search' or appropriate route
        const searchUrl = PRENATAL_CONFIG.searchUrl || '/bhw/patients/search';

        fetch(searchUrl + '?without_prenatal=true', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Handle Laravel Resource Collection structure
                patients = data.data || data; // Laravel resources wrap data in 'data' property
                console.log('Loaded patients without active prenatal records:', patients.length);
            })
            .catch(error => {
                console.error('Error fetching patients:', error);
            });
    }

    /**
     * Searches through the loaded patient data based on query string
     * Filters by name, ID, and contact information
     *
     * @param {string} query - The search query string
     */
    function searchPatients(query) {
        showLoading();

        const filteredPatients = patients.filter(patient => {
            const name = patient.name ? patient.name.toLowerCase() : '';
            const firstName = patient.first_name ? patient.first_name.toLowerCase() : '';
            const lastName = patient.last_name ? patient.last_name.toLowerCase() : '';
            const fullName = (firstName + ' ' + lastName).trim().toLowerCase();
            const id = patient.formatted_patient_id ? patient.formatted_patient_id.toLowerCase() : '';
            const contact = patient.contact ? patient.contact.toLowerCase() : '';

            return name.includes(query) ||
                   firstName.includes(query) ||
                   lastName.includes(query) ||
                   fullName.includes(query) ||
                   id.includes(query) ||
                   contact.includes(query);
        });

        displaySearchResults(filteredPatients);
        hideLoading();
    }

    /**
     * Displays search results in the dropdown menu
     *
     * @param {Array<Object>} results - Array of patient objects matching the search
     */
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

    /**
     * Handles patient selection from the dropdown
     * Updates the form with selected patient data and displays patient info
     *
     * @param {Object} patient - The selected patient object
     * @param {number} patient.id - Patient ID
     * @param {string} patient.name - Patient full name
     * @param {string} patient.first_name - Patient first name
     * @param {string} patient.last_name - Patient last name
     * @param {string} patient.formatted_patient_id - Formatted patient ID
     * @param {number|string} patient.age - Patient age
     * @param {string} patient.contact - Patient contact number
     */
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

    /**
     * Clears the current patient selection
     * Resets all related form fields and UI elements
     */
    function clearSelection() {
        selectedPatientId.value = '';
        searchInput.value = '';
        selectedPatientDisplay.classList.add('hidden');
        hideDropdown();
        hideClearButton();
        searchInput.focus();
    }

    // ============================================================================
    // DROPDOWN UI MANAGEMENT
    // ============================================================================

    /**
     * Shows the search dropdown menu
     */
    function showDropdown() {
        searchDropdown.classList.add('show');
    }

    /**
     * Hides the search dropdown menu
     */
    function hideDropdown() {
        searchDropdown.classList.remove('show');
    }

    /**
     * Shows the loading spinner indicator
     */
    function showLoading() {
        searchLoading.classList.remove('hidden');
    }

    /**
     * Hides the loading spinner indicator
     */
    function hideLoading() {
        searchLoading.classList.add('hidden');
    }

    /**
     * Shows the clear search button
     */
    function showClearButton() {
        clearSearchBtn.classList.remove('hidden');
    }

    /**
     * Hides the clear search button
     */
    function hideClearButton() {
        clearSearchBtn.classList.add('hidden');
    }

    // ============================================================================
    // DATE CALCULATIONS & VALIDATION
    // ============================================================================

    /**
     * Sets up date validation for the Last Menstrual Period (LMP) input
     * Limits LMP to today or earlier dates
     */
    function setupDateValidation() {
        if (lmpInput && eddInput) {
            // Set max date for LMP to today
            const today = new Date().toISOString().split('T')[0];
            lmpInput.setAttribute('max', today);
        }
    }

    /**
     * Calculates Expected Due Date (EDD) based on Last Menstrual Period (LMP)
     * Uses Naegele's rule: LMP + 280 days (40 weeks)
     *
     * @param {string} lmpValue - The LMP date value in ISO format (YYYY-MM-DD)
     * @returns {string} The calculated EDD in ISO format (YYYY-MM-DD)
     */
    function calculateEDD(lmpValue) {
        if (!lmpValue) return '';

        const lmp = new Date(lmpValue);
        const edd = new Date(lmp);
        edd.setDate(edd.getDate() + 280); // Add 280 days (40 weeks)

        return edd.toISOString().split('T')[0];
    }

    // ============================================================================
    // FORM VALIDATION
    // ============================================================================

    /**
     * Validates the prenatal form before submission
     * Checks for patient selection and required fields
     *
     * @param {Event} e - The form submit event
     * @returns {boolean} Whether the form is valid
     */
    function validateForm(e) {
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

        return isValid;
    }

    /**
     * Removes error styling from the search input
     */
    function clearSearchError() {
        searchInput.classList.remove('error');
        const errorMsg = searchInput.parentNode.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }

    /**
     * Removes error styling from the LMP input
     */
    function clearLmpError() {
        lmpInput.classList.remove('error');
    }

    // ============================================================================
    // EVENT LISTENERS SETUP
    // ============================================================================

    /**
     * Sets up all event listeners for the form
     */
    function setupEventListeners() {
        // Search input handler with debouncing
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

        // Clear selection buttons
        clearSearchBtn.addEventListener('click', clearSelection);
        removeSelectionBtn.addEventListener('click', clearSelection);

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                hideDropdown();
            }
        });

        // LMP change handler - auto-calculate EDD
        if (lmpInput && eddInput) {
            lmpInput.addEventListener('change', function() {
                if (this.value && !eddInput.value) {
                    eddInput.value = calculateEDD(this.value);
                }
            });
        }

        // Form submission validation
        if (prenatalForm) {
            prenatalForm.addEventListener('submit', validateForm);
        }

        // Clear error styling on input
        searchInput.addEventListener('input', clearSearchError);

        if (lmpInput) {
            lmpInput.addEventListener('input', clearLmpError);
        }
    }

    // ============================================================================
    // RESTORE SELECTED PATIENT (AFTER VALIDATION ERROR)
    // ============================================================================

    /**
     * Restores the previously selected patient after a validation error
     * This function needs to be called with the old patient ID from the Blade template
     *
     * @param {string|number} oldPatientId - The previously selected patient ID
     *
     * NOTE: This function is called by Blade directive:
     * @if(old('patient_id'))
     *     restoreSelectedPatient('{{ old('patient_id') }}');
     * @endif
     */
    function restoreSelectedPatient(oldPatientId) {
        if (oldPatientId && patients.length > 0) {
            const patient = patients.find(p => p.id == oldPatientId);
            if (patient) {
                selectPatient(patient);
            }
        }
    }

    // Make restoreSelectedPatient available globally for Blade to call
    window.restoreSelectedPatient = restoreSelectedPatient;

    // ============================================================================
    // INITIALIZE APPLICATION
    // ============================================================================

    init();
});
