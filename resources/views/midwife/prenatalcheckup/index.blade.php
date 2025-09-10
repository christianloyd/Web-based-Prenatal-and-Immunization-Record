@extends('layout.midwife')
@section('title', 'Prenatal Checkup Records')
@section('page-title', 'Prenatal Checkup Records')
@section('page-subtitle', 'List of all prenatal checkup records')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
        font-family: 'Inter', sans-serif;
    }
    
    .btn-hover {
        transition: all 0.2s ease;
    }
    
    .btn-hover:hover {
        transform: translateY(-1px);
    }
    
    .input-focus {
        transition: all 0.2s ease;
    }
    
    .input-focus:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-overdue { background-color: #fee2e2; color: #991b1b; }
    .status-upcoming { background-color: #fef3c7; color: #92400e; }
    .status-completed { background-color: #d1fae5; color: #065f46; }
    .status-scheduled { background-color: #dbeafe; color: #1e40af; }
    .status-no_checkups { background-color: #f3f4f6; color: #374151; }
    
    .modal-backdrop {
        backdrop-filter: blur(4px);
    }
    
    table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    th {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
    }
    
    td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    tr:hover {
        background-color: #f8fafc;
    }

    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .alert-success {
        background-color: #d1fae5;
        border: 1px solid #10b981;
        color: #065f46;
    }

    .alert-error {
        background-color: #fee2e2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle mr-2"></i>
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle mr-2"></i>
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-error">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Patient List -->
<div class="bg-white rounded-lg border shadow-sm">
    <div class="border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Patient List</h3>
            <div class="flex space-x-3">
                <input type="text" id="searchInput" placeholder="Search patients..." 
                       class="input-focus px-3 py-2 border border-gray-300 rounded-lg text-sm w-64"
                       onkeyup="searchPatients()">
                <select id="statusFilter" class="input-focus px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        onchange="filterPatients()">
                    <option value="">All Statuses</option>
                    <option value="no_checkups">No Checkups</option>
                    <option value="completed">On Track</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="overdue">Overdue</option>
                    <option value="scheduled">Scheduled</option>
                </select>
                <button onclick="openCheckupModal()" class="btn-hover bg-green-600 text-white px-4 py-2 rounded-lg font-medium">
                    <i class="fas fa-stethoscope mr-2"></i>Add Checkup
                </button>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Age</th>
                    <th>Weeks Pregnant</th>
                    <th>Last Checkup</th>
                    <th>Next Visit</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="patientTableBody">
                @forelse($patients as $patient)
                <tr class="patient-row" data-status="{{ $patient->checkup_status }}">
                    <td>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-pregnant text-pink-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 patient-name">{{ $patient->name }}</p>
                                <!--<p class="text-sm text-gray-600 patient-id">ID: {{ $patient->formatted_patient_id }}</p>-->
                            </div>
                        </div>
                    </td>
                    <td class="text-gray-800">{{ $patient->age }}</td>
                    <td class="text-gray-800">{{ $patient->weeks_pregnant_from_record ?? 'N/A' }}</td>
                    <td class="text-gray-600">
                        @if($patient->latestCheckup)
                            {{ $patient->latestCheckup->checkup_date->format('M d, Y') }}
                        @else
                            No checkups yet
                        @endif
                    </td>
                    <td class="text-gray-600">
                        @php
                            $nextVisit = $patient->nextVisitFromCheckups();
                        @endphp
                        @if($nextVisit)
                            {{ \Carbon\Carbon::parse($nextVisit->next_visit_date)->format('M d, Y') }}
                        @else
                            Not scheduled
                        @endif
                    </td>
                    <td>
                        @php
                            $status = $patient->checkup_status;
                        @endphp
                        <span class="status-badge status-{{ $status }}">
                            @switch($status)
                                @case('overdue')
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Overdue
                                    @break
                                @case('upcoming')
                                    <i class="fas fa-clock mr-1"></i>Upcoming
                                    @break
                                @case('completed')
                                    <i class="fas fa-check mr-1"></i>On Track
                                    @break
                                @case('no_checkups')
                                    <i class="fas fa-info mr-1"></i>No Checkups
                                    @break
                                @default
                                    <i class="fas fa-calendar mr-1"></i>Scheduled
                            @endswitch
                        </span>
                    </td>
                    <td>
                        <div class="flex space-x-2">
                            <a href="{{ route('midwife.prenatalcheckup.patient', $patient) }}" 
                               class="btn-hover text-blue-600 hover:text-blue-700 text-sm" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button onclick="addCheckupForPatient({{ $patient->id }}, '{{ $patient->name }}')" 
                                    class="btn-hover text-purple-600 hover:text-purple-700 text-sm" title="Add Checkup">
                                <i class=""></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-8 text-gray-500">
                        <i class="fas fa-users text-3xl mb-2"></i>
                        <p>No patients with active prenatal records found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Checkup Modal -->
<div id="checkupModal" class="hidden fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-stethoscope mr-2 text-blue-600"></i>
                    New Prenatal Checkup
                </h2>
                <button onclick="closeCheckupModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('midwife.prenatalcheckup.store') }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Basic Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Patient *</label>
                                <select name="patient_id" id="patient_select" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                                    <option value="">Choose a patient...</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }}  - Age {{ $patient->age }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                            <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                            <input type="date" name="checkup_date" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                value="{{ date('Y-m-d') }}" 
                                min="{{ date('Y-m-d') }}" 
                                max="{{ date('Y-m-d') }}" 
                                required readonly>
                        </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                                    <input type="time" name="checkup_time" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                           value="{{ old('checkup_time', date('H:i')) }}" required>
                                </div>
                            </div>
                            <!--<div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weeks Pregnant</label>
                                <input type="text" name="weeks_pregnant" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                       value="{{ old('weeks_pregnant') }}" placeholder="e.g., 24 weeks">
                            </div>-->
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-heartbeat mr-2 text-red-600"></i>Basic Measurements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure</label>
                                <div class="flex space-x-2">
                                    <input type="number" name="bp_high" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                           value="{{ old('bp_high') }}" placeholder="120" min="50" max="300">
                                    <span class="flex items-center text-gray-500">/</span>
                                    <input type="number" name="bp_low" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                           value="{{ old('bp_low') }}" placeholder="80" min="30" max="200">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                       value="{{ old('weight') }}" placeholder="68.5" min="30" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Baby's Heartbeat (bpm)</label>
                                <input type="number" name="baby_heartbeat" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                       value="{{ old('baby_heartbeat') }}" placeholder="140" min="100" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Belly Size (cm)</label>
                                <input type="number" step="0.1" name="belly_size" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                       value="{{ old('belly_size') }}" placeholder="24" min="0" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Health Check -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user-md mr-2 text-green-600"></i>Health Check
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Baby Movement</label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="baby_movement" value="active" class="text-blue-600" 
                                               {{ old('baby_movement') == 'active' ? 'checked' : '' }}>
                                        <span class="text-sm">Active</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="baby_movement" value="normal" class="text-blue-600"
                                               {{ old('baby_movement') == 'normal' ? 'checked' : '' }}>
                                        <span class="text-sm">Normal</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="radio" name="baby_movement" value="less" class="text-blue-600"
                                               {{ old('baby_movement') == 'less' ? 'checked' : '' }}>
                                        <span class="text-sm">Less than usual</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Any Swelling?</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @php
                                        $oldSwelling = old('swelling', []);
                                    @endphp
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="swelling[]" value="feet" class="text-blue-600"
                                               {{ in_array('feet', $oldSwelling) ? 'checked' : '' }}>
                                        <span class="text-sm">Feet</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="swelling[]" value="hands" class="text-blue-600"
                                               {{ in_array('hands', $oldSwelling) ? 'checked' : '' }}>
                                        <span class="text-sm">Hands</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="swelling[]" value="face" class="text-blue-600"
                                               {{ in_array('face', $oldSwelling) ? 'checked' : '' }}>
                                        <span class="text-sm">Face</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="swelling[]" value="none" class="text-blue-600" onchange="toggleNoneSwelling(this)"
                                               {{ in_array('none', $oldSwelling) ? 'checked' : '' }}>
                                        <span class="text-sm">None</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes and Next Visit -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-clipboard mr-2 text-purple-600"></i>Notes & Next Visit
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Health Notes</label>
                                <textarea name="notes" rows="3" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" 
                                          placeholder="Any concerns, advice, or observations...">{{ old('notes') }}</textarea>
                            </div>
                            
                            <div class="border-t pt-4">
                            <div class="flex items-center space-x-3 mb-4">
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                <button type="button" onclick="closeCheckupModal()" class="btn-hover px-6 py-2 border border-gray-300 rounded-lg text-gray-700">
                    Cancel
                </button>
                <button type="submit" class="btn-hover bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Save Checkup
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal functions
    function openCheckupModal() {
        document.getElementById('checkupModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCheckupModal() {
        document.getElementById('checkupModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Add checkup for specific patient
    function addCheckupForPatient(patientId, patientName) {
        openCheckupModal();
        document.getElementById('patient_select').value = patientId;
    }

    // Toggle next visit fields
    function toggleNextVisit() {
        const checkbox = document.getElementById('scheduleNext');
        const fields = document.getElementById('nextVisitFields');
        
        if (checkbox.checked) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
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
@endsection