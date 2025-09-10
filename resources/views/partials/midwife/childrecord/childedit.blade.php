<!-- Edit Child Record Modal -->
<div id="edit-child-modal"
    class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-modal-title"
    onclick="closeEditChildModal(event)">

    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 id="edit-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-edit text-[#68727A] mr-2"></i>
                Edit Child Record
            </h3>
            <button type="button"
                    onclick="closeEditChildModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                    aria-label="Close modal">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Form -->
        <form action="#" 
            method="POST"
            id="edit-child-form"
            class="space-y-5"
            data-update-url="{{ route('midwife.childrecord.update', ':id') }}"
            novalidate>
            @csrf
            @method('PUT')

            <!-- Hidden input to store the record ID -->
            <input type="hidden" id="edit-record-id" name="record_id" value="">

            <!-- Show server-side validation errors for edit -->
            @if (session('edit_errors'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="font-medium">Please correct the following errors:</div>
                    <ul class="list-disc list-inside mt-2">
                        @foreach (session('edit_errors')->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="modal-form-grid grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Basic Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Child Name *</label>
                            <input type="text" id="edit-child-name" name="child_name" required 
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('child_name') error-border @enderror"
                                   placeholder="Enter child's full name">
                            @error('child_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                            <div class="flex space-x-6">
                                <label class="flex items-center">
                                    <input type="radio" id="edit-gender-male" name="gender" value="Male" class="text-[#68727A] focus:ring-[#68727A]">
                                    <span class="ml-2 text-gray-700">Male</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" id="edit-gender-female" name="gender" value="Female" class="text-[#68727A] focus:ring-[#68727A]">
                                    <span class="ml-2 text-gray-700">Female</span>
                                </label>
                            </div>
                            @error('gender')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Birth Date *</label>
                            <input type="date" id="edit-birthdate" name="birthdate" required 
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birthdate') error-border @enderror">
                            @error('birthdate')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Birth Details -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Birth Details</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Birth Height (cm)</label>
                            <input type="number" id="edit-birth-height" name="birth_height" step="0.1" min="0" max="999.99"
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birth_height') error-border @enderror"
                                   placeholder="e.g., 50.5">
                            @error('birth_height')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Birth Weight (kg)</label>
                            <input type="number" id="edit-birth-weight" name="birth_weight" step="0.01" min="0" max="99.999"
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birth_weight') error-border @enderror"
                                   placeholder="e.g., 3.25">
                            @error('birth_weight')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Birth Place</label>
                            <input type="text" id="edit-birthplace" name="birthplace"
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birthplace') error-border @enderror"
                                   placeholder="Hospital or location">
                            @error('birthplace')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Parent Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Parent Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Name *</label>
                            <input type="text" id="edit-mother-name" name="mother_name" required
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('mother_name') error-border @enderror"
                                   placeholder="Enter mother's full name">
                            @error('mother_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Father's Name</label>
                            <input type="text" id="edit-father-name" name="father_name"
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('father_name') error-border @enderror"
                                   placeholder="Enter father's full name">
                            @error('father_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Details -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Contact Details</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <div class="relative">
                                <span class="phone-prefix">+63</span>
                                <input type="tel" id="edit-phone-number" name="phone_number" required
                                       class="form-input input-clean phone-input w-full px-4 py-2.5 rounded-lg @error('phone_number') error-border @enderror"
                                       placeholder="9123456789"
                                       pattern="[9]\d{9}"
                                       maxlength="10">
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Format: 9123456789 (Philippine mobile number)</div>
                            @error('phone_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea id="edit-address" name="address" rows="3"
                                      class="form-input input-clean w-full px-4 py-2.5 rounded-lg resize-none @error('address') error-border @enderror"
                                      placeholder="Enter complete address"></textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                <button type="button"  onclick="closeEditChildModal()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                    Cancel
                </button>
                <button type="submit"
                        id="edit-submit-btn" class="btn-minimal btn-primary-clean px-6 py-2.5 rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i>Update Record
                </button>
            </div> 
        </form>
    </div>
</div>