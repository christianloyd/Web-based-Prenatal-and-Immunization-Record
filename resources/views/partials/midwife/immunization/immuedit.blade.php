<!-- Edit Immunization Modal -->
<div id="editImmunizationModal" 
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeEditModal(event)">
    
    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
         onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-edit text-[#68727A] mr-2"></i>
                Edit Immunization Record
            </h2>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="editImmunizationForm" action="" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            <input type="hidden" id="editImmunizationId" name="id">
            
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
                            <select id="editChildRecordId" name="child_record_id" required 
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg">
                                <option value="">Choose a child...</option>
                                @foreach($childRecords as $child)
                                    <option value="{{ $child->id }}">{{ $child->formatted_child_id ?? 'CH-' . str_pad($child->id, 3, '0', STR_PAD_LEFT) }} - {{ $child->full_name }}</option>
                                @endforeach
                            </select>
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
                            <select id="editVaccineId" name="vaccine_id" required 
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg"
                                    onchange="updateEditVaccineInfo()">
                                <option value="">Choose a vaccine...</option>
                                @foreach($availableVaccines as $vaccine)
                                    <option value="{{ $vaccine->id }}"
                                            data-category="{{ $vaccine->category }}">
                                        {{ $vaccine->name }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Vaccine Info Display -->
                            <div id="editVaccineInfo" class="hidden mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-center">
                                    <div>
                                        <span class="text-xs px-2 py-1 rounded-full" id="editVaccineCategory"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dose *</label>
                            <select id="editDose" name="dose" required 
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg">
                                <option value="">Select dose...</option>
                                @foreach(\App\Models\Immunization::getDoseOptions() as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
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
                            <input type="date" id="editScheduleDate" name="schedule_date" required 
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Time *</label>
                            <input type="time" id="editScheduleTime" name="schedule_time" required min="05:00" max="16:59"
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- Status and Additional Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Status & Notes</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select id="editStatus" name="status" required
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg"
                                    onchange="toggleFieldsBasedOnStatus()">
                                <option value="Upcoming">Upcoming</option>
                                <option value="Done">Done</option>
                                <option value="Missed">Missed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes *</label>
                            <textarea id="editNotes" name="notes" rows="4" required
                                      class="form-input input-clean w-full px-4 py-2.5 rounded-lg resize-none"
                                      placeholder="Any special instructions or notes..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                <button type="button" onclick="closeEditModal()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                    Cancel
                </button>
                <button type="submit" class="btn-minimal btn-primary-clean px-6 py-2.5 rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i>Update Record
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Function to update vaccine information display in edit modal
function updateEditVaccineInfo() {
    const vaccineSelect = document.getElementById('editVaccineId');
    const vaccineInfo = document.getElementById('editVaccineInfo');
    const vaccineCategory = document.getElementById('editVaccineCategory');

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

// Helper function to get category styling class (if not already defined)
function getCategoryClass(category) {
    const classes = {
        'Routine Immunization': 'text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800',
        'COVID-19': 'text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-800',
        'Seasonal': 'text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-800',
        'Travel': 'text-xs px-2 py-1 rounded-full bg-teal-100 text-teal-800'
    };
    return classes[category] || 'text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-800';
}

// Toggle field states based on status
function toggleFieldsBasedOnStatus() {
    const status = document.getElementById('editStatus').value;
    const isDone = (status === 'Done');

    console.log('Toggling fields for status:', status, 'isDone:', isDone);

    // List of fields that should be read-only when status is "Done"
    const fieldsToToggle = [
        'editChildRecordId',
        'editVaccineId',
        'editDose',
        'editScheduleDate',
        'editScheduleTime',
        'editNotes'
    ];

    // When changing to "Done", ensure all fields have values
    if (isDone) {
        let hasEmptyFields = false;
        fieldsToToggle.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.value.trim()) {
                console.warn(`Field ${fieldId} is empty when trying to mark as Done`);
                hasEmptyFields = true;
            }
        });

        if (hasEmptyFields) {
            console.warn('Some required fields are empty. Status change to Done may fail validation.');
        }
    }

    fieldsToToggle.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            if (isDone) {
                // Make field read-only for "Done" status - DON'T use disabled!
                if (field.tagName.toLowerCase() === 'select') {
                    // For select fields, use pointer-events (handled separately below)
                    field.removeAttribute('readonly'); // Select doesn't support readonly
                } else {
                    // For input/textarea fields, use readonly
                    field.setAttribute('readonly', true);
                }
                // Don't use disabled - it prevents form submission
                field.classList.add('bg-gray-100', 'cursor-not-allowed');
                field.classList.remove('focus:ring-2', 'focus:ring-blue-500');
            } else {
                // Make field editable for "Upcoming" status
                field.removeAttribute('readonly');
                // Make sure disabled is removed too
                field.removeAttribute('disabled');
                field.classList.remove('bg-gray-100', 'cursor-not-allowed');
                field.classList.add('focus:ring-2', 'focus:ring-blue-500');
            }
        }
    });

    // Special handling for select fields (they don't support readonly)
    const selectFields = ['editChildRecordId', 'editVaccineId', 'editDose'];
    selectFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            if (isDone) {
                field.style.pointerEvents = 'none';
                field.style.backgroundColor = '#f3f4f6';
            } else {
                field.style.pointerEvents = '';
                field.style.backgroundColor = '';
            }
        }
    });

    // Update form styling to indicate read-only state
    const form = document.getElementById('editImmunizationForm');
    if (form) {
        if (isDone) {
            form.setAttribute('data-readonly', 'true');
        } else {
            form.removeAttribute('data-readonly');
        }
    }
}

// Form validation before submission
const editForm = document.getElementById('editImmunizationForm');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        console.log('Edit form submitted');

        const statusField = document.getElementById('editStatus');
        const currentStatus = statusField ? statusField.value : '';

        console.log('Current status:', currentStatus);

        // If status is "Done", skip client-side validation for readonly fields
        if (currentStatus === 'Done') {
            console.log('Status is Done, allowing form submission without client-side validation');
            return true;
        }

        // For other statuses, perform normal validation
        const vaccineSelect = document.getElementById('editVaccineId');

        // No stock validation needed since vaccines are not stored at barangay health center
        console.log('Edit immunization form submitted');

        console.log('Form validation passed');
    });
}
</script>