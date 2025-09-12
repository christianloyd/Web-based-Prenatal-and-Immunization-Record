<!DOCTYPE html>
<html lang="en">
<head>
    <title>BHW Report - {{ $report_title }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #ffffff;
            color: #374151;
            line-height: 1.6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #243b55;
            padding-bottom: 20px;
        }
        
        .print-header h1 {
            color: #243b55;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .print-header p {
            font-size: 14px;
            color: #6b7280;
            margin: 5px 0;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #243b55;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .metric-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .metric-number {
            font-size: 24px;
            font-weight: 700;
            color: #243b55;
            margin-bottom: 5px;
        }
        
        .metric-label {
            font-size: 14px;
            color: #6b7280;
            font-weight: 500;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .data-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .data-table tr:hover {
            background-color: #f9fafb;
        }
        
        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 20px 0;
        }
        
        .column {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
        }
        
        .print-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        
        @media print {
            body {
                font-size: 12px;
            }
            
            .print-container {
                max-width: none;
                padding: 0;
            }
            
            .section {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
            
            .metric-card {
                break-inside: avoid;
            }
            
            .data-table {
                break-inside: avoid;
            }
            
            /* Header styles - 16px */
            h1, h2, .section-title {
                font-size: 16px !important;
                font-weight: 600 !important;
            }
            
            .print-header h1 {
                font-size: 20px !important;
                font-weight: 700 !important;
            }
            
            /* Subheading styles - 14px */
            h3, .metric-label, .data-table th {
                font-size: 14px !important;
                font-weight: 500 !important;
            }
            
            /* Body text styles - 12px */
            p, td, span, div, .data-table td {
                font-size: 12px !important;
            }
            
            .metric-number {
                font-size: 16px !important;
                font-weight: 700 !important;
            }
            
            .print-header p {
                font-size: 12px !important;
            }
            
            .print-footer {
                font-size: 10px !important;
            }
        }
        
        @page {
            size: A4;
            margin: 1in;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Print Header -->
        <div class="print-header">
            <h1>Community Health Worker Report</h1>
            <p><strong>{{ $report_title ?? 'BHW Activity Report' }}</strong></p>
            <p>Generated: {{ $print_date }}</p>
            <p>Prepared by: {{ $print_user }}</p>
            <p>Period: {{ isset($currentFilters['month']) && $currentFilters['month'] ? ($availableMonths[$currentFilters['month']] ?? 'Selected Month') : 'All Data' }}</p>
        </div>
        
        <!-- Summary Statistics -->
        <div class="section">
            <h2 class="section-title">Summary Statistics</h2>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-number">{{ number_format($totalPatients ?? 0) }}</div>
                    <div class="metric-label">Total Patients</div>
                </div>
                <div class="metric-card">
                    <div class="metric-number">{{ number_format($totalPrenatalRecords ?? 0) }}</div>
                    <div class="metric-label">Prenatal Records</div>
                </div>
                <div class="metric-card">
                    <div class="metric-number">{{ number_format($totalChildRecords ?? 0) }}</div>
                    <div class="metric-label">Child Records</div>
                </div>
                <div class="metric-card">
                    <div class="metric-number">{{ number_format($totalChildImmunizations ?? 0) }}</div>
                    <div class="metric-label">Child Immunizations</div>
                </div>
            </div>
            
            <!-- Child Immunization Breakdown -->
            <div class="two-column" style="margin-top: 20px;">
                <div class="column">
                    <h3 style="margin-bottom: 15px; color: #243b55;">Immunized Children by Gender</h3>
                    <table class="data-table">
                        <tr>
                            <td><strong>Immunized Girls</strong></td>
                            <td>{{ number_format($totalImmunizedGirls ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Immunized Boys</strong></td>
                            <td>{{ number_format($totalImmunizedBoys ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Immunized</strong></td>
                            <td>{{ number_format(($totalImmunizedGirls ?? 0) + ($totalImmunizedBoys ?? 0)) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <h3 style="margin-bottom: 15px; color: #243b55;">Recent Activity</h3>
                    <table class="data-table">
                        <tr>
                            <td><strong>Recent Checkups</strong></td>
                            <td>{{ number_format($thisMonthCheckups ?? 0) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Coverage Rate</strong></td>
                            <td>{{ $totalChildRecords > 0 ? round((($totalImmunizedGirls ?? 0) + ($totalImmunizedBoys ?? 0)) / $totalChildRecords * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr>
                            <td><strong>Immunization Rate</strong></td>
                            <td>{{ $totalChildRecords > 0 ? round(($totalChildImmunizations ?? 0) / $totalChildRecords * 100, 1) : 0 }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Community Activities -->
        @if(isset($communityActivities))
        <div class="section">
            <h2 class="section-title">Community Activities</h2>
            <div class="two-column">
                <div class="column">
                    <h3 style="margin-bottom: 15px; color: #243b55;">Home Visits</h3>
                    <table class="data-table">
                        <tr>
                            <td><strong>Total Visits</strong></td>
                            <td>{{ $communityActivities['home_visits']['total'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Completed</strong></td>
                            <td>{{ $communityActivities['home_visits']['completed'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pending</strong></td>
                            <td>{{ $communityActivities['home_visits']['pending'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <h3 style="margin-bottom: 15px; color: #243b55;">Health Education</h3>
                    <table class="data-table">
                        <tr>
                            <td><strong>Sessions Conducted</strong></td>
                            <td>{{ $communityActivities['health_education']['sessions_conducted'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Participants</strong></td>
                            <td>{{ $communityActivities['health_education']['participants'] ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td><strong>Topics Covered</strong></td>
                            <td>{{ $communityActivities['health_education']['topics_covered'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Referrals -->
        @if(isset($communityActivities['referrals']))
        <div class="section">
            <h2 class="section-title">Referral Statistics</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Count</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Referrals</td>
                        <td>{{ $communityActivities['referrals']['total_referrals'] ?? 0 }}</td>
                        <td>All Cases</td>
                    </tr>
                    <tr>
                        <td>Successful Referrals</td>
                        <td>{{ $communityActivities['referrals']['successful_referrals'] ?? 0 }}</td>
                        <td>Completed</td>
                    </tr>
                    <tr>
                        <td>Pending Follow-up</td>
                        <td>{{ $communityActivities['referrals']['pending_follow_up'] ?? 0 }}</td>
                        <td>In Progress</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        <!-- Custom Report Data -->
        @if(isset($customReportData))
        <div class="section">
            <h2 class="section-title">Community Coverage</h2>
            @if(isset($customReportData['community_coverage']))
            <table class="data-table">
                <tr>
                    <td><strong>Total Households Assigned</strong></td>
                    <td>{{ $customReportData['community_coverage']['total_households_assigned'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Households Visited</strong></td>
                    <td>{{ $customReportData['community_coverage']['households_visited'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td><strong>Coverage Percentage</strong></td>
                    <td>{{ $customReportData['community_coverage']['coverage_percentage'] ?? 0 }}%</td>
                </tr>
            </table>
            @endif
        </div>
        @endif

        <!-- Health Services Delivered -->
        @if(isset($customReportData['health_services_delivered']))
        <div class="section">
            <h2 class="section-title">Health Services Delivered</h2>
            <div class="two-column">
                @if(isset($customReportData['health_services_delivered']['prenatal_monitoring']))
                <div class="column">
                    <h3 style="margin-bottom: 15px; color: #243b55;">Prenatal Monitoring</h3>
                    <table class="data-table">
                        @foreach($customReportData['health_services_delivered']['prenatal_monitoring'] as $key => $value)
                        <tr>
                            <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                            <td>{{ $value }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif
                
                @if(isset($customReportData['health_services_delivered']['child_health']))
                <div class="column">
                    <h3 style="margin-bottom: 15px; color: #243b55;">Child Health</h3>
                    <table class="data-table">
                        @foreach($customReportData['health_services_delivered']['child_health'] as $key => $value)
                        <tr>
                            <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                            <td>{{ $value }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Print Footer -->
        <div class="print-footer">
            Community Health Management System | Generated: {{ $print_date }} | Report by: {{ $print_user }}
        </div>
    </div>

    <script>
        // Auto-print if requested
        if (window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>