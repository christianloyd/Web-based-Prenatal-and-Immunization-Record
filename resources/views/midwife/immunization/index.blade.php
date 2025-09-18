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

    <!-- Disabled Toast Notifications - Using Alert Components instead -->
    {{-- @include('components.toast-notification') --}}

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

    
    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-4 sm:p-6">
            <form method="GET" action="{{ route('midwife.immunization.index') }}" class="search-form">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search child name or vaccine..." 
                               class="input-clean w-full pl-10 pr-4 py-2.5 rounded-lg">
                    </div>
                    <select name="status" class="input-clean px-3 py-2.5 rounded-lg">
                        <option value="all" {{ $currentStatus == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="Upcoming" {{ $currentStatus == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="Done" {{ $currentStatus == 'Done' ? 'selected' : '' }}>Done</option>
                        <option value="Missed" {{ $currentStatus == 'Missed' ? 'selected' : '' }}>Missed</option>
                    </select>
                    <select name="vaccine" class="input-clean px-3 py-2.5 rounded-lg">
                        <option value="">All Vaccines</option>
                        @foreach(\App\Models\Immunization::getVaccineTypes() as $key => $value)
                            <option value="{{ $key }}" {{ request('vaccine') == $key ? 'selected' : '' }}>{{ $key }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           placeholder="From Date" class="input-clean px-3 py-2.5 rounded-lg">
                    <div class="flex gap-2">
                        <button type="submit" class="btn-minimal px-4 py-2.5 bg-[#68727A] text-white rounded-lg">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="{{ route('midwife.immunization.index') }}" class="btn-minimal px-4 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                            <i class="fas fa-times"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($immunizations->count() > 0)
            <div class="table-wrapper">
                <table class="w-full table-container">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Immunization ID</th>
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
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-blue-600">{{ $immunization->formatted_immunization_id ?? 'IM-001' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $immunization->childRecord->child_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $immunization->vaccine_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500 sm:hidden">{{ $immunization->dose ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                                <div class="text-sm sm:text-base">{{ $immunization->schedule_date ? $immunization->schedule_date->format('M j, Y') : 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                                <div class="text-sm sm:text-base">{{ $immunization->schedule_time ? $immunization->schedule_time->format('h:i A') : 'N/A' }}</div>
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
                            <div class="flex items-center justify-center gap-2">
                            <button onclick="openViewModal({{ json_encode($immunization->toArray()) }})" class="btn-action btn-view inline-flex items-center justify-center">
                                <i class="fas fa-eye mr-1"></i>
                            <span class="hidden sm:inline"><!--View--></span>
                            </button>
                            <button onclick="openEditModal({{ json_encode($immunization->toArray()) }})" class="btn-action btn-edit inline-flex items-center justify-center">
                                <i class="fas fa-edit mr-1"></i>
                            <span class="hidden sm:inline"><!--Edit --></span>
                            </button>
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

@endsection

@push('scripts')
<script>
// ==============================================
// MODAL MANAGEMENT
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
        updateElementText('modalChildName', immunization.child_record?.child_name);
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
        alert('Error: No immunization data provided');
        return;
    }

    const modal = document.getElementById('editImmunizationModal');
    const form = document.getElementById('editImmunizationForm');

    if (!modal || !form) {
        console.error('Edit modal elements not found');
        alert('Error: Modal elements not found');
        return;
    }

    try {
        // Set form action
        const userRole = '{{ auth()->user()->role }}';
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
        alert('Error opening edit modal. Please try again.');
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

    // Handle vaccine ID - fallback to finding by name if vaccine_id is missing
    let vaccineId = immunization.vaccine_id;
    if (!vaccineId && immunization.vaccine_name) {
        // Try to find vaccine ID by name from the dropdown options
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

    // Trigger vaccine info update after setting vaccine
    setTimeout(() => {
        try {
            if (typeof updateEditVaccineInfo === 'function') {
                console.log('Calling updateEditVaccineInfo');
                updateEditVaccineInfo();
            } else {
                console.warn('updateEditVaccineInfo function not found');
            }
        } catch (error) {
            console.error('Error calling updateEditVaccineInfo:', error);
        }
    }, 50);

    // Toggle field states based on status after populating the form
    setTimeout(() => {
        try {
            if (typeof toggleFieldsBasedOnStatus === 'function') {
                console.log('Calling toggleFieldsBasedOnStatus for status:', immunization.status);
                toggleFieldsBasedOnStatus();
            } else {
                console.warn('toggleFieldsBasedOnStatus function not found');
            }
        } catch (error) {
            console.error('Error calling toggleFieldsBasedOnStatus:', error);
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
        return timeString.substring(0, 5); // Take HH:MM part
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

    // Clear previous validation styles
    field.classList.remove('error-border', 'success-border');
    
    if (isRequired && !value) {
        isValid = false;
    } else if (value) {
        // Field-specific validation
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

    // Apply validation styling
    if (!isValid) {
        field.classList.add('error-border');
    } else if (value) {
        field.classList.add('success-border');
    }

    return isValid;
}

/**
 * Sets up form validation and submission handling
 */
function setupFormHandling(form, submitBtn, loadingText) {
    if (!form || !submitBtn) return;

    // Add validation to inputs
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        input.addEventListener('input', function() {
            this.classList.remove('error-border');
        });
    });
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${loadingText}
        `;
        
        // Re-enable button after 10 seconds as fallback
        setTimeout(() => {
            if (submitBtn.disabled) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }, 10000);
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
            
            // Set reasonable maximum date (2 years from now)
            const maxDate = new Date();
            maxDate.setFullYear(maxDate.getFullYear() + 2);
            input.setAttribute('max', maxDate.toISOString().split('T')[0]);
        }
    });
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
        }
    });
    
    // Handle validation errors from server - reopen modal with errors
    @if($errors->any())
        openAddModal();
    @endif

    // Handle Laravel session messages and show as alerts
    @if(session('success'))
        window.immunizationAlert.showAlert('success', 'Success!', '{{ session("success") }}');
    @endif

    @if(session('error'))
        window.immunizationAlert.showAlert('error', 'Error!', '{{ session("error") }}');
    @endif

    @if(session('warning'))
        window.immunizationAlert.showAlert('warning', 'Warning!', '{{ session("warning") }}');
    @endif

    @if(session('info'))
        window.immunizationAlert.showAlert('info', 'Information', '{{ session("info") }}');
    @endif

    // Enhanced form submission with toast notifications
    const immunizationForm = document.getElementById('immunizationForm');
    const editImmunizationForm = document.getElementById('editImmunizationForm');

    // Add form submission handlers for alert notifications
    if (immunizationForm) {
        immunizationForm.addEventListener('submit', function(e) {
            // Show pending alert
            setTimeout(() => {
                window.immunizationAlert.showAlert('info', 'Processing', 'Scheduling immunization...');
            }, 100);
        });
    }

    if (editImmunizationForm) {
        editImmunizationForm.addEventListener('submit', function(e) {
            // Show pending alert
            setTimeout(() => {
                window.immunizationAlert.showAlert('info', 'Processing', 'Updating immunization record...');
            }, 100);
        });
    }
});

// Custom alert functions for immunization operations using Flowbite alerts
window.immunizationAlert = {
    scheduled: function(childName, vaccineName) {
        this.showAlert('success', 'Scheduled Successfully!', `Immunization scheduled for ${childName} - ${vaccineName} vaccination`);
    },
    updated: function(childName, status) {
        this.showAlert('success', 'Updated Successfully!', `Record updated for ${childName} - Status changed to ${status}`);
    },
    error: function(message) {
        this.showAlert('error', 'Operation Failed', message);
    },
    lowStock: function(vaccineName, stock) {
        this.showAlert('warning', 'Low Stock Warning', `Only ${stock} units left for ${vaccineName}`);
    },
    showAlert: function(type, title, message) {
        // Remove any existing alerts first
        this.removeExistingAlerts();

        // Create alert HTML based on type
        const alertHtml = this.createAlertHtml(type, title, message);

        // Create temporary container
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = alertHtml;
        const alertElement = tempDiv.firstElementChild;

        // Style for slide-in from top
        alertElement.style.cssText = `
            position: fixed;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 400px;
            max-width: 600px;
            transition: all 0.4s ease-out;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        `;

        // Add to body
        document.body.appendChild(alertElement);

        // Trigger slide-in animation
        setTimeout(() => {
            alertElement.style.top = '20px';
        }, 10);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.hideAlert(alertElement);
        }, 5000);
    },
    createAlertHtml: function(type, title, message) {
        const alertConfigs = {
            success: {
                bgClass: 'bg-green-50',
                textClass: 'text-green-800',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            },
            error: {
                bgClass: 'bg-red-50',
                textClass: 'text-red-800',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            },
            warning: {
                bgClass: 'bg-yellow-50',
                textClass: 'text-yellow-800',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            },
            info: {
                bgClass: 'bg-blue-50',
                textClass: 'text-blue-800',
                iconPath: 'M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z'
            }
        };

        const config = alertConfigs[type] || alertConfigs.info;

        return `
            <div class="flex items-center p-4 mb-4 text-sm ${config.textClass} rounded-lg ${config.bgClass} border border-current/20" role="alert" data-dynamic-alert="true">
                <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="${config.iconPath}"/>
                </svg>
                <span class="sr-only">Alert</span>
                <div>
                    <span class="font-medium">${title}</span> ${message}
                </div>
                <button type="button" class="ms-auto -mx-1.5 -my-1.5 ${config.textClass} rounded-lg focus:ring-2 focus:ring-current p-1.5 hover:bg-current/10 inline-flex items-center justify-center h-8 w-8" onclick="immunizationAlert.hideAlert(this.parentElement)">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
        `;
    },
    hideAlert: function(alertElement) {
        if (alertElement) {
            alertElement.style.opacity = '0';
            alertElement.style.transform = 'translateX(-50%) translateY(-20px)';
            setTimeout(() => {
                if (alertElement.parentNode) {
                    alertElement.parentNode.removeChild(alertElement);
                }
            }, 400);
        }
    },
    removeExistingAlerts: function() {
        const existingAlerts = document.querySelectorAll('[data-dynamic-alert="true"]');
        existingAlerts.forEach(alert => {
            this.hideAlert(alert);
        });
    }
};

// Test function to demonstrate the new alert system (remove in production)
window.testAlerts = function() {
    setTimeout(() => window.immunizationAlert.showAlert('success', 'Success!', 'This is a success alert sliding from the top'), 500);
    setTimeout(() => window.immunizationAlert.showAlert('error', 'Error!', 'This is an error alert sliding from the top'), 1000);
    setTimeout(() => window.immunizationAlert.showAlert('warning', 'Warning!', 'This is a warning alert sliding from the top'), 1500);
    setTimeout(() => window.immunizationAlert.showAlert('info', 'Info!', 'This is an info alert sliding from the top'), 2000);
};
</script>
@endpush