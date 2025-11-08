<!DOCTYPE html>
<html lang="en">
<head>
    <title>BHW Report - {{ $report_title }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Include reports print CSS --}}
    <link rel="stylesheet" href="{{ asset('css/bhw/reports-print.css') }}">
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

    {{-- Include reports print JavaScript --}}
    <script src="{{ asset('js/bhw/reports-print.js') }}"></script>
</body>
</html>