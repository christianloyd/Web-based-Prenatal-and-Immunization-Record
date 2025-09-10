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
        <form id="immunizationForm" action="{{ route('midwife.immunization.store') }}" method="POST" class="space-y-5" novalidate>
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
                                    class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('child_record_id') error-border @enderror">
                                <option value="">Choose a child...</option>
                                @foreach($childRecords as $child)
                                    <option value="{{ $child->id }}" {{ old('child_record_id') == $child->id ? 'selected' : '' }}>
                                        {{ $child->child_name }}
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
                                    onchange="updateVaccineInfo()">
                                <option value="">Choose a vaccine...</option>
                                @foreach($availableVaccines as $vaccine)
                                    <option value="{{ $vaccine->id }}" 
                                            data-stock="{{ $vaccine->current_stock }}"
                                            data-category="{{ $vaccine->category }}"
                                            class="{{ $vaccine->current_stock <= 0 ? 'text-red-500' : ($vaccine->current_stock <= $vaccine->min_stock ? 'text-yellow-600' : 'text-green-600') }}"
                                            {{ $vaccine->current_stock <= 0 ? 'disabled' : '' }}
                                            {{ old('vaccine_id') == $vaccine->id ? 'selected' : '' }}>
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
                            @error('vaccine_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            
                            <!-- Vaccine Info Display -->
                            <div id="vaccineInfo" class="hidden mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm font-medium text-blue-900">Stock Available:</span>
                                        <span id="vaccineStock" class="text-sm font-bold text-blue-700"></span>
                                    </div>
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
                                @foreach(\App\Models\Immunization::getDoseOptions() as $key => $value)
                                    <option value="{{ $key }}" {{ old('dose') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea id="notes" name="notes" rows="4"
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
                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Immunization
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
    const vaccineStock = document.getElementById('vaccineStock');
    const vaccineCategory = document.getElementById('vaccineCategory');
    const lowStockWarning = document.getElementById('lowStockWarning');
    
    if (vaccineSelect.value) {
        const selectedOption = vaccineSelect.options[vaccineSelect.selectedIndex];
        const stock = parseInt(selectedOption.dataset.stock);
        const category = selectedOption.dataset.category;
        
        // Show vaccine info
        vaccineStock.textContent = stock + ' units';
        vaccineCategory.textContent = category;
        vaccineCategory.className = getCategoryClass(category);
        vaccineInfo.classList.remove('hidden');
        
        // Show/hide low stock warning
        if (stock <= 10 && stock > 0) { // Assuming min_stock is typically 10
            lowStockWarning.classList.remove('hidden');
        } else {
            lowStockWarning.classList.add('hidden');
        }
    } else {
        vaccineInfo.classList.add('hidden');
        lowStockWarning.classList.add('hidden');
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

// Form validation before submission
document.getElementById('immunizationForm').addEventListener('submit', function(e) {
    const vaccineSelect = document.getElementById('vaccine_id');
    const selectedOption = vaccineSelect.options[vaccineSelect.selectedIndex];
    
    if (vaccineSelect.value && selectedOption.dataset.stock === '0') {
        e.preventDefault();
        alert('Cannot schedule immunization: Selected vaccine is out of stock.');
        vaccineSelect.focus();
        return false;
    }
});
</script>