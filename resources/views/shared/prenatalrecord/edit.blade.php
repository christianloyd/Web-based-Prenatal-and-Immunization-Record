@extends('layout.' . auth()->user()->role)
@section('title', 'Edit Prenatal Record')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --primary: #D4A373;
        --secondary: #ecb99e;
        --text-dark: #3d2a1b;
    }

    * {
        font-family: 'Inter', sans-serif;
    }

    .section-header {
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .form-input {
        transition: all 0.2s ease;
    }

    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(36, 59, 85, 0.15);
        border-color: var(--primary);
        outline: none;
    }

    .btn-primary-clean {
        background-color: var(--secondary);
        color: var(--text-dark);
    }

    .btn-primary-clean:hover {
        background-color: var(--primary);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-4">
        <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="hover:text-primary">
            <i class="fas fa-home"></i> Home
        </a>
        <span>/</span>
        <a href="{{ route(auth()->user()->role . '.prenatalrecord.index') }}" class="hover:text-primary">
            Prenatal Records
        </a>
        <span>/</span>
        <a href="{{ route(auth()->user()->role . '.prenatalrecord.show', $prenatal->id) }}" class="hover:text-primary">
            {{ $prenatal->patient->name ?? 'Record' }}
        </a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Edit</span>
    </nav>

    <!-- Page Header -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-edit text-primary mr-3"></i>
                    Edit Prenatal Record
                </h1>
                <p class="text-gray-600 mt-1">Update pregnancy information for {{ $prenatal->patient->name ?? 'patient' }}</p>
            </div>
            <a href="{{ route(auth()->user()->role . '.prenatalrecord.show', $prenatal->id) }}"
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Record
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @include('components.flowbite-alert')

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            <div class="font-medium flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Please correct the following errors:
            </div>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errors->all() as $error)
                    <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Form -->
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
        <form action="{{ route(auth()->user()->role . '.prenatalrecord.update', $prenatal->id) }}"
              method="POST"
              class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="patient_id" value="{{ $prenatal->patient_id }}">

            <!-- Patient Information (Read-only) -->
            <div>
                <div class="section-header">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <i class="fas fa-user text-primary mr-2"></i>
                        Patient Information
                    </h3>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                            <div class="text-sm font-semibold text-gray-900">{{ $prenatal->patient->name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Patient ID</label>
                            <div class="text-sm text-gray-700">{{ $prenatal->patient->formatted_patient_id ?? 'PT-' . str_pad($prenatal->patient_id, 3, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Age</label>
                            <div class="text-sm text-gray-700">{{ $prenatal->patient->age ?? 'N/A' }} years old</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- LEFT COLUMN -->
                <div class="space-y-6">
                    <!-- Pregnancy Information -->
                    <div>
                        <div class="section-header">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                Pregnancy Information
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Menstrual Period *</label>
                                <input type="date" name="last_menstrual_period" required
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       value="{{ old('last_menstrual_period', $prenatal->last_menstrual_period?->format('Y-m-d')) }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Expected Due Date</label>
                                <input type="date" name="expected_due_date"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       value="{{ old('expected_due_date', $prenatal->expected_due_date?->format('Y-m-d')) }}">
                                <p class="text-xs text-gray-500 mt-1">Auto-calculated if left blank</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status"
                                        class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300">
                                    <option value="normal" {{ old('status', $prenatal->status) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="monitor" {{ old('status', $prenatal->status) == 'monitor' ? 'selected' : '' }}>Monitor</option>
                                    <option value="high-risk" {{ old('status', $prenatal->status) == 'high-risk' ? 'selected' : '' }}>High Risk</option>
                                    <option value="due" {{ old('status', $prenatal->status) == 'due' ? 'selected' : '' }}>Appointment Due</option>
                                    <option value="completed" {{ old('status', $prenatal->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Use 'Completed' to mark pregnancy as finished</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Gravida</label>
                                    <select name="gravida"
                                            class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300">
                                        @foreach($gravida_options as $value => $label)
                                            <option value="{{ $value }}" {{ old('gravida', $prenatal->gravida) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Para</label>
                                    <select name="para"
                                            class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300">
                                        @foreach($para_options as $value => $label)
                                            <option value="{{ $value }}" {{ old('para', $prenatal->para) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="space-y-6">
                    <!-- Health Information -->
                    <div>
                        <div class="section-header">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                                <i class="fas fa-heartbeat text-primary mr-2"></i>
                                Health Information
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                                <select name="blood_type"
                                        class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300">
                                    <option value="">Select blood type</option>
                                    <option value="A+" {{ old('blood_type', $prenatal->blood_type) == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('blood_type', $prenatal->blood_type) == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('blood_type', $prenatal->blood_type) == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('blood_type', $prenatal->blood_type) == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('blood_type', $prenatal->blood_type) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('blood_type', $prenatal->blood_type) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('blood_type', $prenatal->blood_type) == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('blood_type', $prenatal->blood_type) == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Height (cm)</label>
                                <input type="number" name="height_cm" step="0.1" min="0" max="300"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="e.g., 160.5"
                                       value="{{ old('height_cm', $prenatal->height_cm) }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pre-pregnancy Weight (kg)</label>
                                <input type="number" name="pre_pregnancy_weight" step="0.1" min="0" max="300"
                                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300"
                                       placeholder="e.g., 55.5"
                                       value="{{ old('pre_pregnancy_weight', $prenatal->pre_pregnancy_weight) }}">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Medical History</label>
                                <textarea name="medical_history" rows="4"
                                          class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 resize-none"
                                          placeholder="Previous conditions, surgeries, medications, etc.">{{ old('medical_history', $prenatal->medical_history) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea name="notes" rows="3"
                                          class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 resize-none"
                                          placeholder="Additional observations or notes">{{ old('notes', $prenatal->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route(auth()->user()->role . '.prenatalrecord.show', $prenatal->id) }}"
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" id="submit-btn"
                        class="btn-primary-clean px-6 py-3 rounded-lg font-medium transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i>Update Record
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Form submission with loading state
document.querySelector('form').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
});
</script>
@endpush
@endsection
