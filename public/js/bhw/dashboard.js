/**
 * BHW Dashboard JavaScript
 * Chart initialization and dashboard interactions
 */

/**
 * Initialize all dashboard charts
 * Uses Chart.js library for data visualization
 */
function initializeCharts() {
    console.log('Chart.js loaded:', typeof Chart !== 'undefined');
    console.log('Chart Data:', window.DASHBOARD_DATA);

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
        if (prenatalCtx && window.DASHBOARD_DATA) {
            new Chart(prenatalCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Active Prenatal', 'Completed Prenatal'],
                    datasets: [{
                        data: [
                            window.DASHBOARD_DATA.prenatal.active || 0,
                            window.DASHBOARD_DATA.prenatal.completed || 0
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
        if (registrationsCtx && window.DASHBOARD_DATA) {
            new Chart(registrationsCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: window.DASHBOARD_DATA.monthly_registrations.labels || [],
                    datasets: [{
                        label: 'New Patient Registrations',
                        data: window.DASHBOARD_DATA.monthly_registrations.data || [],
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
}

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initializeCharts, 100);
});

// Handle window resize to update charts
window.addEventListener('resize', function() {
    if (typeof Chart !== 'undefined' && Chart.instances) {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    }
});
