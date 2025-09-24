@extends('layout.admin')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage system users and roles')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
@push('styles')
    <!-- User Management Module Styles -->
    <link href="{{ asset('css/modules/user-management.css') }}" rel="stylesheet">
@endpush

@section('content')
@include('components.flowbite-alert')

<div class="space-y-6">

    <!-- Header Actions -->
<div class="flex justify-end items-center mb-6">
    <div class="flex space-x-3">
        <span class="btn-minimal btn-primary-clean px-4 py-2 rounded-lg font-medium flex items-center space-x-2 bg-gray-100 text-gray-600 cursor-not-allowed">
            <i class="fas fa-eye text-sm"></i>
            <span>View Only</span>
        </span>
    </div>
</div>

    <!-- Search and Filters -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-4 sm:p-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="search-form flex flex-col sm:flex-row gap-4">
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
                    <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <select name="status" class="input-clean px-3 py-2.5 rounded-lg w-full sm:min-w-[120px]">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" onclick="showSkeletonLoaders()" class="btn-minimal px-4 py-2.5 bg-[#68727A] text-white rounded-lg">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-minimal px-4 py-2.5 text-gray-600 border border-gray-300 rounded-lg text-center">
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
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'is_active', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                    Status <i class="fas fa-sort ml-1 text-gray-400"></i>
                </a>
            </th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Contact</th>
            <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Created</th>
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
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ ($user->role ?? '') === 'Midwife' ? 'role-badge-midwife' : (($user->role ?? '') === 'BHW' ? 'role-badge-bhw' : 'role-badge-admin') }}">
                    <i class="fas {{ ($user->role ?? '') === 'Midwife' ? 'fa-user-md' : (($user->role ?? '') === 'BHW' ? 'fa-hands-helping' : 'fa-user-cog') }} mr-1"></i>
                    <span class="hidden sm:inline">{{ $user->role ?? 'N/A' }}</span>
                    <span class="sm:hidden">{{ substr($user->role ?? 'N', 0, 1) }}</span>
                </span>
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                    <span class="hidden sm:inline">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                    <span class="sm:hidden">{{ $user->is_active ? 'A' : 'I' }}</span>
                </span>
            </td>
            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                {{ $user->contact_number ?? 'N/A' }}
            </td>
            <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                {{ $user->created_at->format('M d, Y') }}
            </td>
            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                <div class="action-buttons flex flex-col sm:flex-row sm:justify-center space-y-2 sm:space-y-0 sm:space-x-2">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn-action btn-view inline-flex items-center justify-center">
                        <i class="fas fa-eye mr-1"></i><span class="hidden sm:inline">View</span>
                    </a>
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
                    @if(request()->hasAny(['search', 'role', 'status']))
                        No users match your search criteria. Try adjusting your filters.
                    @else
                        No users are currently available in the system.
                    @endif
                </p>
            </div>
        @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <!-- Laravel Routes Configuration for JavaScript -->
    <script>
        window.userManagementRoutes = {
            // View-only admin routes
            show: '{{ route("admin.users.show", ":id") }}'
        };

        // Skeleton loader functionality
        function showSkeletonLoaders() {
            document.getElementById('table-content').style.display = 'none';
            document.getElementById('table-skeleton').style.display = 'block';
        }

        // Hide skeleton on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('table-skeleton').style.display = 'none';
            document.getElementById('table-content').style.display = 'block';
        });
    </script>

    <!-- User Management Module JavaScript -->
    <script src="{{ asset('js/modules/user-management.js') }}"></script>
@endpush