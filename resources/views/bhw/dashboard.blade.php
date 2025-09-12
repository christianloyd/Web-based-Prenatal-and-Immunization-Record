@extends('layout.bhw')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of barangay health worker activities')

<link rel="icon" type="image/png" sizes="40x40" href="{{ asset('images/dash1.png') }}">

@push('styles')
<style>
    .primary-bg { background-color: #243b55; }
    .secondary-bg { background-color: #141e30; }
    .primary-text { color: #243b55; }
    .secondary-text { color: #141e30; }
    
    .stat-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
        background: white;
        border-radius: 0.5rem;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(36, 59, 85, 0.1);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
        display: block;
        background: #fafafa;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
    }
    
    .chart-container canvas {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        max-width: none !important;
        max-height: none !important;
    }
    
    .chart-card {
        min-height: 400px;
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
    }
    
    .fade-in {
        animation: fadeIn 0.6s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dashboard-grid {
        display: grid;
        gap: 1.5rem;
    }

    @media (min-width: 768px) {
        .dashboard-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
        .dashboard-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
    }

    @media (max-width: 767px) {
        .dashboard-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative fade-in" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative fade-in" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="dashboard-grid cols-4">
        <!-- Total Mothers -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Mothers</p>
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['total_mothers']) }}</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>+{{ $stats['mothers_change'] }} this month
                    </p>
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
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['total_children']) }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-child mr-1"></i>Under care
                    </p>
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
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['girls_count']) }}</p>
                    <p class="text-sm text-pink-600 mt-1">
                        <i class="fas fa-heart mr-1"></i>{{ $stats['girls_percentage'] }}% of children
                    </p>
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
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['boys_count']) }}</p>
                    <p class="text-sm text-indigo-600 mt-1">
                        <i class="fas fa-star mr-1"></i>{{ $stats['boys_percentage'] }}% of children
                    </p>
                </div>
                <div class="bg-indigo-500 text-white p-3 rounded-lg">
                    <i class="fas fa-male text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
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

    <!-- Lists Section -->
    <div class="dashboard-grid cols-1">
        <!-- Recent Patient Registrations -->
        <div class="bg-white rounded-lg border fade-in">
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
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js" 
        onerror="console.error('Primary CDN failed, trying fallback...'); this.onerror=null; this.src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.js';"></script>
<script>
    function initializeCharts() {
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('Chart Data:', {!! json_encode($charts) !!});
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js failed to load!');
            document.querySelectorAll('.chart-container').forEach(container => {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Chart library failed to load</div>';
            });
            return;
        }
        
        console.log('DOM loaded, initializing charts...');
    
        const primaryColor = '#243b55';
        const secondaryColor = '#141e30';
        const chartColors = {
            primary: primaryColor,
            secondary: secondaryColor,
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6'
        };

        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: secondaryColor,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: primaryColor,
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true
                }
            }
        };

        // Prenatal Status Pie Chart
        try {
            const prenatalCtx = document.getElementById('prenatalChart');
            if (prenatalCtx) {
                new Chart(prenatalCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Active Prenatal', 'Completed Prenatal'],
                        datasets: [{
                            data: [
                                {{ $charts['prenatal']['active'] ?? 0 }},
                                {{ $charts['prenatal']['completed'] ?? 0 }}
                            ],
                            backgroundColor: [
                                chartColors.info,
                                chartColors.success
                            ],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { size: 12 }
                                }
                            },
                            tooltip: {
                                ...commonOptions.plugins.tooltip,
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + '%';
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
        } catch (error) {
            console.error('Error creating prenatal chart:', error);
        }

        // Monthly Patient Registrations Line Chart
        try {
            const registrationsCtx = document.getElementById('registrationsChart');
            if (registrationsCtx) {
                new Chart(registrationsCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($charts['monthly_registrations']['labels'] ?? []) !!},
                        datasets: [{
                            label: 'New Patient Registrations',
                            data: {!! json_encode($charts['monthly_registrations']['data'] ?? []) !!},
                            borderColor: chartColors.primary,
                            backgroundColor: chartColors.primary + '20',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointHoverRadius: 8,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: chartColors.primary,
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            x: {
                                grid: { 
                                    color: '#f3f4f6',
                                    borderColor: '#e5e7eb'
                                },
                                ticks: { 
                                    color: '#6b7280',
                                    font: { size: 12 }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { 
                                    color: '#f3f4f6',
                                    borderColor: '#e5e7eb'
                                },
                                ticks: { 
                                    color: '#6b7280',
                                    font: { size: 12 }
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Registrations',
                                    color: '#6b7280'
                                }
                            }
                        }
                    }
                });
            }
        } catch (error) {
            console.error('Error creating registrations chart:', error);
        }

        // Auto-hide success/error messages after 5 seconds
        const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

    } // Close initializeCharts function

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initializeCharts, 100);
    });

    window.addEventListener('resize', function() {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    });
</script>
@endpush