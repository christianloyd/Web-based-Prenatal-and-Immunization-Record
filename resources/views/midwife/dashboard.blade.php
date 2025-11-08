@extends('layout.midwife')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', '')

<link rel="icon" type="image/png" sizes="40x40" href="{{ asset('images/dash1.png') }}">

@push('styles')
<link rel="stylesheet" href="{{ asset('css/midwife/midwife.css') }}">
<link rel="stylesheet" href="{{ asset('css/midwife/dashboard.css') }}">
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')

    <!-- Statistics Cards -->
    <div class="dashboard-grid cols-4">
        <!-- Total Registered Mothers -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-big text-gray-600">Total Registered Mothers</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['total_patients']) }}</p>

                </div>
                <div class="primary-bg text-white p-3 rounded-lg">
                    <i class="fas fa-female text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Prenatal Records -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Prenatal Records</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['active_prenatal_records']) }}</p>

                </div>
                <div class="bg-blue-500 text-white p-3 rounded-lg">
                    <i class="fas fa-baby text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Checkups This Month -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Checkups This Month</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['checkups_this_month']) }}</p>

                </div>
                <div class="bg-green-500 text-white p-3 rounded-lg">
                    <i class="fas fa-stethoscope text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Children Records -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Children Records</p>
                    <p class="stat-number font-bold primary-text">{{ number_format($stats['total_children']) }}</p>

                </div>
                <div class="bg-purple-500 text-white p-3 rounded-lg">
                    <i class="fas fa-child text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Lists Section - MOVED TO TOP -->
    <div class="dashboard-grid cols-2">
        <!-- Upcoming Checkups -->
        <div class="bg-white rounded-lg border fade-in">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold primary-text">
                    <i class="fas fa-calendar-alt mr-2"></i>Upcoming Prenatal Checkups
                </h3>
            </div>
            <div class="p-6">
                @if(isset($upcomingAppointments) && $upcomingAppointments->count() > 0)
                    <div class="space-y-4">
                        @foreach($upcomingAppointments as $appointment)
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-blue-500 text-white p-2 rounded-full">
                                        <i class="fas fa-calendar-check text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $appointment['patient_name'] }}</p>
                                        <p class="text-sm text-gray-600">{{ Carbon\Carbon::parse($appointment['appointment_date'])->format('M j, Y') }}</p>
                                        @if($appointment['appointment_time'])
                                            <p class="text-xs text-blue-600">
                                                <i class="fas fa-clock mr-1"></i>{{ Carbon\Carbon::parse($appointment['appointment_time'])->format('g:i A') }}
                                            </p>
                                        @endif
                                        @if($appointment['gestational_weeks'])
                                            <p class="text-xs text-purple-600">
                                                <i class="fas fa-baby mr-1"></i>{{ $appointment['gestational_weeks'] }} weeks
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        @if($appointment['status'] === 'Upcoming') bg-orange-100 text-orange-800
                                        @elseif($appointment['status'] === 'Confirmed') bg-green-100 text-green-800
                                        @elseif($appointment['status'] === 'Pending') bg-yellow-100 text-yellow-800
                                        @elseif($appointment['status'] === 'Scheduled') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $appointment['status'] ?? $appointment['type'] }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ Carbon\Carbon::parse($appointment['appointment_date'])->diffForHumans() }}
                                    </p>
                                    @if($appointment['notes'])
                                        <p class="text-xs text-gray-400 mt-1 truncate max-w-24" title="{{ $appointment['notes'] }}">
                                            {{ \Illuminate\Support\Str::limit($appointment['notes'], 20) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-alt text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">No upcoming checkups scheduled</p>
                        <p class="text-sm text-gray-400 mt-1">All checkups are up to date</p>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('midwife.prenatalcheckup.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View all checkups <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Upcoming Immunizations -->
        <div class="bg-white rounded-lg border fade-in">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold primary-text">
                    <i class="fas fa-syringe mr-2"></i>Upcoming Immunizations
                </h3>
            </div>
            <div class="p-6">
                @if(isset($upcomingImmunizations) && $upcomingImmunizations->count() > 0)
                    <div class="space-y-4">
                        @foreach($upcomingImmunizations as $immunization)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-green-500 text-white p-2 rounded-full">
                                        <i class="fas fa-syringe text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $immunization['child_name'] }}</p>
                                        <p class="text-sm text-gray-600">{{ $immunization['vaccine_name'] }} - {{ $immunization['dose_number'] }}</p>
                                        @if($immunization['schedule_time'])
                                            <p class="text-xs text-blue-600">
                                                <i class="fas fa-clock mr-1"></i>{{ Carbon\Carbon::parse($immunization['schedule_time'])->format('g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        {{ Carbon\Carbon::parse($immunization['due_date'])->format('M j, Y') }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ Carbon\Carbon::parse($immunization['due_date'])->diffForHumans() }}
                                    </p>
                                    @if($immunization['notes'])
                                        <p class="text-xs text-gray-400 mt-1 truncate max-w-24" title="{{ $immunization['notes'] }}">
                                            {{ \Illuminate\Support\Str::limit($immunization['notes'], 20) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-syringe text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">No upcoming immunizations</p>
                        <p class="text-sm text-gray-400 mt-1">All schedules are up to date</p>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('midwife.immunization.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View immunization schedule <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section - MOVED TO BOTTOM -->
    <div class="dashboard-grid cols-2">
        <!-- Prenatal Checkups Chart -->
        <div class="chart-card fade-in">
            <h3 class="text-lg font-semibold primary-text mb-4">
                <i class="fas fa-chart-line mr-2"></i>Prenatal Checkups Per Month
            </h3>
            <div class="chart-container" id="checkupsContainer">
                <canvas id="checkupsChart"></canvas>
                <div id="checkupsData" style="display: none;" class="p-4 text-sm text-gray-600">
                    <p><strong>Data:</strong> {{ implode(', ', $charts['checkups']['data'] ?? []) }}</p>
                    <p><strong>Labels:</strong> {{ implode(', ', $charts['checkups']['labels'] ?? []) }}</p>
                </div>
            </div>
        </div>

        <!-- Immunization Coverage Chart -->
        <div class="chart-card fade-in">
            <h3 class="text-lg font-semibold primary-text mb-4">
                <i class="fas fa-chart-pie mr-2"></i>Immunization Coverage
            </h3>
            <div class="chart-container">
                <canvas id="immunizationChart"></canvas>
            </div>
        </div>

        <!-- Most Used Vaccines Chart -->
        <div class="chart-card fade-in">
            <h3 class="text-lg font-semibold primary-text mb-4">
                <i class="fas fa-chart-bar mr-2"></i>Most Used Vaccines
            </h3>
            <div class="chart-container">
                <canvas id="vaccinesChart"></canvas>
            </div>
        </div>

        <!-- Patient Registration Trends -->
        <div class="chart-card fade-in">
            <h3 class="text-lg font-semibold primary-text mb-4">
                <i class="fas fa-chart-area mr-2"></i>New Patient Registrations
            </h3>
            <div class="chart-container">
                <canvas id="registrationChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Include Chart.js library with fallback --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"
        onerror="console.error('Primary CDN failed, trying fallback...'); this.onerror=null; this.src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.js';"></script>

{{-- Configuration data for dashboard charts --}}
<script>
    window.DASHBOARD_DATA = {!! json_encode($charts) !!};
</script>

<script src="{{ asset('js/midwife/midwife.js') }}"></script>
<script src="{{ asset('js/midwife/dashboard.js') }}"></script>
@endpush