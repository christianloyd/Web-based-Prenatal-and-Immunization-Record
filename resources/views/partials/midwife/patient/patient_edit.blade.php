<!-- partials/midwife/patient_edit.blade.php -->
<div id="edit-patient-modal"
    class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="edit-modal-title"
    onclick="closeEditPatientModal(event)">

    <div class="modal-content relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6 my-8"
        onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h3 id="edit-modal-title" class="text-xl font-semibold text-gray-900 flex items-center">
                <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                </svg>
                Edit Patient Information
            </h3>
            <button type="button"
                    onclick="closeEditPatientModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
                    aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="" 
            method="POST"
            id="edit-patient-form"
            data-update-url="{{ route('midwife.patients.update', ':id') }}"
            class="space-y-5"
            novalidate>
            @csrf
            @method('PUT')

            <!-- Show server-side validation errors -->
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

            <!-- Personal Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div class="md:col-span-2 border-b pb-2 mb-2">
                    <h4 class="font-semibold text-gray-800">Personal Information</h4>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" id="edit-name" required value="{{ old('name') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('name') error-border @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Age *</label>
                    <input type="number" name="age" id="edit-age" min="15" max="50" required value="{{ old('age') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('age') error-border @enderror">
                    @error('age')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                    <input type="text" name="occupation" id="edit-occupation" value="{{ old('occupation') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('occupation') error-border @enderror">
                    @error('occupation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Contact</label>
                    <input type="tel" name="contact" id="edit-contact" value="{{ old('contact') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('contact') error-border @enderror">
                    @error('contact')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emergency Contact</label>
                    <input type="tel" name="emergency_contact" id="edit-emergency-contact" value="{{ old('emergency_contact') }}"
                        class="form-input w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-primary @error('emergency_contact') error-border @enderror">
                    @error('emergency_contact')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" id="edit-address" rows="3"
                            class="form-input w-full border border-gray-300 rounded-lg p-2.5 resize-none focus:ring-2 focus:ring-primary @error('address') error-border @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-4 border-t">
                <button type="submit"
                        id="edit-submit-btn"
                        class="btn-primary flex-1 bg-primary text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                        <i class="fas fa-save mr-2"></i>
                    Update Patient
                </button>
                <button type="button"
                        onclick="closeEditPatientModal()"
                        class="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg hover:bg-gray-50 font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>