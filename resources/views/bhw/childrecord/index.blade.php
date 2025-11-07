@extends('layout.bhw') 
@section('title', 'Child Records')
@section('page-title', 'Child Records')
@section('page-subtitle', 'Manage and monitor child health records')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<link rel="stylesheet" href="{{ asset('css/bhw/childrecord-index.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>
            
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('bhw.childrecord.create') }}"
                class="btn-minimal btn-primary-clean px-4 py-2 rounded-lg font-medium flex items-center space-x-2">
                <i class="fas fa-plus text-sm"></i>
                <span>Add Record</span>
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-4 sm:p-6">
            <form method="GET" action="{{ route('bhw.childrecord.index') }}" class="search-form flex flex-col sm:flex-row gap-4">
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
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <a href="{{ route('bhw.childrecord.index') }}" class="btn-minimal px-4 py-2.5 text-gray-600 border border-gray-300 rounded-lg text-center">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Include Table Skeleton -->
    @include('components.table-skeleton', [
        'id' => 'bhw-child-table-skeleton',
        'rows' => 5,
        'columns' => 6,
        'showStats' => true,
        'statsId' => 'bhw-child-stats-skeleton'
    ])

    <!-- Records Table -->
    <div id="bhw-child-main-content" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($childRecords->count() > 0)
            <div class="table-wrapper">
                <table class="w-full table-container">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <!--<th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Child ID</th>-->
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
                            <!--<td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-blue-600">{{ $record->formatted_child_id ?? 'CH-001' }}</div>-->
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $record->full_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500 sm:hidden">{{ $record->mother_name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ ($record->gender ?? '') === 'Male' ? 'gender-badge-male' : 'gender-badge-female' }}">
                                    {{ $record->gender ?? 'N/A' }}
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
                                    <a href="{{ route('bhw.childrecord.show', $record->id) }}" class="btn-action btn-view inline-flex items-center justify-center">
                                        <i class="fas fa-eye mr-1"></i><span class="hidden sm:inline">View</span>
                                    </a>
                                    <a href="#" onclick='openEditRecordModal(@json($record->toArray()))' class="btn-action btn-edit inline-flex items-center justify-center">
                                        <i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">Edit</span>
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
                <a href="{{ route('bhw.childrecord.create') }}" class="btn-minimal btn-primary-clean px-6 py-3 rounded-lg font-medium inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add Child Record
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
    @include('partials.bhw.childrecord.childadd')

<!-- View Child Record Modal -->
    @include('partials.bhw.childrecord.childview')

<!-- Edit Modal -->
    @include('partials.bhw.childrecord.childedit')
@endsection

@push('scripts')
{{-- Modular Child Record JavaScript - ES6 Modules --}}
<script type="module" src="{{ asset('js/bhw/childrecord/index.js') }}"></script>

{{-- Fallback to monolithic version for older browsers --}}
<script nomodule src="{{ asset('js/bhw/childrecord-index.js') }}"></script>

{{-- Include Refresh Data Script --}}
@include('components.refresh-data-script', [
    'contentId' => 'bhw-child-main-content',
    'skeletonId' => 'bhw-child-table-skeleton',
    'statsId' => 'bhw-child-stats-container',
    'statsSkeletonId' => 'bhw-child-stats-skeleton',
    'refreshBtnId' => 'bhw-child-refresh-btn',
    'hasStats' => true
])
@endpush
