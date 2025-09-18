@extends('layout.midwife')
@section('title', 'Patient Details - ' . $prenatalRecord->patient->name)
@section('page-title', 'Patient Prenatal Record Details')
@section('page-subtitle', $prenatalRecord->patient->name . ' - Complete Prenatal Record & Checkup History')

@push('styles')
<style>
    /* Compact Button Styles */
    .btn-action {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
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

    .btn-success {
        background-color: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }

    .btn-success:hover {
        background-color: #10b981;
        color: white;
        border-color: #10b981;
    }

    /* Compact Status Badge Styles */
    .status-normal {
        background-color: #10b981;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 500;
    }

    .status-monitor {
        background-color: #f59e0b;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 500;
    }

    .status-high-risk {
        background-color: #ef4444;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 500;
    }


    /* Compact Cards */
    .compact-card {
        padding: 8px 12px;
        margin-bottom: 8px;
    }

    /* Small Icon Size */
    .icon-sm {
        width: 8px;
        height: 8px;
    }
</style>
@endpush

@section('content')

@include('components.flowbite-alert')

<div class="space-y-2">
    <!-- Back Button -->
    <div class="mb-2">
        <a href="{{ route('midwife.prenatalrecord.index') }}" class="btn-action btn-view inline-flex items-center">
            <i class="fas fa-arrow-left mr-1"></i>
            <span class="hidden sm:inline">Back to Records</span>
        </a>
    </div>

    <!-- Patient Header Card -->
    <div class="bg-white rounded shadow-sm border">
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-pregnant text-blue-600 text-xs"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-semibold text-gray-900">{{ $prenatalRecord->patient->name }}</h1>
                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                            <span><i class="fas fa-id-card mr-1"></i>{{ $prenatalRecord->patient->formatted_patient_id }}</span>
                            <span><i class="fas fa-birthday-cake mr-1"></i>{{ $prenatalRecord->patient->age }}y</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <span class="status-{{ $prenatalRecord->status }}">{{ $prenatalRecord->status_text }}</span>
                    @if($prenatalRecord->expected_due_date)
                        <p class="text-xs text-gray-500 mt-1">EDD: {{ $prenatalRecord->expected_due_date->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Prenatal Record Details -->
        <div class="px-4 py-3">
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-clipboard-list mr-1"></i>Checkups
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ $prenatalRecord->patient->prenatalCheckups->count() }}</p>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-calendar-check mr-1"></i>Last
                    </div>
                    <p class="text-sm font-semibold text-gray-900">
                        @if($prenatalRecord->patient->latestCheckup)
                            {{ $prenatalRecord->patient->latestCheckup->checkup_date->format('M d') }}
                        @else
                            None
                        @endif
                    </p>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-clock mr-1"></i>Next
                    </div>
                    <p class="text-sm font-semibold text-gray-900">
                        @php
                            $nextVisit = $prenatalRecord->patient->nextVisitFromCheckups();
                        @endphp
                        @if($nextVisit)
                            {{ \Carbon\Carbon::parse($nextVisit->next_visit_date)->format('M d') }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-baby mr-1"></i>GA
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ $prenatalRecord->gestational_age ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Record Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <div>
                    <h4 class="text-xs font-medium text-gray-700 mb-2">Pregnancy Details</h4>
                    <div class="space-y-1 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>LMP:</span>
                            <span>{{ $prenatalRecord->last_menstrual_period ? $prenatalRecord->last_menstrual_period->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Trimester:</span>
                            <span>{{ $prenatalRecord->trimester }}{{ $prenatalRecord->trimester == 1 ? 'st' : ($prenatalRecord->trimester == 2 ? 'nd' : 'rd') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>G/P:</span>
                            <span>G{{ $prenatalRecord->gravida ?? '0' }}/P{{ $prenatalRecord->para ?? '0' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-medium text-gray-700 mb-2">Physical Info</h4>
                    <div class="space-y-1 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>BP:</span>
                            <span>{{ $prenatalRecord->blood_pressure ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Weight:</span>
                            <span>{{ $prenatalRecord->weight ? $prenatalRecord->weight . 'kg' : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Height:</span>
                            <span>{{ $prenatalRecord->height ? $prenatalRecord->height . 'cm' : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-medium text-gray-700 mb-2">Medical History</h4>
                    <div class="text-xs text-gray-600">
                        <p class="truncate">{{ $prenatalRecord->medical_history ?? 'None recorded' }}</p>
                    </div>
                </div>
            </div>

            @if($prenatalRecord->notes)
            <div class="bg-blue-50 rounded p-2 mb-3">
                <h4 class="text-xs font-medium text-gray-700 mb-1">Notes</h4>
                <p class="text-xs text-gray-600">{{ $prenatalRecord->notes }}</p>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-1">
                <a href="{{ route('midwife.prenatalcheckup.index') }}" class="btn-action btn-success inline-flex items-center">
                    <i class="fas fa-stethoscope mr-1"></i>
                    <span class="hidden sm:inline">Schedule</span>
                </a>
                <button onclick="openEditModal()" class="btn-action btn-edit inline-flex items-center">
                    <i class="fas fa-edit mr-1"></i>
                    <span class="hidden sm:inline">Edit</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Checkup History -->
    <div class="bg-white rounded shadow-sm border">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                <i class="fas fa-history mr-1 text-gray-600"></i>
                Checkup History
            </h3>
        </div>

        @if($prenatalRecord->patient->prenatalCheckups->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weeks</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">BP</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Baby HR</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Belly</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Movement</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($prenatalRecord->patient->prenatalCheckups as $index => $checkup)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                        {{ $checkup->checkup_date->format('M d, Y') }}
                                        @if($index === 0)
                                            <span class="ml-2 px-1 py-0.5 bg-green-100 text-green-800 text-xs rounded">Latest</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <i class="fas fa-clock text-gray-500 mr-1"></i>
                                    {{ \Carbon\Carbon::parse($checkup->checkup_time)->format('h:i A') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($checkup->weeks_pregnant)
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                            {{ $checkup->weeks_pregnant }}w
                                        </span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($checkup->bp_high && $checkup->bp_low)
                                        {{ $checkup->bp_high }}/{{ $checkup->bp_low }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($checkup->weight)
                                        {{ $checkup->weight }}kg
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($checkup->baby_heartbeat)
                                        {{ $checkup->baby_heartbeat }}bpm
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($checkup->belly_size)
                                        {{ $checkup->belly_size }}cm
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($checkup->baby_movement)
                                        <span class="capitalize">{{ $checkup->baby_movement }}</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-900">
                                    @if($checkup->notes)
                                        <div class="max-w-xs">
                                            <p class="truncate" title="{{ $checkup->notes }}">{{ $checkup->notes }}</p>
                                        </div>
                                    @else
                                        <span class="text-gray-400">No notes</span>
                                    @endif

                                    @if($checkup->next_visit_date)
                                        <div class="mt-1 text-xs text-yellow-600">
                                            <i class="fas fa-calendar-plus mr-1"></i>
                                            Next: {{ \Carbon\Carbon::parse($checkup->next_visit_date)->format('M d, Y') }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6">
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-clipboard-list text-xl mb-2 text-gray-400"></i>
                    <p class="text-xs">No checkups recorded yet.</p>
                    <p class="text-xs text-gray-400">Schedule the first checkup for this patient.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection