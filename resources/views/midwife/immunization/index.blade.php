@extends('layout.midwife') 
@section('title', 'Immunization Schedule')
@section('page-title', 'Immunization Schedule')
@section('page-subtitle', 'Manage and track child immunization records')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<style>
    /* Modal Animation Styles */
    .modal-overlay {
        transition: opacity 0.3s ease-out;
    }
    
    .modal-overlay.hidden {
        opacity: 0;
        pointer-events: none;
    }
    
    .modal-overlay.show {
        opacity: 1;
        pointer-events: auto;
    }
    
    .modal-content {
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        transform: translateY(-20px) scale(0.95);
        opacity: 0;
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
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    /* Error and Success Styles */
    .error-border {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
    }

    .success-border {
        border-color: #10b981 !important;
        background-color: #f0fdf4 !important;
    }

    /* Button Styles */
    .btn-minimal {
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }
    
    .btn-minimal:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary-clean {
        background-color: #68727A;
        color: white;
    }
    
    .btn-primary-clean:hover {
        background-color: #5a6269;
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

    .btn-complete {
        background-color: #d1fae5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .btn-complete:hover {
        background-color: #10b981;
        color: white;
        border-color: #10b981;
    }

    .btn-done {
        background-color: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }
    
    .btn-done:hover {
        background-color: #16a34a;
        color: white;
        border-color: #16a34a;
    }

    .btn-missed {
        background-color: #fee2e2;
        color: #dc2626;
        border-color: #fecaca;
    }
    
    .btn-missed:hover {
        background-color: #dc2626;
        color: white;
        border-color: #dc2626;
    }

    .btn-reschedule {
        background-color: #dbeafe;
        color: #1e40af;
        border-color: #bfdbfe;
    }
    
    .btn-reschedule:hover {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    /* Input styles */
    .input-clean {
        transition: all 0.15s ease;
        border: 1px solid #d1d5db;
    }
    
    .input-clean:focus {
        outline: none;
        border-color: #68727A;
        box-shadow: 0 0 0 3px rgba(104, 114, 122, 0.1);
    }

    /* Status badges */
    .status-upcoming {
        background-color: #dbeafe;
        color: #1d4ed8;
    }
    
    .status-done {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .status-missed {
        background-color: #fee2e2;
        color: #dc2626;
    }

    /* Responsive styles */
    @media (max-width: 640px) {
        .table-row-hover td, .table-row-hover th {
            font-size: 0.875rem;
            padding: 0.75rem 0.5rem;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }

        .hide-mobile {
            display: none;
        }

        .modal-form-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-wrapper table {
        min-width: 900px;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Stats -->
    <div class="flex justify-between items-center mb-6">
         <div> </div>
        <div class="flex space-x-3">
            <button onclick="openAddModal()"
                class="btn-minimal btn-primary-clean px-4 py-2 rounded-lg font-medium flex items-center space-x-2">
                <i class="fas fa-plus text-sm"></i>
                <span> Add Schedule</span>
            </button>

            <!-- Test Toast Button (Remove in production) -->
            

            <!-- Test Notification Integration Button (Remove in production) -->
            

            <!-- Test BHW-to-Midwife Notifications (Remove in production) -->
             
        </div>
    </div>

    
    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('midwife.immunization.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-8 gap-4">
                <!-- Search Input - takes 4 columns -->
                <div class="md:col-span-4">
                    <div class="relative">
                        <input type="text" name="search" id="searchInput" value="{{ request('search') }}"
                               placeholder="Search by child name or vaccine"
                               class="w-full pl-10 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-[#68727A] form-input">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                        <!-- Clear button (x) inside input -->
                        @if(request('search'))
                        <button type="button" onclick="clearSearch()" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
                <!-- Status Filter, Vaccine Filter, and Search Button grouped closer - takes 4 columns total -->
                <div class="md:col-span-4 flex gap-2">
                    <!-- Status Filter -->
                    <div class="flex-1">
                        <select name="status" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#68727A] form-input">
                            <option value="">All Status</option>
                            <option value="Upcoming" {{ request('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="Done" {{ request('status') == 'Done' ? 'selected' : '' }}>Done</option>
                            <option value="Missed" {{ request('status') == 'Missed' ? 'selected' : '' }}>Missed</option>
                        </select>
                    </div>
                    <!-- Vaccine Filter -->
                    <div class="flex-1">
                        <select name="vaccine" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-[#68727A] form-input">
                            <option value="">All Vaccines</option>
                            @foreach($availableVaccines ?? [] as $vaccine)
                                <option value="{{ $vaccine->id }}" {{ request('vaccine') == $vaccine->id ? 'selected' : '' }}>
                                    {{ $vaccine->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Search Button -->
                    <div class="flex items-end">
                        <button type="submit" class="bg-[#68727A] text-white px-4 py-2 rounded-lg hover:bg-[#5a6470] transition-all duration-200 btn-primary whitespace-nowrap">
                            Search
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($immunizations->count() > 0)
            <div class="table-wrapper">
                <table class="w-full table-container">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <!--<th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Immunization ID</th>-->
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'child_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Child Name <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'vaccine_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Vaccine <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'schedule_date', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Schedule Date <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'schedule_time', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Schedule Time <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Dose</th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($immunizations as $immunization)
                        <tr class="table-row-hover">
                            <!--<td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-blue-600">{{ $immunization->formatted_immunization_id ?? 'IM-001' }}</div>
                            </td>-->
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $immunization->childRecord->full_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $immunization->vaccine_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500 sm:hidden">{{ $immunization->dose ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                                <div class="text-sm sm:text-base">{{ $immunization->schedule_date ? $immunization->schedule_date->format('M j, Y') : 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                                <div class="text-sm sm:text-base">{{ $immunization->schedule_time ? \Carbon\Carbon::parse($immunization->schedule_time)->format('h:i A') : 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                                {{ $immunization->dose ?? 'N/A' }}
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $immunization->status === 'Upcoming' ? 'status-upcoming' : '' }}
                                    {{ $immunization->status === 'Done' ? 'status-done' : '' }}
                                    {{ $immunization->status === 'Missed' ? 'status-missed' : '' }}">
                                    <i class="fas {{ $immunization->status === 'Done' ? 'fa-check' : ($immunization->status === 'Upcoming' ? 'fa-clock' : 'fa-times') }} mr-1"></i>
                                    {{ $immunization->status }}
                                </span>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap text-center align-middle">
                            <div class="flex items-center justify-center gap-1 flex-wrap">
                                @php
                                    $immunizationData = [
                                        'id' => $immunization->id,
                                        'child_record' => ['full_name' => $immunization->childRecord->full_name ?? 'Unknown'],
                                        'vaccine' => ['name' => $immunization->vaccine->name ?? $immunization->vaccine_name],
                                        'vaccine_name' => $immunization->vaccine_name,
                                        'dose' => $immunization->dose,
                                        'schedule_date' => $immunization->schedule_date,
                                        'status' => $immunization->status,
                                        'notes' => $immunization->notes,
                                        'batch_number' => $immunization->batch_number,
                                        'administered_by' => $immunization->administered_by,
                                        'child_record_id' => $immunization->child_record_id,
                                        'vaccine_id' => $immunization->vaccine_id
                                    ];
                                @endphp

                                <!-- View Button -->
                                <button onclick='openViewModal(@json($immunizationData))'
                                        class="btn-action btn-view inline-flex items-center justify-center"
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($immunization->status === 'Upcoming')
                                    @if(!$immunization->hasBeenRescheduled())
                                        <!-- Mark as Complete Button -->
                                        <button onclick='openMarkDoneModal(@json($immunizationData))'
                                                class="btn-action btn-complete inline-flex items-center justify-center"
                                                title="Mark as Complete">
                                            <i class="fas fa-check-circle"></i>
                                        </button>

                                        <!-- Mark as Missed Button -->
                                        <button onclick='openMarkMissedModal(@json($immunizationData))'
                                                class="btn-action btn-missed inline-flex items-center justify-center"
                                                title="Mark as Missed">
                                            <i class="fas fa-times"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <button onclick="openEditModal({{ json_encode($immunization->toArray()) }})"
                                                class="btn-action btn-edit inline-flex items-center justify-center"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @else
                                        <!-- Show link to new appointment if rescheduled -->
                                        @if($immunization->rescheduledToImmunization)
                                            <a href="{{ route('midwife.immunization.index') }}"
                                               class="btn-action btn-view inline-flex items-center justify-center"
                                               title="View New Appointment">
                                                <i class="fas fa-arrow-right mr-1"></i>
                                                View New
                                            </a>
                                        @endif
                                    @endif
                                @elseif($immunization->status === 'Missed')
                                    @if(!$immunization->hasBeenRescheduled())
                                        <!-- Reschedule Button for Missed Immunizations -->
                                        <button onclick='openImmunizationRescheduleModal(@json($immunizationData))'
                                                class="btn-action btn-reschedule inline-flex items-center justify-center"
                                                title="Reschedule">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    @else
                                        <!-- Show indicator that it has been rescheduled (matching prenatal checkup style) -->
                                        <span class="text-xs text-gray-500 italic" title="This immunization has been rescheduled">
                                            <i class="fas fa-check-circle text-green-500"></i> Rescheduled
                                        </span>
                                    @endif
                                @else
                                    <!-- For completed immunizations - only show edit -->
                                    <button onclick="openEditModal({{ json_encode($immunization->toArray()) }})"
                                            class="btn-action btn-edit inline-flex items-center justify-center"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 overflow-x-auto">
                {{ $immunizations->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-syringe text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No immunization schedules found</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                    @if(request()->hasAny(['search', 'status', 'vaccine', 'date_from']))
                        No records match your search criteria. Try adjusting your filters.
                    @else
                        Get started by scheduling the first immunization.
                    @endif
                </p>
                <button onclick="openAddModal()" class="btn-minimal btn-primary-clean px-6 py-3 rounded-lg font-medium inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Schedule Immunization
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Add Immunization Modal -->
 @include ('partials.midwife.immunization.immuadd')

<!-- View Immunization Modal -->
 @include ('partials.midwife.immunization.immuview')

<!-- Edit Immunization Modal -->
 @include ('partials.midwife.immunization.immuedit')

<!-- Confirm Missed Modal -->
 @include ('partials.midwife.immunization.mark-missed-modal')

<!-- Reschedule Modal -->
 @include ('partials.midwife.immunization.reschedule_modal')

<!-- Mark as Done Modal -->
 @include ('partials.midwife.immunization.mark-done-modal')

@endsection

@push('scripts')
<script>
// ==============================================
// MODAL MANAGEMENT - Updated 2025-10-31 v2
// ==============================================

// ==============================================
// MODAL MANAGEMENT - Fixed for Global Scope
// ==============================================

/**
 * Opens the Add Immunization modal
 */
 function openAddModal() {
    const modal = document.getElementById('immunizationModal');
    const form = document.getElementById('immunizationForm');
    
    if (!modal || !form) {
        console.error('Add modal elements not found');
        return;
    }
    
    // Reset form
    form.reset();
    clearValidationStates(form);
    
    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    
    // Focus first input
    setTimeout(() => {
        const firstInput = form.querySelector('select[name="child_record_id"]');
        if (firstInput) firstInput.focus();
    }, 300);
}

/**
 * Closes the Add Immunization modal
 */
function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    
    const modal = document.getElementById('immunizationModal');
    if (!modal) return;
    
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Reset form only if no validation errors
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('immunizationForm');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }
        }
    }, 300);
}

/**
 * Opens the View Immunization modal
 */
function openViewModal(immunization) {
    if (!immunization) {
        console.error('No immunization record provided');
        return;
    }
    
    try {
        // Populate modal fields
        updateElementText('modalChildName', immunization.child_record?.full_name);
        updateElementText('modalVaccineName', immunization.vaccine_name);
        updateElementText('modalDose', immunization.dose);
        updateElementText('modalStatus', immunization.status);
        updateElementText('modalNotes', immunization.notes);
        
        // Format and display schedule date
        if (immunization.schedule_date) {
            const scheduleDate = new Date(immunization.schedule_date);
            updateElementText('modalScheduleDate', scheduleDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            }));
        } else {
            updateElementText('modalScheduleDate', 'N/A');
        }
        
        // Display schedule time
        updateElementText('modalScheduleTime', immunization.schedule_time || 'N/A');
        
        // Show modal with animation
        const modal = document.getElementById('viewImmunizationModal');
        const content = document.getElementById('viewImmunizationModalContent');
        
        if (!modal || !content) {
            console.error('View modal elements not found');
            return;
        }
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Trigger animation
        requestAnimationFrame(() => {
            content.classList.remove('-translate-y-10', 'opacity-0');
            content.classList.add('translate-y-0', 'opacity-100');
        });
        
    } catch (error) {
        console.error('Error opening view modal:', error);
    }
}

/**
 * Closes the View Immunization modal
 */
function closeViewModal(event) {
    if (event && event.target !== event.currentTarget) return;
    
    const modal = document.getElementById('viewImmunizationModal');
    const content = document.getElementById('viewImmunizationModalContent');
    
    if (!modal || !content) return;
    
    content.classList.remove('translate-y-0', 'opacity-100');
    content.classList.add('-translate-y-10', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

/**
 * Opens the Edit Immunization modal
 */
function openEditModal(immunization) {
    console.log('Opening edit modal for immunization:', immunization);

    if (!immunization) {
        console.error('No immunization record provided');
        showError('Error: No immunization data provided');
        return;
    }

    const modal = document.getElementById('editImmunizationModal');
    const form = document.getElementById('editImmunizationForm');

    if (!modal || !form) {
        console.error('Edit modal elements not found');
        showError('Error: Modal elements not found');
        return;
    }

    try {
        // Set form action - get user role from a data attribute or PHP variable
        const userRole = document.body.getAttribute('data-user-role') || 'midwife';
        const routeName = userRole === 'bhw' ? 'immunizations' : 'immunization';
        form.action = `/${userRole}/${routeName}/${immunization.id}`;
        console.log('Form action set to:', form.action);

        // Populate form fields
        populateEditForm(immunization);

        // Clear validation states
        clearValidationStates(form);

        // Show modal
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.add('show'));
        document.body.style.overflow = 'hidden';

        // Focus first input (but only if not readonly)
        setTimeout(() => {
            const firstInput = document.getElementById('editChildRecordId');
            if (firstInput && !firstInput.disabled) {
                firstInput.focus();
            }
        }, 300);

    } catch (error) {
        console.error('Error opening edit modal:', error);
        showError('Error opening edit modal. Please try again.');
    }
}

/**
 * Closes the Edit Immunization modal
 */
function closeEditModal(event) {
    if (event && event.target !== event.currentTarget) return;
    
    const modal = document.getElementById('editImmunizationModal');
    if (!modal) return;
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        const form = document.getElementById('editImmunizationForm');
        if (form) {
            form.reset();
            clearValidationStates(form);
        }
    }, 300);
}

/**
 * Opens the Mark as Done modal
 */
function openMarkDoneModal(immunizationData) {
    console.log('Opening mark done modal:', immunizationData);

    // Set immunization details in modal
    const childNameElement = document.getElementById('done-child-name');
    const vaccineNameElement = document.getElementById('done-vaccine-name');
    const doseElement = document.getElementById('done-dose');

    if (childNameElement) {
        childNameElement.textContent = immunizationData.child_record?.full_name || 'N/A';
    }
    if (vaccineNameElement) {
        vaccineNameElement.textContent = immunizationData.vaccine_name || immunizationData.vaccine?.name || 'N/A';
    }
    if (doseElement) {
        doseElement.textContent = immunizationData.dose || 'N/A';
    }

    // Set form action
    const form = document.getElementById('markDoneForm');
    if (form) {
        const userRole = document.body.getAttribute('data-user-role') || 'midwife';
        const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
        form.action = `/${userRole}/${endpoint}/${immunizationData.id}/complete`;
        console.log('Form action set to:', form.action);
    }

    // Show modal with proper flexbox centering
    const modal = document.getElementById('markDoneModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        console.log('Modal should be visible now');
    } else {
        console.error('markDoneModal not found');
    }
}

/**
 * Closes the Mark as Done modal
 */
function closeMarkDoneModal() {
    const modal = document.getElementById('markDoneModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

/**
 * Opens the Mark as Missed modal
 */
function openMarkMissedModal(immunization) {
    if (!immunization) {
        console.error('No immunization data provided');
        return;
    }

    // Populate immunization details
    document.getElementById('missed-immunization-id').value = immunization.id;
    document.getElementById('missed-child-name').textContent = immunization.child_record?.full_name || 'Unknown';
    document.getElementById('missed-vaccine-name').textContent = immunization.vaccine?.name || immunization.vaccine_name || 'Unknown';
    document.getElementById('missed-dose').textContent = immunization.dose || 'N/A';
    document.getElementById('missed-schedule-date').textContent = new Date(immunization.schedule_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Set form action
    const userRole = document.body.getAttribute('data-user-role') || 'midwife';
    const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
    document.getElementById('markMissedForm').action = `/${userRole}/${endpoint}/${immunization.id}/mark-missed`;

    // Reset form
    document.getElementById('markMissedForm').reset();
    document.getElementById('missed-immunization-id').value = immunization.id;
    document.getElementById('missed-confirm-checkbox').checked = false;
    document.getElementById('missed-reschedule-checkbox').checked = false;
    document.getElementById('reschedule-fields').classList.add('hidden');

    // Show modal
    const modal = document.getElementById('markMissedModal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        document.getElementById('missed-reason').focus();
    }, 300);
}

/**
 * Closes the Mark as Missed modal
 */
function closeMarkMissedModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('markMissedModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('markMissedForm').reset();
    }, 300);
}

/**
 * Opens the Reschedule modal
 */
function openImmunizationRescheduleModal(immunization) {
    console.log('Opening reschedule modal with data:', immunization);

    if (!immunization) {
        console.error('No immunization data provided');
        return;
    }

    // Populate immunization details
    const childNameEl = document.getElementById('reschedule-child-name');
    const vaccineNameEl = document.getElementById('reschedule-vaccine-name');
    const doseEl = document.getElementById('reschedule-dose');
    const originalDateEl = document.getElementById('reschedule-original-date');

    if (childNameEl) childNameEl.textContent = immunization.child_record?.full_name || 'Unknown';
    
    let vaccineName = 'Unknown';
    if (immunization.vaccine && immunization.vaccine.name) {
        vaccineName = immunization.vaccine.name;
    } else if (immunization.vaccine_name) {
        vaccineName = immunization.vaccine_name;
    }
    if (vaccineNameEl) vaccineNameEl.textContent = vaccineName;
    
    if (doseEl) doseEl.textContent = immunization.dose || 'N/A';

    if (originalDateEl) {
        const scheduleDate = new Date(immunization.schedule_date);
        originalDateEl.textContent = scheduleDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Reset form
    const form = document.getElementById('rescheduleForm');
    if (form) form.reset();

    // Set current reschedule immunization for form submission
    window.currentRescheduleImmunization = immunization;

    // Show modal
    const modal = document.getElementById('rescheduleModal');
    if (modal) {
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.add('show'));
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            const dateInput = document.getElementById('reschedule-date');
            if (dateInput) dateInput.focus();
        }, 300);
    }
}

/**
 * Closes the Reschedule modal
 */
function closeImmunizationRescheduleModal(event) {
    if (event && event.target !== event.currentTarget && arguments.length > 0) return;

    const modal = document.getElementById('rescheduleModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        window.currentRescheduleImmunization = null;

        const form = document.getElementById('rescheduleForm');
        if (form) form.reset();
    }, 300);
}

// ==============================================
// UTILITY FUNCTIONS
// ==============================================

/**
 * Updates text content of an element safely
 */
function updateElementText(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value || 'N/A';
    }
}

/**
 * Populates the edit form with immunization data
 */
function populateEditForm(immunization) {
    console.log('Populating edit form with:', immunization);

    let vaccineId = immunization.vaccine_id;
    if (!vaccineId && immunization.vaccine_name) {
        const vaccineSelect = document.getElementById('editVaccineId');
        if (vaccineSelect) {
            for (let option of vaccineSelect.options) {
                if (option.textContent.includes(immunization.vaccine_name)) {
                    vaccineId = option.value;
                    console.log(`Found vaccine ID ${vaccineId} for vaccine name "${immunization.vaccine_name}"`);
                    break;
                }
            }
        }
    }

    const fieldMappings = [
        { id: 'editImmunizationId', value: immunization.id },
        { id: 'editChildRecordId', value: immunization.child_record_id },
        { id: 'editVaccineId', value: vaccineId },
        { id: 'editDose', value: immunization.dose },
        { id: 'editScheduleDate', value: formatDateForInput(immunization.schedule_date) },
        { id: 'editScheduleTime', value: formatTimeForInput(immunization.schedule_time) },
        { id: 'editStatus', value: immunization.status },
        { id: 'editNotes', value: immunization.notes }
    ];

    fieldMappings.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.value = field.value || '';
            element.classList.remove('error-border', 'success-border');
            console.log(`Set ${field.id} to: ${field.value}`);
        } else {
            console.warn(`Element not found: ${field.id}`);
        }
    });

    setTimeout(() => {
        if (typeof updateEditVaccineInfo === 'function') {
            updateEditVaccineInfo();
        }
    }, 50);

    setTimeout(() => {
        if (typeof toggleFieldsBasedOnStatus === 'function') {
            toggleFieldsBasedOnStatus();
        }
    }, 100);
}

/**
 * Formats date string for HTML date input
 */
function formatDateForInput(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
}

/**
 * Formats time string for HTML time input
 */
function formatTimeForInput(timeString) {
    if (!timeString) return '';
    if (timeString.includes(':')) {
        return timeString.substring(0, 5);
    }
    return timeString;
}

/**
 * Clears validation states from form elements
 */
function clearValidationStates(form) {
    if (!form) return;
    
    form.querySelectorAll('.error-border, .success-border').forEach(input => {
        input.classList.remove('error-border', 'success-border');
    });
}

/**
 * Validates a form field
 */
function validateField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    let isValid = true;

    field.classList.remove('error-border', 'success-border');
    
    if (isRequired && !value) {
        isValid = false;
    } else if (value) {
        switch (field.name) {
            case 'schedule_date':
                const scheduleDate = new Date(value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (scheduleDate < today) {
                    isValid = false;
                }
                break;
            case 'schedule_time':
                const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
                if (!timeRegex.test(value)) {
                    isValid = false;
                }
                break;
        }
    }

    if (!isValid) {
        field.classList.add('error-border');
    } else if (value) {
        field.classList.add('success-border');
    }

    return isValid;
}

/**
 * Sets up form validation and submission handling with AJAX + SweetAlert
 */
function setupFormHandling(form, submitBtn, loadingText) {
    if (!form || !submitBtn) return;

    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        input.addEventListener('input', function() {
            this.classList.remove('error-border');
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        const originalText = submitBtn.innerHTML;
        const formData = new FormData(this);
        const formAction = this.action;

        // Disable submit button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText}
        `;

        // Send AJAX request
        fetch(formAction, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(result => {
            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            if (result.status === 200 && result.body.success) {
                // Close modal ONLY on success
                closeModal();

                // Show success SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.body.message || 'Immunization schedule created successfully!',
                    confirmButtonColor: '#68727A',
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Reload page to show new data
                    window.location.reload();
                });
            } else {
                // DON'T close modal - keep it open for corrections
                // Format validation errors
                let errorMessage = '';
                if (result.body.errors) {
                    // Laravel validation errors format
                    errorMessage = '<div class="text-left"><strong>Please correct the following errors:</strong><ul class="mt-2 ml-4 list-disc">';
                    Object.keys(result.body.errors).forEach(key => {
                        result.body.errors[key].forEach(error => {
                            errorMessage += `<li class="mb-1">${error}</li>`;
                        });
                    });
                    errorMessage += '</ul></div>';
                } else {
                    errorMessage = result.body.message || 'Please correct the errors and try again.';
                }

                // Show validation error with SweetAlert (modal stays open in background)
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorMessage,
                    confirmButtonColor: '#68727A',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // Restore button
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            // Show error SweetAlert
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred. Please try again.',
                confirmButtonColor: '#68727A',
                confirmButtonText: 'OK'
            });
        });
    });
}

/**
 * Sets date constraints on date inputs
 */
function setDateConstraints() {
    const scheduleDateInputs = ['schedule_date', 'editScheduleDate'];
    
    scheduleDateInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);
            
            const maxDate = new Date();
            maxDate.setFullYear(maxDate.getFullYear() + 2);
            input.setAttribute('max', maxDate.toISOString().split('T')[0]);
        }
    });
}

/**
 * Clear search function
 */
function clearSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.value = '';
        searchInput.form.submit();
    }
}

// ==============================================
// INITIALIZATION
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    // Setup form handling for Add form
    const addForm = document.getElementById('immunizationForm');
    const addSubmitBtn = document.getElementById('submit-btn');
    if (addForm && addSubmitBtn) {
        setupFormHandling(addForm, addSubmitBtn, 'Scheduling...');
    }

    // Setup form handling for Edit form
    const editForm = document.getElementById('editImmunizationForm');
    const editSubmitBtn = editForm?.querySelector('button[type="submit"]');
    if (editForm && editSubmitBtn) {
        setupFormHandling(editForm, editSubmitBtn, 'Updating...');
    }
    
    // Set date constraints
    setDateConstraints();
    
    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
            closeEditModal();
            closeViewModal();
            closeImmunizationRescheduleModal();
            closeMarkDoneModal();
            closeMarkMissedModal();
        }
    });

    // Note: Reschedule form submission is handled in reschedule_modal.blade.php

    // Setup mark missed reschedule checkbox
    const missedRescheduleCheckbox = document.getElementById('missed-reschedule-checkbox');
    if (missedRescheduleCheckbox) {
        missedRescheduleCheckbox.addEventListener('change', function() {
            const rescheduleFields = document.getElementById('reschedule-fields');
            const dateInput = document.getElementById('missed-reschedule-date');
            const timeInput = document.getElementById('missed-reschedule-time');

            if (this.checked) {
                rescheduleFields.classList.remove('hidden');
                dateInput.required = true;
                timeInput.required = true;
            } else {
                rescheduleFields.classList.add('hidden');
                dateInput.required = false;
                timeInput.required = false;
                dateInput.value = '';
                timeInput.value = '';
            }
        });
    }

    // Setup mark missed form submission
    const markMissedForm = document.getElementById('markMissedForm');
    if (markMissedForm) {
        markMissedForm.addEventListener('submit', function(e) {
            if (!document.getElementById('missed-confirm-checkbox').checked) {
                e.preventDefault();
                showError('Please confirm by checking the checkbox');
                return false;
            }

            const submitBtn = document.getElementById('missed-submit-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            }
        });
    }

    // Setup mark done modal click outside
    const doneModal = document.getElementById('markDoneModal');
    if (doneModal) {
        doneModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMarkDoneModal();
            }
        });
    }
});

</script>
@endpush