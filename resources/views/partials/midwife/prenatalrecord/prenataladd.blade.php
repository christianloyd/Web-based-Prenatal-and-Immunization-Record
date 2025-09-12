<!-- Add Prenatal Record Modal -->
<div id="prenatal-modal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-2 sm:p-4 pt-4 sm:pt-8" 
     role="dialog" aria-modal="true" onclick="closePrenatalModal(event)">
    
    <div class="modal-content relative w-full max-w-xs sm:max-w-md md:max-w-2xl lg:max-w-4xl bg-white rounded-lg sm:rounded-xl shadow-2xl p-3 sm:p-4 md:p-6 my-2 sm:my-4 md:my-8" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                <span class="hidden sm:inline">Add New Prenatal Record</span>
                <span class="sm:hidden">Add Prenatal Record</span>
            </h3>
            <button type="button" onclick="closePrenatalModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('midwife.prenatalrecord.store') }}" method="POST" id="prenatal-form" class="space-y-4 sm:space-y-6">
            @csrf

            <!-- Patient Selection Section -->
            <div class="border-b pb-3 sm:pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Patient Selection</h4>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Select Patient/Mother *</label>
                    <select name="patient_id" id="patient-select" required 
                            class="w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Choose a patient...</option>
                        @if(isset($patients) && count($patients) > 0)
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">
                                    {{ $patient->name }}
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>No patients available</option>
                        @endif
                    </select>
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
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Expected Due Date</label>
                        <input type="date" name="expected_due_date" id="edd-input"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Auto-calculated if left blank</p>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="normal">Normal</option>
                            <option value="monitor">Monitor</option>
                            <option value="high-risk">High Risk</option>
                            <option value="due">Appointment Due</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
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
                        <input type="text" name="blood_pressure" placeholder="e.g., 120/80"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" name="weight" step="0.1" min="30" max="200" placeholder="e.g., 65.5"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                        <input type="number" name="height" min="120" max="200" placeholder="e.g., 165"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Medical Information Section -->
            <div class="space-y-3 sm:space-y-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Medical History</label>
                    <textarea name="medical_history" rows="2" sm:rows="3"
                              placeholder="Any relevant medical history, previous pregnancies, complications, etc."
                              class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                    <textarea name="notes" rows="2" 
                              placeholder="Any additional notes or observations..."
                              class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 sm:pt-6 border-t">
                <button type="button" onclick="closePrenatalModal()" 
                        class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm sm:text-base text-gray-600 border border-gray-300 rounded-md sm:rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm sm:text-base bg-blue-600 text-white rounded-md sm:rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Save Record
            </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for this modal -->
<script>
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
    
    // Form validation
    const prenatalForm = document.getElementById('prenatal-form');
    if (prenatalForm) {
        prenatalForm.addEventListener('submit', function(e) {
            const patientSelect = document.getElementById('patient-select');
            const lmpInput = document.getElementById('lmp-input');
            
            let isValid = true;
            
            // Validate patient selection
            if (!patientSelect.value) {
                patientSelect.classList.add('border-red-500');
                isValid = false;
            } else {
                patientSelect.classList.remove('border-red-500');
            }
            
            // Validate LMP
            if (!lmpInput.value) {
                lmpInput.classList.add('border-red-500');
                isValid = false;
            } else {
                lmpInput.classList.remove('border-red-500');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields (Patient and Last Menstrual Period).');
            }
        });
    }
});
</script>