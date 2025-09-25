@extends('layout.midwife')
@section('title', 'Prenatal Checkups')
@section('page-title', 'Prenatal Checkups')
@section('page-subtitle', 'Manage and monitor prenatal checkup appointments')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        --primary: #243b55;
        --secondary: #141e30;
    }

    * {
        font-family: 'Inter', sans-serif;
    }

    /* Modal Animation Styles */
    .modal-overlay {
        transition: opacity 0.3s ease-out;
        z-index: 9999 !important;
        backdrop-filter: blur(4px);
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

    /* Form Input Focus Styles */
    .form-input {
        transition: all 0.2s ease;
    }

    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(36, 59, 85, 0.15);
        border-color: var(--primary);
        outline: none;
    }

    /* Button Styles */
    .btn-primary {
        transition: all 0.2s ease;
        background-color: var(--primary);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(36, 59, 85, 0.3);
        background-color: var(--secondary);
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

    .btn-reschedule {
        background-color: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .btn-reschedule:hover {
        background-color: #1d4ed8;
        color: white;
        border-color: #1d4ed8;
    }

    .btn-checkup {
        background-color: #d1fae5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .btn-checkup:hover {
        background-color: #10b981;
        color: white;
        border-color: #10b981;
    }

    /* Status Badge Styles */
    .status-done {
        background-color: #10b981;
        color: white;
    }

    .status-upcoming {
        background-color: #dbeafe;
        color: #1d4ed8;
    }

    /* Alert Styles */
    .alert {
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
        border: 1px solid;
        display: flex;
        align-items: center;
    }

    .alert-success {
        background-color: #d1fae5;
        border-color: #10b981;
        color: #065f46;
    }

    .alert-error {
        background-color: #fee2e2;
        border-color: #ef4444;
        color: #991b1b;
    }

    /* Table Styles */
    .table-container {
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    table {
        border-collapse: separate;
        border-spacing: 0;
    }

    th {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        background-color: white;
    }

    tr:hover td {
        background-color: #f8fafc;
    }

    /* Patient Card Styles */
    .patient-card {
        background-color: white;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .patient-card:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: translateY(-1px);
    }

    /* ====================================
       LAPTOP-SPECIFIC OPTIMIZATIONS
       ==================================== */

    /* Small Laptops - 1366x768 */
    @media screen and (min-width: 1024px) and (max-width: 1366px) and (max-height: 768px) {
        /* Compact search/filter section */
        .grid-cols-1.md\\:grid-cols-4 {
            grid-template-columns: 2fr 1fr 1fr;
            gap: 0.75rem;
        }

        /* Smaller table cells */
        th, td {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
        }

        /* Compact modal */
        .modal-content {
            max-width: 95%;
            max-height: 90vh;
        }

        .grid-cols-1.lg\\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
    }

    /* MacBook Air 13" - 1440x900 */
    @media screen and (min-width: 1367px) and (max-width: 1440px) and (max-height: 900px) {
        /* Better use of horizontal space */
        .space-y-6 > * + * {
            margin-top: 1.25rem;
        }

        /* Optimize search layout */
        .grid-cols-1.md\\:grid-cols-4 {
            grid-template-columns: 2fr 1fr 1fr;
        }

        /* Modal optimization */
        .modal-content {
            max-width: 90%;
            max-height: 85vh;
        }

        /* Form optimization */
        .grid-cols-1.lg\\:grid-cols-2 {
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    }

    /* MacBook Pro 13" & Huawei MateBook 14 - 1440x960+ */
    @media screen and (min-width: 1441px) and (max-width: 1680px) and (min-height: 900px) {
        /* Full 4-column search layout */
        .grid-cols-1.md\\:grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        /* Better table spacing */
        th, td {
            padding: 1rem 1.25rem;
        }

        /* Optimal modal size */
        .modal-content {
            max-width: 85%;
            max-height: 90vh;
        }

        /* Two-column form layout */
        .grid-cols-1.lg\\:grid-cols-2 {
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        /* Enhanced button spacing */
        .flex.space-x-2 > * + * {
            margin-left: 0.75rem;
        }
    }

    /* Large Laptops - MacBook Pro 14"/16" */
    @media screen and (min-width: 1681px) and (max-width: 1920px) {
        /* Spacious layout */
        .space-y-6 > * + * {
            margin-top: 2rem;
        }

        /* Enhanced search layout */
        .grid-cols-1.md\\:grid-cols-4 {
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1.5rem;
        }

        /* Larger table cells */
        th, td {
            padding: 1.25rem 1.5rem;
            font-size: 0.95rem;
        }

        /* Large modal */
        .modal-content {
            max-width: 80%;
            max-height: 85vh;
        }

        /* Enhanced form spacing */
        .space-y-4 > * + * {
            margin-top: 1.25rem;
        }

        /* Better button sizing */
        .btn-action {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
    }

    /* Ultra High-Res Laptops - 1921px+ */
    @media screen and (min-width: 1921px) {
        /* Maximum layout optimization */
        .space-y-6 > * + * {
            margin-top: 2.5rem;
        }

        /* Enhanced search with more spacing */
        .grid-cols-1.md\\:grid-cols-4 {
            grid-template-columns: 3fr 1fr 1fr auto;
            gap: 2rem;
        }

        /* Large table cells */
        th, td {
            padding: 1.5rem 2rem;
            font-size: 1rem;
        }

        /* Full-size modal */
        .modal-content {
            max-width: 75%;
            max-height: 80vh;
        }

        /* Enhanced typography */
        .page-title {
            font-size: 2rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
        }

        /* Larger action buttons */
        .btn-action {
            padding: 10px 20px;
            font-size: 1rem;
        }

        /* Enhanced form spacing */
        .space-y-4 > * + * {
            margin-top: 1.5rem;
        }
    }

    /* Retina Display Optimizations */
    @media screen and (-webkit-min-device-pixel-ratio: 2) {
        /* Crisp table borders */
        .table-container {
            border: 0.5px solid #e5e7eb;
        }

        th {
            border-bottom: 0.5px solid #e2e8f0;
        }

        td {
            border-bottom: 0.5px solid #f1f5f9;
        }
`
        /* Crisp modal borders */
        .modal-content {
            border: 0.5px solid #e5e7eb;
        }

        /* Sharp icons */
        .fas, .fa {
            -webkit-font-smoothing: antialiased;
        }

        /* Simple dropdown styles - no complex search needed */
            padding: 0.75rem;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.375rem;
        }

        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

    /* Patient search styles */
    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #d1d5db;
        border-top: none;
        border-radius: 0 0 0.375rem 0.375rem;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .search-dropdown.show {
        display: block;
    }

    .search-option {
        padding: 0.75rem;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: background-color 0.15s ease;
    }

    .search-option:hover {
        background-color: #f9fafb;
    }

    .search-option:last-child {
        border-bottom: none;
    }

    .search-option.selected {
        background-color: #eff6ff;
        color: var(--primary);
    }

    .patient-info {
        display: flex;
        flex-direction: column;
    }

    .patient-name {
        font-weight: 500;
        color: #000000;
    }

    .patient-details {
        font-size: 0.75rem;
        color: #374151;
        margin-top: 0.25rem;
    }

    /* Selected patient display */
    .selected-patient {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-top: 0.5rem;
    }

    .selected-patient .patient-name {
        color: var(--primary);
        font-weight: 600;
    }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')

    @if($errors->any())
    <div class="alert alert-error">
        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
            @foreach($errors->all() as $error)
                <p class="mb-1">{{ $error }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <div></div>
        <div class="flex space-x-3">
            <button onclick="openCheckupModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-all duration-200 flex items-center btn-primary" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-plus mr-2"></i>
                Add Checkup
            </button>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
        <form method="GET" action="{{ route('midwife.prenatalcheckup.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by patient name"
                               class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input" style="border-color: #e5e7eb;">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <select name="status" class="w-full border rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-primary form-input" style="border-color: #e5e7eb; focus:border-color: var(--primary);">
                        <option value="">All Status</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-all duration-200 btn-primary" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                    <a href="{{ route('midwife.prenatalcheckup.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Prenatal Checkups Table -->
    <div class="bg-white rounded-lg shadow-sm border table-container">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr>
                        <!--<th>Patient ID</th>-->
                        <th>Patient Name</th>
                        <th>Checkup Date</th>
                        <th>Checkup Time</th>
                        <th>Status</th>
                        <th>Next Visit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($checkups as $checkup)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <!--<td class="font-medium text-blue-600">
                        {{ $checkup->prenatalRecord->patient->formatted_patient_id ?? 'N/A' }}
                    </td>-->
                    <td>
                        <div class="flex items-center space-x-3">
                             
                                
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $checkup->patient->name ?? ($checkup->prenatalRecord->patient->name ?? 'N/A') }}</p>
                                    </div>
                        </div>
                    </td>
                    <td class="text-gray-900">
                        <span class="font-medium">{{ $checkup->checkup_date ? $checkup->checkup_date->format('M d, Y') : 'N/A' }}</span>
                    </td>
                    <td class="text-gray-900">
                        <span class="text-sm">{{ $checkup->checkup_time ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-{{ $checkup->status ?? 'upcoming' }}">
                            <i class="fas {{ $checkup->status === 'done' ? 'fa-check' : 'fa-clock' }} mr-1"></i>
                            {{ ucfirst($checkup->status ?? 'Upcoming') }}
                        </span>
                    </td>
                    <td class="text-gray-600">
                        @if($checkup->next_visit_date)
                            {{ \Carbon\Carbon::parse($checkup->next_visit_date)->format('M d, Y') }}
                        @else
                            <span class="text-gray-500">Not scheduled</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex space-x-2">
                            <button onclick="openViewCheckupModal({{ $checkup->id }})"
                                    class="btn-action btn-view inline-flex items-center justify-center" title="View Checkup Details">
                                <i class="fas fa-eye"></i>
                            </button>

                            @if($checkup->status === 'upcoming')
                                <!-- Mark as missed button - always available for upcoming checkups -->
                                <button onclick="markAsMissed({{ $checkup->id }})"
                                        class="btn-action btn-missed inline-flex items-center justify-center" title="Mark as Missed">
                                    <i class="fas fa-times"></i>
                                </button>
                                <!-- Always show edit for scheduled -->
                                <button onclick="openScheduleEditModal({{ $checkup->id }})"
                                        class="btn-action btn-edit inline-flex items-center justify-center" title="Edit Schedule">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @elseif($checkup->status === 'missed')
                                <!-- For missed checkups - show reschedule button -->
                                <button onclick="openRescheduleModal({{ $checkup->id }})"
                                        class="btn-action btn-reschedule inline-flex items-center justify-center" title="Reschedule">
                                    <i class="fas fa-calendar-plus"></i>
                                </button>
                            @else
                                <!-- For completed checkups - only show edit -->
                                <button onclick="openScheduleEditModal({{ $checkup->id }})"
                                        class="btn-action btn-edit inline-flex items-center justify-center" title="Edit Schedule">
                                    <i class="fas fa-edit"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-2">No prenatal checkups found</p>
                            <p class="text-gray-600 mb-4">Get started by creating your first prenatal checkup</p>
                            <button onclick="openCheckupModal()" class="btn-primary" style="background-color: var(--primary); color: white; padding: 8px 16px; border-radius: 8px; border: none; transition: all 0.2s ease;" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                                <i class="fas fa-plus mr-2"></i>
                                Create First Checkup
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Checkup Modal -->
<div id="checkupModal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="closeCheckupModal(event)">
    <div class="modal-content relative w-full max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl my-4 flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-calendar-plus mr-2 text-primary"></i>
                    Complete Today's Checkup & Schedule Next Visit
                </h2>
                <button type="button" onclick="closeCheckupModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <form id="checkupForm" action="{{ route('midwife.prenatalcheckup.store') }}" method="POST" class="p-6">
            @csrf
            <!-- Hidden field for conducted_by -->
            <input type="hidden" name="conducted_by" value="{{ auth()->id() }}">

             
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Left Column -->
                <div class="space-y-4">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Search and Select Patient/Mother *
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           id="patient-search"
                                           placeholder="Type patient name or ID to search..."
                                           class="form-input pl-10 pr-10 @error('patient_id') error @enderror"
                                           autocomplete="off">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <div id="search-loading" class="hidden">
                                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                        </div>
                                        <button type="button" id="clear-search" class="hidden text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Search Dropdown -->
                                    <div id="search-dropdown" class="search-dropdown">
                                        <!-- Results will be populated here -->
                                    </div>
                                </div>

                                <!-- Hidden input for selected patient ID -->
                                <input type="hidden" name="patient_id" id="selected-patient-id" value="{{ old('patient_id') }}">

                                @error('patient_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                                <!-- Selected Patient Display -->
                                <div id="selected-patient-display" class="selected-patient hidden">
                                    <div class="flex justify-between items-start">
                                        <div class="patient-info">
                                            <div class="patient-name" id="selected-patient-name"></div>
                                            <div class="patient-details" id="selected-patient-details"></div>
                                        </div>
                                        <button type="button" id="remove-selection" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                 
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                                    <input type="date" name="checkup_date" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        value="{{ date('Y-m-d') }}"
                                        min="{{ date('Y-m-d') }}"
                                        max="{{ date('Y-m-d') }}"
                                        required readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                                    <input type="time" name="checkup_time" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                           value="{{ old('checkup_time', date('H:i')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-heartbeat mr-2 text-red-600"></i>Basic Measurements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure</label>
                                <div class="flex space-x-2">
                                    <input type="number" name="blood_pressure_systolic" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('blood_pressure_systolic') }}" placeholder="120" min="70" max="250">
                                    <span class="flex items-center text-gray-500">/</span>
                                    <input type="number" name="blood_pressure_diastolic" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('blood_pressure_diastolic') }}" placeholder="80" min="40" max="150">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight_kg" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('weight_kg') }}" placeholder="68.5" min="30" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fetal Heart Rate (bpm)</label>
                                <input type="number" name="fetal_heart_rate" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('fetal_heart_rate') }}" placeholder="140" min="100" max="180">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fundal Height (cm)</label>
                                <input type="number" step="0.1" name="fundal_height_cm" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('fundal_height_cm') }}" placeholder="24" min="10" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Health Assessment -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-user-md mr-2 text-green-600"></i>Health Assessment
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Baby Movement</label>
                                <select name="baby_movement" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">-- Select Baby Movement --</option>
                                    <option value="active" {{ old('baby_movement') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="normal" {{ old('baby_movement') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="less" {{ old('baby_movement') == 'less' ? 'selected' : '' }}>Less Active</option>
                                    <option value="none" {{ old('baby_movement') == 'none' ? 'selected' : '' }}>No Movement</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Symptoms</label>
                                <textarea name="symptoms" rows="2" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Any symptoms reported by the patient...">{{ old('symptoms') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clinical Notes</label>
                                <textarea name="notes" rows="3" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Clinical observations, recommendations, and notes...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Next Visit -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-calendar mr-2 text-purple-600"></i>Next Visit
                        </h3>
                        <div class="flex items-center space-x-3 mb-3">
                            <input type="checkbox" id="scheduleNext" name="schedule_next" value="1" class="text-blue-600"
                                onchange="toggleNextVisit()" {{ old('schedule_next') ? 'checked' : '' }}>
                            <label for="scheduleNext" class="text-sm font-medium text-gray-700">Schedule next visit</label>
                        </div>

                        <div id="nextVisitFields" class="{{ old('schedule_next') ? '' : 'hidden' }} space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Next Visit Date</label>
                                    <input type="date"
                                           name="next_visit_date"
                                           id="next-visit-date"
                                           class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('next_visit_date') }}"
                                           min="{{ date('Y-m-d', strtotime('+8 days')) }}">
                                    <p class="text-xs text-gray-500 mt-1">Minimum 1 week gap required from today</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                    <input type="time" name="next_visit_time" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('next_visit_time') }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Notes</label>
                                <textarea name="next_visit_notes" rows="2" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="What to prepare or remember for next visit...">{{ old('next_visit_notes') }}</textarea>
                            </div>
                        </div>

                        <div id="noNextVisitMessage" class="{{ old('schedule_next') ? 'hidden' : '' }}">
                            <div class="bg-gray-100 rounded-lg p-3 text-center">
                                <p class="text-gray-600 text-sm">No next visit will be scheduled.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t bg-white rounded-b-xl">
            <button type="button" onclick="closeCheckupModal()" class="btn-hover px-6 py-2 border border-gray-300 rounded-lg text-gray-700">
                Cancel
            </button>
            <button type="submit" form="checkupForm" class="btn-hover bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition-all duration-200" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-save mr-2"></i>
                Save Checkup
            </button>
        </div>
    </div>
</div>

<script>
    // Modal functions
    function openCheckupModal() {
        const modal = document.getElementById('checkupModal');
        if (!modal) {
            console.error('Checkup modal not found');
            return;
        }

        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });
        document.body.style.overflow = 'hidden';

        // Initialize patient search when modal opens
        setTimeout(() => {
            initializePatientSearch();
        }, 100);
    }

    // Simple patient search functionality
    function initializePatientSearch() {
        const searchInput = document.getElementById('patient-search');
        const searchDropdown = document.getElementById('search-dropdown');
        const selectedPatientId = document.getElementById('selected-patient-id');
        const selectedPatientDisplay = document.getElementById('selected-patient-display');
        const selectedPatientName = document.getElementById('selected-patient-name');
        const selectedPatientDetails = document.getElementById('selected-patient-details');

        if (!searchInput || !selectedPatientId || !searchDropdown) {
            console.warn('Search elements not found, retrying...');
            setTimeout(initializePatientSearch, 100);
            return;
        }

        console.log('Initializing patient search...');

        let searchTimeout;
        let patients = [];

        // Fetch patients with active prenatal records
        fetch('{{ route("midwife.prenatalcheckup.patients.search") }}')
            .then(response => response.json())
            .then(data => {
                patients = data;
                console.log('Loaded patients:', patients.length);
            })
            .catch(error => {
                console.error('Error fetching patients:', error);
            });

        // Search input handler
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                searchDropdown.classList.remove('show');
                return;
            }

            searchTimeout = setTimeout(() => {
                const filteredPatients = patients.filter(patient => {
                    const name = patient.name || (patient.first_name + ' ' + patient.last_name);
                    const id = patient.formatted_patient_id || '';
                    return name.toLowerCase().includes(query) || id.toLowerCase().includes(query);
                });

                displayResults(filteredPatients);
            }, 300);
        });

        function displayResults(results) {
            searchDropdown.innerHTML = '';

            if (results.length === 0) {
                searchDropdown.innerHTML = '<div class="search-option" style="color: #374151;">No patients found</div>';
            } else {
                results.forEach(patient => {
                    const option = document.createElement('div');
                    option.className = 'search-option';
                    option.innerHTML = `
                        <div class="patient-info">
                            <div class="patient-name">${patient.name || (patient.first_name + ' ' + patient.last_name)}</div>
                            <div class="patient-details">${patient.formatted_patient_id || 'P-' + String(patient.id).padStart(3, '0')} • Age: ${patient.age || 'N/A'}</div>
                        </div>
                    `;
                    option.addEventListener('click', () => selectPatient(patient));
                    searchDropdown.appendChild(option);
                });
            }

            searchDropdown.classList.add('show');
        }

        function selectPatient(patient) {
            selectedPatientId.value = patient.id;
            selectedPatientName.textContent = patient.name || (patient.first_name + ' ' + patient.last_name);
            selectedPatientDetails.textContent = `${patient.formatted_patient_id || 'P-' + String(patient.id).padStart(3, '0')} • Age: ${patient.age || 'N/A'}`;

            searchInput.value = patient.name || (patient.first_name + ' ' + patient.last_name);
            selectedPatientDisplay.classList.remove('hidden');
            searchDropdown.classList.remove('show');

            console.log('Patient selected:', patient.name || (patient.first_name + ' ' + patient.last_name));
        }

        // Clear button
        const clearBtn = document.getElementById('clear-search');
        const removeBtn = document.getElementById('remove-selection');

        if (clearBtn) {
            clearBtn.addEventListener('click', clearSelection);
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', clearSelection);
        }

        function clearSelection() {
            selectedPatientId.value = '';
            searchInput.value = '';
            selectedPatientDisplay.classList.add('hidden');
            searchDropdown.classList.remove('show');
        }

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.remove('show');
            }
        });
    }

    function closeCheckupModal(e) {
        // Don't close if click is inside modal content
        if (e && e.target !== e.currentTarget) return;

        const modal = document.getElementById('checkupModal');
        if (!modal) return;

        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    // Add checkup for specific patient (removed - no longer needed)

    // View checkup details for patient
    function viewCheckupDetails(patientId) {
        console.log('View checkup details for patient:', patientId);
        // This will be implemented to show existing checkup details
        alert('View checkup functionality - Patient ID: ' + patientId);
    }

    // Edit scheduled checkup
    function editScheduledCheckup(patientId) {
        console.log('Edit scheduled checkup for patient:', patientId);
        alert('Edit scheduled checkup functionality - Patient ID: ' + patientId);
    }

    // Toggle next visit fields
    function toggleNextVisit() {
        const checkbox = document.getElementById('scheduleNext');
        const fields = document.getElementById('nextVisitFields');
        const noMessage = document.getElementById('noNextVisitMessage');

        if (checkbox.checked) {
            fields.classList.remove('hidden');
            if (noMessage) noMessage.classList.add('hidden');
        } else {
            fields.classList.add('hidden');
            if (noMessage) noMessage.classList.remove('hidden');
        }
    }

    // Handle "None" swelling checkbox
    function toggleNoneSwelling(noneCheckbox) {
        const swellingCheckboxes = document.querySelectorAll('input[name="swelling[]"]:not([value="none"])');
        
        if (noneCheckbox.checked) {
            swellingCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }

    // Handle other swelling checkboxes
    document.querySelectorAll('input[name="swelling[]"]:not([value="none"])').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                document.querySelector('input[name="swelling[]"][value="none"]').checked = false;
            }
        });
    });

    // Search patients
    function searchPatients() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.patient-row');
        
        rows.forEach(row => {
            const patientName = row.querySelector('.patient-name').textContent.toLowerCase();
            const patientId = row.querySelector('.patient-id').textContent.toLowerCase();
            
            if (patientName.includes(searchTerm) || patientId.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Filter patients by status
    function filterPatients() {
        const filterValue = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('.patient-row');
        
        rows.forEach(row => {
            if (!filterValue || row.getAttribute('data-status') === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'checkupModal') {
            closeCheckupModal();
        }
    });

    // Form submission with loading state (NO AJAX - just visual feedback)
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#checkupModal form');
        if (form) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                const originalText = submitButton.innerHTML;

                form.addEventListener('submit', function() {
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
                });
            }
        }

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });

        // Show modal if there are validation errors
        @if($errors->any())
        openCheckupModal();
        @endif
    });

    // Mark checkup as missed
    function markAsMissed(checkupId) {
        if (!confirm('Are you sure you want to mark this checkup as missed?')) {
            return;
        }

        // Create form data
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url()->current() }}/../prenatalcheckup/${checkupId}/mark-missed`;

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        // Add reason (optional)
        const reason = document.createElement('input');
        reason.type = 'hidden';
        reason.name = 'reason';
        reason.value = 'Patient did not show up';
        form.appendChild(reason);

        document.body.appendChild(form);
        form.submit();
    }

    // Open reschedule modal
    function openRescheduleModal(checkupId) {
        // Create modal HTML
        const modalHtml = `
            <div id="rescheduleModal" class="modal-overlay fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
                <div class="modal-content relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Reschedule Missed Checkup</h2>
                        <button onclick="closeRescheduleModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                            <i class="fas fa-times text-lg"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ url()->current() }}/../prenatalcheckup/${checkupId}/reschedule" class="space-y-4">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Checkup Date *</label>
                                <input type="date" name="new_checkup_date" required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Checkup Time *</label>
                                <input type="time" name="new_checkup_time" required
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reschedule Notes</label>
                            <textarea name="reschedule_notes" rows="3"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      placeholder="Optional notes about the rescheduling..."></textarea>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button type="button" onclick="closeRescheduleModal()"
                                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-calendar-plus mr-2"></i>Reschedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal with animation
        setTimeout(() => {
            document.getElementById('rescheduleModal').classList.add('show');
        }, 10);
    }

    // Close reschedule modal
    function closeRescheduleModal() {
        const modal = document.getElementById('rescheduleModal');
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    }
</script>

<!-- Include Edit, Schedule Edit and View Partials -->
@include('partials.midwife.prenatalcheckup.prenatalcheckupedit')
@include('partials.midwife.prenatalcheckup.schedule_edit')
@include('partials.midwife.prenatalcheckup.prenatalcheckupview')

@endsection
