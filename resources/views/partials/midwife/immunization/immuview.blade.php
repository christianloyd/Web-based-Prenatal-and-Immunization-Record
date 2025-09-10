<!-- View Immunization Modal -->
<div id="viewImmunizationModal" 
     class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeViewModal(event)">
    
    <div id="viewImmunizationModalContent" 
         class="relative w-full max-w-3xl bg-white rounded-xl shadow-2xl p-6 transform -translate-y-10 opacity-0 transition-all duration-300"
         onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-syringe text-[#68727A] mr-2"></i>
                Immunization Details
            </h2>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Patient Information -->
            <div class="space-y-4">
                <div class="border-b pb-2 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Patient Information</h3>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-600">Child Name:</span>
                        <span id="modalChildName" class="text-sm text-gray-900 font-medium">-</span>
                    </div>
                </div>
            </div>

            <!-- Vaccine Information -->
            <div class="space-y-4">
                <div class="border-b pb-2 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Vaccine Details</h3>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-600">Vaccine:</span>
                        <span id="modalVaccineName" class="text-sm text-gray-900 font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-600">Dose:</span>
                        <span id="modalDose" class="text-sm text-gray-900">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-600">Status:</span>
                        <span id="modalStatus" class="text-sm font-medium">-</span>
                    </div>
                </div>
            </div>

            <!-- Schedule Information -->
            <div class="space-y-4">
                <div class="border-b pb-2 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Schedule Information</h3>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-600">Schedule Date:</span>
                        <span id="modalScheduleDate" class="text-sm text-gray-900">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-600">Schedule Time:</span>
                        <span id="modalScheduleTime" class="text-sm text-gray-900">-</span>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="space-y-4">
                <div class="border-b pb-2 mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-600 block mb-1">Notes:</span>
                        <div id="modalNotes" class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg min-h-[60px]">-</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t mt-6">
            <button onclick="closeViewModal()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                Close
            </button>
        </div>
    </div>
</div>
