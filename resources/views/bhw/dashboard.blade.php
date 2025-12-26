@extends('layout.bhw')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of barangay health worker activities')

<link rel="icon" type="image/png" sizes="40x40" href="{{ asset('images/dash1.png') }}">

@push('styles')
{{-- Include shared BHW CSS --}}
<link rel="stylesheet" href="{{ asset('css/bhw/bhw.css') }}">

{{-- Include dashboard specific CSS --}}
<link rel="stylesheet" href="{{ asset('css/bhw/dashboard.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')

    <!-- Statistics Cards -->
    <div class="dashboard-grid cols-4">
        <!-- Total Mothers -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Mothers</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['total_mothers']) }}</p>
                </div>
                <div class="primary-bg text-white p-3 rounded-lg">
                    <i class="fas fa-female text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Children -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Children</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['total_children']) }}</p>
                </div>
                <div class="bg-blue-500 text-white p-3 rounded-lg">
                    <i class="fas fa-child text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Girls -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Girls</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['girls_count']) }}</p>
                </div>
                <div class="bg-pink-500 text-white p-3 rounded-lg">
                    <i class="fas fa-female text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Boys -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Boys</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['boys_count']) }}</p>
                </div>
                <div class="bg-indigo-500 text-white p-3 rounded-lg">
                    <i class="fas fa-male text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Lists Section - MOVED TO TOP -->
    <div class="dashboard-grid cols-2">
        <!-- Recent Patient Registrations -->
        <div class="rounded-lg border fade-in" style="background-color: #FFFFFF;">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold primary-text">
                    <i class="fas fa-user-plus mr-2"></i>Recent Patient Registrations
                </h3>
            </div>
            <div class="p-6">
                @if($recentRegistrations->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentRegistrations as $registration)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="primary-bg text-white p-2 rounded-full">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $registration['patient_name'] }}</p>
                                        <p class="text-sm text-gray-600">Age: {{ $registration['age'] }} - {{ $registration['contact'] }}</p>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500 font-medium">
                                    {{ $registration['registration_date']->format('M j, Y') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-user-plus text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">No recent registrations</p>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('bhw.patients.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View all patients <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Child Records -->
        <div class="rounded-lg border fade-in" style="background-color: #FFFFFF;">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold primary-text">
                    <i class="fas fa-child mr-2"></i>Recent Child Records
                </h3>
            </div>
            <div class="p-6">
                @if(isset($recentChildRecords) && $recentChildRecords->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentChildRecords as $child)
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-purple-500 text-white p-2 rounded-full">
                                        <i class="fas fa-child text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $child['child_name'] ?? 'Child Name' }}</p>
                                        <p class="text-sm text-gray-600">Born: {{ isset($child['date_of_birth']) ? $child['date_of_birth']->format('M j, Y') : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        {{ $child['gender'] ?? 'N/A' }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Age: {{ $child['age'] ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-child text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">No recent child records</p>
                        <p class="text-sm text-gray-400 mt-1">No new children registered</p>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('bhw.childrecord.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View child records <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section - MOVED TO BOTTOM -->
    <div class="dashboard-grid cols-2">
        <!-- Prenatal Status Chart -->
        <div class="chart-card fade-in">
            <h3 class="text-lg font-semibold primary-text mb-4">
                <i class="fas fa-chart-pie mr-2"></i>Prenatal Records Status
            </h3>
            <div class="chart-container">
                <canvas id="prenatalChart"></canvas>
            </div>
        </div>

        <!-- Monthly Patient Registrations Chart -->
        <div class="chart-card fade-in">
            <h3 class="text-lg font-semibold primary-text mb-4">
                <i class="fas fa-chart-line mr-2"></i>Monthly Patient Registrations
            </h3>
            <div class="chart-container">
                <canvas id="registrationsChart"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Include Chart.js library with fallback --}}
<!-- Chart.js loaded via Vite in layout -->

{{-- Configuration data for dashboard charts --}}
<script>
    window.DASHBOARD_DATA = {!! json_encode($charts) !!};
</script>

{{-- Include shared BHW JavaScript --}}
<script src="{{ asset('js/bhw/bhw.js') }}"></script>

{{-- Include dashboard specific JavaScript --}}
<script src="{{ asset('js/bhw/dashboard.js') }}"></script>
@endpush