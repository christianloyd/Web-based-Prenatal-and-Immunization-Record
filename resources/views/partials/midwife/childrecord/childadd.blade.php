<!-- Add Modal with Mother Confirmation -->
<div id="recordModal" 
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeModal(event)">
    
    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
         onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-baby text-[#68727A] mr-2"></i>
                Add Child Record
            </h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Mother Confirmation Step -->
        <div id="motherConfirmationStep" class="text-center py-8">
            <div class="mb-6">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-question text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Mother Information</h3>
                <p class="text-gray-600">Is the mother already registered in our system?</p>
            </div>
            
            <div class="flex justify-center space-x-4">
                <button onclick="showMotherForm(true)" 
                        class="btn-minimal bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 transition-colors">
                    <i class="fas fa-check mr-2"></i>Yes, Select Existing Mother
                </button>
                <button onclick="showMotherForm(false)" 
                        class="btn-minimal bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>No, Add New Mother
                </button>
            </div>
        </div>

        <!-- Main Form (Initially Hidden) -->
        <div id="childRecordForm" class="hidden">
            <form id="recordForm" 
                  action="{{ route('midwife.childrecord.store') }}" 
                  data-store-url="{{ route('midwife.childrecord.store') }}"
                  method="POST" 
                  class="space-y-5" 
                  novalidate>
                @csrf
                <input type="hidden" id="recordId" name="id">
                <input type="hidden" id="motherExists" name="mother_exists" value="">
                
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
                
                <div class="modal-form-grid grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Basic Information -->
                    <div>
                        <div class="section-header border-b pb-2 mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Child Name *</label>
                                <input type="text" id="child_name" name="child_name" required 
                                       class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('child_name') error-border @enderror"
                                       placeholder="Enter child's full name"
                                       value="{{ old('child_name') }}">
                                @error('child_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <div class="flex space-x-6">
                                    <label class="flex items-center">
                                        <input type="radio" name="gender" value="Male" class="text-[#68727A] focus:ring-[#68727A]" {{ old('gender') == 'Male' ? 'checked' : '' }}>
                                        <span class="ml-2 text-gray-700">Male</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="gender" value="Female" class="text-[#68727A] focus:ring-[#68727A]" {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                        <span class="ml-2 text-gray-700">Female</span>
                                    </label>
                                </div>
                                @error('gender')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Birth Date *</label>
                                <input type="date" id="birthdate" name="birthdate" required 
                                       class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birthdate') error-border @enderror"
                                       value="{{ old('birthdate') }}">
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
                                <input type="number" id="birth_height" name="birth_height" step="0.1" min="0" max="999.99"
                                       class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birth_height') error-border @enderror"
                                       placeholder="e.g., 50.5"
                                       value="{{ old('birth_height') }}">
                                @error('birth_height')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Birth Weight (kg)</label>
                                <input type="number" id="birth_weight" name="birth_weight" step="0.01" min="0" max="99.999"
                                       class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birth_weight') error-border @enderror"
                                       placeholder="e.g., 3.25"
                                       value="{{ old('birth_weight') }}">
                                @error('birth_weight')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Birth Place</label>
                                <input type="text" id="birthplace" name="birthplace"
                                       class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('birthplace') error-border @enderror"
                                       placeholder="Hospital or location"
                                       value="{{ old('birthplace') }}">
                                @error('birthplace')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Mother Information Section -->
                    <div>
                        <div class="section-header border-b pb-2 mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Mother Information</h3>
                            <button type="button" onclick="changeMotherType()" class="text-sm text-blue-600 hover:text-blue-800 mt-1">
                                <i class="fas fa-edit mr-1"></i>Change Selection
                            </button>
                        </div>
                        
                        <!-- Existing Mother Selection -->
                        <div id="existingMotherSection" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Mother *</label>
                                <select name="mother_id" id="mother_id"
                                        class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('mother_id') error-border @enderror">
                                    <option value="">-- Select Mother --</option>
                                    @foreach($mothers as $mother)
                                        <option value="{{ $mother->id }}" 
                                                data-name="{{ $mother->name }}"
                                                data-age="{{ $mother->age ?? '' }}"
                                                data-contact="{{ $mother->contact ?? '' }}"
                                                data-address="{{ $mother->address ?? '' }}"
                                                {{ old('mother_id') == $mother->id ? 'selected' : '' }}>
                                            {{ $mother->name }} (ID: {{ $mother->formatted_patient_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('mother_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Mother Details Display -->
                            <div id="motherDetails" class="hidden bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Mother Details</h4>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p><strong>Name:</strong> <span id="motherName">-</span></p>
                                    <p><strong>Age:</strong> <span id="motherAge">-</span></p>
                                    <p><strong>Contact:</strong> <span id="motherContact">-</span></p>
                                    <p><strong>Address:</strong> <span id="motherAddress">-</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- New Mother Input -->
                        <div id="newMotherSection" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Full Name *</label>
                                <input type="text" name="mother_name" id="mother_name"
                                       class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('mother_name') error-border @enderror"
                                       placeholder="Enter mother's full name"
                                       value="{{ old('mother_name') }}">
                                @error('mother_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Age *</label>
                                <input type="number" name="mother_age" id="mother_age" min="15" max="50"
                                       class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('mother_age') error-border @enderror"
                                       placeholder="Enter mother's age"
                                       value="{{ old('mother_age') }}">
                                @error('mother_age')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Contact Number *</label>
                                <div class="relative">
                                    <span class="phone-prefix">+63</span>
                                    <input type="tel" name="mother_contact" id="mother_contact"
                                           class="form-input input-clean phone-input w-full px-4 py-2.5 rounded-lg @error('mother_contact') error-border @enderror"
                                           placeholder="9123456789"
                                           pattern="[9]\d{9}"
                                           maxlength="10"
                                           value="{{ old('mother_contact') }}">
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Format: 9123456789 (Philippine mobile number)</div>
                                @error('mother_contact')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mother's Address *</label>
                                <textarea name="mother_address" id="mother_address" rows="2"
                                          class="form-input input-clean w-full px-4 py-2.5 rounded-lg resize-none @error('mother_address') error-border @enderror"
                                          placeholder="Enter mother's complete address">{{ old('mother_address') }}</textarea>
                                @error('mother_address')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Father's Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Father's Name</label>
                            <input type="text" id="father_name" name="father_name"
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('father_name') error-border @enderror"
                                   placeholder="Enter father's full name"
                                   value="{{ old('father_name') }}">
                            @error('father_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div>
                        <div class="section-header border-b pb-2 mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Contact Details</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number 
                                    <span id="phoneNumberNote" class="text-xs text-gray-500">(Will use mother's contact if existing mother selected)</span>
                                </label>
                                <div class="relative">
                                    <span class="phone-prefix">+63</span>
                                    <input type="tel" id="phone_number" name="phone_number" required
                                           class="form-input input-clean phone-input w-full px-4 py-2.5 rounded-lg @error('phone_number') error-border @enderror"
                                           placeholder="9123456789"
                                           pattern="[9]\d{9}"
                                           maxlength="10"
                                           value="{{ old('phone_number') }}">
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Format: 9123456789 (Philippine mobile number)</div>
                                @error('phone_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Address
                                    <span id="addressNote" class="text-xs text-gray-500">(Will use mother's address if existing mother selected)</span>
                                </label>
                                <textarea id="address" name="address" rows="3"
                                          class="form-input input-clean w-full px-4 py-2.5 rounded-lg resize-none @error('address') error-border @enderror"
                                          placeholder="Enter complete address">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                    <button type="button" onclick="goBackToConfirmation()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </button>
                    <button type="button" onclick="closeModal()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit" id="submit-btn" class="btn-minimal btn-primary-clean px-6 py-2.5 rounded-lg font-medium">
                        <i class="fas fa-save mr-2"></i>Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>