<!-- Add Prenatal Record Modal -->
<div id="prenatal-modal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4 pt-8"
     role="dialog" aria-modal="true" onclick="closePrenatalModal(event)">
    
    <div class="modal-content relative w-full max-w-2xl md:max-w-4xl lg:max-w-6xl bg-white rounded-lg sm:rounded-xl shadow-2xl p-4 sm:p-6 my-4 sm:my-8" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-semibold flex items-center" style="color: #1f2937;">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2" fill="currentColor" viewBox="0 0 20 20" style="color: #243b55;">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                <span class="hidden sm:inline" style="color: #1f2937;">Add New Prenatal Record</span>
                <span class="sm:hidden" style="color: #1f2937;">Add Prenatal Record</span>
            </h3>
            <button type="button" onclick="closePrenatalModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('midwife.prenatalrecord.store') }}" method="POST" id="prenatal-form" class="space-y-4 sm:space-y-6" novalidate onsubmit="return validatePrenatalForm(event)">
            @csrf

            <!-- Show server-side validation errors -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="font-medium">Please correct the following errors:</div>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Patient Selection Section -->
            <div class="border-b pb-3 sm:pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Patient Selection</h4>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Select Patient/Mother *</label>
                    <select name="patient_id" id="patient-select" required 
                            class="w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('patient_id') error-border @enderror">
                        <option value="">Choose a patient...</option>
                        @if(isset($patients) && count($patients) > 0)
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">
                                    {{ $patient->formatted_patient_id ?? 'P-' . str_pad($patient->id, 3, '0', STR_PAD_LEFT) }} - {{ $patient->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>No patients available</option>
                        @endif
                    </select>
                    @error('patient_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Don't see the patient? <a href="{{ route('midwife.patients.index') }}" class="text-blue-600 hover:text-blue-800 underline" target="_blank">Register a new patient first</a>
                    </p>
                </div>
            </div>

            <!-- Pregnancy Information Section -->
            <div class="border-b pb-3 sm:pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Pregnancy Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Last Menstrual Period *</label>
                        <input type="date" name="last_menstrual_period" id="lmp-input" required 
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('last_menstrual_period') error-border @enderror"
                               value="{{ old('last_menstrual_period') }}">
                        @error('last_menstrual_period')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Expected Due Date</label>
                        <input type="date" name="expected_due_date" id="edd-input"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                       <!-- <p class="text-xs text-gray-500 mt-1">Auto-calculated if left blank</p>-->
                    </div>
                    <!-- Status field removed - will be calculated on backend -->
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Gravida</label>
                        <select name="gravida" class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            @if(isset($gravida_options))
                                @foreach($gravida_options as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            @else
                                <option value="1">G1</option>
                                <option value="2">G2</option>
                                <option value="3">G3</option>
                                <option value="4">G4+</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Para</label>
                        <select name="para" class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            @if(isset($para_options))
                                @foreach($para_options as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            @else
                                <option value="0">P0</option>
                                <option value="1">P1</option>
                                <option value="2">P2</option>
                                <option value="3">P3+</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <!-- Physical Measurements Section -->
            <div class="border-b pb-3 sm:pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Physical Measurements</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                        <input type="text" name="blood_pressure" placeholder="e.g., 120/80" pattern="[0-9]{2,3}\/[0-9]{2,3}"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" name="weight" step="0.1" min="30" max="200" placeholder="e.g., 65.5" inputmode="decimal"
                               onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                        <input type="number" name="height" min="120" max="200" placeholder="e.g., 165" inputmode="numeric"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Medical Information Section -->
            <div class="space-y-3 sm:space-y-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Medical History *</label>
                    <textarea name="medical_history" id="medical-history" rows="2" sm:rows="3" required
                              placeholder="Any relevant medical history, previous pregnancies, complications, etc."
                              class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p class="text-red-500 text-xs mt-1 hidden" id="medical-history-error">Please enter valid medical history (cannot be N/A or empty)</p>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Additional Notes *</label>
                    <textarea name="notes" id="notes" rows="2" required
                              placeholder="Any additional notes or observations..."
                              class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p class="text-red-500 text-xs mt-1 hidden" id="notes-error">Please enter valid notes (cannot be N/A or empty)</p>
                </div>
                <!-- Note about status calculation -->
                <div class="bg-blue-50 p-3 rounded-lg">
                   <!-- <p class="text-xs sm:text-sm text-blue-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Note:</strong> The record status will be automatically calculated based on the pregnancy information and medical assessments.
                    </p>-->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 sm:pt-6 border-t">
                <button type="button" onclick="closePrenatalModal()" 
                        class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm sm:text-base text-gray-600 border border-gray-300 rounded-md sm:rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm sm:text-base bg-primary text-white rounded-md sm:rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Save Record
            </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for this modal -->
<script>
// Validation function
function validatePrenatalForm(event) {
    const patientSelect = document.getElementById('patient-select');
    const lmpInput = document.getElementById('lmp-input');
    const bloodPressure = document.querySelector('input[name="blood_pressure"]');
    const weight = document.querySelector('input[name="weight"]');
    const height = document.querySelector('input[name="height"]');
    const medicalHistory = document.getElementById('medical-history');
    const notes = document.getElementById('notes');

    let isValid = true;
    let errorMessages = [];

    // Validate patient selection
    if (!patientSelect || !patientSelect.value) {
        if (patientSelect) patientSelect.classList.add('border-red-500');
        isValid = false;
        errorMessages.push('Please select a patient');
    } else {
        patientSelect.classList.remove('border-red-500');
    }

    // Validate LMP
    if (!lmpInput || !lmpInput.value) {
        if (lmpInput) lmpInput.classList.add('border-red-500');
        isValid = false;
        errorMessages.push('Please enter Last Menstrual Period');
    } else {
        lmpInput.classList.remove('border-red-500');
    }

    // Validate Blood Pressure - must be numbers/format like 120/80
    if (bloodPressure && bloodPressure.value.trim()) {
        const bpPattern = /^[0-9]{2,3}\/[0-9]{2,3}$/;
        if (!bpPattern.test(bloodPressure.value.trim())) {
            bloodPressure.classList.add('border-red-500');
            isValid = false;
            errorMessages.push('Blood pressure must be in format: 120/80 (numbers only)');
        } else {
            bloodPressure.classList.remove('border-red-500');
        }
    }

    // Validate Weight - must be a number
    if (weight && weight.value.trim()) {
        const weightValue = weight.value.trim();
        if (isNaN(weightValue) || weightValue === '') {
            weight.classList.add('border-red-500');
            isValid = false;
            errorMessages.push('Weight must be a valid number');
        } else {
            weight.classList.remove('border-red-500');
        }
    }

    // Validate Height - must be a number
    if (height && height.value.trim()) {
        const heightValue = height.value.trim();
        if (isNaN(heightValue) || heightValue === '') {
            height.classList.add('border-red-500');
            isValid = false;
            errorMessages.push('Height must be a valid number');
        } else {
            height.classList.remove('border-red-500');
        }
    }

    // Validate Medical History - cannot be empty, N/A, null, etc.
    if (medicalHistory) {
        const medicalValue = medicalHistory.value.trim().toLowerCase();
        const medicalError = document.getElementById('medical-history-error');

        if (!medicalValue || medicalValue === 'n/a' || medicalValue === 'na' || medicalValue === 'null' || medicalValue === 'none') {
            medicalHistory.classList.add('border-red-500');
            if (medicalError) medicalError.classList.remove('hidden');
            isValid = false;
            errorMessages.push('Please enter valid medical history (cannot be N/A or empty)');
        } else {
            medicalHistory.classList.remove('border-red-500');
            if (medicalError) medicalError.classList.add('hidden');
        }
    }

    // Validate Notes - cannot be empty, N/A, null, etc.
    if (notes) {
        const notesValue = notes.value.trim().toLowerCase();
        const notesError = document.getElementById('notes-error');

        if (!notesValue || notesValue === 'n/a' || notesValue === 'na' || notesValue === 'null' || notesValue === 'none') {
            notes.classList.add('border-red-500');
            if (notesError) notesError.classList.remove('hidden');
            isValid = false;
            errorMessages.push('Please enter valid notes (cannot be N/A or empty)');
        } else {
            notes.classList.remove('border-red-500');
            if (notesError) notesError.classList.add('hidden');
        }
    }

    if (!isValid) {
        event.preventDefault();
        alert('Please correct the following errors:\n\n- ' + errorMessages.join('\n- '));
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate EDD when LMP is selected
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

    // Real-time validation for medical history
    const medicalHistory = document.getElementById('medical-history');
    if (medicalHistory) {
        medicalHistory.addEventListener('input', function() {
            const value = this.value.trim().toLowerCase();
            const error = document.getElementById('medical-history-error');

            if (!value || value === 'n/a' || value === 'na' || value === 'null' || value === 'none') {
                this.classList.add('border-red-500');
                if (error) error.classList.remove('hidden');
            } else {
                this.classList.remove('border-red-500');
                if (error) error.classList.add('hidden');
            }
        });
    }

    // Real-time validation for notes
    const notes = document.getElementById('notes');
    if (notes) {
        notes.addEventListener('input', function() {
            const value = this.value.trim().toLowerCase();
            const error = document.getElementById('notes-error');

            if (!value || value === 'n/a' || value === 'na' || value === 'null' || value === 'none') {
                this.classList.add('border-red-500');
                if (error) error.classList.remove('hidden');
            } else {
                this.classList.remove('border-red-500');
                if (error) error.classList.add('hidden');
            }
        });
    }
});
</script>