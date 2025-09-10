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
        <form id="editImmunizationForm" action="" method="POST" class="space-y-5" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" id="editImmunizationId" name="id">
            
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
                                    <option value="{{ $child->id }}">{{ $child->child_name }}</option>
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
                                            data-stock="{{ $vaccine->current_stock }}"
                                            data-category="{{ $vaccine->category }}"
                                            class="{{ $vaccine->current_stock <= 0 ? 'text-red-500' : ($vaccine->current_stock <= $vaccine->min_stock ? 'text-yellow-600' : 'text-green-600') }}"
                                            {{ $vaccine->current_stock <= 0 ? 'disabled' : '' }}>
                                        {{ $vaccine->name }} 
                                        @if($vaccine->current_stock <= 0)
                                            (OUT OF STOCK)
                                        @elseif($vaccine->current_stock <= $vaccine->min_stock)
                                            (LOW STOCK: {{ $vaccine->current_stock }})
                                        @else
                                            (Stock: {{ $vaccine->current_stock }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Vaccine Info Display -->
                            <div id="editVaccineInfo" class="hidden mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-blue-900">Stock Available:</span>
                                        <span id="editVaccineStock" class="text-sm font-bold text-blue-700"></span>
                                    </div>
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
                            <input type="time" id="editScheduleTime" name="schedule_time" required 
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
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg">
                                <option value="Upcoming">Upcoming</option>
                                <option value="Done">Done</option>
                                <option value="Missed">Missed</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea id="editNotes" name="notes" rows="4"
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
    const vaccineStock = document.getElementById('editVaccineStock');
    const vaccineCategory = document.getElementById('editVaccineCategory');
    
    if (vaccineSelect.value) {
        const selectedOption = vaccineSelect.options[vaccineSelect.selectedIndex];
        const stock = parseInt(selectedOption.dataset.stock);
        const category = selectedOption.dataset.category;
        
        // Show vaccine info
        vaccineStock.textContent = stock + ' units';
        vaccineCategory.textContent = category;
        vaccineCategory.className = getCategoryClass(category);
        vaccineInfo.classList.remove('hidden');
    } else {
        vaccineInfo.classList.add('hidden');
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

// Form validation before submission
document.getElementById('editImmunizationForm').addEventListener('submit', function(e) {
    const vaccineSelect = document.getElementById('editVaccineId');
    const selectedOption = vaccineSelect.options[vaccineSelect.selectedIndex];
    
    if (vaccineSelect.value && selectedOption.dataset.stock === '0') {
        e.preventDefault();
        alert('Cannot update immunization: Selected vaccine is out of stock.');
        vaccineSelect.focus();
        return false;
    }
});
</script>