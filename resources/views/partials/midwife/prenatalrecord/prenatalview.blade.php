<!-- View Prenatal Record Modal -->
<div id="view-prenatal-modal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" 
     role="dialog" aria-modal="true" onclick="closeViewPrenatalModal(event)">
    
    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Prenatal Record Details
            </h3>
            <button type="button" onclick="closeViewPrenatalModal()" 
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Column - Patient & Pregnancy Info -->
            <div class="space-y-6">
                
                <!-- Patient Information -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        Patient Information
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Name</label>
                            <p class="text-gray-900 font-medium" id="viewPatientName">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Patient ID</label>
                            <p class="text-gray-900 font-medium" id="viewPatientId">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Age</label>
                            <p class="text-gray-900 font-medium" id="viewPatientAge">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Status</label>
                            <p class="text-gray-900 font-medium" id="viewStatus">-</p>
                        </div>
                    </div>
                </div>

                <!-- Pregnancy Information -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        Pregnancy Information
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Gestational Age</label>
                            <p class="text-gray-900 font-medium" id="viewGestationalAge">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Trimester</label>
                            <p class="text-gray-900 font-medium" id="viewTrimester">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Last Menstrual Period</label>
                            <p class="text-gray-900 font-medium" id="viewLMP">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Expected Due Date</label>
                            <p class="text-gray-900 font-medium" id="viewEDD">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Gravida</label>
                            <p class="text-gray-900 font-medium" id="viewGravida">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Para</label>
                            <p class="text-gray-900 font-medium" id="viewPara">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Medical Info -->
            <div class="space-y-6">
                
                <!-- Physical Measurements -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Physical Measurements
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Blood Pressure</label>
                            <p class="text-gray-900 font-medium" id="viewBloodPressure">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Weight</label>
                            <p class="text-gray-900 font-medium" id="viewWeight">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Height</label>
                            <p class="text-gray-900 font-medium" id="viewHeight">-</p>
                        </div>
                    </div>
                </div>

                <!-- Medical History & Notes -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        Medical Information
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Medical History</label>
                            <p class="text-gray-900 leading-relaxed" id="viewMedicalHistory">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Additional Notes</label>
                            <p class="text-gray-900 leading-relaxed" id="viewNotes">-</p>
                        </div>
                    </div>
                </div>

                <!-- Visit Information -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        Visit Information
                    </h4>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Last Visit</label>
                            <p class="text-gray-900 font-medium" id="viewLastVisit">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Next Appointment</label>
                            <p class="text-gray-900 font-medium" id="viewNextAppointment">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-3 pt-6 border-t mt-6">
            <button type="button" onclick="closeViewPrenatalModal()" 
                    class="px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>