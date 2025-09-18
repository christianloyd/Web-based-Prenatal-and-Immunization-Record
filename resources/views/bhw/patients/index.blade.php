@extends('layout.bhw')
@section('title', 'Patient Management')
@section('page-title', 'Patient Management')
@section('page-subtitle', 'Manage patient basic information')

@push('styles')
<style>
    :root {
        --primary: #243b55;
        --secondary: #141e30;
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
    
    /* Patient Avatar Styles */
    .patient-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #3B82F6, #1D4ED8);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
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

    /* Badge Styles */
    .badge-success {
        background-color: #10b981;
        color: white;
    }

    .badge-warning {
        background-color: #f59e0b;
        color: white;
    }
     
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <!--<div id="patient-stats-container" class="flex space-x-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border">
                    <div class="text-2xl font-bold text-primary">{{ $patients->total() ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Total Patients</div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm border">
                    <div class="text-2xl font-bold text-green-600">{{ $patients->filter(function($patient) { return $patient->has_active_prenatal_record; })->count() }}</div>
                    <div class="text-sm text-gray-600">With Active Records</div>
                </div>
            </div>-->
        </div>
        <div class="flex space-x-3">
             
            <button onclick="openPatientModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-charcoal-700 transition-all duration-200 flex items-center btn-primary">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                </svg>
                Register New Patient
            </button>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('bhw.patients.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-paynes-charcoal-700 transition-all duration-200 btn-primary">
                        Search
                    </button>
                    <a href="{{ route('bhw.patients.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Include Table Skeleton -->
    @include('components.table-skeleton', [
        'id' => 'bhw-patient-table-skeleton',
        'rows' => 5,
        'columns' => 7,
        'showStats' => true,
        'statsId' => 'bhw-patient-stats-skeleton'
    ])

    <!-- Patients Table -->
    <div id="bhw-patient-main-content" class="bg-white rounded-lg shadow-sm border">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prenatal Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($patients as $patient)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-blue-600">{{ $patient->formatted_patient_id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                 
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $patient->name }}</div>
                                    <!--<div class="text-sm text-gray-500">ID: {{ $patient->formatted_patient_id }}</div>-->
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient->age }} years</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $patient->contact ?? 'N/A' }}</div>
                           
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ Str::limit($patient->address ?? 'N/A', 30) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="text-lg font-semibold">{{ $patient->total_prenatal_records }}</span>
                            @if($patient->has_active_prenatal_record)
                                <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Active</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($patient->is_high_risk_patient)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    High Risk Age
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Normal
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                            <button data-patient='@json($patient)' onclick='openViewPatientModal(JSON.parse(this.dataset.patient))' class="btn-action btn-view inline-flex items-center justify-center">
                                <i class="fas fa-eye mr-1"></i>
                            <span class="hidden sm:inline">View</span>
                            </button>
                        <button data-patient='@json($patient)' onclick='openEditPatientModal(JSON.parse(this.dataset.patient))' class="btn-action btn-edit inline-flex items-center justify-center">
                                <i class="fas fa-edit mr-1"></i>
                                <span class="hidden sm:inline">Edit</span>
                        </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 mb-2">No patients found</p>
                                <p class="text-gray-600 mb-4">Get started by registering your first patient</p>
                                <button onclick="openPatientModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors btn-primary">
                                    Register First Patient
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($patients->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add New Patient Modal -->
@include('partials.bhw.patient.patient_add')

<!-- View Patient Modal -->
@include('partials.bhw.patient.patient_view')

<!-- Edit Patient Modal -->
@include('partials.bhw.patient.patient_edit')

@endsection

@push('scripts')
<script>
// Patient Modal Management
// Patient Modal Management
function openPatientModal() {
    const modal = document.getElementById('patient-modal');
    if (!modal) return console.error('Patient modal not found');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        const nameInput = modal.querySelector('input[name="name"]');
        if (nameInput) nameInput.focus();
    }, 300);
}

function closePatientModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('patient-modal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        const form = modal.querySelector('form');
        if (form && !document.querySelector('.bg-red-100')) {
            form.reset();
        }
    }, 300);
}

// Global variable to store current patient data
let currentPatientData = null;

function openViewPatientModal(patient) {
    if (!patient) return console.error('No patient data provided');
    
    // Store patient data globally for other functions
    currentPatientData = patient;
    
    // Populate modal fields
    document.getElementById('viewPatientName').textContent = patient.name || 'N/A';
    document.getElementById('viewPatientId').textContent = patient.formatted_patient_id || 'N/A';
    document.getElementById('viewPatientAge').textContent = patient.age ? patient.age + ' years' : 'N/A';
    document.getElementById('viewPatientContact').textContent = patient.contact || 'N/A';
    document.getElementById('viewPatientEmergencyContact').textContent = patient.emergency_contact || 'N/A';
    document.getElementById('viewPatientAddress').textContent = patient.address || 'N/A';
    document.getElementById('viewPatientOccupation').textContent = patient.occupation || 'N/A';
    
    // Set status from prenatal record with appropriate styling
    const riskStatusElement = document.getElementById('viewPatientRiskStatus');
    let statusHtml = '';

    if (patient.active_prenatal_record && patient.active_prenatal_record.status) {
        const status = patient.active_prenatal_record.status;

        switch(status) {
            case 'normal':
                statusHtml = '<span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Normal</span>';
                break;
            case 'monitor':
                statusHtml = '<span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Monitor</span>';
                break;
            case 'high-risk':
                statusHtml = '<span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">High Risk</span>';
                break;
            case 'due':
                statusHtml = '<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Due</span>';
                break;
            case 'completed':
                statusHtml = '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Completed</span>';
                break;
            default:
                statusHtml = '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Unknown</span>';
        }
    } else {
        statusHtml = '<span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">No Prenatal Record</span>';
    }

    riskStatusElement.innerHTML = statusHtml;
    
    // Set created date if available
    const createdAtElement = document.getElementById('viewPatientCreatedAt');
    if (patient.created_at) {
        const date = new Date(patient.created_at);
        createdAtElement.textContent = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    } else {
        createdAtElement.textContent = 'N/A';
    }
    
    // Update prenatal records link
    const prenatalLink = document.getElementById('viewPrenatalRecordsLink');
    if (prenatalLink) {
        const baseUrl = "{!! route('bhw.prenatalrecord.index') !!}";
        prenatalLink.href = baseUrl + '?search=' + encodeURIComponent(patient.name || patient.formatted_patient_id);
    }
    
    // Show modal
    const modal = document.getElementById('view-patient-modal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}

function closeViewPatientModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('view-patient-modal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentPatientData = null; // Clear stored data
    }, 300);
}

function closeViewPatientModalAndEdit() {
    if (!currentPatientData) return;
    closeViewPatientModal();
    // Wait for the view modal to close before opening edit modal
    setTimeout(() => {
        openEditPatientModal(currentPatientData);
    }, 350);
}

function openEditPatientModal(patient) {
    if (!patient) return console.error('No patient data provided');
    
    const modal = document.getElementById('edit-patient-modal');
    const form = document.getElementById('edit-patient-form');
    if (!modal || !form) return console.error('Edit modal elements not found');
    
    // Set form action
    if (form.dataset.updateUrl) {
        form.action = form.dataset.updateUrl.replace(':id', patient.id);
    }
    
    // Populate form fields
    const fields = {
        'edit-name': patient.name || '',
        'edit-age': patient.age || '',
        'edit-contact': patient.contact || '',
        'edit-emergency-contact': patient.emergency_contact || '',
        'edit-address': patient.address || '',
        'edit-occupation': patient.occupation || ''
    };
    
    Object.entries(fields).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    });
    
    // Show modal
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        const firstInput = document.getElementById('edit-name');
        if (firstInput) firstInput.focus();
    }, 100);
}

function closeEditPatientModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('edit-patient-modal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// Close modals on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closePatientModal();
        closeViewPatientModal();
        closeEditPatientModal();
    }
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[id*="patient-form"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const nameInput = this.querySelector('input[name="name"]');
            const ageInput = this.querySelector('input[name="age"]');
            
            if (!nameInput || !nameInput.value.trim()) {
                e.preventDefault();
                if (nameInput) nameInput.focus();
                alert('Patient name is required.');
                return;
            }
            
            if (!ageInput || !ageInput.value || ageInput.value < 15 || ageInput.value > 50) {
                e.preventDefault();
                if (ageInput) ageInput.focus();
                alert('Age must be between 15 and 50 years.');
                return;
            }
        });
    });
    
    // Auto-hide success/error messages after 5 seconds (but not status badges)
    const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Search form enhancement
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form[method="GET"]');
    const searchInput = searchForm?.querySelector('input[name="search"]');
    
    if (searchInput) {
        // Clear search on double click
        searchInput.addEventListener('dblclick', function() {
            this.value = '';
            this.focus();
        });
        
        // Submit on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
    }
});
</script>

{{-- Include Refresh Data Script --}}
@include('components.refresh-data-script', [
    'contentId' => 'bhw-patient-main-content',
    'skeletonId' => 'bhw-patient-table-skeleton',
    'statsId' => 'patient-stats-container',
    'statsSkeletonId' => 'bhw-patient-stats-skeleton',
    'refreshBtnId' => 'bhw-patient-refresh-btn',
    'hasStats' => true
])
@endpush