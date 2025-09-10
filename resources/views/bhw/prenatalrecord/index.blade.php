@extends('layout.bhw')
@section('title', 'Prenatal Records')
@section('page-title', 'Prenatal Records')
@section('page-subtitle', 'Manage and monitor prenatal records')

@push('styles')
<style>

    /* Additional Mobile Responsive Styles */
@media (max-width: 640px) {
    .modal-content {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }
    
    .btn-action {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
    
    .btn-action .fas {
        margin-right: 2px;
    }
}
    /* Modal Animation Styles */
    .modal-overlay {
        transition: opacity 0.3s ease-out;
        z-index: 9999 !important; /* Ensure modal is on top */
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
    
    /* Debug styles - remove after testing */
    #prenatal-modal {
        border: 2px solid red !important; /* Temporary debug border */
    }
    
    #prenatal-modal.show {
        border: 2px solid green !important; /* Shows when modal is active */
    }
    
    /* Form Input Focus Styles */
    .form-input {
        transition: all 0.2s ease;
    }
    
    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
    
    /* Button Hover Effects */
    .btn-primary {
        transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Status Badge Styles */
    .status-normal {
        background-color: #10b981;
        color: white;
    }
    
    .status-monitor {
        background-color: #f59e0b;
        color: white;
    }
    
    .status-high-risk {
        background-color: #ef4444;
        color: white;
    }
    
    .status-due {
        background-color: #3b82f6;
        color: white;
    }
    
    .status-completed {
        background-color: #6b7280;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex space-x-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border">
                    <div class="text-2xl font-bold text-primary">{{ $prenatalRecords->total() ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total Records</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border">
                    <div class="text-2xl font-bold text-green-600">{{ $prenatalRecords->where('status', 'normal')->count() }}</div>
                    <div class="text-sm text-gray-600">Normal</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border">
                    <div class="text-2xl font-bold text-red-600">{{ $prenatalRecords->where('status', 'high-risk')->count() }}</div>
                    <div class="text-sm text-gray-600">High Risk</div>
                </div>
            </div>
        </div>
        <div class="flex space-x-3">
            <!-- FIXED: Changed from anchor to button that opens modal -->
            <button onclick="openPrenatalModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Add Prenatal Record
            </button>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('bhw.prenatalrecord.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name or ID..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <select name="status" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <option value="">All Status</option>
                        <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="monitor" {{ request('status') == 'monitor' ? 'selected' : '' }}>Monitor</option>
                        <option value="high-risk" {{ request('status') == 'high-risk' ? 'selected' : '' }}>High Risk</option>
                        <option value="due" {{ request('status') == 'due' ? 'selected' : '' }}>Appointment Due</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200 btn-primary">
                        Search
                    </button>
                    <a href="{{ route('bhw.prenatalrecord.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Prenatal Records Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gestational Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trimester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Visit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($prenatalRecords as $record)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                            {{ $record->formatted_prenatal_id ?? 'PR-001' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->patient->formatted_patient_id ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $record->patient->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->gestational_age ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->trimester)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                    {{ $record->trimester }}{{ $record->trimester == 1 ? 'st' : ($record->trimester == 2 ? 'nd' : 'rd') }} Trimester
                                </span>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($record->expected_due_date)
                                {{ $record->expected_due_date->format('M d, Y') }}
                                @if($record->is_overdue)
                                    <span class="text-red-600 text-xs block">Overdue</span>
                                @elseif($record->days_until_due <= 14 && $record->days_until_due >= 0)
                                    <span class="text-orange-600 text-xs block">{{ $record->days_until_due }} days left</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-{{ $record->status }}">
                                {{ $record->status_text }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->last_visit ? $record->last_visit->format('M d, Y') : 'No visits' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-4">
                                <button onclick="openViewPrenatalModal({{ $record }})" 
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                    View
                                </button>
                                <button onclick="openEditPrenatalModal({{ $record }})" 
                                        class="text-yellow-600 hover:text-yellow-800 bg-yellow-100 px-3 py-1 rounded font-medium">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-2">No prenatal records found</p>
                                <p class="text-gray-600 mb-4">Get started by creating your first prenatal record</p>
                                <button onclick="openPrenatalModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors btn-primary">
                                    Create First Record
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($prenatalRecords->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $prenatalRecords->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Prenatal Record Modal -->
@include('partials.bhw.prenatalrecord.prenataladd')

<!-- View Prenatal Record Modal -->
@include('partials.bhw.prenatalrecord.prenatalview')

<!-- Edit Prenatal Record Modal -->
@include('partials.bhw.prenatalrecord.prenataledit')

@endsection

@push('scripts')
<script>
// --------------------
// Add Prenatal Record Modal
// --------------------
function openPrenatalModal() {
    const modal = document.getElementById('prenatal-modal');
    if (!modal) {
        console.error('Prenatal modal not found');
        return;
    }
    
    // Reset form if no validation errors
    if (!document.querySelector('.bg-red-100')) {
        const form = modal.querySelector('form');
        if (form) form.reset();
    }
    
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });
    document.body.style.overflow = 'hidden';
}

function closePrenatalModal(e) {
    // Don't close if click is inside modal content
    if (e && e.target !== e.currentTarget) return;
    
    const modal = document.getElementById('prenatal-modal');
    if (!modal) return;
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Only reset form if there are no validation errors
        if (!document.querySelector('.bg-red-100')) {
            const form = modal.querySelector('form');
            if (form) form.reset();
        }
    }, 300);
}

// --------------------
// View Prenatal Record Modal
// --------------------
function openViewPrenatalModal(record) {
    if (!record) {
        console.error('No prenatal record provided');
        return;
    }

    // Helper function to safely set text content
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || 'N/A';
        }
    };

    // Populate modal fields
    setText('viewPatientName', record.patient?.name);
    setText('viewPatientId', record.patient?.formatted_patient_id);
    setText('viewPatientAge', record.patient?.age ? `${record.patient.age} years` : null);
    setText('viewGestationalAge', record.gestational_age);
    setText('viewTrimester', record.trimester ? 
        `${record.trimester}${record.trimester == 1 ? 'st' : (record.trimester == 2 ? 'nd' : 'rd')} Trimester` : null);
    setText('viewLMP', record.last_menstrual_period);
    setText('viewEDD', record.expected_due_date);
    setText('viewGravida', record.gravida ? `G${record.gravida}` : null);
    setText('viewPara', record.para !== null ? `P${record.para}` : null);
    setText('viewStatus', record.status_text || record.status);
    setText('viewBloodPressure', record.blood_pressure);
    setText('viewWeight', record.weight ? `${record.weight} kg` : null);
    setText('viewHeight', record.height ? `${record.height} cm` : null);
    setText('viewMedicalHistory', record.medical_history);
    setText('viewNotes', record.notes);
    setText('viewLastVisit', record.last_visit);
    setText('viewNextAppointment', record.next_appointment);

    // Show modal
    const modal = document.getElementById('view-prenatal-modal');
    if (!modal) {
        console.error('View prenatal modal not found');
        return;
    }

    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeViewPrenatalModal(e) {
    if (e && e.target !== e.currentTarget) return;
    
    const modal = document.getElementById('view-prenatal-modal');
    if (!modal) return;
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// --------------------
// Edit Prenatal Record Modal
// --------------------
function openEditPrenatalModal(record) {
    if (!record) {
        console.error('No prenatal record provided');
        return;
    }
    
    const modal = document.getElementById('edit-prenatal-modal');
    const form = document.getElementById('edit-prenatal-form');
    
    if (!modal || !form) {
        console.error('Edit modal or form not found');
        return;
    }
    
    // Set form action URL
    if (form.dataset.updateUrl) {
        form.action = form.dataset.updateUrl.replace(':id', record.id);
    }
    
    // Helper function to format dates for input fields
    const formatDate = (dateString) => {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toISOString().split('T')[0];
        } catch (error) {
            return '';
        }
    };
    
    // Helper function to safely set form values
    const setValue = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
            // Remove any validation styling
            element.classList.remove('error-border', 'success-border');
        }
    };

    // Helper function to set text content
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value || 'N/A';
        }
    };
    
    // Populate patient information display (read-only)
    setText('edit-patient-name-display', record.patient?.name);
    setText('edit-patient-id-display', record.patient?.formatted_patient_id);
    setText('edit-patient-age-display', record.patient?.age ? `${record.patient.age} years` : null);
    
    // Set hidden patient_id field
    setValue('edit-patient-id-hidden', record.patient_id || '');
    
    // Populate editable form fields
    setValue('edit-lmp', formatDate(record.last_menstrual_period));
    setValue('edit-due-date', formatDate(record.expected_due_date));
    setValue('edit-gravida', record.gravida || '');
    setValue('edit-para', record.para || '');
    setValue('edit-status', record.status || 'normal');
    setValue('edit-blood-pressure', record.blood_pressure || '');
    setValue('edit-weight', record.weight || '');
    setValue('edit-height', record.height || '');
    setValue('edit-medical-history', record.medical_history || '');
    setValue('edit-notes', record.notes || '');

    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeEditPrenatalModal(e) {
    if (e && e.target !== e.currentTarget) return;
    
    const modal = document.getElementById('edit-prenatal-modal');
    if (!modal) return;
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// --------------------
// Date Validation and Auto-calculation
// --------------------
function calculateEDD(lmpDate) {
    if (!lmpDate) return '';
    
    const lmp = new Date(lmpDate);
    const edd = new Date(lmp);
    edd.setDate(edd.getDate() + 280); // Add 280 days (40 weeks)
    
    return edd.toISOString().split('T')[0];
}

function setupDateValidation() {
    const today = new Date().toISOString().split('T')[0];
    
    // Setup for Add Modal
    const addLmpInput = document.querySelector('#prenatal-modal input[name="last_menstrual_period"]');
    const addEddInput = document.querySelector('#prenatal-modal input[name="expected_due_date"]');
    
    if (addLmpInput) {
        addLmpInput.setAttribute('max', today);
        
        if (addEddInput) {
            addLmpInput.addEventListener('change', function() {
                if (this.value && !addEddInput.value) {
                    addEddInput.value = calculateEDD(this.value);
                }
            });
        }
    }
    
    // Setup for Edit Modal
    const editLmpInput = document.querySelector('#edit-prenatal-modal input[name="last_menstrual_period"]');
    const editEddInput = document.querySelector('#edit-prenatal-modal input[name="expected_due_date"]');
    
    if (editLmpInput) {
        editLmpInput.setAttribute('max', today);
        
        if (editEddInput) {
            editLmpInput.addEventListener('change', function() {
                if (this.value && !editEddInput.value) {
                    editEddInput.value = calculateEDD(this.value);
                }
            });
        }
    }
}

// --------------------
// Form Validation
// --------------------
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    return isValid;
}

// --------------------
// Event Listeners
// --------------------
document.addEventListener('DOMContentLoaded', function() {
    setupDateValidation();
    
    // Form submission validation
    const prenatalForm = document.getElementById('prenatal-form');
    if (prenatalForm) {
        prenatalForm.addEventListener('submit', function(e) {
            if (!validateForm('prenatal-form')) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
    
    const editPrenatalForm = document.getElementById('edit-prenatal-form');
    if (editPrenatalForm) {
        editPrenatalForm.addEventListener('submit', function(e) {
            if (!validateForm('edit-prenatal-form')) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePrenatalModal();
        closeViewPrenatalModal();
        closeEditPrenatalModal();
    }
});

// Prevent modal close when clicking inside modal content
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        const modalId = e.target.id;
        switch (modalId) {
            case 'prenatal-modal':
                closePrenatalModal(e);
                break;
            case 'view-prenatal-modal':
                closeViewPrenatalModal(e);
                break;
            case 'edit-prenatal-modal':
                closeEditPrenatalModal(e);
                break;
        }
    }
});
</script>
@endpush