<!-- View Patient Modal Component -->
<div id="viewPatientModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 p-4">
        <div id="viewPatientModalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto relative transform transition-all duration-300 -translate-y-10 opacity-0">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-charcoal to-paynes-gray text-white p-6 rounded-t-xl relative">
                <button onclick="closeViewPatientModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-white hover:bg-opacity-20">
                    <i class="fas fa-times"></i>
                </button>
                <div class="flex items-center space-x-3">
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-user-md text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Patient Information</h2>
                        <p class="text-gray-200 text-sm">Complete patient details and medical history</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                
                <!-- Main Grid - Landscape Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Left Column -->
                    <div class="space-y-8">
                        
                        <!-- Personal Information Section -->
                        <div>
                            <h3 class="text-lg font-semibold text-charcoal mb-4 flex items-center border-b border-gray-200 pb-2">
                                <i class="fas fa-user text-paynes-gray mr-2"></i>
                                Personal Information
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Full Name</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalName">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Patient ID</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalId">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Age</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalAge">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Status</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalStatus">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information Section -->
                        <div>
                            <h3 class="text-lg font-semibold text-charcoal mb-4 flex items-center border-b border-gray-200 pb-2">
                                <i class="fas fa-phone text-paynes-gray mr-2"></i>
                                Contact Information
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Primary Contact</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalContact">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Emergency Contact</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalEmergencyContact">-</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column -->
                    <div class="space-y-8">
                        
                        <!-- Appointment Information Section -->
                        <div>
                            <h3 class="text-lg font-semibold text-charcoal mb-4 flex items-center border-b border-gray-200 pb-2">
                                <i class="fas fa-calendar-alt text-paynes-gray mr-2"></i>
                                Appointment Schedule
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Last Visit</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalLastVisit">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Next Appointment</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalNextAppointment">-</p>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information Section -->
                        <div>
                            <h3 class="text-lg font-semibold text-charcoal mb-4 flex items-center border-b border-gray-200 pb-2">
                                <i class="fas fa-heartbeat text-paynes-gray mr-2"></i>
                                Medical Information
                            </h3>
                            <div class="space-y-4">
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide">Gravida / Para</label>
                                    <p class="text-charcoal font-semibold text-lg mt-1" id="modalGravidaPara">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide mb-2 block">Medical History</label>
                                    <p class="text-charcoal leading-relaxed" id="modalMedicalHistory">-</p>
                                </div>
                                <div class="border border-gray-200 p-4 rounded-lg">
                                    <label class="text-sm font-medium text-paynes-gray uppercase tracking-wide mb-2 block">Additional Notes</label>
                                    <p class="text-charcoal leading-relaxed" id="modalNotes">-</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t">
                <div class="flex justify-end">
                    <button onclick="closeViewPatientModal()" class="bg-charcoal text-white px-6 py-2 rounded-lg hover:bg-paynes-gray transition-colors flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Close</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
