@extends('layout.midwife')
@section('title', 'Child Record Details - ' . $childRecord->child_name)
@section('page-title', 'Child Record Details')
@section('page-subtitle', $childRecord->child_name . ' - Complete Child Record & Immunization History')

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
    .status-done {
        background-color: #10b981;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 500;
    }

    .status-upcoming {
        background-color: #f59e0b;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 500;
    }

    .status-missed {
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
        <a href="{{ route('midwife.childrecord.index') }}" class="btn-action btn-view inline-flex items-center">
            <i class="fas fa-arrow-left mr-1"></i>
            <span class="hidden sm:inline">Back to Records</span>
        </a>
    </div>

    <!-- Child Header Card -->
    <div class="bg-white rounded shadow-sm border">
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-pink-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-baby text-pink-600 text-xs"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-semibold text-gray-900">{{ $childRecord->child_name }}</h1>
                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                            <span><i class="fas fa-id-card mr-1"></i>{{ $childRecord->formatted_child_id ?? 'CH-' . str_pad($childRecord->id, 3, '0', STR_PAD_LEFT) }}</span>
                            <span><i class="fas fa-birthday-cake mr-1"></i>{{ $childRecord->age ?? 'N/A' }}</span>
                            <span><i class="fas fa-{{ $childRecord->gender === 'Male' ? 'mars text-blue-500' : 'venus text-pink-500' }} mr-1"></i>{{ $childRecord->gender }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    @php
                        $upcomingImmunizations = $childRecord->immunizations->where('status', 'Upcoming');
                        $completedImmunizations = $childRecord->immunizations->where('status', 'Done');
                    @endphp
                    <span class="status-{{ $upcomingImmunizations->count() > 0 ? 'upcoming' : 'done' }}">
                        {{ $upcomingImmunizations->count() > 0 ? 'Active' : 'Up to Date' }}
                    </span>
                    @if($childRecord->birthdate)
                        <p class="text-xs text-gray-500 mt-1">Born: {{ $childRecord->birthdate->format('M d, Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Child Record Details -->
        <div class="px-4 py-3">
            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-syringe mr-1"></i>Immunizations
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ $childRecord->immunizations->count() }}</p>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-check-circle mr-1"></i>Completed
                    </div>
                    @php
                        $totalRequiredImmunizations = 12; // Standard childhood immunization schedule
                        $completedCount = $completedImmunizations->count();
                        $progressPercentage = $totalRequiredImmunizations > 0 ? round(($completedCount / $totalRequiredImmunizations) * 100) : 0;
                    @endphp
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $completedCount }}/{{ $totalRequiredImmunizations }}
                    </p>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $progressPercentage }}% complete
                    </div>
                    <!-- Mini progress bar -->
                    <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                        <div class="bg-green-600 h-1 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-clock mr-1"></i>Upcoming
                    </div>
                    <p class="text-sm font-semibold text-gray-900">{{ $upcomingImmunizations->count() }}</p>
                </div>
                <div class="bg-gray-50 rounded p-2 text-center">
                    <div class="text-xs text-gray-600 mb-1">
                        <i class="fas fa-calendar-check mr-1"></i>Last Done
                    </div>
                    <p class="text-sm font-semibold text-gray-900">
                        @php
                            $lastDone = $completedImmunizations->sortByDesc('schedule_date')->first();
                        @endphp
                        {{ $lastDone ? $lastDone->schedule_date->format('M d') : 'None' }}
                    </p>
                </div>
            </div>

            <!-- Record Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <div>
                    <h4 class="text-xs font-medium text-gray-700 mb-2">Birth Details</h4>
                    <div class="space-y-1 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>Birth Date:</span>
                            <span>{{ $childRecord->birthdate ? $childRecord->birthdate->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Birth Weight:</span>
                            <span>{{ $childRecord->birth_weight ? number_format($childRecord->birth_weight, 3) . 'kg' : 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Birth Height:</span>
                            <span>{{ $childRecord->birth_height ? number_format($childRecord->birth_height, 1) . 'cm' : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-medium text-gray-700 mb-2">Parent Information</h4>
                    <div class="space-y-1 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>Mother:</span>
                            <span>{{ $childRecord->mother_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Father:</span>
                            <span>{{ $childRecord->father_name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Contact:</span>
                            <span>{{ $childRecord->phone_number ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-xs font-medium text-gray-700 mb-2">Location</h4>
                    <div class="text-xs text-gray-600">
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span>Birth Place:</span>
                                <span>{{ $childRecord->birthplace ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span>Address:</span>
                                <p class="mt-1 text-xs">{{ $childRecord->address ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-1">
                <a href="{{ route('midwife.immunization.index') }}" class="btn-action btn-success inline-flex items-center">
                    <i class="fas fa-syringe mr-1"></i>
                    <span class="hidden sm:inline">Schedule</span>
                </a>
                <a href="{{ route('midwife.childrecord.edit', $childRecord->id) }}" class="btn-action btn-edit inline-flex items-center">
                    <i class="fas fa-edit mr-1"></i>
                    <span class="hidden sm:inline">Edit</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Immunization History -->
    <div class="bg-white rounded shadow-sm border">
        <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                <i class="fas fa-history mr-1 text-gray-600"></i>
                Immunization History
            </h3>
        </div>

        @if($childRecord->immunizations->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vaccine</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dose</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Due</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($childRecord->immunizations->sortByDesc('schedule_date') as $index => $immunization)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-day text-gray-500 mr-1"></i>
                                        {{ $immunization->schedule_date->format('M d, Y') }}
                                        @if($immunization->status === 'Done' && $index === 0)
                                            <span class="ml-2 px-1 py-0.5 bg-green-100 text-green-800 text-xs rounded">Latest</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <i class="fas fa-clock text-gray-500 mr-1"></i>
                                    {{ $immunization->schedule_time ? \Carbon\Carbon::parse($immunization->schedule_time)->format('h:i A') : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <span class="font-medium">{{ $immunization->vaccine_name }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                        {{ $immunization->dose }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    <span class="status-{{ strtolower($immunization->status) }}">{{ $immunization->status }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-900">
                                    @if($immunization->next_due_date)
                                        <span class="text-yellow-600">
                                            <i class="fas fa-calendar-plus mr-1"></i>
                                            {{ \Carbon\Carbon::parse($immunization->next_due_date)->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-900">
                                    @if($immunization->notes)
                                        <div class="max-w-xs">
                                            <p class="truncate" title="{{ $immunization->notes }}">{{ $immunization->notes }}</p>
                                        </div>
                                    @else
                                        <span class="text-gray-400">No notes</span>
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
                    <i class="fas fa-syringe text-xl mb-2 text-gray-400"></i>
                    <p class="text-xs">No immunizations recorded yet.</p>
                    <p class="text-xs text-gray-400">Schedule the first immunization for this child.</p>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection