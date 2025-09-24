@extends('layout.admin')
@section('title', 'Records Overview')
@section('page-title', 'Records Overview')
@section('page-subtitle', 'System-wide medical records summary')
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

@push('styles')
<style>
    :root {
        --primary: #D4A373; /* Warm brown for primary elements */
        --secondary: #ecb99e; /* Peach for buttons and accents */
        --neutral: #FFFFFF; /* White for content backgrounds */
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

    .input-clean {
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
        background: white;
    }

    .input-clean:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(212, 163, 115, 0.15); /* Warm brown shadow */
        border-color: #D4A373; /* Warm brown border */
        outline: none;
    }

    .btn-minimal {
        transition: all 0.2s ease;
    }

    .btn-minimal:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .table-row-hover {
        transition: background-color 0.15s ease;
    }

    .table-row-hover:hover {
        background-color: #f9fafb;
    }

    /* Statistics Cards */
    .stats-card {
        transition: all 0.2s ease;
    }

    .stats-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Responsive adjustments */
    @media screen and (max-width: 768px) {
        .hide-mobile {
            display: none;
        }
    }
</style>
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

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Prenatal Records -->
        <div class="bg-white p-4 rounded-lg shadow-sm border stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-pink-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-medical text-white"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['prenatal_records'] }}</div>
                    <div class="text-sm text-gray-600">Prenatal Records</div>
                </div>
            </div>
        </div>

        <!-- Child Records -->
        <div class="bg-white p-4 rounded-lg shadow-sm border stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-white"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['child_records'] }}</div>
                    <div class="text-sm text-gray-600">Child Records</div>
                </div>
            </div>
        </div>

        <!-- Completed Immunizations -->
        <div class="bg-white p-4 rounded-lg shadow-sm border stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-syringe text-white"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['completed_immunizations'] }}</div>
                    <div class="text-sm text-gray-600">Completed Immunizations</div>
                </div>
            </div>
        </div>

        <!-- Pending Immunizations -->
        <div class="bg-white p-4 rounded-lg shadow-sm border stats-card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['pending_immunizations'] }}</div>
                    <div class="text-sm text-gray-600">Pending Immunizations</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Records -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Prenatal Records -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Prenatal Records</h3>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                        <i class="fas fa-file-medical mr-1"></i>
                        {{ $recentPrenatal->count() }}
                    </span>
                </div>
                @if($recentPrenatal->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentPrenatal as $record)
                        <div class="flex items-center py-3 border-b border-gray-100 last:border-b-0 table-row-hover">
                            <div class="flex items-center flex-1">
                                <div class="w-3 h-3 bg-pink-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        @if($record->patient)
                                            @if($record->patient->first_name && $record->patient->last_name)
                                                {{ $record->patient->first_name }} {{ $record->patient->last_name }}
                                            @else
                                                {{ $record->patient->name }}
                                            @endif
                                        @else
                                            Unknown Patient
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Prenatal Record • {{ $record->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Only
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-file-medical text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500">No recent prenatal records found.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Child Records -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Child Records</h3>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clipboard-list mr-1"></i>
                        {{ $recentChildren->count() }}
                    </span>
                </div>
                @if($recentChildren->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentChildren as $record)
                        <div class="flex items-center py-3 border-b border-gray-100 last:border-b-0 table-row-hover">
                            <div class="flex items-center flex-1">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $record->full_name ?? 'Unknown Child' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Child Record • Mother: {{ $record->mother ? ($record->mother->first_name && $record->mother->last_name ? $record->mother->first_name . ' ' . $record->mother->last_name : $record->mother->name) : 'Unknown' }} • {{ $record->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-800">
                                    <i class="fas fa-eye mr-1"></i>
                                    View Only
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-clipboard-list text-gray-400"></i>
                        </div>
                        <p class="text-sm text-gray-500">No recent child records found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-hide success/error messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
</script>
@endpush