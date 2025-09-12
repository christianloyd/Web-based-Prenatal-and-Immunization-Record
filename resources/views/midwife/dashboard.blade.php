@extends('layout.midwife')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your midwife practice')

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
    }
    
    .fade-in {
        animation: fadeIn 0.6s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Button Hover Effects */
    .btn-primary {
        transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Chart styling */
    .chart-card {
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
    }

    /* Dashboard grid improvements */
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
        <!-- Total Registered Mothers -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Registered Mothers</p>
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['total_patients']) }}</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>+{{ $stats['patients_change'] }} this month
                    </p>
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
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['active_prenatal_records']) }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-heartbeat mr-1"></i>Currently monitoring
                    </p>
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
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['checkups_this_month']) }}</p>
                    <p class="text-sm {{ $stats['checkups_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        <i class="fas fa-arrow-{{ $stats['checkups_change'] >= 0 ? 'up' : 'down' }} mr-1"></i>
                        {{ $stats['checkups_change'] >= 0 ? '+' : '' }}{{ $stats['checkups_change'] }} vs last month
                    </p>
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
                    <p class="text-3xl font-bold primary-text">{{ number_format($stats['total_children']) }}</p>
                    <p class="text-sm text-purple-600 mt-1">
                        <i class="fas fa-arrow-up mr-1"></i>+{{ $stats['children_change'] }} this month
                    </p>
                </div>
                <div class="bg-purple-500 text-white p-3 rounded-lg">
                    <i class="fas fa-child text-2xl"></i>
                </div>
            </div>
        </div>

        
    </div>

    <!-- Charts Section -->
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

    <!-- Lists Section -->
    <div class="dashboard-grid cols-2">
        <!-- Recent Checkups -->
        <div class="bg-white rounded-lg border fade-in">
            <div class="border-b px-6 py-4">
                <h3 class="text-lg font-semibold primary-text">
                    <i class="fas fa-history mr-2"></i>Recent Prenatal Checkups
                </h3>
            </div>
            <div class="p-6">
                @if($recentCheckups->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentCheckups as $checkup)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="primary-bg text-white p-2 rounded-full">
                                        <i class="fas fa-user-check text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $checkup['patient_name'] }}</p>
                                        <p class="text-sm text-gray-600">{{ $checkup['checkup_date']->format('M j, Y') }}</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    @if($checkup['status'] === 'High Risk') bg-red-100 text-red-800
                                    @elseif($checkup['status'] === 'Follow-up') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ $checkup['status'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-2"></i>
                        <p class="text-gray-500">No recent checkups</p>
                    </div>
                @endif
                
                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('midwife.prenatalrecord.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View all checkups <i class="fas fa-arrow-right ml-1"></i>
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
    // Debug: Check if Chart.js loaded
    function initializeCharts() {
        console.log('Chart.js loaded:', typeof Chart !== 'undefined');
        console.log('Chart Data:', {!! json_encode($charts) !!});
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js failed to load!');
            // Show fallback message
            document.querySelectorAll('.chart-container').forEach(container => {
                container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Chart library failed to load</div>';
            });
            return;
        }
        
        console.log('DOM loaded, initializing charts...');
    
    // Chart.js configuration with custom colors
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

    // Common chart options
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
        },
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
                }
            }
        }
    };

    // Prenatal Checkups Line Chart
    try {
        const checkupsCtx = document.getElementById('checkupsChart');
        if (checkupsCtx) {
            new Chart(checkupsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($charts['checkups']['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Monthly Checkups',
                        data: {!! json_encode($charts['checkups']['data'] ?? []) !!},
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
                        ...commonOptions.scales,
                        y: {
                            ...commonOptions.scales.y,
                            title: {
                                display: true,
                                text: 'Number of Checkups',
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error creating checkups chart:', error);
    }

    // Immunization Coverage Pie Chart
    try {
        const immunizationCtx = document.getElementById('immunizationChart');
        if (immunizationCtx) {
            new Chart(immunizationCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Fully Immunized', 'Partially Immunized', 'Not Immunized'],
                    datasets: [{
                        data: [
                            {{ $charts['immunization']['fully'] ?? 0 }},
                            {{ $charts['immunization']['partially'] ?? 0 }},
                            {{ $charts['immunization']['not'] ?? 100 }}
                        ],
                        backgroundColor: [
                            chartColors.success,
                            chartColors.warning,
                            chartColors.danger
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
        console.error('Error creating immunization chart:', error);
    }

    // Most Used Vaccines Bar Chart
    try {
        const vaccinesCtx = document.getElementById('vaccinesChart');
        if (vaccinesCtx) {
            new Chart(vaccinesCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($charts['vaccines']['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Usage Count',
                        data: {!! json_encode($charts['vaccines']['data'] ?? []) !!},
                        backgroundColor: chartColors.info + '80',
                        borderColor: chartColors.info,
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        ...commonOptions.plugins,
                        legend: { display: false }
                    },
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            ...commonOptions.scales.y,
                            title: {
                                display: true,
                                text: 'Number of Administrations',
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error creating vaccines chart:', error);
    }

    // Patient Registration Trends Area Chart
    try {
        const registrationCtx = document.getElementById('registrationChart');
        if (registrationCtx) {
            new Chart(registrationCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($charts['registration']['labels'] ?? []) !!},
                    datasets: [{
                        label: 'New Registrations',
                        data: {!! json_encode($charts['registration']['data'] ?? []) !!},
                        borderColor: chartColors.success,
                        backgroundColor: chartColors.success + '30',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: chartColors.success,
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            ...commonOptions.scales.y,
                            title: {
                                display: true,
                                text: 'New Patients',
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error creating registration chart:', error);
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

    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Add small delay to ensure Chart.js is fully loaded
        setTimeout(initializeCharts, 100);
    });

    // Add loading states for charts
    function showChartLoading(chartId) {
        const container = document.getElementById(chartId).parentElement;
        container.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-2xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">Loading chart...</p>
                </div>
            </div>
        `;
    }

    // Responsive chart handling
    window.addEventListener('resize', function() {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    });
</script>
@endpush