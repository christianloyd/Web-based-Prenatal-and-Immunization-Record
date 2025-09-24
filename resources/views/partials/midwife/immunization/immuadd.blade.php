<!-- Add Immunization Modal -->
<div id="immunizationModal" 
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeModal(event)">
    
    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
         onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-syringe text-[#68727A] mr-2"></i>
                Schedule Immunization
            </h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="immunizationForm" action="{{ route('midwife.immunization.store') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" id="immunizationId" name="id">
            
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
                <!-- Patient Selection -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Patient Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Child *</label>
                            <select id="child_record_id" name="child_record_id" required
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('child_record_id') error-border @enderror"
                                    onchange="loadAvailableVaccines()">
                                <option value="">Choose a child...</option>
                                @foreach($childRecords as $child)
                                    <option value="{{ $child->id }}" {{ old('child_record_id') == $child->id ? 'selected' : '' }}>
                                        {{ $child->formatted_child_id ?? 'CH-' . str_pad($child->id, 3, '0', STR_PAD_LEFT) }} - {{ $child->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('child_record_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Vaccine Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Vaccine Details</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Vaccine *</label>
                            <select id="vaccine_id" name="vaccine_id" required
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('vaccine_id') error-border @enderror"
                                    onchange="loadAvailableDoses()">
                                <option value="">Choose a vaccine...</option>
                                @foreach($availableVaccines as $vaccine)
                                    <option value="{{ $vaccine->id }}"
                                            data-category="{{ $vaccine->category }}"
                                            {{ old('vaccine_id') == $vaccine->id ? 'selected' : '' }}>
                                        {{ $vaccine->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vaccine_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            
                            <!-- Vaccine Info Display -->
                            <div id="vaccineInfo" class="hidden mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-center">
                                    <div>
                                        <span class="text-xs px-2 py-1 rounded-full" id="vaccineCategory"></span>
                                    </div>
                                </div>
                            </div>

                           
                            
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dose *</label>
                            <select id="dose" name="dose" required
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('dose') error-border @enderror">
                                <option value="">Select dose...</option>
                            </select>
                            @error('dose')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Schedule Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Schedule Details</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date *</label>
                            <input type="date" id="schedule_date" name="schedule_date" required 
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('schedule_date') error-border @enderror"
                                   value="{{ old('schedule_date') }}">
                            @error('schedule_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Time *</label>
                            <input type="time" id="schedule_time" name="schedule_time" required 
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('schedule_time') error-border @enderror"
                                   value="{{ old('schedule_time') }}">
                            @error('schedule_time')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes *</label>
                            <textarea id="notes" name="notes" rows="4" required
                                      class="form-input input-clean w-full px-4 py-2.5 rounded-lg resize-none @error('notes') error-border @enderror"
                                      placeholder="Any special instructions or notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                    Cancel
                </button>
                <button type="submit" id="submit-btn" class="btn-minimal btn-primary-clean px-6 py-2.5 rounded-lg font-medium">
                    <i class="fas fa-calendar-plus mr-2"></i>Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Function to update vaccine information display
function updateVaccineInfo() {
    const vaccineSelect = document.getElementById('vaccine_id');
    const vaccineInfo = document.getElementById('vaccineInfo');
    const vaccineCategory = document.getElementById('vaccineCategory');

    if (vaccineSelect && vaccineSelect.value) {
        const selectedOption = vaccineSelect.options[vaccineSelect.selectedIndex];
        const category = selectedOption.dataset.category;

        // Show vaccine info
        if (vaccineCategory) {
            vaccineCategory.textContent = category;
            vaccineCategory.className = getCategoryClass(category);
        }
        if (vaccineInfo) {
            vaccineInfo.classList.remove('hidden');
        }
    } else {
        // Hide vaccine info
        if (vaccineInfo) {
            vaccineInfo.classList.add('hidden');
        }
    }
}

// Helper function to get category styling class
function getCategoryClass(category) {
    const classes = {
        'Routine Immunization': 'text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800',
        'COVID-19': 'text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-800',
        'Seasonal': 'text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-800',
        'Travel': 'text-xs px-2 py-1 rounded-full bg-teal-100 text-teal-800'
    };
    return classes[category] || 'text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-800';
}

// Load available vaccines when child is selected
async function loadAvailableVaccines() {
    const childId = document.getElementById('child_record_id').value;
    const vaccineSelect = document.getElementById('vaccine_id');
    const doseSelect = document.getElementById('dose');

    // Reset vaccine and dose dropdowns
    vaccineSelect.innerHTML = '<option value="">Choose a vaccine...</option>';
    doseSelect.innerHTML = '<option value="">Select dose...</option>';

    if (!childId) return;

    try {
        const userRole = '{{ auth()->user()->role }}';
        const routeName = userRole === 'bhw' ? 'immunizations' : 'immunization';

        const response = await fetch(`/${userRole}/${routeName}/child/${childId}/vaccines`);
        const data = await response.json();

        if (data.success && data.vaccines) {
            data.vaccines.forEach(vaccine => {
                const option = document.createElement('option');
                option.value = vaccine.id;
                option.dataset.category = vaccine.category;
                option.textContent = vaccine.name;

                vaccineSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading vaccines:', error);
    }
}

// Load available doses when vaccine is selected
async function loadAvailableDoses() {
    const childIdElement = document.getElementById('child_record_id');
    const vaccineIdElement = document.getElementById('vaccine_id');
    const doseSelect = document.getElementById('dose');

    if (!childIdElement || !vaccineIdElement || !doseSelect) {
        console.error('Required elements not found');
        return;
    }

    const childId = childIdElement.value;
    const vaccineId = vaccineIdElement.value;

    console.log('loadAvailableDoses called', {childId, vaccineId});

    // Update vaccine info display (with error handling)
    try {
        updateVaccineInfo();
    } catch (error) {
        console.error('Error updating vaccine info:', error);
    }

    // Reset dose dropdown
    doseSelect.innerHTML = '<option value="">Select dose...</option>';

    if (!childId || !vaccineId) {
        console.log('Missing childId or vaccineId, returning');
        return;
    }

    try {
        const userRole = '{{ auth()->user()->role }}';
        const routeName = userRole === 'bhw' ? 'immunizations' : 'immunization';
        const url = `/${userRole}/${routeName}/child/${childId}/vaccines/${vaccineId}/doses`;

        console.log('Fetching doses from:', url);

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        console.log('Dose response:', data);

        if (data.success && data.doses) {
            if (Object.keys(data.doses).length === 0) {
                console.log('No doses available for this vaccine and child combination');
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No available doses';
                option.disabled = true;
                doseSelect.appendChild(option);
            } else {
                Object.entries(data.doses).forEach(([key, value]) => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = value;
                    doseSelect.appendChild(option);
                    console.log('Added dose option:', key, value);
                });
            }
        } else {
            console.error('Invalid response format or no doses available:', data);
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Error loading doses';
            option.disabled = true;
            doseSelect.appendChild(option);
        }
    } catch (error) {
        console.error('Error loading doses:', error);
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Error loading doses';
        option.disabled = true;
        doseSelect.appendChild(option);
    }
}

// Form validation before submission
document.getElementById('immunizationForm').addEventListener('submit', function(e) {
    // No stock validation needed since vaccines are not stored at barangay health center
    console.log('Immunization form submitted');
});
</script>