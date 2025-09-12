<!-- partials/bhw/patient_view.blade.php -->
<div id="view-patient-modal" 
    class="modal-overlay hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    onclick="closeViewPatientModal(event)">
    
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative modal-content"
         onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-charcoal to-paynes-gray text-white p-6 rounded-t-xl relative">
            <button onclick="closeViewPatientModal()" 
                    class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-white hover:bg-opacity-20">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex items-center space-x-4">
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Patient Information</h2>
                    <p class="text-gray-200 text-sm">Complete patient details and information</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Left Column -->
                <div class="space-y-6">
                    
                    <!-- Personal Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-charcoal mb-4 flex items-center border-b border-gray-200 pb-2">
                            <svg class="w-5 h-5 text-paynes-gray mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6z"/>
                            </svg>
                            Personal Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Full Name</label>
                                <p class="text-charcoal font-semibold text-lg mt-1" id="viewPatientName">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Patient ID</label>
                                <p class="text-charcoal font-semibold text-lg mt-1" id="viewPatientId">-</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Age</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="viewPatientAge">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Risk Status</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="viewPatientRiskStatus">-</p>
                                </div>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Occupation</label>
                                <p class="text-charcoal font-semibold text-lg mt-1" id="viewPatientOccupation">-</p>
                            </div>
                        </div>
                    </div>

                   

                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    
                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-charcoal mb-4 flex items-center border-b border-gray-200 pb-2">
                            <svg class="w-5 h-5 text-paynes-gray mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            Contact Information
                        </h3>
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Primary Contact</label>
                                <p class="text-charcoal font-semibold text-lg mt-1" id="viewPatientContact">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Emergency Contact</label>
                                <p class="text-charcoal font-semibold text-lg mt-1" id="viewPatientEmergencyContact">-</p>
                            </div>
                        </div>
                    </div>

                     <!-- Address Information -->
                     <div>
                        <h3 class="text-lg font-semibold text-charcoal mb-4 flex items-center border-b border-gray-200 pb-2">
                            <svg class="w-5 h-5 text-paynes-gray mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            Address Information
                        </h3>
                        <div class="border border-gray-200 p-4 rounded-lg">
                            <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide mb-2 block">Complete Address</label>
                            <p class="text-charcoal leading-relaxed" id="viewPatientAddress">-</p>
                        </div>
                    </div>


                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    <span>Patient registered on: </span>
                    <span id="viewPatientCreatedAt" class="font-medium">-</span>
                </div>
                <div class="flex space-x-3">
                    
                    <button onclick="closeViewPatientModal()" 
                            class="bg-charcoal text-white px-6 py-2 rounded-lg hover:bg-paynes-gray transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>