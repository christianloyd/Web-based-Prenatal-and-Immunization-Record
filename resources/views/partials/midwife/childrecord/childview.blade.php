<!-- View Child Record Modal -->
<div id="viewChildModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 p-4">
    <div id="viewChildModalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto relative transform transition-all duration-300 -translate-y-10 opacity-0">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-charcoal to-paynes-gray text-white p-6 rounded-t-xl relative">
            <button onclick="closeViewChildModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-white hover:bg-opacity-20">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex items-center space-x-3">
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <i class="fas fa-baby text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">Child Record Information</h2>
                    <p class="text-gray-200 text-sm">Complete child details and health information</p>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            
            <!-- Main Grid - Landscape Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Left Column -->
                <div class="space-y-8">
                    
                    <!-- Child Information Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
                            <i class="fas fa-baby text-[#36535E] mr-2"></i>
                            Child Information
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Full Name</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalChildName">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Gender</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalChildGender">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Birth Date</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalBirthDate">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Age</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalChildAge">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Birth Details Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
                            <i class="fas fa-weight text-[#36535E] mr-2"></i>
                            Birth Details
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Birth Weight</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalBirthWeight">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Birth Height</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalBirthHeight">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg col-span-2">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Birth Place</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalBirthPlace">-</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="space-y-8">
                    
                    <!-- Parent Information Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
                            <i class="fas fa-users text-[#36535E] mr-2"></i>
                            Parent Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Mother's Name</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalMotherName">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Father's Name</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalFatherName">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
                            <i class="fas fa-phone text-[#36535E] mr-2"></i>
                            Contact Information
                        </h3>
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Phone Number</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalPhoneNumber">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide mb-2 block">Address</label>
                                <p class="text-[#68727A] leading-relaxed" id="modalAddress">-</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Record created on <span id="modalCreatedDate">-</span>
                </div>
                <div class="flex space-x-3">
                    <button onclick="editRecordFromModal()" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition-colors flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Record</span>
                    </button>
                    <button onclick="closeViewChildModal()" class="bg-secondary text-white px-6 py-2 rounded-lg hover:bg-charcoal transition-colors flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Close</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>