@extends('layout.bhw')
@section('title', 'Child Record Details')
@section('page-title', 'Child Record Details')
@section('page-subtitle', 'View child information and immunization history')

@push('styles')
<style>
    .record-section {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .section-header {
        background: linear-gradient(135deg, #68727A 0%, #36535E 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px 12px 0 0;
    }

    .section-content {
        padding: 1.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.25rem;
    }

    .info-item {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        background: #fafafa;
    }

    .info-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #36535E;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-size: 1.125rem;
        font-weight: 600;
        color: #68727A;
    }

    .immunization-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.25rem;
        background: white;
        transition: all 0.2s ease;
    }

    .immunization-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .vaccine-name {
        font-size: 1.125rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .vaccine-date {
        font-size: 0.875rem;
        color: #68727A;
        margin-bottom: 0.75rem;
    }

    .vaccine-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .vaccine-detail {
        font-size: 0.875rem;
    }

    .vaccine-detail-label {
        font-weight: 600;
        color: #6b7280;
    }

    .vaccine-detail-value {
        color: #374151;
    }

    .btn-action {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: #68727A;
        color: white;
        border: 1px solid #68727A;
    }

    .btn-primary:hover {
        background: #36535E;
        border-color: #36535E;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(104, 114, 122, 0.3);
    }

    .btn-secondary {
        background: white;
        color: #68727A;
        border: 1px solid #68727A;
    }

    .btn-secondary:hover {
        background: #68727A;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(104, 114, 122, 0.3);
    }

    .btn-success {
        background: #10b981;
        color: white;
        border: 1px solid #10b981;
    }

    .btn-success:hover {
        background: #059669;
        border-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: #f9fafb;
        border-radius: 8px;
        border: 2px dashed #d1d5db;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }

    .breadcrumb a {
        color: #68727A;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .breadcrumb a:hover {
        color: #36535E;
    }

    .breadcrumb-separator {
        color: #9ca3af;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .vaccine-details {
            grid-template-columns: 1fr;
        }
        
        .section-content {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="{{ route('bhw.childrecord.index') }}">
            <i class="fas fa-baby"></i> Child Records
        </a>
        <span class="breadcrumb-separator">/</span>
        <span class="text-gray-900 font-medium">{{ $childRecord->child_name }}</span>
    </nav>

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $childRecord->child_name }}</h1>
            <p class="text-gray-600">Child ID: {{ $childRecord->formatted_child_id }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('bhw.childrecord.edit', $childRecord->id) }}" class="btn-action btn-secondary">
                <i class="fas fa-edit"></i>
                Edit Record
            </a>
            <a href="{{ route('bhw.childrecord.index') }}" class="btn-action btn-primary">
                <i class="fas fa-arrow-left"></i>
                Back to List
            </a>
        </div>
    </div>

    <!-- Same content structure as midwife version -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Child Information -->
        <div class="record-section">
            <div class="section-header">
                <h2 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-baby mr-3"></i>
                    Child Information
                </h2>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value">{{ $childRecord->child_name }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value">
                            <i class="fas {{ $childRecord->gender === 'Male' ? 'fa-mars text-blue-500' : 'fa-venus text-pink-500' }} mr-2"></i>
                            {{ $childRecord->gender }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Birth Date</div>
                        <div class="info-value">{{ $childRecord->birthdate ? $childRecord->birthdate->format('F j, Y') : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Age</div>
                        <div class="info-value">{{ $childRecord->age ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Birth Details -->
        <div class="record-section">
            <div class="section-header">
                <h2 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-weight mr-3"></i>
                    Birth Details
                </h2>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Birth Weight</div>
                        <div class="info-value">{{ $childRecord->birth_weight ? number_format($childRecord->birth_weight, 3) . ' kg' : 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Birth Height</div>
                        <div class="info-value">{{ $childRecord->birth_height ? number_format($childRecord->birth_height, 1) . ' cm' : 'N/A' }}</div>
                    </div>
                    <div class="info-item lg:col-span-2">
                        <div class="info-label">Birth Place</div>
                        <div class="info-value">{{ $childRecord->birthplace ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Parent Information -->
        <div class="record-section">
            <div class="section-header">
                <h2 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-users mr-3"></i>
                    Parent Information
                </h2>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Mother's Name</div>
                        <div class="info-value">{{ $childRecord->mother_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Father's Name</div>
                        <div class="info-value">{{ $childRecord->father_name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="record-section">
            <div class="section-header">
                <h2 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-phone mr-3"></i>
                    Contact Information
                </h2>
            </div>
            <div class="section-content">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value">
                            @if($childRecord->phone_number)
                                @php
                                    $phone = $childRecord->phone_number;
                                    if (strlen($phone) === 10 && str_starts_with($phone, '9')) {
                                        $phone = '+63' . $phone;
                                    }
                                @endphp
                                {{ $phone }}
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value">{{ $childRecord->address ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Immunization History -->
    <div class="record-section">
        <div class="section-header">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-syringe mr-3"></i>
                    Immunization History
                </h2>
                <button onclick="openAddImmunizationModal()" class="btn-action btn-success">
                    <i class="fas fa-plus"></i>
                    Add Immunization
                </button>
            </div>
        </div>
        <div class="section-content">
            @if($childRecord->immunizations->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($childRecord->immunizations as $immunization)
                    <div class="immunization-card">
                        <div class="vaccine-name">{{ $immunization->vaccine_name }}</div>
                        <div class="vaccine-date">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            {{ $immunization->vaccination_date->format('F j, Y') }}
                        </div>
                        
                        @if($immunization->vaccine_description)
                        <div class="text-gray-600 text-sm mb-3">
                            {{ $immunization->vaccine_description }}
                        </div>
                        @endif
                        
                        <div class="vaccine-details">
                            <div class="vaccine-detail">
                                <div class="vaccine-detail-label">Administered by:</div>
                                <div class="vaccine-detail-value">{{ $immunization->administered_by }}</div>
                            </div>
                            
                            @if($immunization->batch_number)
                            <div class="vaccine-detail">
                                <div class="vaccine-detail-label">Batch Number:</div>
                                <div class="vaccine-detail-value">{{ $immunization->batch_number }}</div>
                            </div>
                            @endif
                            
                            @if($immunization->next_due_date)
                            <div class="vaccine-detail">
                                <div class="vaccine-detail-label">Next Due:</div>
                                <div class="vaccine-detail-value">{{ $immunization->next_due_date }}</div>
                            </div>
                            @endif
                        </div>
                        
                        @if($immunization->notes)
                        <div class="mt-3 p-3 bg-gray-50 rounded-md">
                            <div class="text-sm font-medium text-gray-700 mb-1">Notes:</div>
                            <div class="text-sm text-gray-600">{{ $immunization->notes }}</div>
                        </div>
                        @endif
                        
                        <div class="flex justify-end mt-4 gap-2">
                            <button onclick="editImmunization({{ $immunization->id }})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="deleteImmunization({{ $immunization->id }})" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-syringe text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Immunizations Recorded</h3>
                    <p class="text-gray-500 mb-6">Start tracking this child's immunization history by adding their first vaccination record.</p>
                    <button onclick="openAddImmunizationModal()" class="btn-action btn-success">
                        <i class="fas fa-plus mr-2"></i>Add First Immunization
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Immunization Modal -->
<div id="addImmunizationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-[#68727A] to-[#36535E] text-white p-6 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold flex items-center">
                    <i class="fas fa-syringe mr-3"></i>
                    Add Immunization Record
                </h3>
                <button onclick="closeAddImmunizationModal()" class="text-white hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <form id="addImmunizationForm" action="{{ route('bhw.childrecord.immunizations.store', $childRecord->id) }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vaccine Name *</label>
                    <input type="text" name="vaccine_name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#68727A] focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vaccination Date *</label>
                    <input type="date" name="vaccination_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#68727A] focus:border-transparent">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Vaccine Description</label>
                <textarea name="vaccine_description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#68727A] focus:border-transparent"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Administered By *</label>
                    <input type="text" name="administered_by" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#68727A] focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                    <input type="text" name="batch_number" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#68727A] focus:border-transparent">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Next Due Date</label>
                    <input type="text" name="next_due_date" placeholder="e.g., 6 months, 1 year" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#68727A] focus:border-transparent">
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3" placeholder="Any additional notes or observations..." class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#68727A] focus:border-transparent"></textarea>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeAddImmunizationModal()" class="btn-action btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn-action btn-success">
                    <i class="fas fa-save mr-2"></i>
                    Save Immunization
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openAddImmunizationModal() {
    document.getElementById('addImmunizationModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Set max date to today
    const dateInput = document.querySelector('input[name="vaccination_date"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('max', today);
    }
}

function closeAddImmunizationModal() {
    document.getElementById('addImmunizationModal').classList.add('hidden');
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('addImmunizationForm').reset();
}

function editImmunization(id) {
    // TODO: Implement edit functionality
    console.log('Edit immunization:', id);
}

function deleteImmunization(id) {
    if (confirm('Are you sure you want to delete this immunization record?')) {
        // TODO: Implement delete functionality
        console.log('Delete immunization:', id);
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('addImmunizationModal');
    if (event.target === modal) {
        closeAddImmunizationModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddImmunizationModal();
    }
});
</script>
@endpush