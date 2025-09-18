<!-- partials/midwife/vaccine/vaccine_edit.blade.php -->
<div id="edit-vaccine-modal"
    class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-vaccine-modal-title"
    onclick="closeEditVaccineModal(event)">

    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 id="edit-vaccine-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                Edit Vaccine
            </h3>
            <button type="button"
                    onclick="closeEditVaccineModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                    aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('midwife.vaccines.update', ':id') }}" 
            method="POST"
            id="edit-vaccine-form"
            class="space-y-5"
            data-update-url="{{ route('midwife.vaccines.update', ':id') }}"
            novalidate>
            @csrf
            @method('PUT')

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
                            <input type="text" name="name" id="edit-name" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary"
                                placeholder="Enter vaccine name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select name="category" id="edit-category" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary">
                                <option value="">Select Category</option>
                                <option value="Routine Immunization">Routine Immunization</option>
                                <option value="COVID-19">COVID-19</option>
                                <option value="Seasonal">Seasonal</option>
                                <option value="Travel">Travel</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosage (ml) *</label>
                            <input type="text" name="dosage" id="edit-dosage" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary"
                                placeholder="e.g., 0.5, 1.0">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Doses Required *</label>
                            <select name="dose_count" id="edit-dose-count" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary">
                                <option value="">Select Number of Doses</option>
                                <option value="1">1 Dose (Single)</option>
                                <option value="2">2 Doses</option>
                                <option value="3">3 Doses</option>
                                <option value="4">4 Doses</option>
                                <option value="5">5 Doses</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Storage Temperature *</label>
                            <select name="storage_temp" id="edit-storage-temp" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary">
                                <option value="">Select Storage Temperature</option>
                                <option value="2-8°C">2-8°C (Refrigerated)</option>
                                <option value="15-25°C">15-25°C (Room Temperature)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Stock & Expiry Information -->
                <div class="section-divider">
                    <h4 class="text-lg font-medium mb-4 text-gray-800">EXPIRY INFORMATION</h4>
                    <div class="space-y-4">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                            <input type="date" name="expiry_date" id="edit-expiry-date" required
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" id="edit-notes" rows="6"
                                class="form-input w-full border border-gray-300 rounded-lg p-2.5 resize-none focus:ring-2 focus:ring-primary"
                                placeholder="Additional notes or instructions"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-6 border-t">
                <button type="submit"
                        id="edit-submit-btn"
                        class="btn-primary flex-1 bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Update Vaccine
                </button>
                <button type="button"
                        onclick="closeEditVaccineModal()"
                        class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set minimum date for edit expiry date to today
    const editExpiryInput = document.getElementById('edit-expiry-date');
    if (editExpiryInput) {
        const today = new Date().toISOString().split('T')[0];
        editExpiryInput.min = today;
    }
});
</script>