<!-- Edit Prenatal Record Modal -->
<div id="edit-prenatal-modal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" 
     role="dialog" aria-modal="true" onclick="closeEditPrenatalModal(event)">
    
    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-4 sm:p-6 my-8" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                <span class="hidden sm:inline">Edit Prenatal Record</span>
                <span class="sm:hidden">Edit Record</span>
            </h3>
            <button type="button" onclick="closeEditPrenatalModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="" method="POST" id="edit-prenatal-form" 
              data-update-url="{{ route('bhw.prenatalrecord.update', ':id') }}" class="space-y-4 sm:space-y-6">
            @csrf
            @method('PUT')

            <!-- Patient Information Section (Read-only) -->
            <div class="border-b pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Patient Information</h4>
                <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
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
                    <p class="text-xs text-gray-500 mt-2">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Patient information cannot be changed. To assign this record to a different patient, please create a new record.
                    </p>
                </div>
                <!-- Hidden input to maintain patient_id -->
                <input type="hidden" name="patient_id" id="edit-patient-id-hidden" value="">
            </div>

            <!-- Pregnancy Information Section -->
            <div class="border-b pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Pregnancy Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Last Menstrual Period *</label>
                        <input type="date" name="last_menstrual_period" id="edit-lmp" required 
                               class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Expected Due Date</label>
                        <input type="date" name="expected_due_date" id="edit-due-date" 
                               class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <p class="text-xs text-gray-500 mt-1">Auto-calculated if left blank</p>
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="edit-status" 
                                class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="normal">Normal</option>
                            <option value="monitor">Monitor</option>
                            <option value="high-risk">High Risk</option>
                            <option value="due">Appointment Due</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Gravida</label>
                        <select name="gravida" id="edit-gravida" 
                                class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
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
                                class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
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
            <div class="border-b pb-4 mb-4 sm:mb-6">
                <h4 class="font-semibold text-gray-800 mb-3 sm:mb-4 text-sm sm:text-base">Physical Measurements</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                        <input type="text" name="blood_pressure" id="edit-blood-pressure" placeholder="e.g., 120/80"
                               class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                        <input type="number" name="weight" id="edit-weight" step="0.1" min="30" max="200" placeholder="e.g., 65.5"
                               class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                        <input type="number" name="height" id="edit-height" min="120" max="200" placeholder="e.g., 165"
                               class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- Medical Information Section -->
            <div class="space-y-3 sm:space-y-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Medical History</label>
                    <textarea name="medical_history" id="edit-medical-history" rows="3" 
                              placeholder="Any relevant medical history, previous pregnancies, complications, etc."
                              class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Additional Notes</label>
                    <textarea name="notes" id="edit-notes" rows="2" 
                              placeholder="Any additional notes or observations..."
                              class="form-input w-full border border-gray-300 rounded-lg p-2 sm:p-2.5 resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 sm:pt-6 border-t">
                <button type="button" onclick="closeEditPrenatalModal()" 
                        class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm sm:text-base order-2 sm:order-1">
                    Cancel
                </button>
                <button type="submit" 
                        class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors text-sm sm:text-base order-1 sm:order-2">
                    Update Prenatal Record
                </button>
            </div>
        </form>
    </div>
</div>