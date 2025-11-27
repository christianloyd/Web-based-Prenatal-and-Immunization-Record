<!-- partials/midwife/vaccine/vaccine_add.blade.php -->
<div id="vaccine-modal"
    class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="add-vaccine-modal-title"
    onclick="closeVaccineModal(event)">

    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 id="add-vaccine-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add New Vaccine
            </h3>
            <button type="button"
                    onclick="closeVaccineModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                    aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('midwife.vaccines.store') }}" 
            method="POST"
            id="vaccine-form"
            class="space-y-5"
            novalidate>
            @csrf

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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Vaccine Information -->
                <div class="section-divider">
                    <h4 class="text-lg font-medium mb-4 text-gray-800">VACCINE INFORMATION</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Vaccine Name *</label>
                            <input type="text" name="name" id="add-name" required value="{{ old('name') }}"
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('name') error-border @enderror"
                                placeholder="Enter vaccine name">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select name="category" id="add-category" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('category') error-border @enderror">
                                <option value="">Select Category</option>
                                <option value="Routine Immunization" {{ old('category') === 'Routine Immunization' ? 'selected' : '' }}>Routine Immunization</option>
                                <option value="COVID-19" {{ old('category') === 'COVID-19' ? 'selected' : '' }}>COVID-19</option>
                                <option value="Seasonal" {{ old('category') === 'Seasonal' ? 'selected' : '' }}>Seasonal</option>
                                <option value="Travel" {{ old('category') === 'Travel' ? 'selected' : '' }}>Travel</option>
                            </select>
                            @error('category')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosage (ml) *</label>
                            <input type="text" name="dosage" id="add-dosage" required value="{{ old('dosage') }}"
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('dosage') error-border @enderror"
                                placeholder="e.g., 0.5, 1.0">
                            @error('dosage')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Doses Required *</label>
                            <select name="dose_count" id="add-dose-count" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('dose_count') error-border @enderror">
                                <option value="">Select Number of Doses</option>
                                <option value="1" {{ old('dose_count') == '1' ? 'selected' : '' }}>1 Dose (Single)</option>
                                <option value="2" {{ old('dose_count') == '2' ? 'selected' : '' }}>2 Doses</option>
                                <option value="3" {{ old('dose_count') == '3' ? 'selected' : '' }}>3 Doses</option>
                                <option value="4" {{ old('dose_count') == '4' ? 'selected' : '' }}>4 Doses</option>
                                <option value="5" {{ old('dose_count') == '5' ? 'selected' : '' }}>5 Doses</option>
                            </select>
                            @error('dose_count')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock *</label>
                                <input type="number" name="initial_stock" id="add-initial-stock" required min="0"
                                    value="{{ old('initial_stock', 0) }}"
                                    class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('initial_stock') error-border @enderror"
                                    placeholder="Units received (e.g., 25)">
                                @error('initial_stock')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Threshold *</label>
                                <input type="number" name="min_stock" id="add-min-stock" required min="0"
                                    value="{{ old('min_stock', 10) }}"
                                    class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('min_stock') error-border @enderror"
                                    placeholder="Alert threshold (e.g., 10)">
                                @error('min_stock')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Storage Temperature *</label>
                            <select name="storage_temp" id="add-storage-temp" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('storage_temp') error-border @enderror">
                                <option value="">Select Storage Temperature</option>
                                <option value="2-8°C" {{ old('storage_temp') === '2-8°C' ? 'selected' : '' }}>2-8°C (Refrigerated)</option>
                                <option value="15-25°C" {{ old('storage_temp') === '15-25°C' ? 'selected' : '' }}>15-25°C (Room Temperature)</option>
                            </select>
                            @error('storage_temp')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Stock & Expiry Information -->
                <div class="section-divider">
                    <h4 class="text-lg font-medium mb-4 text-gray-800">EXPIRY INFORMATION</h4>
                    <div class="space-y-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                            <input type="date" name="expiry_date" id="add-expiry-date" required value="{{ old('expiry_date') }}"
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('expiry_date') error-border @enderror">
                            @error('expiry_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" id="add-notes" rows="4"
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 resize-none focus:ring-2 focus:ring-primary @error('notes') error-border @enderror"
                                placeholder="Additional notes or instructions">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-6 border-t">
                <button type="submit"
                        id="add-submit-btn"
                        class="btn-primary flex-1 bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Save Vaccine
                </button>
                <button type="button"
                        onclick="closeVaccineModal()"
                        class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for expiry date to today
    const expiryInput = document.getElementById('add-expiry-date');
    if (expiryInput) {
        const today = new Date().toISOString().split('T')[0];
        expiryInput.min = today;
    }
});
</script>