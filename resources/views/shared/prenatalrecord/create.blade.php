@extends('layout.' . auth()->user()->role)
@section('title', 'Add Prenatal Record')
@section('page-title', 'Add Prenatal Record')
@section('page-subtitle', 'Create a new prenatal record for a patient')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/' . auth()->user()->role . '/' . auth()->user()->role . '.css') }}">
<link rel="stylesheet" href="{{ asset('css/' . auth()->user()->role . '/prenatalrecord-create.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <a href="{{ route(auth()->user()->role . '.prenatalrecord.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Add New Prenatal Record</h1>
                <p class="text-gray-600">Create a comprehensive prenatal record for a patient</p>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route(auth()->user()->role . '.prenatalrecord.store') }}" method="POST" id="prenatal-form" class="space-y-6">
        @csrf

        <!-- Patient Selection Section -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Patient Selection
            </h3>

            <div class="space-y-4">
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search and Select Patient/Mother *
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="patient-search"
                               placeholder="Type patient name or ID to search..."
                               class="form-input pl-10 pr-10 @error('patient_id') error @enderror"
                               autocomplete="off">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <div id="search-loading" class="hidden">
                                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                            </div>
                            <button type="button" id="clear-search" class="hidden text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Search Dropdown -->
                        <div id="search-dropdown" class="search-dropdown">
                            <!-- Results will be populated here -->
                        </div>
                    </div>

                    <!-- Hidden input for selected patient ID -->
                    <input type="hidden" name="patient_id" id="selected-patient-id" value="{{ old('patient_id') }}">

                    @error('patient_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Selected Patient Display -->
                    <div id="selected-patient-display" class="selected-patient hidden">
                        <div class="flex justify-between items-start">
                            <div class="patient-info">
                                <div class="patient-name" id="selected-patient-name"></div>
                                <div class="patient-details" id="selected-patient-details"></div>
                            </div>
                            <button type="button" id="remove-selection" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 mt-2">
                        Don't see the patient?
                        <a href="{{ route(auth()->user()->role . '.patients.index') }}" class="text-blue-600 hover:text-blue-800 underline" target="_blank">
                            Register a new patient first
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pregnancy Information & Physical Measurements Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pregnancy Information Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-baby"></i>
                    Pregnancy Information
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Menstrual Period *</label>
                        <input type="date"
                               name="last_menstrual_period"
                               id="lmp-input"
                               required
                               class="form-input @error('last_menstrual_period') error @enderror"
                               value="{{ old('last_menstrual_period') }}">
                        @error('last_menstrual_period')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expected Due Date</label>
                        <input type="date"
                               name="expected_due_date"
                               id="edd-input"
                               class="form-input @error('expected_due_date') error @enderror"
                               value="{{ old('expected_due_date') }}">
                        <p class="text-xs text-gray-500 mt-1">Auto-calculated from LMP</p>
                        @error('expected_due_date')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gravida</label>
                            <select name="gravida" class="form-input @error('gravida') error @enderror">
                                <option value="">Select</option>
                                <option value="1" {{ old('gravida') == '1' ? 'selected' : '' }}>G1</option>
                                <option value="2" {{ old('gravida') == '2' ? 'selected' : '' }}>G2</option>
                                <option value="3" {{ old('gravida') == '3' ? 'selected' : '' }}>G3</option>
                                <option value="4" {{ old('gravida') == '4' ? 'selected' : '' }}>G4+</option>
                            </select>
                            @error('gravida')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Para</label>
                            <select name="para" class="form-input @error('para') error @enderror">
                                <option value="">Select</option>
                                <option value="0" {{ old('para') == '0' ? 'selected' : '' }}>P0</option>
                                <option value="1" {{ old('para') == '1' ? 'selected' : '' }}>P1</option>
                                <option value="2" {{ old('para') == '2' ? 'selected' : '' }}>P2</option>
                                <option value="3" {{ old('para') == '3' ? 'selected' : '' }}>P3+</option>
                            </select>
                            @error('para')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Physical Measurements Section -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-weight"></i>
                    Physical Measurements
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure *</label>
                        <input type="text"
                               name="blood_pressure"
                               required
                               placeholder="e.g., 120/80"
                               pattern="\d{2,3}/\d{2,3}"
                               title="Please enter blood pressure in format XXX/XXX (e.g., 120/80)"
                               class="form-input @error('blood_pressure') error @enderror"
                               value="{{ old('blood_pressure') }}">
                        <p class="text-xs text-gray-500 mt-1">Format: XXX/XXX (numbers only, e.g., 120/80)</p>
                        @error('blood_pressure')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg) *</label>
                        <input type="number"
                               name="weight"
                               required
                               step="0.1"
                               min="30"
                               max="200"
                               placeholder="e.g., 65.5"
                               class="form-input @error('weight') error @enderror"
                               value="{{ old('weight') }}">
                        @error('weight')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Height (cm) *</label>
                        <input type="number"
                               name="height"
                               required
                               min="120"
                               max="200"
                               placeholder="e.g., 165"
                               class="form-input @error('height') error @enderror"
                               value="{{ old('height') }}">
                        @error('height')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information Section -->
        <div class="form-section">
            <h3 class="section-title">
                <i class="fas fa-notes-medical"></i>
                Medical Information
            </h3>

            <p class="text-sm text-gray-600 mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Medical history is required. Additional notes are optional.
            </p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Medical History *</label>
                    <textarea name="medical_history"
                              rows="4"
                              required
                              placeholder="Any relevant medical history, previous pregnancies, complications, etc."
                              class="form-input resize-none @error('medical_history') error @enderror">{{ old('medical_history') }}</textarea>
                    @error('medical_history')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Notes</label>
                    <textarea name="notes"
                              rows="3"
                              placeholder="Any additional notes or observations..."
                              class="form-input resize-none @error('notes') error @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 border-t bg-white rounded-lg p-6">
            <a href="{{ route(auth()->user()->role . '.prenatalrecord.index') }}" class="btn-secondary">
                <i class="fas fa-times mr-2"></i>
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i>
                Save Prenatal Record
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- Configuration for prenatal record creation --}}
<script>
    window.PRENATAL_CREATE_CONFIG = {
        searchUrl: '{{ route(auth()->user()->role . ".patients.search") }}'
        @if(old('patient_id'))
        , oldPatientId: '{{ old("patient_id") }}'
        @endif
    };
</script>

<script src="{{ asset('js/' . auth()->user()->role . '/' . auth()->user()->role . '.js') }}"></script>
<script src="{{ asset('js/' . auth()->user()->role . '/prenatalrecord-create.js') }}"></script>
@endpush
