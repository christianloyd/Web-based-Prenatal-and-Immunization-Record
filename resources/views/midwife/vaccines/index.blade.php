@extends('layout.midwife')
@section('title', 'Vaccine Management')
@section('page-title', 'Vaccine Management')
@section('page-subtitle', 'Manage vaccine information')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/midwife/midwife.css') }}">
<link rel="stylesheet" href="{{ asset('css/midwife/vaccines-index.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <div></div>
        <div class="flex space-x-3">
            <button onclick="openVaccineModal()" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-hover-color transition-all duration-200 flex items-center btn-primary">
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
                    <button type="submit" class="flex-1 bg-secondary text-white px-4 py-2 rounded-lg hover:bg-hover-color transition-all duration-200 btn-primary">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
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
                            <div class="flex items-center space-x-2">
                                <span class="font-medium {{ $vaccine->stock_status_color }}">
                                    {{ $vaccine->current_stock }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vaccine->stock_status_badge_color }}">
                                    {{ $vaccine->stock_status }}
                                </span>
                            </div>
                            @if($vaccine->is_low_stock)
                                <div class="text-xs text-amber-600 mt-1">Min: {{ $vaccine->min_stock }}</div>
                            @endif
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
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
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
<script src="{{ asset('js/midwife/midwife.js') }}"></script>
<script src="{{ asset('js/midwife/vaccines-index.js') }}"></script>
@endpush