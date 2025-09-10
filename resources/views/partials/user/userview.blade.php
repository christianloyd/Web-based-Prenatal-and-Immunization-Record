<!-- View User Modal -->
<div id="viewUserModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300 p-4">
    <div id="viewUserModalContent" class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto relative transform transition-all duration-300 -translate-y-10 opacity-0">
        <!-- Header -->
    <div class="bg-gradient-to-r from-[#68727A] to-[#36535E] text-white p-6 rounded-t-xl relative">
            <button onclick="closeViewUserModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors text-2xl w-8 h-8 flex items-center justify-center rounded-full hover:bg-white hover:bg-opacity-20">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex items-center space-x-3">
                <div class="bg-white bg-opacity-20 p-3 rounded-full">
                    <i class="fas fa-user text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">User Information</h2>
                    <p class="text-gray-200 text-sm">Complete user details and account information</p>
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
        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
            <i class="fas fa-user text-[#36535E] mr-2"></i>
            Personal Information
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <div class="border border-gray-200 p-4 rounded-lg">
                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Full Name</label>
                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalFullName">-</p>
            </div>
            <div class="border border-gray-200 p-4 rounded-lg">
                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Gender</label>
                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalGender">-</p>
            </div>
            <div class="border border-gray-200 p-4 rounded-lg">
                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Age</label>
                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalAge">-</p>
            </div>
            <div class="border border-gray-200 p-4 rounded-lg">
                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Role</label>
                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalRole">-</p>
            </div>
        </div>
    </div>

    <!-- Account Information Section -->
    <div>
        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
            <i class="fas fa-id-card text-[#36535E] mr-2"></i>
            Account Information
        </h3>
        <div class="grid grid-cols-1 gap-4">
            <div class="border border-gray-200 p-4 rounded-lg">
                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Username</label>
                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalUsername">-</p>
            </div>
            <!-- NEW STATUS FIELD -->
            <div class="border border-gray-200 p-4 rounded-lg">
                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Account Status</label>
                <p class="font-semibold text-lg mt-1" id="modalStatus">-</p>
            </div>
            <div class="border border-gray-200 p-4 rounded-lg">
                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Account Created</label>
                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalCreatedAt">-</p>
            </div>
        </div>
    </div>
</div>

                <!-- Right Column -->
                <div class="space-y-8">
                    
                    <!-- Contact Information Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
                            <i class="fas fa-phone text-[#36535E] mr-2"></i>
                            Contact Information
                        </h3>
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Contact Number</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalContactNumber">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide mb-2 block">Address</label>
                                <p class="text-[#68727A] leading-relaxed" id="modalUserAddress">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Role Information Section -->
                    <div>
                        <h3 class="text-lg font-semibold text-[#68727A] mb-4 flex items-center border-b border-gray-200 pb-2">
                            <i class="fas fa-briefcase text-[#36535E] mr-2"></i>
                            Role Information
                        </h3>
                        <div class="space-y-4">
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Role Description</label>
                                <p class="text-[#68727A] leading-relaxed mt-2" id="modalRoleDescription">-</p>
                            </div>
                            <div class="border border-gray-200 p-4 rounded-lg">
                                <label class="text-sm font-medium text-[#36535E] uppercase tracking-wide">Access Level</label>
                                <p class="text-[#68727A] font-semibold text-lg mt-1" id="modalAccessLevel">-</p>
                            </div>
                        </div>
                    </div> 
                </div>
            </div>
            <!-- Footer -->
            <div class="mt-8 flex justify-end">
                <button onclick="closeViewUserModal()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>