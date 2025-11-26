/* =================================================================
   Midwife Dashboard JavaScript
   Extracted from: resources/views/midwife/dashboard.blade.php

   IMPORTANT: This file requires Chart.js to be loaded via CDN
   Chart.js is loaded in the Blade template:
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

   DATA REQUIREMENTS (passed from Laravel Controller via Blade):
   The following Blade variables must be available:
   - $charts['checkups']['labels'] - Array of month labels
   - $charts['checkups']['data'] - Array of checkup counts
   - $charts['immunization']['fully'] - Fully immunized count
   - $charts['immunization']['partially'] - Partially immunized count
   - $charts['immunization']['not'] - Not immunized count
   - $charts['vaccines']['labels'] - Array of vaccine names
   - $charts['vaccines']['data'] - Array of vaccine usage counts
   - $charts['registration']['labels'] - Array of month labels
   - $charts['registration']['data'] - Array of new patient counts

   NOTE: When using this as an external file, you'll need to pass chart data
   as a separate configuration. See migration steps below.
   ================================================================= */

/* ========== CHART INITIALIZATION ========== */

/**
 * Initialize all dashboard charts
 * Checks if Chart.js is loaded and creates four chart instances
 */
function initializeCharts() {
    // Debug: Check if Chart.js loaded
    console.log('Chart.js loaded:', typeof Chart !== 'undefined');
    const chartData = window.DASHBOARD_DATA || {};
    const checkupsData = chartData.checkups || {};
    const immunizationData = chartData.immunization || {};
    const vaccineData = chartData.vaccines || {};
    const registrationData = chartData.registration || {};
    console.log('Chart Data:', chartData);

    if (typeof Chart === 'undefined') {
        console.error('Chart.js failed to load!');
        // Show fallback message
        document.querySelectorAll('.chart-container').forEach(container => {
            container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Chart library failed to load</div>';
        });
        return;
    }

    console.log('DOM loaded, initializing charts...');

    /* ========== CHART CONFIGURATION ========== */

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

    // Common chart options for consistent styling
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

    /* ========== CHART 1: PRENATAL CHECKUPS LINE CHART ========== */
    // Displays monthly checkup trends as a line chart
    try {
        const checkupsCtx = document.getElementById('checkupsChart');
        if (checkupsCtx) {
            new Chart(checkupsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: Array.isArray(checkupsData.labels) ? checkupsData.labels : [],
                    datasets: [{
                        label: 'Monthly Checkups',
                        data: Array.isArray(checkupsData.data) ? checkupsData.data : [],
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

    /* ========== CHART 2: IMMUNIZATION COVERAGE DOUGHNUT CHART ========== */
    // Displays distribution of fully immunized, partially immunized, and not immunized
    try {
        const immunizationCtx = document.getElementById('immunizationChart');
        if (immunizationCtx) {
            new Chart(immunizationCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Fully Immunized', 'Partially Immunized', 'Not Immunized'],
                    datasets: [{
                        data: [
                            Number(immunizationData.fully ?? 0),
                            Number(immunizationData.partially ?? 0),
                            Number(immunizationData.not ?? 100)
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

    /* ========== CHART 3: MOST USED VACCINES BAR CHART ========== */
    // Displays usage count for different vaccine types
    try {
        const vaccinesCtx = document.getElementById('vaccinesChart');
        if (vaccinesCtx) {
            new Chart(vaccinesCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Array.isArray(vaccineData.labels) ? vaccineData.labels : [],
                    datasets: [{
                        label: 'Usage Count',
                        data: Array.isArray(vaccineData.data) ? vaccineData.data : [],
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

    /* ========== CHART 4: PATIENT REGISTRATION TRENDS AREA CHART ========== */
    // Displays new patient registrations over time as an area chart
    try {
        const registrationCtx = document.getElementById('registrationChart');
        if (registrationCtx) {
            new Chart(registrationCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: Array.isArray(registrationData.labels) ? registrationData.labels : [],
                    datasets: [{
                        label: 'New Registrations',
                        data: Array.isArray(registrationData.data) ? registrationData.data : [],
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

    /* ========== ALERT AUTO-DISMISS ========== */

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

/* ========== DOM READY HANDLER ========== */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add small delay to ensure Chart.js is fully loaded
    setTimeout(initializeCharts, 100);
});

/* ========== UTILITY FUNCTIONS ========== */

/**
 * Show loading state for a specific chart
 * @param {string} chartId - The ID of the chart element
 */
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

/* ========== RESPONSIVE CHART HANDLING ========== */

// Handle window resize to maintain responsive charts
window.addEventListener('resize', function() {
    Object.values(Chart.instances).forEach(function(instance) {
        instance.resize();
    });
});
