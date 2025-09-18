<!-- partials/midwife/vaccine/vaccine_view.blade.php -->
<div id="view-vaccine-modal"
    class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="view-vaccine-modal-title"
    onclick="closeViewVaccineModal(event)">

    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 id="view-vaccine-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                </svg>
                Vaccine Details
            </h3>
            <div class="flex items-center space-x-2">
                <button type="button"
                        onclick="closeViewVaccineModalAndEdit()"
                        class="text-primary hover:text-secondary transition-colors px-3 py-1 rounded-lg hover:bg-blue-50 text-sm font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                    </svg>
                    Edit
                </button>
                <button type="button"
                        onclick="closeViewVaccineModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                        aria-label="Close modal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Vaccine Information -->
            <div class="section-divider">
                <h4 class="text-lg font-medium mb-4 text-gray-800">VACCINE INFORMATION</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Vaccine Name</label>
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <p id="viewVaccineName" class="text-gray-900 font-medium">-</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Category</label>
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <div id="viewVaccineCategory">-</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Dosage (ml)</label>
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <p id="viewVaccineDosage" class="text-gray-900">-</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Number of Doses</label>
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <div id="viewVaccineDoseCount">-</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Storage Temperature</label>
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <p id="viewVaccineStorageTemp" class="text-gray-900">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock & Expiry Information -->
            <div class="section-divider">
                <h4 class="text-lg font-medium mb-4 text-gray-800">EXPIRY INFORMATION</h4>
                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Expiry Date</label>
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <p id="viewVaccineExpiryDate" class="text-gray-900">-</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Date Added</label>
                        <div class="bg-gray-50 border rounded-lg p-3">
                            <p id="viewVaccineCreatedAt" class="text-gray-900">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Section (Full Width) -->
        <div class="mt-6 pt-6 border-t">
            <label class="block text-sm font-medium text-gray-600 mb-2">Notes</label>
            <div class="bg-gray-50 border rounded-lg p-4">
                <p id="viewVaccineNotes" class="text-gray-900 whitespace-pre-wrap">-</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4 pt-6 border-t">
            <button type="button"
                    onclick="closeViewVaccineModalAndEdit()"
                    class="btn-primary flex-1 bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                Edit Vaccine
            </button>
            <button type="button"
                    onclick="closeViewVaccineModal()"
                    class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 font-semibold">
                Close
            </button>
        </div>
    </div>
</div>