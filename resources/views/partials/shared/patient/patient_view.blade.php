{{-- Shared Patient View Modal - Works for both Midwife and BHW --}}
<div id="view-patient-modal"
    class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="view-modal-title"
    onclick="closeViewPatientModal(event)">

    <div class="modal-content relative w-full max-w-3xl bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h3 id="view-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                </svg>
                Patient Information
            </h3>
            <button type="button"
                    onclick="closeViewPatientModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                    aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Patient Details -->
        <div class="space-y-6">
            <!-- Basic Information -->
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Basic Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Full Name</label>
                        <p id="viewPatientName" class="text-base text-gray-900 mt-1">-</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Patient ID</label>
                        <p id="viewPatientId" class="text-base text-gray-900 mt-1 font-mono">-</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Age</label>
                        <p id="viewPatientAge" class="text-base text-gray-900 mt-1">-</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Occupation</label>
                        <p id="viewPatientOccupation" class="text-base text-gray-900 mt-1">-</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                    </svg>
                    Contact Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Primary Contact</label>
                        <p id="viewPatientContact" class="text-base text-gray-900 mt-1">-</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Emergency Contact</label>
                        <p id="viewPatientEmergencyContact" class="text-base text-gray-900 mt-1">-</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-sm font-medium text-gray-600">Address</label>
                        <p id="viewPatientAddress" class="text-base text-gray-900 mt-1">-</p>
                    </div>
                </div>
            </div>

            <!-- Prenatal Information (if available) -->
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Prenatal Status
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <label class="text-sm font-medium text-gray-600">Risk Status</label>
                        <div id="viewPatientRiskStatus" class="mt-1">
                            <span class="text-gray-500">N/A</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600">Expected Due Date</label>
                        <p id="viewPatientEDD" class="text-base text-gray-900 mt-1">-</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                    </svg>
                    Notes
                </h4>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p id="viewPatientNotes" class="text-base text-gray-700 whitespace-pre-wrap">-</p>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex gap-4 pt-6 border-t mt-6">
            <a href="#"
               id="viewPatientProfileBtn"
               class="btn-primary flex-1 bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 text-center"
               style="background-color: var(--primary);"
               onmouseover="this.style.backgroundColor='var(--secondary)'"
               onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-user mr-2"></i>
                View Full Profile
            </a>
            <button type="button"
                    onclick="closeViewPatientModal()"
                    class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 font-semibold">
                Close
            </button>
        </div>
    </div>
</div>
