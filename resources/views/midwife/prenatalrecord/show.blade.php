@extends('layout.midwife')
@section('title', 'Patient Details - ' . $prenatalRecord->patient->name)
@section('page-title', 'Patient Prenatal Record Details')
@section('page-subtitle', $prenatalRecord->patient->name . ' - Complete Prenatal Record & Checkup History')

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
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

@if(session('success'))
<div class="alert alert-success">
    <i class="fas fa-check-circle mr-2"></i>
    {{ session('success') }}
</div>
@endif

<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('midwife.prenatalrecord.index') }}" class="btn-hover inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to Prenatal Records
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
                    <h1 class="text-2xl font-bold text-white">{{ $prenatalRecord->patient->name }}</h1>
                    <div class="flex items-center space-x-4 text-white text-opacity-90 mt-1">
                        <span><i class="fas fa-id-card mr-1"></i>{{ $prenatalRecord->patient->formatted_patient_id }}</span>
                        <span><i class="fas fa-birthday-cake mr-1"></i>{{ $prenatalRecord->patient->age }} years old</span>
                        <span><i class="fas fa-calendar-alt mr-1"></i>EDD: {{ $prenatalRecord->expected_due_date ? $prenatalRecord->expected_due_date->format('M d, Y') : 'Not set' }}</span>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-lg p-3 text-center">
                    <p class="text-white text-opacity-75 text-sm">Record Status</p>
                    <span class="text-white font-semibold text-lg">{{ $prenatalRecord->status_text }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Prenatal Record Details -->
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Prenatal Record Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Pregnancy Details</h4>
                <div class="space-y-1 text-sm">
                    <p><span class="font-medium">LMP:</span> {{ $prenatalRecord->last_menstrual_period ? $prenatalRecord->last_menstrual_period->format('M d, Y') : 'N/A' }}</p>
                    <p><span class="font-medium">Gestational Age:</span> {{ $prenatalRecord->gestational_age ?? 'N/A' }}</p>
                    <p><span class="font-medium">Trimester:</span> {{ $prenatalRecord->trimester }}{{ $prenatalRecord->trimester == 1 ? 'st' : ($prenatalRecord->trimester == 2 ? 'nd' : 'rd') }}</p>
                    <p><span class="font-medium">Gravida:</span> G{{ $prenatalRecord->gravida ?? 'N/A' }}</p>
                    <p><span class="font-medium">Para:</span> P{{ $prenatalRecord->para ?? 'N/A' }}</p>
                </div>
            </div>
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Physical Information</h4>
                <div class="space-y-1 text-sm">
                    <p><span class="font-medium">Blood Pressure:</span> {{ $prenatalRecord->blood_pressure ?? 'N/A' }}</p>
                    <p><span class="font-medium">Weight:</span> {{ $prenatalRecord->weight ? $prenatalRecord->weight . ' kg' : 'N/A' }}</p>
                    <p><span class="font-medium">Height:</span> {{ $prenatalRecord->height ? $prenatalRecord->height . ' cm' : 'N/A' }}</p>
                </div>
            </div>
            <div>
                <h4 class="font-medium text-gray-700 mb-2">Medical History</h4>
                <div class="text-sm">
                    <p>{{ $prenatalRecord->medical_history ?? 'No medical history recorded' }}</p>
                </div>
            </div>
        </div>
        @if($prenatalRecord->notes)
        <div class="mt-4">
            <h4 class="font-medium text-gray-700 mb-2">Notes</h4>
            <p class="text-sm text-gray-600">{{ $prenatalRecord->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Statistics -->
    <div class="px-6 py-4 border-b">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="stat-card bg-blue-50 rounded-lg p-4 text-center">
                <i class="fas fa-clipboard-list text-2xl text-blue-600 mb-2"></i>
                <p class="text-2xl font-bold text-blue-600">{{ $prenatalRecord->patient->prenatalCheckups->count() }}</p>
                <p class="text-sm text-gray-600">Total Checkups</p>
            </div>
            <div class="stat-card bg-green-50 rounded-lg p-4 text-center">
                <i class="fas fa-calendar-check text-2xl text-green-600 mb-2"></i>
                <p class="text-2xl font-bold text-green-600">
                    @if($prenatalRecord->patient->latestCheckup)
                        {{ $prenatalRecord->patient->latestCheckup->checkup_date->format('M d') }}
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
                        $nextVisit = $prenatalRecord->patient->nextVisitFromCheckups();
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
                    {{ $prenatalRecord->gestational_age ?? 'N/A' }}
                </p>
                <p class="text-sm text-gray-600">Current Age</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="px-6 py-4">
        <div class="flex justify-end space-x-3">
            <a href="{{ route('midwife.prenatalcheckup.index') }}" class="btn-hover bg-green-600 text-white px-6 py-2 rounded-lg font-medium">
                <i class="fas fa-stethoscope mr-2"></i>Schedule Checkup
            </a>
            <button onclick="openEditModal()" class="btn-hover bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                <i class="fas fa-edit mr-2"></i>Edit Record
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
        @if($prenatalRecord->patient->prenatalCheckups->count() > 0)
            <div class="checkup-timeline">
                @foreach($prenatalRecord->patient->prenatalCheckups as $index => $checkup)
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
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-3">
                                @if($checkup->bp_high && $checkup->bp_low)
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                                        <span class="text-gray-700">BP: {{ $checkup->bp_high }}/{{ $checkup->bp_low }}</span>
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
                                        <span class="ml-1">{{ is_array($checkup->swelling) ? (in_array('none', $checkup->swelling) ? 'None' : implode(', ', $checkup->swelling)) : $checkup->swelling }}</span>
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
                <p class="text-sm">Schedule the first checkup for this patient.</p>
            </div>
        @endif
    </div>
</div>

@endsection