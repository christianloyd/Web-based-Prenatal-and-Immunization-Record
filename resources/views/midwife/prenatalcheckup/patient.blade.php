@extends('layout.midwife')
@section('title', 'Patient Details - ' . $patient->name)
@section('page-title', 'Patient Prenatal Records')
@section('page-subtitle', $patient->name . ' - Prenatal Checkup History')

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

    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .stat-card {
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .checkup-timeline {
        position: relative;
    }

    .checkup-timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #3b82f6, #10b981);
    }

    .timeline-item {
        position: relative;
        padding-left: 60px;
        margin-bottom: 2rem;
    }

    .timeline-dot {
        position: absolute;
        left: 12px;
        top: 8px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #3b82f6;
        border: 4px solid white;
        box-shadow: 0 0 0 2px #3b82f6;
    }

    .timeline-dot.latest {
        background: #10b981;
        box-shadow: 0 0 0 2px #10b981;
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

<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('midwife.prenatalcheckup.index') }}" class="btn-hover inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Patient List
    </a>
</div>

<!-- Patient Information Card -->
<div class="bg-white rounded-lg shadow-sm border mb-6">
    <div class="info-card rounded-t-lg px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-pregnant text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $patient->name }}</h1>
                    <div class="flex items-center space-x-4 text-white text-opacity-90 mt-1">
                        <span><i class="fas fa-id-card mr-1"></i>{{ $patient->formatted_patient_id }}</span>
                        <span><i class="fas fa-birthday-cake mr-1"></i>{{ $patient->age }} years old</span>
                        @if($patient->activePrenatalRecord)
                            <span><i class="fas fa-calendar-alt mr-1"></i>EDD: {{ $patient->activePrenatalRecord->expected_due_date ? $patient->activePrenatalRecord->expected_due_date->format('M d, Y') : 'Not set' }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right">
                @php
                    $status = $patient->checkup_status;
                @endphp
                <div class="bg-white bg-opacity-20 rounded-lg p-3 text-center">
                    <p class="text-white text-opacity-75 text-sm">Current Status</p>
                    <span class="text-white font-semibold text-lg">
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
                </div>
            </div>
        </div>
    </div>
    
    <div class="px-6 py-4 border-b">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat-card bg-blue-50 rounded-lg p-4 text-center">
                <i class="fas fa-clipboard-list text-2xl text-blue-600 mb-2"></i>
                <p class="text-2xl font-bold text-blue-600">{{ $patient->prenatalCheckups->count() }}</p>
                <p class="text-sm text-gray-600">Total Checkups</p>
            </div>
            <div class="stat-card bg-green-50 rounded-lg p-4 text-center">
                <i class="fas fa-calendar-check text-2xl text-green-600 mb-2"></i>
                <p class="text-2xl font-bold text-green-600">
                    @if($patient->latestCheckup)
                        {{ $patient->latestCheckup->checkup_date->format('M d') }}
                    @else
                        None
                    @endif
                </p>
                <p class="text-sm text-gray-600">Last Checkup</p>
            </div>
            <div class="stat-card bg-purple-50 rounded-lg p-4 text-center">
                <i class="fas fa-clock text-2xl text-purple-600 mb-2"></i>
                <p class="text-2xl font-bold text-purple-600">
                    @php
                        $nextVisit = $patient->nextVisitFromCheckups();
                    @endphp
                    @if($nextVisit)
                        {{ \Carbon\Carbon::parse($nextVisit->next_visit_date)->format('M d') }}
                    @else
                        Not Set
                    @endif
                </p>
                <p class="text-sm text-gray-600">Next Visit</p>
            </div>
            <div class="stat-card bg-pink-50 rounded-lg p-4 text-center">
                <i class="fas fa-baby text-2xl text-pink-600 mb-2"></i>
                <p class="text-2xl font-bold text-pink-600">
                    {{ $patient->weeks_pregnant_from_record ?? 'N/A' }}
                </p>
                <p class="text-sm text-gray-600">Weeks Pregnant</p>
            </div>
        </div>
    </div>

    <div class="px-6 py-4">
        <div class="flex justify-end">
            <button onclick="openCheckupModal()" class="btn-hover bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                <i class="fas fa-plus mr-2"></i>Add New Checkup
            </button>
        </div>
    </div>
</div>

<!-- Checkup History -->
<div class="bg-white rounded-lg shadow-sm border">
    <div class="border-b px-6 py-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-history mr-2 text-blue-600"></i>
            Prenatal Checkup History
        </h3>
    </div>

    <div class="p-6">
        @if($patient->prenatalCheckups->count() > 0)
            <div class="checkup-timeline">
                @foreach($patient->prenatalCheckups as $index => $checkup)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $index === 0 ? 'latest' : '' }}"></div>
                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-800 flex items-center">
                                        <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
                                        {{ $checkup->checkup_date->format('F d, Y') }}
                                        @if($index === 0)
                                            <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Latest</span>
                                        @endif
                                    </h4>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ \Carbon\Carbon::parse($checkup->checkup_time)->format('h:i A') }}
                                        @if($checkup->weeks_pregnant)
                                            | <i class="fas fa-baby mr-1"></i>{{ $checkup->weeks_pregnant }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="viewCheckupDetails({{ $checkup->id }})" class="text-blue-600 hover:text-blue-700 text-sm">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editCheckup({{ $checkup->id }})" class="text-green-600 hover:text-green-700 text-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-3">
                                @if($checkup->blood_pressure)
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                                        <span class="text-gray-700">BP: {{ $checkup->blood_pressure }}</span>
                                    </div>
                                @endif
                                
                                @if($checkup->weight)
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-weight text-blue-500 mr-2"></i>
                                        <span class="text-gray-700">Weight: {{ $checkup->weight }}kg</span>
                                    </div>
                                @endif
                                
                                @if($checkup->baby_heartbeat)
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-heart text-pink-500 mr-2"></i>
                                        <span class="text-gray-700">Baby HR: {{ $checkup->baby_heartbeat }}bpm</span>
                                    </div>
                                @endif
                                
                                @if($checkup->belly_size)
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-expand-arrows-alt text-purple-500 mr-2"></i>
                                        <span class="text-gray-700">Belly: {{ $checkup->belly_size }}cm</span>
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if($checkup->baby_movement)
                                    <div>
                                        <span class="font-medium text-gray-700">Baby Movement:</span>
                                        <span class="ml-1 capitalize">{{ $checkup->baby_movement }}</span>
                                    </div>
                                @endif
                                
                                @if($checkup->swelling)
                                    <div>
                                        <span class="font-medium text-gray-700">Swelling:</span>
                                        <span class="ml-1">{{ $checkup->swelling_text }}</span>
                                    </div>
                                @endif
                            </div>

                            @if($checkup->notes)
                                <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-sticky-note text-blue-600 mr-2"></i>
                                        <strong>Notes:</strong> {{ $checkup->notes }}
                                    </p>
                                </div>
                            @endif

                            @if($checkup->next_visit_date)
                                <div class="mt-3 p-3 bg-yellow-50 rounded-lg">
                                    <p class="text-sm text-gray-700">
                                        <i class="fas fa-calendar-plus text-yellow-600 mr-2"></i>
                                        <strong>Next Visit:</strong> {{ \Carbon\Carbon::parse($checkup->next_visit_date)->format('F d, Y') }}
                                        @if($checkup->next_visit_time)
                                            at {{ \Carbon\Carbon::parse($checkup->next_visit_time)->format('h:i A') }}
                                        @endif
                                        @if($checkup->next_visit_notes)
                                            <br><span class="ml-6">{{ $checkup->next_visit_notes }}</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                <p class="text-lg">No prenatal checkups recorded yet.</p>
                <p class="text-sm">Start by adding the first checkup for this patient.</p>
            </div>
        @endif
    </div>
</div>

<!-- Add Checkup Modal -->
<div id="checkupModal" class="hidden fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[95vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-stethoscope mr-2 text-blue-600"></i>
                    New Prenatal Checkup for {{ $patient->name }}
                </h2>
                <button onclick="closeCheckupModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('midwife.prenatalcheckup.store') }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Basic Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Next Visit Date</label>
                                <input type="date" name="next_visit_date" 
                                    class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                    value="{{ old('next_visit_date') }}"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
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

<!-- Checkup Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-file-medical mr-2 text-blue-600"></i>
                    Checkup Details
                </h2>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div id="detailsContent" class="p-6">
            <!-- Content will be loaded dynamically -->
        </div>
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

    function openDetailsModal() {
        document.getElementById('detailsModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // View checkup details
    function viewCheckupDetails(checkupId) {
        const checkups = @json($patient->prenatalCheckups);
        const checkup = checkups.find(c => c.id === checkupId);
        
        if (!checkup) return;
        
        const detailsContent = document.getElementById('detailsContent');
        
        detailsContent.innerHTML = `
            <div class="space-y-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-800 mb-3">Checkup Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Date:</strong> ${new Date(checkup.checkup_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</div>
                        <div><strong>Time:</strong> ${checkup.checkup_time ? new Date('2000-01-01 ' + checkup.checkup_time).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'}) : 'Not specified'}</div>
                        <div><strong>Weeks Pregnant:</strong> ${checkup.weeks_pregnant || 'Not specified'}</div>
                    </div>
                </div>
                
                <div class="bg-red-50 rounded-lg p-4">
                    <h3 class="font-semibold text-red-800 mb-3">Vital Signs</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div><strong>Blood Pressure:</strong> ${checkup.bp_high && checkup.bp_low ? checkup.bp_high + '/' + checkup.bp_low : 'Not recorded'}</div>
                        <div><strong>Weight:</strong> ${checkup.weight ? checkup.weight + ' kg' : 'Not recorded'}</div>
                        <div><strong>Baby Heartbeat:</strong> ${checkup.baby_heartbeat ? checkup.baby_heartbeat + ' bpm' : 'Not recorded'}</div>
                        <div><strong>Belly Size:</strong> ${checkup.belly_size ? checkup.belly_size + ' cm' : 'Not recorded'}</div>
                    </div>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4">
                    <h3 class="font-semibold text-green-800 mb-3">Health Assessment</h3>
                    <div class="grid grid-cols-1 gap-3 text-sm">
                        <div><strong>Baby Movement:</strong> ${checkup.baby_movement ? checkup.baby_movement.charAt(0).toUpperCase() + checkup.baby_movement.slice(1) : 'Not assessed'}</div>
                        <div><strong>Swelling:</strong> ${checkup.swelling ? (checkup.swelling.includes('none') ? 'None' : checkup.swelling.join(', ').replace(/^\w/, c => c.toUpperCase())) : 'Not assessed'}</div>
                    </div>
                </div>
                
                ${checkup.notes ? `
                <div class="bg-purple-50 rounded-lg p-4">
                    <h3 class="font-semibold text-purple-800 mb-3">Notes</h3>
                    <p class="text-sm text-gray-700">${checkup.notes}</p>
                </div>
                ` : ''}
                
                ${checkup.next_visit_date ? `
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h3 class="font-semibold text-yellow-800 mb-3">Next Visit</h3>
                    <div class="text-sm">
                        <div><strong>Date:</strong> ${new Date(checkup.next_visit_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</div>
                        ${checkup.next_visit_time ? `<div><strong>Time:</strong> ${new Date('2000-01-01 ' + checkup.next_visit_time).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}</div>` : ''}
                        ${checkup.next_visit_notes ? `<div class="mt-2"><strong>Notes:</strong> ${checkup.next_visit_notes}</div>` : ''}
                    </div>
                </div>
                ` : ''}
            </div>
        `;
        
        openDetailsModal();
    }

    // Edit checkup (placeholder function)
    function editCheckup(checkupId) {
        // You can implement edit functionality here
        alert('Edit functionality can be implemented here. Checkup ID: ' + checkupId);
    }

    // Toggle next visit fields
    // Toggle next visit fields
function toggleNextVisit() {
    const checkbox = document.getElementById('scheduleNext');
    const fields = document.getElementById('nextVisitFields');
    
    if (checkbox.checked) {
        fields.classList.remove('hidden');
        // Make next visit date required when scheduling
        const nextVisitDate = document.querySelector('input[name="next_visit_date"]');
        if (nextVisitDate) {
            nextVisitDate.setAttribute('required', 'required');
        }
    } else {
        fields.classList.add('hidden');
        // Remove required attribute when not scheduling
        const nextVisitDate = document.querySelector('input[name="next_visit_date"]');
        if (nextVisitDate) {
            nextVisitDate.removeAttribute('required');
        }
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

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'checkupModal') {
            closeCheckupModal();
        }
        if (e.target.id === 'detailsModal') {
            closeDetailsModal();
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