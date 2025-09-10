@extends('layout.midwife') 
@section('title', 'Child Records')
@section('page-title', 'Child Records')
@section('page-subtitle', 'Manage and monitor child health records')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script type="module" src="https://unpkg.com/cally"></script>

@push('styles')
<style>
    /* Modal Animation Styles - Following Prenatal Record approach */
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

    /* Form Input Focus Styles - Consistent with Prenatal Record */
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
    
    .btn-delete {
        background-color: #fee2e2;
        color: #dc2626;
        border-color: #fecaca;
    }
    
    .btn-delete:hover {
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

    /* Phone input specific styling */
    .phone-input {
        padding-left: 2.5rem;
    }

    .phone-prefix {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-size: 0.875rem;
        pointer-events: none;
        z-index: 10;
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
        min-width: 800px;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative animate-pulse" role="alert">
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
             
        </div>
        <div class="flex space-x-3">
            <button onclick="openAddModal()" 
                class="btn-minimal btn-primary-clean px-4 py-2 rounded-lg font-medium flex items-center space-x-2">
                <i class="fas fa-plus text-sm"></i>
                <span>Add Record</span>
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-4 sm:p-6">
            <form method="GET" action="{{ route('midwife.childrecord.index') }}" class="search-form flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search by child name, mother's name..." 
                               class="input-clean w-full pl-10 pr-4 py-2.5 rounded-lg">
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 search-controls">
                    <select name="gender" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                        <option value="">All Genders</option>
                        <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    <button type="submit" class="btn-minimal px-4 py-2.5 bg-[#68727A] text-white rounded-lg">
                        <i class="fas fa-filter mr-2"></i>Search
                    </button>
                    <a href="{{ route('midwife.childrecord.index') }}" class="btn-minimal px-4 py-2.5 text-gray-600 border border-gray-300 rounded-lg text-center">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Records Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($childRecords->count() > 0)
            <div class="table-wrapper">
                <table class="w-full table-container">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'child_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Child Name <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Gender</th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'birthdate', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                                    Birth Date <i class="fas fa-sort ml-1 text-gray-400"></i>
                                </a>
                            </th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Mother's Name</th>
                            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Phone Number</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($childRecords as $record)
                        <tr class="table-row-hover">
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $record->child_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500 sm:hidden">{{ $record->mother_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ ($record->gender ?? '') === 'Male' ? 'gender-badge-male' : 'gender-badge-female' }}">
                                    <i class="fas {{ ($record->gender ?? '') === 'Male' ? 'fa-mars' : 'fa-venus' }} mr-1"></i>
                                    <span class="hidden sm:inline">{{ $record->gender ?? 'N/A' }}</span>
                                    <span class="sm:hidden">{{ substr($record->gender ?? 'N', 0, 1) }}</span>
                                </span>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                                <div class="text-sm sm:text-base">{{ $record->birthdate ? $record->birthdate->format('M j, Y') : 'N/A' }}</div>
                                <div class="text-xs text-gray-500 sm:hidden">{{ $record->phone_number ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                                {{ $record->mother_name ?? 'N/A' }}
                            </td>
                            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                                {{ $record->phone_number ?? 'N/A' }}
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="action-buttons flex flex-col sm:flex-row sm:justify-center space-y-2 sm:space-y-0 sm:space-x-2">
                                    <a href="#" onclick='openViewRecordModal(@json($record->toArray()))' class="btn-action btn-view inline-flex items-center justify-center">
                                        <i class="fas fa-eye mr-1"></i><span class="hidden sm:inline">View</span>
                                    </a>
                                    <a href="#" onclick='openEditRecordModal(@json($record->toArray()))' class="btn-action btn-edit inline-flex items-center justify-center">
                                        <i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">Edit</span>
                                    </a>
                                    <!--<form method="POST" action="{{ route('midwife.childrecord.destroy', $record->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this child record? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action btn-delete inline-flex items-center justify-center">
                                            <i class="fas fa-trash mr-1"></i><span class="hidden sm:inline">Delete</span>
                                        </button>
                                    </form>-->
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 overflow-x-auto">
                {{ $childRecords->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-baby text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No child records found</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                    @if(request()->hasAny(['search', 'gender']))
                        No records match your search criteria. Try adjusting your filters.
                    @else
                        Get started by adding your first child record.
                    @endif
                </p>
                <button onclick="openAddModal()" class="btn-minimal btn-primary-clean px-6 py-3 rounded-lg font-medium inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add Child Record
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
    @include('partials.midwife.childrecord.childadd')

<!-- View Child Record Modal -->
    @include('partials.midwife.childrecord.childview')

<!-- Edit Modal -->
    @include('partials.midwife.childrecord.childedit')
@endsection

@push('scripts')
<script>
// Global variables to store current record for modal operations
let currentRecord = null;
let isExistingMother = false;

// Close Edit Child Modal - FIXED FUNCTION (moved to global scope)
function closeEditChildModal(event) {
    // Prevent closing if click is inside modal content
    if (event && event.target !== event.currentTarget) return;
    
    const modal = document.getElementById('edit-child-modal');
    if (!modal) return;
    
    // Remove show class to trigger fade out animation
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Clear form if no server validation errors
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('edit-child-form');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }
        }
    }, 300);
}

// Phone number validation and formatting
function formatPhoneNumber(input) {
    // Remove all non-digits
    let value = input.value.replace(/\D/g, '');
    
    // Handle different input formats
    if (value.startsWith('63')) {
        value = value.substring(2); // Remove country code
    } else if (value.startsWith('0')) {
        value = value.substring(1); // Remove leading zero
    }
    
    // Ensure it starts with 9 for Philippine mobile
    if (value.length > 0 && !value.startsWith('9')) {
        // If it doesn't start with 9, try to correct common patterns
        if (value.length >= 10) {
            value = '9' + value.substring(1);
        }
    }
    
    // Limit to 10 digits
    value = value.substring(0, 10);
    
    input.value = value;
    
    // Validate format
    const isValid = /^9\d{9}$/.test(value);
    input.classList.toggle('error-border', !isValid && value.length === 10);
    input.classList.toggle('success-border', isValid);
    
    return isValid;
}

// Form validation following Prenatal Record approach
function validateField() {
    const field = this;
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
            case 'child_name':
            case 'mother_name':
                if (value.length < 2) {
                    isValid = false;
                }
                break;
            case 'phone_number':
            case 'mother_contact':
                isValid = formatPhoneNumber(field);
                break;
            case 'birthdate':
                const birthDate = new Date(value);
                const today = new Date();
                if (birthDate > today) {
                    isValid = false;
                }
                break;
            case 'birth_height':
                const height = parseFloat(value);
                if (value && (isNaN(height) || height < 0 || height > 999.99)) {
                    isValid = false;
                }
                break;
            case 'birth_weight':
                const weight = parseFloat(value);
                if (value && (isNaN(weight) || weight < 0 || weight > 99.999)) {
                    isValid = false;
                }
                break;
            case 'mother_age':
                const age = parseInt(value);
                if (value && (isNaN(age) || age < 15 || age > 50)) {
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

// Add modal functions - includes mother selection functionality
function openAddModal() {
    const modal = document.getElementById('recordModal');
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');
    
    if (!modal) {
        console.error('Add modal element not found');
        return;
    }
    
    // Check if this modal has mother selection functionality
    const hasMotherSelection = motherConfirmationStep && childRecordForm;
    
    if (hasMotherSelection) {
        // Reset modal state for mother selection version
        resetModalState();
        
        // Show confirmation step, hide form
        motherConfirmationStep.classList.remove('hidden');
        childRecordForm.classList.add('hidden');
    } else {
        // Simple modal version - reset form directly
        const form = document.getElementById('recordForm');
        if (form) {
            // Set form action dynamically
            const storeUrl = form.dataset.storeUrl || form.action;
            form.action = storeUrl;
            form.reset();
            
            // Clear validation states
            clearValidationStates(form);
        }
        
        // Set modal title if available
        const modalTitle = document.getElementById('modalTitle');
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-baby text-[#68727A] mr-2"></i>Add Child Record';
        }
    }
    
    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    
    // Focus first input after animation
    setTimeout(() => {
        let firstInput;
        if (hasMotherSelection) {
            // Focus will be handled after mother selection
            return;
        } else {
            firstInput = document.querySelector('#recordForm input[name="child_name"]');
        }
        
        if (firstInput) firstInput.focus();
    }, 300);
}

function resetModalState() {
    const form = document.getElementById('recordForm');
    if (form) {
        form.reset();
        clearValidationStates(form);
    }
    
    // Reset mother selection sections if they exist
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherDetails = document.getElementById('motherDetails');
    
    if (existingMotherSection) existingMotherSection.classList.add('hidden');
    if (newMotherSection) newMotherSection.classList.add('hidden');
    if (motherDetails) motherDetails.classList.add('hidden');
    
    // Clear mother exists flag
    const motherExistsInput = document.getElementById('motherExists');
    if (motherExistsInput) {
        motherExistsInput.value = '';
    }
    
    isExistingMother = false;
}

function showMotherForm(exists) {
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherExistsInput = document.getElementById('motherExists');
    
    if (!motherConfirmationStep || !childRecordForm) {
        console.error('Mother selection elements not found');
        return;
    }
    
    // Store the choice
    isExistingMother = exists;
    if (motherExistsInput) {
        motherExistsInput.value = exists ? 'yes' : 'no';
    }
    
    // Hide confirmation step, show form
    motherConfirmationStep.classList.add('hidden');
    childRecordForm.classList.remove('hidden');
    
    // Show appropriate section
    if (exists) {
        if (existingMotherSection) existingMotherSection.classList.remove('hidden');
        if (newMotherSection) newMotherSection.classList.add('hidden');
        updateRequiredFields(true);
        
        // Clear new mother fields
        clearNewMotherFields();
    } else {
        if (existingMotherSection) existingMotherSection.classList.add('hidden');
        if (newMotherSection) newMotherSection.classList.remove('hidden');
        updateRequiredFields(false);
        
        // Clear existing mother selection
        clearExistingMotherSelection();
    }
    
    // Focus first input
    setTimeout(() => {
        const firstInput = document.querySelector('#recordForm input[name="child_name"]');
        if (firstInput) firstInput.focus();
    }, 100);
}

function changeMotherType() {
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');
    
    if (!motherConfirmationStep || !childRecordForm) return;
    
    // Show confirmation step again
    childRecordForm.classList.add('hidden');
    motherConfirmationStep.classList.remove('hidden');
    
    // Reset sections
    resetMotherSections();
}

function goBackToConfirmation() {
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');
    
    if (!motherConfirmationStep || !childRecordForm) return;
    
    childRecordForm.classList.add('hidden');
    motherConfirmationStep.classList.remove('hidden');
    
    // Reset sections
    resetMotherSections();
}

function resetMotherSections() {
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherDetails = document.getElementById('motherDetails');
    
    if (existingMotherSection) existingMotherSection.classList.add('hidden');
    if (newMotherSection) newMotherSection.classList.add('hidden');
    if (motherDetails) motherDetails.classList.add('hidden');
    
    clearExistingMotherSelection();
    clearNewMotherFields();
}

// Mother selection handling
function setupMotherSelection() {
    const motherSelect = document.getElementById('mother_id');
    if (!motherSelect) return;
    
    motherSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const motherDetails = document.getElementById('motherDetails');
        
        if (!motherDetails) return;
        
        if (this.value && selectedOption.dataset.name) {
            // Show mother details
            const motherName = document.getElementById('motherName');
            const motherAge = document.getElementById('motherAge');
            const motherContact = document.getElementById('motherContact');
            const motherAddress = document.getElementById('motherAddress');
            
            if (motherName) motherName.textContent = selectedOption.dataset.name || '-';
            if (motherAge) motherAge.textContent = selectedOption.dataset.age || '-';
            if (motherContact) motherContact.textContent = selectedOption.dataset.contact || '-';
            if (motherAddress) motherAddress.textContent = selectedOption.dataset.address || '-';
            
            motherDetails.classList.remove('hidden');
            
            // Auto-fill contact details from mother info
            const phoneInput = document.getElementById('phone_number');
            const addressInput = document.getElementById('address');
            
            if (phoneInput && selectedOption.dataset.contact) {
                let contact = selectedOption.dataset.contact;
                // Format for phone input (remove +63 if present)
                if (contact.startsWith('+63')) {
                    contact = contact.substring(3);
                } else if (contact.startsWith('63')) {
                    contact = contact.substring(2);
                } else if (contact.startsWith('0')) {
                    contact = contact.substring(1);
                }
                phoneInput.value = contact;
                phoneInput.readOnly = true;
                phoneInput.classList.add('bg-gray-100');
            }
            
            if (addressInput && selectedOption.dataset.address) {
                addressInput.value = selectedOption.dataset.address;
                addressInput.readOnly = true;
                addressInput.classList.add('bg-gray-100');
            }
            
        } else {
            motherDetails.classList.add('hidden');
            // Clear and enable contact inputs
            const phoneInput = document.getElementById('phone_number');
            const addressInput = document.getElementById('address');
            
            if (phoneInput) {
                phoneInput.value = '';
                phoneInput.readOnly = false;
                phoneInput.classList.remove('bg-gray-100');
            }
            
            if (addressInput) {
                addressInput.value = '';
                addressInput.readOnly = false;
                addressInput.classList.remove('bg-gray-100');
            }
        }
    });
}

// Field management functions
function updateRequiredFields(isExisting) {
    const motherIdSelect = document.getElementById('mother_id');
    const motherNameInput = document.getElementById('mother_name');
    const motherAgeInput = document.getElementById('mother_age');
    const motherContactInput = document.getElementById('mother_contact');
    const motherAddressInput = document.getElementById('mother_address');
    
    if (isExisting) {
        // Existing mother - require selection
        if (motherIdSelect) motherIdSelect.setAttribute('required', 'required');
        if (motherNameInput) motherNameInput.removeAttribute('required');
        if (motherAgeInput) motherAgeInput.removeAttribute('required');
        if (motherContactInput) motherContactInput.removeAttribute('required');
        if (motherAddressInput) motherAddressInput.removeAttribute('required');
    } else {
        // New mother - require manual inputs
        if (motherIdSelect) motherIdSelect.removeAttribute('required');
        if (motherNameInput) motherNameInput.setAttribute('required', 'required');
        if (motherAgeInput) motherAgeInput.setAttribute('required', 'required');
        if (motherContactInput) motherContactInput.setAttribute('required', 'required');
        if (motherAddressInput) motherAddressInput.setAttribute('required', 'required');
    }
}

function clearExistingMotherSelection() {
    const motherSelect = document.getElementById('mother_id');
    if (motherSelect) {
        motherSelect.value = '';
        motherSelect.dispatchEvent(new Event('change'));
    }
}

function clearNewMotherFields() {
    const fields = ['mother_name', 'mother_age', 'mother_contact', 'mother_address'];
    fields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.value = '';
            field.classList.remove('error-border', 'success-border');
        }
    });
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    
    const modal = document.getElementById('recordModal');
    if (!modal) return;
    
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        
        // Only reset if no validation errors from server
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('recordForm');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }
            
            // Reset modal state including mother selection
            resetModalState();
        }
    }, 300);
}

function clearValidationStates(form) {
    if (!form) return;
    
    form.querySelectorAll('.error-border, .success-border').forEach(input => {
        input.classList.remove('error-border', 'success-border');
    });
}

// View Record Modal Functions
function openViewRecordModal(record) {
    if (!record) {
        console.error('No child record provided');
        return;
    }
    
    try {
        // Store current record
        currentRecord = record;
        
        // Populate modal fields - safely handle null/undefined values
        const fieldMappings = [
            { id: 'modalChildName', value: record.child_name },
            { id: 'modalChildGender', value: record.gender },
            { id: 'modalMotherName', value: record.mother_name },
            { id: 'modalFatherName', value: record.father_name },
            { id: 'modalBirthPlace', value: record.birthplace }
        ];
        
        fieldMappings.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.textContent = field.value || 'N/A';
            }
        });
        
        // Format birth date and calculate age
        if (record.birthdate) {
            const birthDate = new Date(record.birthdate);
            const birthdateElement = document.getElementById('modalBirthDate');
            if (birthdateElement) {
                birthdateElement.textContent = birthDate.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            }
            
            // Calculate age
            const today = new Date();
            const ageInMonths = (today.getFullYear() - birthDate.getFullYear()) * 12 + (today.getMonth() - birthDate.getMonth());
            const years = Math.floor(ageInMonths / 12);
            const months = ageInMonths % 12;
            
            let ageString = '';
            if (years > 0) {
                ageString = `${years} year${years > 1 ? 's' : ''}`;
                if (months > 0) {
                    ageString += ` ${months} month${months > 1 ? 's' : ''}`;
                }
            } else {
                ageString = `${months} month${months > 1 ? 's' : ''}`;
            }
            
            const ageElement = document.getElementById('modalChildAge');
            if (ageElement) {
                ageElement.textContent = ageString;
            }
        } else {
            const birthdateElement = document.getElementById('modalBirthDate');
            const ageElement = document.getElementById('modalChildAge');
            if (birthdateElement) birthdateElement.textContent = 'N/A';
            if (ageElement) ageElement.textContent = 'N/A';
        }
        
        // Birth details
        const birthWeightElement = document.getElementById('modalBirthWeight');
        if (birthWeightElement) {
            birthWeightElement.textContent = record.birth_weight ? `${record.birth_weight} kg` : 'N/A';
        }
        
        const birthHeightElement = document.getElementById('modalBirthHeight');
        if (birthHeightElement) {
            birthHeightElement.textContent = record.birth_height ? `${record.birth_height} cm` : 'N/A';
        }
        
        // Contact information - Format phone number for display
        let displayPhone = record.phone_number || 'N/A';
        if (displayPhone !== 'N/A' && displayPhone.length === 10 && displayPhone.startsWith('9')) {
            displayPhone = `+63${displayPhone}`;
        }
        const phoneElement = document.getElementById('modalPhoneNumber');
        if (phoneElement) {
            phoneElement.textContent = displayPhone;
        }
        
        const addressElement = document.getElementById('modalAddress');
        if (addressElement) {
            addressElement.textContent = record.address || 'N/A';
        }
        
        // Created date
        if (record.created_at) {
            const createdDate = new Date(record.created_at);
            const createdDateElement = document.getElementById('modalCreatedDate');
            if (createdDateElement) {
                createdDateElement.textContent = createdDate.toLocaleDateString();
            }
        } else {
            const createdDateElement = document.getElementById('modalCreatedDate');
            if (createdDateElement) {
                createdDateElement.textContent = 'N/A';
            }
        }
        
        // Show modal with animation
        const modal = document.getElementById('viewChildModal');
        const content = document.getElementById('viewChildModalContent');
        
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

function closeViewChildModal(event) {
    if (event && event.target !== event.currentTarget) return;
    
    const modal = document.getElementById('viewChildModal');
    const content = document.getElementById('viewChildModalContent');
    
    if (!modal || !content) return;
    
    content.classList.remove('translate-y-0', 'opacity-100');
    content.classList.add('-translate-y-10', 'opacity-0');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// Edit Record Modal Functions - CORRECTED VERSION
function openEditRecordModal(record) {
    if (!record) {
        console.error('No child record provided');
        return;
    }

    const modal = document.getElementById('edit-child-modal');
    if (!modal) {
        console.error('Edit modal element not found');
        return;
    }

    const form = document.getElementById('edit-child-form');
    if (!form) {
        console.error('Edit form not found');
        return;
    }

    // CRITICAL FIX: Update form action with correct ID
    const updateUrl = form.dataset.updateUrl;
    if (updateUrl && record.id) {
        // Replace :id placeholder with actual record ID
        form.action = updateUrl.replace(':id', record.id);
        console.log('Form action set to:', form.action); // Debug log
    } else {
        console.error('Unable to set form action. UpdateUrl:', updateUrl, 'Record ID:', record.id);
        return;
    }

    // Store current record
    currentRecord = record;

    // Format the date to "yyyy-MM-dd" for date inputs
    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    };

    // Populate form fields
    const fieldMappings = [
        { id: 'edit-record-id', value: record.id },
        { id: 'edit-child-name', value: record.child_name },
        { id: 'edit-birthdate', value: formatDate(record.birthdate) },
        { id: 'edit-birth-height', value: record.birth_height },
        { id: 'edit-birth-weight', value: record.birth_weight },
        { id: 'edit-birthplace', value: record.birthplace },
        { id: 'edit-mother-name', value: record.mother_name },
        { id: 'edit-father-name', value: record.father_name },
        { id: 'edit-address', value: record.address }
    ];

    fieldMappings.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.value = field.value || '';
            element.classList.remove('error-border', 'success-border');
        } else {
            console.warn('Element not found:', field.id);
        }
    });

    // Set gender radio button
    const maleRadio = document.getElementById('edit-gender-male');
    const femaleRadio = document.getElementById('edit-gender-female');
    if (maleRadio && femaleRadio) {
        maleRadio.checked = record.gender === 'Male';
        femaleRadio.checked = record.gender === 'Female';
    }

    // Format phone number for editing (remove +63 prefix if present)
    let phoneValue = record.phone_number || '';
    if (phoneValue.startsWith('+63')) {
        phoneValue = phoneValue.substring(3);
    } else if (phoneValue.startsWith('63')) {
        phoneValue = phoneValue.substring(2);
    } else if (phoneValue.startsWith('0')) {
        phoneValue = phoneValue.substring(1);
    }
    const phoneInput = document.getElementById('edit-phone-number');
    if (phoneInput) {
        phoneInput.value = phoneValue;
    }

    // Clear validation states
    clearValidationStates(form);

    // Show modal with proper animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        const nameInput = document.getElementById('edit-child-name');
        if (nameInput) nameInput.focus();
    }, 100);
}

// Date constraints - UPDATED to disable future dates for birthdate
function setDateConstraints() {
    const birthdateInputs = ['birthdate', 'edit-birthdate'];
    
    birthdateInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            // Get today's date in YYYY-MM-DD format
            const today = new Date();
            const todayString = today.toISOString().split('T')[0];
            
            // Set maximum date to today (prevents future dates)
            input.setAttribute('max', todayString);
            
            // Set reasonable minimum date (100 years ago for maximum flexibility)
            const minDate = new Date();
            minDate.setFullYear(minDate.getFullYear() - 100);
            const minDateString = minDate.toISOString().split('T')[0];
            input.setAttribute('min', minDateString);
            
            // Add event listener to validate on change
            input.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const currentDate = new Date();
                
                if (selectedDate > currentDate) {
                    this.setCustomValidity('Birth date cannot be in the future');
                    this.classList.add('error-border');
                    this.classList.remove('success-border');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('error-border');
                    if (this.value) {
                        this.classList.add('success-border');
                    }
                }
            });
            
            // Also validate on input (for manual typing)
            input.addEventListener('input', function() {
                if (this.value) {
                    const selectedDate = new Date(this.value);
                    const currentDate = new Date();
                    
                    if (selectedDate > currentDate) {
                        this.setCustomValidity('Birth date cannot be in the future');
                        this.classList.add('error-border');
                        this.classList.remove('success-border');
                    } else {
                        this.setCustomValidity('');
                        this.classList.remove('error-border');
                        this.classList.add('success-border');
                    }
                }
            });
        }
    });
}

// Setup form handling
function setupFormHandling() {
    // Add validation to all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', function() {
                if (this.classList.contains('error-border')) {
                    validateField.call(this);
                }
            });
        });
    });
}



// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Make closeEditChildModal globally available
    window.closeEditChildModal = closeEditChildModal;
    
    // Setup form handling
    setupFormHandling();
    
    // Set date constraints
    setDateConstraints();
    
    // Setup mother selection if available
    setupMotherSelection();
    
    // Close modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
            closeEditChildModal();
            closeViewChildModal();
        }
    });

    const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 2000);
    });
});
</script>
@endpush
