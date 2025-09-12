@extends('layout.midwife')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage system users and roles')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
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

    /* Role Badge Styles */
    .role-badge-midwife {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .role-badge-bhw {
        background-color: #dcfce7;
        color: #166534;
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
    .btn-deactivate {
        transition: all 0.15s ease;
    }
    
    .btn-activate {
        transition: all 0.15s ease;
    }
    
    .btn-deactivate:hover,
    .btn-activate:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Flowbite Modal Transition Styles */
    .modal-transition {
        transition: opacity 0.25s ease-in-out;
        background-color: rgba(0, 0, 0, 0);
    }
    
    .modal-transition:not(.hidden) {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-transition.hidden {
        opacity: 0;
        background-color: rgba(0, 0, 0, 0);
    }
    
    .modal-content-transition {
        transition: transform 0.25s ease-out, opacity 0.25s ease-out;
        transform: translateY(-16px) scale(0.95);
        opacity: 0;
    }
    
    .modal-transition:not(.hidden) .modal-content-transition {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    
    /* Smooth modal entrance animation */
    .modal-transition.show {
        animation: modalFadeIn 0.25s ease-out forwards;
    }
    
    .modal-transition.show .modal-content-transition {
        animation: modalSlideIn 0.25s ease-out forwards;
    }
    
    @keyframes modalFadeIn {
        from {
            opacity: 0;
            background-color: rgba(0, 0, 0, 0);
        }
        to {
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }
    }
    
    @keyframes modalSlideIn {
        from {
            transform: translateY(-16px) scale(0.95);
            opacity: 0;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
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
        <!-- Statistics Cards -->
        <div id="stats-container" class="flex space-x-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-primary">{{ $users->total() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Total Users</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-green-600">{{ $users->where('is_active', true)->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Active Users</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-red-600">{{ $users->where('is_active', false)->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Inactive Users</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-blue-600">{{ $users->where('role', 'Midwife')->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">Midwives</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="text-2xl font-bold text-purple-600">{{ $users->where('role', 'BHW')->count() ?? 0 }}</div>
                <div class="text-sm text-gray-600">BHWs</div>
            </div>
        </div>
        
        <!-- Statistics Skeleton -->
        <div id="stats-skeleton" class="hidden flex space-x-4">
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse">
                <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
                <div class="h-4 bg-gray-200 rounded w-20"></div>
            </div>
        </div>
    </div>
    <div class="flex space-x-3">
        <button onclick="simulateDataRefresh()" 
            class="btn-minimal px-4 py-2 bg-gray-600 text-white rounded-lg font-medium flex items-center space-x-2 hover:bg-gray-700">
            <i class="fas fa-sync-alt text-sm"></i>
            <span>Refresh Data</span>
        </button>
        <button onclick="openAddModal()" 
            class="btn-minimal btn-primary-clean px-4 py-2 rounded-lg font-medium flex items-center space-x-2">
            <i class="fas fa-plus text-sm"></i>
            <span>Add User</span>
        </button>
    </div>
</div>

    <!-- Search and Filters -->
    <!-- Add this to your search and filters section (around line 186) -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4 sm:p-6">
        <form method="GET" action="{{ route('midwife.user.index') }}" class="search-form flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by name, username..." 
                           class="input-clean w-full pl-10 pr-4 py-2.5 rounded-lg">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 search-controls">
                <select name="role" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                    <option value="">All Roles</option>
                    <option value="Midwife" {{ request('role') == 'Midwife' ? 'selected' : '' }}>Midwife</option>
                    <option value="BHW" {{ request('role') == 'BHW' ? 'selected' : '' }}>BHW</option>
                </select>
                <select name="gender" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                    <option value="">All Genders</option>
                    <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
                <!-- NEW STATUS FILTER -->
                <select name="status" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" onclick="showSkeletonLoaders()" class="btn-minimal px-4 py-2.5 bg-[#68727A] text-white rounded-lg">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('midwife.user.index') }}" class="btn-minimal px-4 py-2.5 text-gray-600 border border-gray-300 rounded-lg text-center">
                    <i class="fas fa-times mr-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table Skeleton -->
        <div id="table-skeleton" class="hidden">
            <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-4 animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-20"></div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                        <div class="h-4 bg-gray-200 rounded w-12"></div>
                        <div class="h-4 bg-gray-200 rounded w-14"></div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                        <div class="h-4 bg-gray-200 rounded w-20"></div>
                        <div class="h-4 bg-gray-200 rounded w-16"></div>
                    </div>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @for($i = 0; $i < 5; $i++)
                <div class="px-4 py-3 animate-pulse">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="h-4 bg-gray-200 rounded w-32"></div>
                            <div class="h-4 bg-gray-200 rounded w-24"></div>
                            <div class="h-6 bg-gray-200 rounded-full w-20"></div>
                            <div class="h-6 bg-gray-200 rounded-full w-16"></div>
                            <div class="h-4 bg-gray-200 rounded w-20"></div>
                            <div class="h-4 bg-gray-200 rounded w-28"></div>
                        </div>
                        <div class="flex space-x-2">
                            <div class="h-8 bg-gray-200 rounded w-12"></div>
                            <div class="h-8 bg-gray-200 rounded w-12"></div>
                            <div class="h-8 bg-gray-200 rounded w-20"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Actual Table Content -->
        <div id="table-content">
        @if($users->count() > 0)
            <div class="table-wrapper">
            <table class="w-full table-container">
    <thead class="bg-gray-50 border-b border-gray-200">
        <tr>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                    Full Name <i class="fas fa-sort ml-1 text-gray-400"></i>
                </a>
            </th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Username</th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Role</th>
            <!-- NEW STATUS COLUMN -->
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                    Status <i class="fas fa-sort ml-1 text-gray-400"></i>
                </a>
            </th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Gender</th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Contact</th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        @foreach($users as $user)
        <tr class="table-row-hover">
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <div class="font-medium text-gray-900">{{ $user->name ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500 sm:hidden">{{ $user->username ?? 'N/A' }}</div>
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <div class="text-sm sm:text-base text-gray-700">{{ $user->username ?? 'N/A' }}</div>
                <div class="text-xs text-gray-500 sm:hidden">{{ $user->contact_number ?? 'N/A' }}</div>
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ ($user->role ?? '') === 'Midwife' ? 'role-badge-midwife' : 'role-badge-bhw' }}">
                    <i class="fas {{ ($user->role ?? '') === 'Midwife' ? 'fa-user-md' : 'fa-hands-helping' }} mr-1"></i>
                    <span class="hidden sm:inline">{{ $user->role ?? 'N/A' }}</span>
                    <span class="sm:hidden">{{ substr($user->role ?? 'N', 0, 1) }}</span>
                </span>
            </td>
            <!-- NEW STATUS COLUMN -->
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                    <span class="hidden sm:inline">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="sm:hidden">{{ $user->is_active ? 'A' : 'I' }}</span>
                </span>
            </td>
            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ ($user->gender ?? '') === 'Male' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                    <i class="fas {{ ($user->gender ?? '') === 'Male' ? 'fa-mars' : 'fa-venus' }} mr-1"></i>
                    {{ $user->gender ?? 'N/A' }}
                </span>
            </td>
            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                {{ $user->contact_number ?? 'N/A' }}
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <div class="action-buttons flex flex-col sm:flex-row sm:justify-center space-y-2 sm:space-y-0 sm:space-x-2">
                    <a href="#" onclick='openViewUserModal(@json($user->toArray()))' class="btn-action btn-view inline-flex items-center justify-center">
                        <i class="fas fa-eye mr-1"></i><span class="hidden sm:inline">View</span>
                    </a>
                    <a href="#" onclick='openEditUserModal(@json($user->toArray()))' class="btn-action btn-edit inline-flex items-center justify-center">
                        <i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">Edit</span>
                    </a>
                    <!-- UPDATED ACTION BUTTONS -->
                    @if($user->is_active)
                        <button onclick="confirmDeactivate('{{ $user->name }}', function() { deactivateUser({{ $user->id }}) })" class="btn-action btn-deactivate inline-flex items-center justify-center bg-orange-100 text-orange-700 border-orange-200 hover:bg-orange-500 hover:text-white hover:border-orange-500">
                            <i class="fas fa-user-slash mr-1"></i><span class="hidden sm:inline">Deactivate</span>
                        </button>
                    @else
                        <button onclick="confirmActivate('{{ $user->name }}', function() { activateUser({{ $user->id }}) })" class="btn-action btn-activate inline-flex items-center justify-center bg-green-100 text-green-700 border-green-200 hover:bg-green-500 hover:text-white hover:border-green-500">
                            <i class="fas fa-user-check mr-1"></i><span class="hidden sm:inline">Activate</span>
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
                {{ $users->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                    @if(request()->hasAny(['search', 'role', 'gender']))
                        No users match your search criteria. Try adjusting your filters.
                    @else
                        Get started by adding your first user.
                    @endif
                </p>
                <button onclick="openAddModal()" class="btn-minimal btn-primary-clean px-6 py-3 rounded-lg font-medium inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add User
                </button>
            </div>
        @endif
        </div> <!-- Close table-content div -->
    </div>
</div>

<!-- Add/Edit User Modal -->
@include('partials.user.addform')

<!-- View User Modal -->
@include('partials.user.userview')

{{-- Activation/Deactivation now uses the global confirmation modal --}}

@endsection
@push('scripts')
<script>
/**
 * User Management Module JavaScript
 * Global variables and functions for user management functionality
 */

// Global variables - accessible throughout the page
let currentViewUser = null;
let isEditMode = false;

/**
 * Modal Management Functions - Global scope for onclick handlers
 */

// Open Add User Modal
function openAddModal() {
    console.log('Opening add modal...'); // Debug log
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus text-[#68727A] mr-2"></i>Add User';
    document.getElementById('userForm').action = '{{ route("midwife.user.store") }}';
    document.getElementById('userId').value = '';
    
    // Remove method override if it exists
    removeMethodOverride();
    
    // Show password section for new users
    const passwordSection = document.getElementById('passwordSection');
    const passwordInput = document.getElementById('password');
    if (passwordSection && passwordInput) {
        passwordSection.style.display = 'block';
        passwordInput.required = true;
        passwordInput.placeholder = 'Enter password';
        const passwordLabel = passwordSection.querySelector('label');
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password *';
        }
    }

    // Update submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save User';
    }

    isEditMode = false;
    showModal('userModal');
}

// Open Edit User Modal
function openEditUserModal(user) {
    console.log('Opening edit modal for user:', user); // Debug log
    resetForm();
    populateEditForm(user);
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-[#68727A] mr-2"></i>Edit User';
    document.getElementById('userForm').action = '/midwife/user/' + user.id;
    
    // Add method override for PUT
    addMethodOverride('PUT');

    // Show password section for edit but make it optional
    const passwordSection = document.getElementById('passwordSection');
    const passwordInput = document.getElementById('password');
    if (passwordSection && passwordInput) {
        passwordSection.style.display = 'block';
        passwordInput.required = false;
        passwordInput.placeholder = 'Leave blank to keep current password';
        const passwordLabel = passwordSection.querySelector('label');
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password';
        }
    }

    // Update submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update User';
    }

    isEditMode = true;
    showModal('userModal');
}

// Open View User Modal
function openViewUserModal(user) {
    console.log('Opening view modal for user:', user); // Debug log
    currentViewUser = user;
    populateViewModal(user);
    showModal('viewUserModal');
}

/**
 * User Activation/Deactivation Functions
 */

function deactivateUser(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/midwife/user/${userId}/deactivate`;
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add method override for PATCH
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}

function activateUser(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/midwife/user/${userId}/activate`;
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add method override for PATCH
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}

/**
 * Modal Show/Hide Functions
 */
function showModal(modalId) {
    console.log('Showing modal:', modalId); // Debug log
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with id '${modalId}' not found`);
        return;
    }

    modal.classList.remove('hidden');
    // Force reflow
    modal.offsetHeight;
    
    setTimeout(() => {
        modal.classList.add('show');
        if (modalId === 'viewUserModal') {
            const content = document.getElementById('viewUserModalContent');
            if (content) {
                content.classList.remove('-translate-y-10', 'opacity-0');
            }
        }
    }, 10);
    document.body.style.overflow = 'hidden';
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('show');
    if (modalId === 'viewUserModal') {
        const content = document.getElementById('viewUserModalContent');
        if (content) {
            content.classList.add('-translate-y-10', 'opacity-0');
        }
    }
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    hideModal('userModal');
}

function closeViewUserModal() {
    hideModal('viewUserModal');
    currentViewUser = null;
}

/**
 * Form Management Functions
 */
function resetForm() {
    const form = document.getElementById('userForm');
    if (form) {
        form.reset();
        clearValidationErrors();
    }
    
    // Reset hidden fields
    const userIdInput = document.getElementById('userId');
    if (userIdInput) {
        userIdInput.value = '';
    }
    
    // Remove method override
    removeMethodOverride();
}

function populateEditForm(user) {
    const fields = {
        'userId': user.id,
        'name': user.name || '',
        'username': user.username || '',
        'age': user.age || '',
        'contact_number': user.contact_number || '',
        'address': user.address || '',
        'role': user.role || ''
    };

    // Populate text inputs
    Object.keys(fields).forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.value = fields[fieldId];
        }
    });

    // Set gender radio button
    setGenderRadio(user.gender);
}

function populateViewModal(user) {
    const viewFields = {
        'modalFullName': user.name || 'N/A',
        'modalGender': user.gender || 'N/A',
        'modalAge': user.age || 'N/A',
        'modalRole': user.role || 'N/A',
        'modalUsername': user.username || 'N/A',
        'modalContactNumber': user.contact_number ? '+63' + user.contact_number : 'N/A',
        'modalUserAddress': user.address || 'N/A',
        'modalStatus': user.is_active ? 'Active' : 'Inactive'
    };

    // Populate view fields
    Object.keys(viewFields).forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.textContent = viewFields[fieldId];
        }
    });

    // Update status styling in modal
    const statusElement = document.getElementById('modalStatus');
    if (statusElement) {
        statusElement.className = `text-lg font-semibold mt-1 ${user.is_active ? 'text-green-600' : 'text-red-600'}`;
    }

    // Set dates and role information
    setModalDates(user);
    setRoleInformation(user.role);
}

/**
 * Helper Functions
 */
function setGenderRadio(gender) {
    const maleRadio = document.querySelector('input[name="gender"][value="Male"]');
    const femaleRadio = document.querySelector('input[name="gender"][value="Female"]');
    
    if (maleRadio) maleRadio.checked = gender === 'Male';
    if (femaleRadio) femaleRadio.checked = gender === 'Female';
}

function setModalDates(user) {
    const createdAtElement = document.getElementById('modalCreatedAt');
    
    if (user.created_at && createdAtElement) {
        const formattedDate = new Date(user.created_at).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        createdAtElement.textContent = formattedDate;
    }
}

function setRoleInformation(role) {
    const roleDescriptions = {
        'Midwife': 'Healthcare professional responsible for prenatal care, delivery assistance, and maternal health services. Has full system access including user management.',
        'BHW': 'Community health worker providing basic healthcare services and health education at the barangay level. Has limited system access focused on patient records.'
    };
    
    const accessLevels = {
        'Midwife': 'Full System Access',
        'BHW': 'Limited Access'
    };

    const descElement = document.getElementById('modalRoleDescription');
    const accessElement = document.getElementById('modalAccessLevel');
    
    if (descElement) {
        descElement.textContent = roleDescriptions[role] || 'No description available';
    }
    
    if (accessElement) {
        accessElement.textContent = accessLevels[role] || 'N/A';
    }
}

function addMethodOverride(method) {
    removeMethodOverride(); // Remove existing if any
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = method;
    methodInput.id = 'methodOverride';
    
    const form = document.getElementById('userForm');
    if (form) {
        form.appendChild(methodInput);
    }
}

function removeMethodOverride() {
    const methodOverride = document.getElementById('methodOverride');
    if (methodOverride) {
        methodOverride.remove();
    }
}

function clearValidationErrors() {
    // Remove error borders
    const errorElements = document.querySelectorAll('.error-border');
    errorElements.forEach(element => {
        element.classList.remove('error-border');
    });
    
    // Remove error messages
    const errorMessages = document.querySelectorAll('.text-red-500');
    errorMessages.forEach(element => {
        if (element.classList.contains('mt-1')) {
            element.remove();
        }
    });
    
    // Remove validation error container
    const errorContainer = document.querySelector('.validation-errors');
    if (errorContainer) {
        errorContainer.remove();
    }
}

/**
 * Phone Number Formatting Functions
 */
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 0 && !value.startsWith('9')) {
        value = '';
    }
    
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    
    input.value = value;
}

function setupPhoneNumberFormatting() {
    const phoneInput = document.getElementById('contact_number');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            formatPhoneNumber(e.target);
        });
        
        phoneInput.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });
        
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = paste.replace(/\D/g, '');
            
            let phoneNumber = cleaned;
            if (phoneNumber.startsWith('63')) {
                phoneNumber = phoneNumber.substring(2);
            }
            if (phoneNumber.startsWith('0')) {
                phoneNumber = phoneNumber.substring(1);
            }
            
            if (phoneNumber.startsWith('9') && phoneNumber.length <= 10) {
                phoneInput.value = phoneNumber;
                formatPhoneNumber(phoneInput);
            }
        });
    }
}

/**
 * Form Validation Functions
 */
function validateForm() {
    const requiredFields = ['name', 'username', 'age', 'contact_number', 'role'];
    let isValid = true;
    const errors = [];

    clearValidationErrors();

    requiredFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input && !input.value.trim()) {
            input.classList.add('error-border');
            errors.push(`${getFieldLabel(fieldId)} is required.`);
            isValid = false;
        } else if (input) {
            input.classList.remove('error-border');
        }
    });

    // Check gender radio buttons
    const genderChecked = document.querySelector('input[name="gender"]:checked');
    if (!genderChecked) {
        errors.push('Gender is required.');
        isValid = false;
    }

    // Validate phone number format
    const phoneInput = document.getElementById('contact_number');
    if (phoneInput && phoneInput.value) {
        const phonePattern = /^9\d{9}$/;
        if (!phonePattern.test(phoneInput.value)) {
            phoneInput.classList.add('error-border');
            errors.push('Contact number must be a valid Philippine mobile number starting with 9.');
            isValid = false;
        }
    }

    // Validate age range
    const ageInput = document.getElementById('age');
    if (ageInput && ageInput.value) {
        const age = parseInt(ageInput.value);
        if (age < 18 || age > 100) {
            ageInput.classList.add('error-border');
            errors.push('Age must be between 18 and 100 years.');
            isValid = false;
        }
    }

    // Validate password
    const passwordInput = document.getElementById('password');
    if (passwordInput && passwordInput.value && passwordInput.value.length < 8) {
        passwordInput.classList.add('error-border');
        errors.push('Password must be at least 8 characters long.');
        isValid = false;
    }
    
    if (!isEditMode && passwordInput && !passwordInput.value.trim()) {
        passwordInput.classList.add('error-border');
        errors.push('Password is required.');
        isValid = false;
    }

    return { isValid, errors };
}

function setupFormValidation() {
    const form = document.getElementById('userForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const validation = validateForm();
            if (!validation.isValid) {
                e.preventDefault();
                showValidationErrors(validation.errors);
                return false;
            }
        });
    }
}

function showValidationErrors(errors) {
    if (errors.length === 0) return;
    
    let errorContainer = document.querySelector('#userModal .validation-errors');
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.className = 'validation-errors bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        
        const modalHeader = document.querySelector('#userModal .flex.justify-between.items-center');
        if (modalHeader) {
            modalHeader.parentNode.insertBefore(errorContainer, modalHeader.nextSibling);
        }
    }
    
    errorContainer.innerHTML = `
        <div class="font-medium">Please correct the following errors:</div>
        <ul class="list-disc list-inside mt-2">
            ${errors.map(error => `<li class="text-sm">${error}</li>`).join('')}
        </ul>
    `;
    
    const modalContent = document.querySelector('#userModal .modal-content');
    if (modalContent) {
        modalContent.scrollTop = 0;
    }
}

function getFieldLabel(fieldId) {
    const labels = {
        'name': 'Full Name',
        'username': 'Username',
        'age': 'Age',
        'contact_number': 'Contact Number',
        'role': 'Role'
    };
    return labels[fieldId] || fieldId;
}

/**
 * Modal Event Setup Functions
 */
function setupModalEventListeners() {
    // Close modal when clicking outside
    const userModal = document.getElementById('userModal');
    if (userModal) {
        userModal.addEventListener('click', function(e) {
            if (e.target === userModal) {
                closeModal();
            }
        });
    }
    
    const viewUserModal = document.getElementById('viewUserModal');
    if (viewUserModal) {
        viewUserModal.addEventListener('click', function(e) {
            if (e.target === viewUserModal) {
                closeViewUserModal();
            }
        });
    }
    
    // ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!document.getElementById('userModal').classList.contains('hidden')) {
                closeModal();
            }
            if (!document.getElementById('viewUserModal').classList.contains('hidden')) {
                closeViewUserModal();
            }
        }
    });
}

/**
 * Skeleton Loading Functions
 */
function showSkeletonLoaders() {
    // Hide actual content
    document.getElementById('stats-container').classList.add('hidden');
    document.getElementById('table-content').classList.add('hidden');
    
    // Show skeletons
    document.getElementById('stats-skeleton').classList.remove('hidden');
    document.getElementById('table-skeleton').classList.remove('hidden');
}

function hideSkeletonLoaders() {
    // Show actual content
    document.getElementById('stats-container').classList.remove('hidden');
    document.getElementById('table-content').classList.remove('hidden');
    
    // Hide skeletons
    document.getElementById('stats-skeleton').classList.add('hidden');
    document.getElementById('table-skeleton').classList.add('hidden');
}

function simulateDataRefresh() {
    // Add spinning animation to refresh button
    const refreshBtn = document.querySelector('button[onclick="simulateDataRefresh()"]');
    const refreshIcon = refreshBtn.querySelector('i');
    const originalText = refreshBtn.querySelector('span').textContent;
    
    if (refreshIcon) {
        refreshIcon.classList.add('fa-spin');
    }
    refreshBtn.querySelector('span').textContent = 'Refreshing...';
    refreshBtn.disabled = true;
    refreshBtn.classList.add('opacity-75', 'cursor-not-allowed');
    
    showSkeletonLoaders();
    
    // Get current URL with all filters and search parameters
    const currentUrl = window.location.href;
    
    // Add a small delay to show the skeleton, then reload
    setTimeout(() => {
        window.location.href = currentUrl;
    }, 800);
}

// Alternative function for immediate refresh without skeleton
function refreshData() {
    window.location.reload();
}

/**
 * Document Ready Event Listener - Only for setup functions
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...'); // Debug log
    
    setupPhoneNumberFormatting();
    setupFormValidation();
    setupModalEventListeners();
    
    // Debug: Check if modals exist
    console.log('User Modal found:', document.getElementById('userModal') !== null);
    console.log('View User Modal found:', document.getElementById('viewUserModal') !== null);
    
    // Check for server-side validation errors and show modal if needed
    if (document.querySelectorAll('.error-border').length > 0 || 
        document.querySelector('#userForm .text-red-500')) {
        const userIdInput = document.getElementById('userId');
        if (userIdInput && userIdInput.value) {
            isEditMode = true;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-[#68727A] mr-2"></i>Edit User';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save mr-2"></i>Update User';
            const userId = userIdInput.value;
            document.getElementById('userForm').action = '/midwife/user/' + userId;
            addMethodOverride('PUT');
        } else {
            isEditMode = false;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus text-[#68727A] mr-2"></i>Add User';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save mr-2"></i>Save User';
            document.getElementById('userForm').action = '/midwife/user';
        }
        showModal('userModal');
    }
});
</script>

@endpush