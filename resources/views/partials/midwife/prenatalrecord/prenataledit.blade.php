<!-- Edit Prenatal Record Modal -->
<div id="edit-prenatal-modal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4 pt-8"
     role="dialog" aria-modal="true" onclick="closeEditPrenatalModal(event)">
    
    <div class="modal-content relative w-full max-w-2xl md:max-w-4xl lg:max-w-6xl bg-white rounded-lg sm:rounded-xl shadow-2xl p-4 sm:p-6 my-4 sm:my-8" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-edit w-5 h-5 sm:w-6 sm:h-6 text-primary mr-2"></i>
                <span class="hidden sm:inline">Edit Prenatal Record</span>
                <span class="sm:hidden">Edit Record</span>
            </h3>
            <button type="button" onclick="closeEditPrenatalModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times w-5 h-5 sm:w-6 sm:h-6"></i>
            </button>
        </div>

        <!-- Form -->
        <form action="" method="POST" id="edit-prenatal-form" 
              data-update-url="{{ route('midwife.prenatalrecord.update', ':id') }}" class="space-y-4 sm:space-y-6" novalidate>
            @csrf
            @method('PUT')

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

            <!-- Patient Information Section (Read-only) -->
            <div class="border-b pb-3 sm:pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Patient Information</h4>
                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                            <div id="edit-patient-name-display" class="text-xs sm:text-sm font-medium text-gray-900 py-2">
                                Loading...
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Patient ID</label>
                            <div id="edit-patient-id-display" class="text-xs sm:text-sm text-gray-700 py-2">
                                Loading...
                            </div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Age</label>
                            <div id="edit-patient-age-display" class="text-xs sm:text-sm text-gray-700 py-2">
                                Loading...
                            </div>
                        </div>
                    </div>
                    <!--<p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle w-3 h-3 sm:w-4 sm:h-4 inline mr-1"></i>
                        Patient information cannot be changed. To assign this record to a different patient, please create a new record.
                    </p>-->
                </div>
                <!-- Hidden input to maintain patient_id -->
                <input type="hidden" name="patient_id" id="edit-patient-id-hidden" value="">
            </div>

            <!-- Pregnancy Information Section -->
            <div class="border-b pb-3 sm:pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Pregnancy Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Last Menstrual Period *</label>
                        <input type="date" name="last_menstrual_period" id="edit-lmp" required 
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Expected Due Date</label>
                        <input type="date" name="expected_due_date" id="edit-due-date" 
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Auto-calculated if left blank</p>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="edit-status" 
                                class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="normal">Normal</option>
                            <option value="monitor">Monitor</option>
                            <option value="high-risk">High Risk</option>
                            <option value="due">Appointment Due</option>
                            <option value="completed">Completed</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Status is auto-calculated by system. Use 'Completed' to mark pregnancy as finished.
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Gravida</label>
                        <select name="gravida" id="edit-gravida" 
                                class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            <option value="1">G1</option>
                            <option value="2">G2</option>
                            <option value="3">G3</option>
                            <option value="4">G4+</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Para</label>
                        <select name="para" id="edit-para" 
                                class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select</option>
                            <option value="0">P0</option>
                            <option value="1">P1</option>
                            <option value="2">P2</option>
                            <option value="3">P3+</option>
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
                        <input type="text" name="blood_pressure" id="edit-blood-pressure" placeholder="e.g., 120/80"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" name="weight" id="edit-weight" step="0.1" min="30" max="200" placeholder="e.g., 65.5"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                        <input type="number" name="height" id="edit-height" min="120" max="200" placeholder="e.g., 165"
                               class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Medical Information Section -->
            <div class="space-y-3 sm:space-y-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Medical History</label>
                    <textarea name="medical_history" id="edit-medical-history" rows="2" sm:rows="3"
                              placeholder="Any relevant medical history, previous pregnancies, complications, etc."
                              class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                    <textarea name="notes" id="edit-notes" rows="2" 
                              placeholder="Any additional notes or observations..."
                              class="form-input w-full border border-gray-300 rounded-md sm:rounded-lg p-2 sm:p-2.5 text-sm sm:text-base resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <!-- Note about status functionality 
                <div class="bg-yellow-50 border border-yellow-200 p-3 rounded-lg">
                    <p class="text-xs sm:text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Status Information:</strong> The system automatically calculates the appropriate status based on pregnancy progression and medical factors. You can manually override the status if needed, particularly to mark a pregnancy as "Completed" when the patient has delivered.
                    </p>
                </div>-->
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-3 border-t">
                <button type="button" onclick="closeEditPrenatalModal()" 
                        class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm sm:text-base text-gray-600 border border-gray-300 rounded-md sm:rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 sm:px-6 py-2 sm:py-2.5 text-sm sm:text-base bg-blue-600 text-white rounded-md sm:rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Update Record
            </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript for this modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-calculate EDD when LMP is selected (for edit modal)
    const editLmpInput = document.getElementById('edit-lmp');
    const editEddInput = document.getElementById('edit-due-date');
    
    if (editLmpInput && editEddInput) {
        // Set max date for LMP to today (prevent future dates)
        const today = new Date();
        const todayString = today.toISOString().split('T')[0];
        editLmpInput.setAttribute('max', todayString);
        
        // Function to validate and handle date selection
        function validateAndCalculateEDD() {
            if (editLmpInput.value) {
                const selectedDate = new Date(editLmpInput.value + 'T00:00:00');
                const currentDate = new Date();
                currentDate.setHours(0, 0, 0, 0); // Reset time for accurate comparison
                
                // Check if selected date is in the future
                if (selectedDate > currentDate) {
                    editLmpInput.value = '';
                    editLmpInput.classList.add('border-red-500');
                    alert('Last Menstrual Period cannot be a future date. Please select a valid date.');
                    return false;
                } else {
                    editLmpInput.classList.remove('border-red-500');
                    
                    // Auto-calculate EDD
                    const lmp = new Date(editLmpInput.value + 'T00:00:00');
                    const edd = new Date(lmp);
                    edd.setDate(edd.getDate() + 280); // Add 280 days (40 weeks)
                    editEddInput.value = edd.toISOString().split('T')[0];
                    
                    // Visual feedback that EDD was auto-calculated
                    editEddInput.style.backgroundColor = '#f0f9ff';
                    editEddInput.style.transition = 'background-color 0.3s ease';
                    setTimeout(() => {
                        editEddInput.style.backgroundColor = '';
                    }, 2000);
                    
                    return true;
                }
            }
        }
        
        // Multiple event listeners to catch all possible date inputs
        editLmpInput.addEventListener('change', validateAndCalculateEDD);
        editLmpInput.addEventListener('input', validateAndCalculateEDD);
        editLmpInput.addEventListener('blur', validateAndCalculateEDD);
        
        // Additional prevention for keyboard input
        editLmpInput.addEventListener('keydown', function(e) {
            // Allow navigation keys, backspace, delete, etc.
            const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'];
            if (allowedKeys.includes(e.key)) {
                return;
            }
        });
        
        // Prevent paste of invalid dates
        editLmpInput.addEventListener('paste', function(e) {
            setTimeout(validateAndCalculateEDD, 0);
        });
    }
    
    // Form validation for edit modal
    const editPrenatalForm = document.getElementById('edit-prenatal-form');
    if (editPrenatalForm) {
        editPrenatalForm.addEventListener('submit', function(e) {
            const editLmpInput = document.getElementById('edit-lmp');
            
            let isValid = true;
            
            // Validate LMP
            if (!editLmpInput.value) {
                editLmpInput.classList.add('border-red-500');
                isValid = false;
            } else {
                editLmpInput.classList.remove('border-red-500');
                
                // Additional validation: Check if LMP is not a future date
                const selectedDate = new Date(editLmpInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate > today) {
                    editLmpInput.classList.add('border-red-500');
                    alert('Last Menstrual Period cannot be a future date.');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                if (editLmpInput.classList.contains('border-red-500') && editLmpInput.value) {
                    // If there's a value but it's invalid (future date)
                    return;
                } else {
                    alert('Please fill in all required fields (Last Menstrual Period).');
                }
            }
        });
    }
});
</script>