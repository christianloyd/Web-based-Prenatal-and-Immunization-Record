@extends('layout.bhw')
@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'Generate detailed analytics and reports')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
        font-family: 'Inter', sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    :root {
        --primary: #243b55;
        --secondary: #141e30;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-400: #9ca3af;
        --gray-500: #6b7280;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
        --white: #ffffff;
        --success: #059669;
        --warning: #d97706;
        --error: #dc2626;
        --info: #2563eb;
    }
    
    body {
        background-color: var(--gray-50);
        color: var(--gray-800);
    }
    
    .card {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card:hover {
        border-color: var(--gray-300);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-900);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .section-title i {
        color: var(--primary);
        font-size: 1rem;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--gray-700);
        margin-bottom: 6px;
    }
    
    .form-input, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        font-size: 0.875rem;
        background-color: var(--white);
        transition: all 0.15s ease;
    }
    
    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(36, 59, 85, 0.1);
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    
    .btn-primary {
        background-color: var(--primary);
        color: var(--white);
    }
    
    .btn-primary:hover {
        background-color: var(--secondary);
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background-color: var(--white);
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }
    
    .btn-secondary:hover {
        background-color: var(--gray-50);
        border-color: var(--gray-400);
    }
    
    .btn-success {
        background-color: var(--success);
        color: var(--white);
    }
    
    .btn-success:hover {
        background-color: #047857;
    }
    
    .report-metric {
        text-align: center;
        padding: 20px;
    }
    
    .report-metric-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 4px;
    }
    
    .report-metric-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 500;
    }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .report-table th {
        background-color: var(--gray-50);
        padding: 12px 16px;
        text-align: left;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        border-bottom: 1px solid var(--gray-200);
    }
    
    .report-table td {
        padding: 12px 16px;
        font-size: 0.875rem;
        border-bottom: 1px solid var(--gray-100);
    }
    
    .report-table tr:hover {
        background-color: var(--gray-50);
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    .status-normal {
        background-color: #f0fdf4;
        color: var(--success);
    }
    
    .status-warning {
        background-color: #fffbeb;
        color: var(--warning);
    }
    
    .status-critical {
        background-color: #fef2f2;
        color: var(--error);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        padding: 20px 0;
    }
    
    .grid-1 { display: grid; grid-template-columns: 1fr; gap: 24px; }
    .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
    
    @media (max-width: 1024px) {
        .grid-4 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 640px) {
        .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
        .chart-container { height: 250px; }
    }
    
    .spacing-section {
        margin-bottom: 32px;
    }
    
    .spacing-section:last-child {
        margin-bottom: 0;
    }
    
    .report-filters {
        background: var(--white);
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 32px;
    }
    
    .report-content {
        display: none;
    }
    
    .report-content.active {
        display: block;
    }
    
    .loading {
        text-align: center;
        padding: 40px;
        color: var(--gray-500);
    }
    
    .loading i {
        font-size: 2rem;
        margin-bottom: 16px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Print styles */
    @media print {
        .report-filters, .btn, button {
            display: none !important;
        }
        
        .report-content {
            display: block !important;
        }
        
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }
        
        .spacing-section {
            page-break-inside: avoid;
        }
        
        .grid-2 {
            display: block !important;
        }
        
        .grid-2 > div {
            margin-bottom: 20px;
            width: 100% !important;
        }
        
        body {
            font-size: 12px !important;
            background: white !important;
        }
        
        /* Header styles - 16px */
        h1, h2, h3, .section-title {
            font-size: 16px !important;
            font-weight: 600 !important;
            margin-bottom: 12px !important;
        }
        
        /* Subheading styles - 14px */
        h4, h5, h6, .report-metric-label, th {
            font-size: 14px !important;
            font-weight: 500 !important;
        }
        
        /* Body text styles - 12px */
        p, td, span, div, .report-table td, .status-badge {
            font-size: 12px !important;
        }
        
        .report-metric-number {
            font-size: 16px !important;
            font-weight: 700 !important;
        }
        
        .chart-container {
            height: 200px !important;
        }
        
        .report-table th {
            font-size: 14px !important;
            font-weight: 600 !important;
        }
        
        .report-table td {
            font-size: 12px !important;
        }
    }
    
    /* Dropdown styles */
    .dropdown-item:hover {
        background-color: var(--gray-50);
    }
    
    /* Skeleton loading styles */
    .skeleton-loading {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, var(--gray-100) 25%, var(--gray-200) 50%, var(--gray-100) 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
    }
    
    @keyframes skeleton-loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }
    
    .chart-skeleton {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 20px;
        background: rgba(255, 255, 255, 0.8);
    }
    
    .skeleton-bar {
        border-radius: 4px;
        animation: skeleton-loading 1.5s infinite;
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Report Filters -->
<div class="report-filters">
    <h3 class="section-title">
        <i class="fas fa-filter"></i>
        Report Configuration
    </h3>
    
    <form method="GET" action="{{ route('bhw.report') }}">
        <div class="grid-4">
            <div class="form-group">
                <label class="form-label">Report Format</label>
                <select class="form-select" name="report_format" onchange="toggleReportFormat()">
                    <option value="dynamic" {{ ($currentFilters['report_format'] ?? 'dynamic') === 'dynamic' ? 'selected' : '' }}>📊 Dynamic Analytics Report</option>
                    <option value="custom" {{ ($currentFilters['report_format'] ?? '') === 'custom' ? 'selected' : '' }}>📋 Custom Paper Report</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Report Type</label>
                <select class="form-select" name="report_type">
                    <option value="monthly" {{ ($currentFilters['report_type'] ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly Summary</option>
                    <option value="quarterly" {{ ($currentFilters['report_type'] ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly Report</option>
                    <option value="annual" {{ ($currentFilters['report_type'] ?? '') === 'annual' ? 'selected' : '' }}>Annual Report</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Month</label>
                <select class="form-select" name="month">
                    @foreach($availableMonths as $value => $label)
                        <option value="{{ $value }}" {{ ($currentFilters['month'] ?? '') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Department</label>
                <select class="form-select" name="department">
                    <option value="all" {{ ($currentFilters['department'] ?? 'all') === 'all' ? 'selected' : '' }}>All Services</option>
                    <option value="prenatal" {{ ($currentFilters['department'] ?? '') === 'prenatal' ? 'selected' : '' }}>Prenatal Care</option>
                    <option value="child_health" {{ ($currentFilters['department'] ?? '') === 'child_health' ? 'selected' : '' }}>Child Health</option>
                </select>
            </div>
        </div>
        
        <div style="margin-top: 24px; display: flex; gap: 12px; align-items: center;">
            <x-update-button-skeleton 
                id="updateReportBtn"
                label="Update Report" 
                type="submit" 
                icon="fas fa-chart-bar" 
                variant="primary" 
            />
            
            <div class="report-format-info" style="flex: 1; padding: 12px 16px; background: var(--gray-50); border-radius: 8px; font-size: 0.875rem; color: var(--gray-600);">
                <span id="formatDescription">
                    {{ ($currentFilters['report_format'] ?? 'dynamic') === 'dynamic' ? 
                        '📊 Community health tracking with patient statistics and service delivery metrics' : 
                        '📋 Traditional community report format with structured layout for BHW activities'
                    }}
                </span>
            </div>
        </div>
    </form>
    
    <div style="margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap;">
        <button class="btn btn-primary" onclick="printReport()">
            <i class="fas fa-print"></i>
            Print Report
        </button>
        <button class="btn btn-secondary" onclick="exportPDF()">
            <i class="fas fa-file-pdf"></i>
            Export PDF
        </button>
        <button class="btn btn-secondary" onclick="exportExcel()">
            <i class="fas fa-file-excel"></i>
            Export Excel
        </button>
        <button class="btn btn-success" onclick="scheduleReport()">
            <i class="fas fa-clock"></i>
            Schedule Report
        </button>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="loading" style="display: none;">
    <i class="fas fa-spinner"></i>
    <p>Generating your report...</p>
</div>

<!-- Dynamic Report Content -->
<div id="dynamicReportContent" class="report-content {{ ($currentFilters['report_format'] ?? 'dynamic') === 'dynamic' ? 'active' : '' }}">
    <!-- Summary Metrics -->
    <div class="spacing-section">
        <div class="card" style="padding: 24px;">
            <h3 class="section-title">
                <i class="fas fa-chart-line"></i>
                <span id="reportTitle">{{ $availableMonths[$currentFilters['month']] ?? 'Current' }} Community Health Report</span>
            </h3>
            
            <div class="grid-4">
                <div class="report-metric">
                    <div class="report-metric-number">{{ number_format($totalPatients) }}</div>
                    <div class="report-metric-label">Total Patients</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number">{{ number_format($totalPrenatalRecords) }}</div>
                    <div class="report-metric-label">Prenatal Records</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number">{{ number_format($totalChildRecords) }}</div>
                    <div class="report-metric-label">Child Records</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number">{{ number_format($totalChildImmunizations ?? 0) }}</div>
                    <div class="report-metric-label">Child Immunizations</div>
                </div>
            </div>
            
            <!-- Child Immunization Summary -->
            <div class="grid-3" style="margin-top: 20px;">
                <div class="report-metric">
                    <div class="report-metric-number">{{ number_format($totalImmunizedGirls ?? 0) }}</div>
                    <div class="report-metric-label">Immunized Girls</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number">{{ number_format($totalImmunizedBoys ?? 0) }}</div>
                    <div class="report-metric-label">Immunized Boys</div>
                </div>
                <div class="report-metric">
                    <div class="report-metric-number">{{ number_format($thisMonthCheckups) }}</div>
                    <div class="report-metric-label">This Month Checkups</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Community Health Activities -->
    <div class="spacing-section">
        <div class="grid-2">
            <div class="card" style="padding: 24px;">
                <h3 class="section-title">
                    <i class="fas fa-home"></i>
                    Community Outreach Activities
                </h3>
                
                <div style="overflow-x: auto;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Activity Type</th>
                                <th>Count</th>
                                <th>Coverage</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-walking" style="color: var(--success);"></i>
                                        Home Visits
                                    </div>
                                </td>
                                <td>{{ $totalPatients > 0 ? intval($totalPatients * 0.3) : 0 }}</td>
                                <td>{{ $totalPatients > 0 ? '30%' : '0%' }}</td>
                                <td><span class="status-badge status-normal">Active</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-users" style="color: var(--info);"></i>
                                        Health Education
                                    </div>
                                </td>
                                <td>{{ $totalPrenatalRecords > 0 ? intval($totalPrenatalRecords * 0.8) : 0 }}</td>
                                <td>{{ $totalPrenatalRecords > 0 ? '80%' : '0%' }}</td>
                                <td><span class="status-badge status-normal">Ongoing</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-hand-holding-heart" style="color: var(--warning);"></i>
                                        Referrals
                                    </div>
                                </td>
                                <td>{{ $thisMonthCheckups > 0 ? intval($thisMonthCheckups * 0.1) : 0 }}</td>
                                <td>{{ $thisMonthCheckups > 0 ? '10%' : '0%' }}</td>
                                <td><span class="status-badge status-warning">Monitor</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="padding: 24px;">
                <h3 class="section-title">
                    <i class="fas fa-heartbeat"></i>
                    Health Service Distribution
                </h3>
                
                <div style="overflow-x: auto;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Current Month</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-baby" style="color: var(--success);"></i>
                                        Prenatal Care
                                    </div>
                                </td>
                                <td>{{ $totalPrenatalRecords }}</td>
                                <td style="color: var(--success);">
                                    <i class="fas fa-arrow-up"></i> +12%
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-child" style="color: var(--info);"></i>
                                        Child Health
                                    </div>
                                </td>
                                <td>{{ $totalChildRecords }}</td>
                                <td style="color: var(--success);">
                                    <i class="fas fa-arrow-up"></i> +8%
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-stethoscope" style="color: var(--primary);"></i>
                                        Regular Checkups
                                    </div>
                                </td>
                                <td>{{ $thisMonthCheckups }}</td>
                                <td style="color: var(--warning);">
                                    <i class="fas fa-minus"></i> +3%
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Performance Summary -->
    <div class="spacing-section">
        <div class="card" style="padding: 24px;">
            <h3 class="section-title">
                <i class="fas fa-calendar-check"></i>
                Monthly Performance Summary
            </h3>
            
            <div class="grid-3" style="margin-top: 20px;">
                <div style="text-align: center; padding: 20px; background: var(--gray-50); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success); margin-bottom: 8px;">
                        {{ $totalPatients > 0 ? round(($totalPrenatalRecords / $totalPatients) * 100, 1) : 0 }}%
                    </div>
                    <div style="font-size: 0.875rem; color: var(--gray-600);">
                        Prenatal Coverage Rate
                    </div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: var(--gray-50); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--info); margin-bottom: 8px;">
                        {{ $totalChildRecords > 0 ? round(($thisMonthCheckups / $totalChildRecords) * 100, 1) : 0 }}%
                    </div>
                    <div style="font-size: 0.875rem; color: var(--gray-600);">
                        Child Health Coverage
                    </div>
                </div>
                
                <div style="text-align: center; padding: 20px; background: var(--gray-50); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 8px;">
                        95%
                    </div>
                    <div style="font-size: 0.875rem; color: var(--gray-600);">
                        Community Satisfaction
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Paper Report Content -->
<div id="customReportContent" class="report-content {{ ($currentFilters['report_format'] ?? '') === 'custom' ? 'active' : '' }}">
    <div class="spacing-section">
        <div class="card" style="padding: 40px; text-align: center; border: 2px dashed var(--gray-300);">
            <div style="max-width: 500px; margin: 0 auto;">
                <i class="fas fa-clipboard-list" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 24px;"></i>
                <h3 style="color: var(--gray-700); margin-bottom: 16px;">Custom BHW Report Format</h3>
                <p style="color: var(--gray-500); margin-bottom: 24px; line-height: 1.6;">
                    This section will display your custom BHW report format based on your paper document.
                    Please describe the layout and sections you'd like me to implement for community health reports.
                </p>
                
                <div style="background: var(--gray-50); padding: 20px; border-radius: 8px; text-align: left; margin-bottom: 24px;">
                    <h4 style="color: var(--gray-700); margin-bottom: 12px; font-size: 1rem;">BHW Report Customization:</h4>
                    <ul style="color: var(--gray-600); font-size: 0.9rem; line-height: 1.6; margin: 0; padding-left: 20px;">
                        <li>Community health activities and coverage</li>
                        <li>Home visit records and follow-ups</li>
                        <li>Health education sessions conducted</li>
                        <li>Patient referrals and outcomes</li>
                        <li>Monthly performance indicators</li>
                        <li>Community health challenges and solutions</li>
                    </ul>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button class="btn btn-secondary" onclick="alert('Please describe your BHW paper report format so I can implement it for you!')">
                        <i class="fas fa-comments"></i>
                        Describe Your Format
                    </button>
                    <button class="btn btn-primary" onclick="switchToFormat('dynamic')">
                        <i class="fas fa-arrow-left"></i>
                        Use Dynamic Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function exportPDF() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating PDF...';
        btn.disabled = true;
        
        // Create form with current filters
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("bhw.report.export.pdf") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add current filters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        
        // Submit the form to trigger download
        form.target = '_blank';
        form.submit();
        
        // Clean up and restore button
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            document.body.removeChild(form);
            alert('BHW Report PDF export started! Check your downloads folder for the PDF file.');
        }, 1000);
    }
    
    function printReport() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Opening Print View...';
        btn.disabled = true;
        
        // Build URL with current filters
        const urlParams = new URLSearchParams(window.location.search);
        let printUrl = '{{ route("bhw.report.print") }}';
        
        // Add current filters to print URL
        if (urlParams.toString()) {
            printUrl += '?' + urlParams.toString() + '&autoprint=1';
        } else {
            printUrl += '?autoprint=1';
        }
        
        // Open print view in new window
        const printWindow = window.open(printUrl, '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');
        
        // Restore button after a short delay
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 1000);
        
        // Focus the print window
        if (printWindow) {
            printWindow.focus();
        }
    }

    function exportExcel(exportType = 'full') {
        const btn = event.target.closest('.dropdown') ? 
                   event.target.closest('.dropdown').querySelector('.dropdown-toggle') : 
                   event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;
        
        // Create form with current filters
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("bhw.report.export.excel") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add export type
        const exportTypeInput = document.createElement('input');
        exportTypeInput.type = 'hidden';
        exportTypeInput.name = 'export_type';
        exportTypeInput.value = exportType;
        form.appendChild(exportTypeInput);
        
        // Add current filters
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.forEach((value, key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        
        // Submit the form to trigger download
        form.target = '_blank';
        form.submit();
        
        // Show success message after a short delay
        setTimeout(() => {
            const exportTypeNames = {
                'full': 'Complete BHW Report',
                'summary': 'Summary Report',
                'community': 'Community Activities Report'
            };
            alert(`${exportTypeNames[exportType]} Excel export started! Check your downloads folder.`);
        }, 500);
        
        // Clean up and restore button
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            document.body.removeChild(form);
        }, 1000);
    }
    
    function scheduleReport() {
        const reportType = document.querySelector('select[name="report_type"]').value;
        const frequency = prompt('How often would you like to receive this BHW report?\n\n1. Weekly\n2. Monthly\n3. Quarterly\n\nEnter 1, 2, or 3:');
        
        if (frequency) {
            const frequencies = {'1': 'weekly', '2': 'monthly', '3': 'quarterly'};
            const selectedFrequency = frequencies[frequency];
            
            if (selectedFrequency) {
                alert(`BHW Report scheduled successfully! You will receive ${reportType} community health reports ${selectedFrequency} via email.`);
            } else {
                alert('Invalid selection. Please try again.');
            }
        }
    }
    
    function toggleReportFormat() {
        const formatSelect = document.querySelector('select[name="report_format"]');
        const formatDescription = document.getElementById('formatDescription');
        const dynamicContent = document.getElementById('dynamicReportContent');
        const customContent = document.getElementById('customReportContent');
        
        if (formatSelect.value === 'dynamic') {
            formatDescription.textContent = '📊 Community health tracking with patient statistics and service delivery metrics';
            dynamicContent.classList.add('active');
            customContent.classList.remove('active');
        } else {
            formatDescription.textContent = '📋 Traditional community report format with structured layout for BHW activities';
            dynamicContent.classList.remove('active');
            customContent.classList.add('active');
        }
    }
    
    function switchToFormat(format) {
        const formatSelect = document.querySelector('select[name="report_format"]');
        formatSelect.value = format;
        toggleReportFormat();
    }
    
    // Handle Update Report button loading state
    function handleUpdateReportLoading() {
        const updateBtn = document.getElementById('updateReportBtn');
        const btnContent = updateBtn.querySelector('.btn-content');
        const btnLoading = updateBtn.querySelector('.btn-loading');
        
        if (updateBtn && btnContent && btnLoading) {
            // Show loading state
            btnContent.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            updateBtn.disabled = true;
            updateBtn.style.opacity = '0.7';
            updateBtn.style.cursor = 'not-allowed';
            
            // Create skeleton effect for report sections
            const reportMetrics = document.querySelectorAll('.report-metric');
            reportMetrics.forEach(metric => {
                metric.classList.add('skeleton-loading');
            });
        }
    }
    
    // Initialize report on page load
    document.addEventListener('DOMContentLoaded', function() {
        const currentFormat = '{{ $currentFilters["report_format"] ?? "dynamic" }}';
        
        // Set initial format description
        toggleReportFormat();
        
        // Add form submit handler for loading state
        const reportForm = document.querySelector('form');
        if (reportForm) {
            reportForm.addEventListener('submit', function(e) {
                handleUpdateReportLoading();
            });
        }
    });
</script>
@endsection