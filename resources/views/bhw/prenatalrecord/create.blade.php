@extends('layout.bhw')
@section('title', 'Add Prenatal Record')
@section('page-title', 'Add Prenatal Record')
@section('page-subtitle', 'Create a new prenatal record for a patient')

@push('styles')
<style>
    :root {
        --primary: #243b55;
        --secondary: #141e30;
    }

    /* Form styling */
    .form-section {
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 0.5rem;
        color: var(--primary);
    }

    .form-input {
        transition: all 0.2s ease;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.625rem;
        width: 100%;
        font-size: 0.875rem;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(36, 59, 85, 0.1);
    }

    .form-input.error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    /* Patient search styles */
    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .search-dropdown.show {
        display: block;
    }

    .search-option {
        padding: 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.15s ease;
    }

    .search-option:hover {
        background-color: #f9fafb;
    }

    .search-option:last-child {
        border-bottom: none;
    }

    .search-option.selected {
        background-color: #eff6ff;
        color: var(--primary);
    }

    .patient-info {
        display: flex;
        flex-direction: column;
    }

    .patient-name {
        font-weight: 500;
        color: #000000; /* Changed to pure black for better visibility */
    }

    .patient-details {
        font-size: 0.75rem;
        color: #374151; /* Changed to darker gray for better visibility */
        margin-top: 0.25rem;
    }

    /* Button styles */
    .btn-primary {
        background-color: var(--primary);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary:hover {
        background-color: #1e2f3f;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(36, 59, 85, 0.3);
    }

    .btn-secondary {
        background-color: #f9fafb;
        color: #374151;
        border: 1px solid #d1d5db;
        padding: 0.75rem 1.5rem;
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .btn-secondary:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }

    /* Loading state */
    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Selected patient display */
    .selected-patient {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 0.5rem;
    }

    .selected-patient .patient-name {
        color: var(--primary);
        font-weight: 600;
    }

    /* Responsive grid */
    @media (min-width: 768px) {
        .form-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .form-grid-3 { grid-template-columns: repeat(3, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <a href="{{ route('bhw.prenatalrecord.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Add New Prenatal Record</h1>
                <p class="text-gray-600">Create a comprehensive prenatal record for a patient</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('bhw.prenatalrecord.store') }}" method="POST" id="prenatal-form" class="space-y-6">
        @csrf

        <!-- Patient Selection Section -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Patient Selection
            </h3>

            <div class="space-y-4">
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search and Select Patient/Mother *
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="patient-search"
                               placeholder="Type patient name or ID to search..."
                               class="form-input pl-10 pr-10 @error('patient_id') error @enderror"
                               autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <div id="search-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                            </div>
                            <button type="button" id="clear-search" class="hidden text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Search Dropdown -->
                        <div id="search-dropdown" class="search-dropdown">
                            <!-- Results will be populated here -->
                        </div>
                    </div>

                    <!-- Hidden input for selected patient ID -->
                    <input type="hidden" name="patient_id" id="selected-patient-id" value="{{ old('patient_id') }}">

                    @error('patient_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Selected Patient Display -->
                    <div id="selected-patient-display" class="selected-patient hidden">
                        <div class="flex justify-between items-start">
                            <div class="patient-info">
                                <div class="patient-name" id="selected-patient-name"></div>
                                <div class="patient-details" id="selected-patient-details"></div>
                            </div>
                            <button type="button" id="remove-selection" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mt-2">
                        Don't see the patient?
                        <a href="{{ route('bhw.patients.index') }}" class="text-blue-600 hover:text-blue-800 underline" target="_blank">
                            Register a new patient first
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pregnancy Information & Physical Measurements Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pregnancy Information Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-baby"></i>
                    Pregnancy Information
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Menstrual Period *</label>
                        <input type="date"
                               name="last_menstrual_period"
                               id="lmp-input"
                               required
                               class="form-input @error('last_menstrual_period') error @enderror"
                               value="{{ old('last_menstrual_period') }}">
                        @error('last_menstrual_period')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected Due Date</label>
                        <input type="date"
                               name="expected_due_date"
                               id="edd-input"
                               class="form-input @error('expected_due_date') error @enderror"
                               value="{{ old('expected_due_date') }}">
                        <p class="text-xs text-gray-500 mt-1">Auto-calculated from LMP</p>
                        @error('expected_due_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gravida</label>
                            <select name="gravida" class="form-input @error('gravida') error @enderror">
                                <option value="">Select</option>
                                <option value="1" {{ old('gravida') == '1' ? 'selected' : '' }}>G1</option>
                                <option value="2" {{ old('gravida') == '2' ? 'selected' : '' }}>G2</option>
                                <option value="3" {{ old('gravida') == '3' ? 'selected' : '' }}>G3</option>
                                <option value="4" {{ old('gravida') == '4' ? 'selected' : '' }}>G4+</option>
                            </select>
                            @error('gravida')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Para</label>
                            <select name="para" class="form-input @error('para') error @enderror">
                                <option value="">Select</option>
                                <option value="0" {{ old('para') == '0' ? 'selected' : '' }}>P0</option>
                                <option value="1" {{ old('para') == '1' ? 'selected' : '' }}>P1</option>
                                <option value="2" {{ old('para') == '2' ? 'selected' : '' }}>P2</option>
                                <option value="3" {{ old('para') == '3' ? 'selected' : '' }}>P3+</option>
                            </select>
                            @error('para')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Physical Measurements Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-weight"></i>
                    Physical Measurements
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure</label>
                        <input type="text"
                               name="blood_pressure"
                               placeholder="e.g., 120/80"
                               class="form-input @error('blood_pressure') error @enderror"
                               value="{{ old('blood_pressure') }}">
                        @error('blood_pressure')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                        <input type="number"
                               name="weight"
                               step="0.1"
                               min="30"
                               max="200"
                               placeholder="e.g., 65.5"
                               class="form-input @error('weight') error @enderror"
                               value="{{ old('weight') }}">
                        @error('weight')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                        <input type="number"
                               name="height"
                               min="120"
                               max="200"
                               placeholder="e.g., 165"
                               class="form-input @error('height') error @enderror"
                               value="{{ old('height') }}">
                        @error('height')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information Section -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-notes-medical"></i>
                Medical Information
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Medical History</label>
                    <textarea name="medical_history"
                              rows="4"
                              placeholder="Any relevant medical history, previous pregnancies, complications, etc."
                              class="form-input resize-none @error('medical_history') error @enderror">{{ old('medical_history') }}</textarea>
                    @error('medical_history')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <textarea name="notes"
                              rows="3"
                              placeholder="Any additional notes or observations..."
                              class="form-input resize-none @error('notes') error @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t bg-white rounded-lg p-6">
            <a href="{{ route('bhw.prenatalrecord.index') }}" class="btn-secondary">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i>
                Save Prenatal Record
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
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
        fetch('{{ route("bhw.patients.search") }}')
            .then(response => response.json())
            .then(data => {
                patients = data;
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

    // Restore selected patient if there's an old value (after validation error)
    @if(old('patient_id'))
        const oldPatientId = '{{ old('patient_id') }}';
        if (oldPatientId && patients.length > 0) {
            const patient = patients.find(p => p.id == oldPatientId);
            if (patient) {
                selectPatient(patient);
            }
        }
    @endif
});
</script>
@endpush