@extends('layout.midwife')
@section('title', 'Vaccine Management')
@section('page-title', 'Vaccine Management')
@section('page-subtitle', 'Manage vaccine information')

@push('styles')
<style>
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
    
    /* Vaccine Icon Styles */
    .vaccine-icon {
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
    
     
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    /* Section Divider */
    .section-divider {
        border-left: 2px solid #3B82F6;
        padding-left: 1rem;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <div></div>
        <div class="flex space-x-3">
            <button onclick="openVaccineModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-charcoal-700 transition-all duration-200 flex items-center btn-primary">
                <i class="fas fa-plus w-4 h-4 mr-2"></i>
                Add Vaccine
            </button>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('midwife.vaccines.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or category..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <i class="fas fa-search w-5 h-5 text-gray-400 absolute left-3 top-2.5"></i>
                    </div>
                </div>
                <div>
                    <select name="category" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-paynes-charcoal-700 transition-all duration-200 btn-primary">
                        Search
                    </button>
                    <a href="{{ route('midwife.vaccines.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Vaccines Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vaccine</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage (ml)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doses</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($vaccines as $vaccine)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                 
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $vaccine->name }}</div> 
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vaccine->category_color }}">
                                {{ $vaccine->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $vaccine->dosage }}{{ !str_contains($vaccine->dosage, 'ml') ? ' ml' : '' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $vaccine->dose_count }} {{ $vaccine->dose_count == 1 ? 'Dose' : 'Doses' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="{{ $vaccine->is_expiring_soon ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                {{ $vaccine->expiry_date->format('M d, Y') }}
                                @if($vaccine->is_expiring_soon)
                                    <div class="text-xs text-red-600">Expiring Soon</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                            <button data-vaccine='@json($vaccine)' onclick='openViewVaccineModal(JSON.parse(this.dataset.vaccine))' class="btn-action btn-view inline-flex items-center justify-center">
                                <i class="fas fa-eye mr-1"></i>
                            <span class="hidden sm:inline"><!--View--></span>
                            </button>
                                <button data-vaccine='@json($vaccine)' onclick='openEditVaccineModal(JSON.parse(this.dataset.vaccine))' class="btn-action btn-edit inline-flex items-center justify-center">
                                <i class="fas fa-edit mr-1"></i>
                                <span class="hidden sm:inline"><!--Edit--></span>
                            </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-flask w-12 h-12 text-gray-400 mb-4"></i>
                                <p class="text-lg font-medium text-gray-900 mb-2">No vaccines found</p>
                                <p class="text-gray-600 mb-4">Get started by adding your first vaccine</p>
                                <button onclick="openVaccineModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors btn-primary">
                                    Add First Vaccine
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($vaccines->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $vaccines->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add New Vaccine Modal -->
@include('partials.midwife.vaccine.vaccine_add')

<!-- View Vaccine Modal -->
@include('partials.midwife.vaccine.vaccine_view')

<!-- Edit Vaccine Modal -->
@include('partials.midwife.vaccine.vaccine_edit')

<!-- Stock Management Modal -->

@endsection

@push('scripts')
<script>
// Vaccine Modal Management
function openVaccineModal() {
    const modal = document.getElementById('vaccine-modal');
    if (!modal) return console.error('Vaccine modal not found');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        const nameInput = modal.querySelector('input[name="name"]');
        if (nameInput) nameInput.focus();
    }, 300);
}

function closeVaccineModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('vaccine-modal');
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

// Global variable to store current vaccine data
let currentVaccineData = null;

function openViewVaccineModal(vaccine) {
    if (!vaccine) return console.error('No vaccine data provided');
    
    currentVaccineData = vaccine;
    
    // Populate modal fields
    document.getElementById('viewVaccineName').textContent = vaccine.name || 'N/A';
    document.getElementById('viewVaccineCategory').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${vaccine.category_color}">${vaccine.category}</span>`;
    // Format dosage to ensure it shows ml
    const dosageText = vaccine.dosage || 'N/A';
    document.getElementById('viewVaccineDosage').textContent = dosageText !== 'N/A' && !dosageText.includes('ml') ? dosageText + ' ml' : dosageText;

    // Set dose count with badge styling
    const doseCount = vaccine.dose_count || 1;
    document.getElementById('viewVaccineDoseCount').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">${doseCount} ${doseCount == 1 ? 'Dose' : 'Doses'}</span>`;
    document.getElementById('viewVaccineExpiryDate').textContent = new Date(vaccine.expiry_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('viewVaccineStorageTemp').textContent = vaccine.storage_temp || 'N/A';
    document.getElementById('viewVaccineNotes').textContent = vaccine.notes || 'No notes available';
    
    // Set created date
    const createdAtElement = document.getElementById('viewVaccineCreatedAt');
    if (vaccine.created_at) {
        const date = new Date(vaccine.created_at);
        createdAtElement.textContent = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    } else {
        createdAtElement.textContent = 'N/A';
    }
    
    // Show modal
    const modal = document.getElementById('view-vaccine-modal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}

function closeViewVaccineModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('view-vaccine-modal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentVaccineData = null;
    }, 300);
}

function closeViewVaccineModalAndEdit() {
    if (!currentVaccineData) return;
    closeViewVaccineModal();
    setTimeout(() => {
        openEditVaccineModal(currentVaccineData);
    }, 350);
}

function openEditVaccineModal(vaccine) {
    if (!vaccine) return console.error('No vaccine data provided');
    
    const modal = document.getElementById('edit-vaccine-modal');
    const form = document.getElementById('edit-vaccine-form');
    if (!modal || !form) return console.error('Edit modal elements not found');
    
    // Set form action
    if (form.dataset.updateUrl) {
        form.action = form.dataset.updateUrl.replace(':id', vaccine.id);
    }
    
    // Populate form fields
    const fields = {
        'edit-name': vaccine.name || '',
        'edit-category': vaccine.category || '',
        'edit-dosage': vaccine.dosage || '',
        'edit-dose-count': vaccine.dose_count || '1',
        'edit-expiry-date': vaccine.expiry_date || '',
        'edit-storage-temp': vaccine.storage_temp || '',
        'edit-notes': vaccine.notes || ''
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

function closeEditVaccineModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('edit-vaccine-modal');
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
        closeVaccineModal();
        closeViewVaccineModal();
        closeEditVaccineModal();
    }
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[id*="vaccine-form"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const nameInput = this.querySelector('input[name="name"]');
            const categoryInput = this.querySelector('select[name="category"]');
            const expiryInput = this.querySelector('input[name="expiry_date"]');

            if (!nameInput || !nameInput.value.trim()) {
                e.preventDefault();
                if (nameInput) nameInput.focus();
                showError('Vaccine name is required.');
                return;
            }

            if (!categoryInput || !categoryInput.value) {
                e.preventDefault();
                if (categoryInput) categoryInput.focus();
                showError('Category is required.');
                return;
            }

            if (expiryInput && expiryInput.value) {
                const today = new Date();
                const expiry = new Date(expiryInput.value);
                if (expiry <= today) {
                    e.preventDefault();
                    if (expiryInput) expiryInput.focus();
                    showError('Expiry date must be in the future.');
                    return;
                }
            }
        });
    });
    
    // Auto-hide success/error messages after 5 seconds
    const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Update stock info when vaccine selection changes
    const vaccineSelect = document.getElementById('vaccine_id');
    if (vaccineSelect) {
        vaccineSelect.addEventListener('change', updateStockInfo);
    }
});
</script>
@endpush