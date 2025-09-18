@extends('layout.midwife')
@section('title', 'Prenatal Checkups')
@section('page-title', 'Prenatal Checkups')
@section('page-subtitle', 'Manage and monitor prenatal checkup appointments')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --primary: #243b55;
        --secondary: #141e30;
    }

    * {
        font-family: 'Inter', sans-serif;
    }

    /* Modal Animation Styles */
    .modal-overlay {
        transition: opacity 0.3s ease-out;
        z-index: 9999 !important;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.hidden {
        opacity: 0;
        pointer-events: none;
        visibility: hidden;
    }

    .modal-overlay.show {
        opacity: 1;
        pointer-events: auto;
        visibility: visible;
    }

    .modal-content {
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        transform: translateY(-20px) scale(0.95);
        opacity: 0;
        z-index: 10000;
    }

    .modal-overlay.show .modal-content {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Form Input Focus Styles */
    .form-input {
        transition: all 0.2s ease;
    }

    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(36, 59, 85, 0.15);
        border-color: var(--primary);
        outline: none;
    }

    /* Button Styles */
    .btn-primary {
        transition: all 0.2s ease;
        background-color: var(--primary);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(36, 59, 85, 0.3);
        background-color: var(--secondary);
    }

    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }

    .btn-view {
        background-color: #f8fafc;
        color: #475569;
        border-color: #e2e8f0;
    }

    .btn-view:hover {
        background-color: #68727A;
        color: white;
        border-color: #68727A;
    }

    .btn-edit {
        background-color: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    .btn-edit:hover {
        background-color: #f59e0b;
        color: white;
        border-color: #f59e0b;
    }

    .btn-checkup {
        background-color: #d1fae5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .btn-checkup:hover {
        background-color: #10b981;
        color: white;
        border-color: #10b981;
    }

    /* Status Badge Styles */
    .status-done {
        background-color: #10b981;
        color: white;
    }

    .status-upcoming {
        background-color: #dbeafe;
        color: #1d4ed8;
    }

    /* Alert Styles */
    .alert {
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid;
        display: flex;
        align-items: center;
    }

    .alert-success {
        background-color: #d1fae5;
        border-color: #10b981;
        color: #065f46;
    }

    .alert-error {
        background-color: #fee2e2;
        border-color: #ef4444;
        color: #991b1b;
    }

    /* Table Styles */
    .table-container {
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    table {
        border-collapse: separate;
        border-spacing: 0;
    }

    th {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        background-color: white;
    }

    tr:hover td {
        background-color: #f8fafc;
    }

    /* Patient Card Styles */
    .patient-card {
        background-color: white;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .patient-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    

    @if($errors->any())
    <div class="alert alert-error">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
            @foreach($errors->all() as $error)
                <p class="mb-1">{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <div></div>
        <div class="flex space-x-3">
            <button onclick="openCheckupModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-all duration-200 flex items-center btn-primary" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-plus mr-2"></i>
                Add Checkup
            </button>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('midwife.prenatalcheckup.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name"
                               class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input" style="border-color: #e5e7eb;">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <select name="status" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary form-input" style="border-color: #e5e7eb; focus:border-color: var(--primary);">
                        <option value="">All Status</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-all duration-200 btn-primary" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                    <a href="{{ route('midwife.prenatalcheckup.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Prenatal Checkups Table -->
    <div class="bg-white rounded-lg shadow-sm border table-container">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Patient Name</th>
                        <th>Checkup Date</th>
                        <th>Checkup Time</th>
                        <th>Status</th>
                        <th>Next Visit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($checkups as $checkup)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="font-medium text-blue-600">
                        {{ $checkup->prenatalRecord->patient->formatted_patient_id ?? 'N/A' }}
                    </td>
                    <td>
                        <div class="flex items-center space-x-3">
                             
                                
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $checkup->prenatalRecord->patient->name ?? 'N/A' }}</p>
                                    </div>
                        </div>
                    </td>
                    <td class="text-gray-900">
                        <span class="font-medium">{{ $checkup->checkup_date ? $checkup->checkup_date->format('M d, Y') : 'N/A' }}</span>
                    </td>
                    <td class="text-gray-900">
                        <span class="text-sm">{{ $checkup->checkup_time ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-{{ $checkup->status ?? 'upcoming' }}">
                            <i class="fas {{ $checkup->status === 'done' ? 'fa-check' : 'fa-clock' }} mr-1"></i>
                            {{ ucfirst($checkup->status ?? 'Upcoming') }}
                        </span>
                    </td>
                    <td class="text-gray-600">
                        @if($checkup->next_visit_date)
                            {{ \Carbon\Carbon::parse($checkup->next_visit_date)->format('M d, Y') }}
                        @else
                            <span class="text-gray-500">Not scheduled</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex space-x-2">
                            <button onclick="openViewCheckupModal({{ $checkup->id }})"
                                    class="btn-action btn-view inline-flex items-center justify-center" title="View Checkup Details">
                                <i class="fas fa-eye mr-1"></i>
                                <span class="hidden sm:inline">View</span>
                            </button>
                            <button onclick="openScheduleEditModal({{ $checkup->id }})"
                                    class="btn-action btn-edit inline-flex items-center justify-center" title="Edit Schedule">
                                <i class="fas fa-calendar-edit mr-1"></i>
                                <span class="hidden sm:inline">Edit Schedule</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">No prenatal checkups found</p>
                            <p class="text-gray-600 mb-4">Get started by creating your first prenatal checkup</p>
                            <button onclick="openCheckupModal()" class="btn-primary" style="background-color: var(--primary); color: white; padding: 8px 16px; border-radius: 8px; border: none; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                                <i class="fas fa-plus mr-2"></i>
                                Create First Checkup
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Checkup Modal -->
<div id="checkupModal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="closeCheckupModal(event)">
    <div class="modal-content relative w-full max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl my-4 flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-calendar-plus mr-2 text-primary"></i>
                    Complete Today's Checkup & Schedule Next Visit
                </h2>
                <button type="button" onclick="closeCheckupModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <form id="checkupForm" action="{{ route('midwife.prenatalcheckup.store') }}" method="POST" class="p-6">
            @csrf
            <!-- Hidden field for conducted_by -->
            <input type="hidden" name="conducted_by" value="{{ auth()->id() }}">

            <!-- Workflow Instructions -->
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-blue-800">How it works:</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            <strong>Option 1:</strong> Fill only date, time & patient → Creates "Upcoming" checkup<br>
                            <strong>Option 2:</strong> Fill date, time, patient + medical data → Creates "Done" checkup
                        </p>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Left Column -->
                <div class="space-y-4">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Patient *</label>
                                <select name="patient_id" id="patient_select" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                    <option value="">Choose a patient...</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} - Age {{ $patient->age }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                                    <input type="date" name="checkup_date" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        value="{{ date('Y-m-d') }}"
                                        min="{{ date('Y-m-d') }}"
                                        max="{{ date('Y-m-d') }}"
                                        required readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                                    <input type="time" name="checkup_time" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                           value="{{ old('checkup_time', date('H:i')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-heartbeat mr-2 text-red-600"></i>Basic Measurements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure</label>
                                <div class="flex space-x-2">
                                    <input type="number" name="blood_pressure_systolic" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('blood_pressure_systolic') }}" placeholder="120" min="70" max="250">
                                    <span class="flex items-center text-gray-500">/</span>
                                    <input type="number" name="blood_pressure_diastolic" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('blood_pressure_diastolic') }}" placeholder="80" min="40" max="150">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight_kg" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('weight_kg') }}" placeholder="68.5" min="30" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fetal Heart Rate (bpm)</label>
                                <input type="number" name="fetal_heart_rate" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('fetal_heart_rate') }}" placeholder="140" min="100" max="180">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fundal Height (cm)</label>
                                <input type="number" step="0.1" name="fundal_height_cm" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('fundal_height_cm') }}" placeholder="24" min="10" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Health Assessment -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-user-md mr-2 text-green-600"></i>Health Assessment
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Symptoms</label>
                                <textarea name="symptoms" rows="2" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Any symptoms reported by the patient...">{{ old('symptoms') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clinical Notes</label>
                                <textarea name="notes" rows="3" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Clinical observations, recommendations, and notes...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Next Visit -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-calendar mr-2 text-purple-600"></i>Next Visit
                        </h3>
                        <div class="flex items-center space-x-3 mb-3">
                            <input type="checkbox" id="scheduleNext" name="schedule_next" value="1" class="text-blue-600"
                                onchange="toggleNextVisit()" {{ old('schedule_next') ? 'checked' : '' }}>
                            <label for="scheduleNext" class="text-sm font-medium text-gray-700">Schedule next visit</label>
                        </div>

                        <div id="nextVisitFields" class="{{ old('schedule_next') ? '' : 'hidden' }} space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Next Visit Date</label>
                                    <input type="date" name="next_visit_date" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('next_visit_date') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                    <input type="time" name="next_visit_time" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('next_visit_time') }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Notes</label>
                                <textarea name="next_visit_notes" rows="2" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="What to prepare or remember for next visit...">{{ old('next_visit_notes') }}</textarea>
                            </div>
                        </div>

                        <div id="noNextVisitMessage" class="{{ old('schedule_next') ? 'hidden' : '' }}">
                            <div class="bg-gray-100 rounded-lg p-3 text-center">
                                <p class="text-gray-600 text-sm">No next visit will be scheduled.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t bg-white rounded-b-xl">
            <button type="button" onclick="closeCheckupModal()" class="btn-hover px-6 py-2 border border-gray-300 rounded-lg text-gray-700">
                Cancel
            </button>
            <button type="submit" form="checkupForm" class="btn-hover bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition-all duration-200" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-save mr-2"></i>
                Save Checkup
            </button>
        </div>
    </div>
</div>

<script>
    // Modal functions
    function openCheckupModal() {
        const modal = document.getElementById('checkupModal');
        if (!modal) {
            console.error('Checkup modal not found');
            return;
        }

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });
        document.body.style.overflow = 'hidden';
    }

    function closeCheckupModal(e) {
        // Don't close if click is inside modal content
        if (e && e.target !== e.currentTarget) return;

        const modal = document.getElementById('checkupModal');
        if (!modal) return;

        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    // Add checkup for specific patient (removed - no longer needed)

    // View checkup details for patient
    function viewCheckupDetails(patientId) {
        console.log('View checkup details for patient:', patientId);
        // This will be implemented to show existing checkup details
        alert('View checkup functionality - Patient ID: ' + patientId);
    }

    // Edit scheduled checkup
    function editScheduledCheckup(patientId) {
        console.log('Edit scheduled checkup for patient:', patientId);
        alert('Edit scheduled checkup functionality - Patient ID: ' + patientId);
    }

    // Toggle next visit fields
    function toggleNextVisit() {
        const checkbox = document.getElementById('scheduleNext');
        const fields = document.getElementById('nextVisitFields');
        const noMessage = document.getElementById('noNextVisitMessage');

        if (checkbox.checked) {
            fields.classList.remove('hidden');
            if (noMessage) noMessage.classList.add('hidden');
        } else {
            fields.classList.add('hidden');
            if (noMessage) noMessage.classList.remove('hidden');
        }
    }

    // Handle "None" swelling checkbox
    function toggleNoneSwelling(noneCheckbox) {
        const swellingCheckboxes = document.querySelectorAll('input[name="swelling[]"]:not([value="none"])');
        
        if (noneCheckbox.checked) {
            swellingCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }

    // Handle other swelling checkboxes
    document.querySelectorAll('input[name="swelling[]"]:not([value="none"])').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                document.querySelector('input[name="swelling[]"][value="none"]').checked = false;
            }
        });
    });

    // Search patients
    function searchPatients() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.patient-row');
        
        rows.forEach(row => {
            const patientName = row.querySelector('.patient-name').textContent.toLowerCase();
            const patientId = row.querySelector('.patient-id').textContent.toLowerCase();
            
            if (patientName.includes(searchTerm) || patientId.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Filter patients by status
    function filterPatients() {
        const filterValue = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('.patient-row');
        
        rows.forEach(row => {
            if (!filterValue || row.getAttribute('data-status') === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'checkupModal') {
            closeCheckupModal();
        }
    });

    // Form submission with loading state (NO AJAX - just visual feedback)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#checkupModal form');
        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        form.addEventListener('submit', function() {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        });

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });

        // Show modal if there are validation errors
        @if($errors->any())
        openCheckupModal();
        @endif
    });
</script>

<!-- Include Edit, Schedule Edit and View Partials -->
@include('partials.midwife.prenatalcheckup.prenatalcheckupedit')
@include('partials.midwife.prenatalcheckup.schedule_edit')
@include('partials.midwife.prenatalcheckup.prenatalcheckupview')

@endsection