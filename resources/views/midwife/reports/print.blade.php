<!DOCTYPE html>
<html>
<head>
    <title>Healthcare Report - {{ $report_title }}</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #243b55;
            padding-bottom: 15px;
        }
        
        .print-header h1 {
            font-size: 24px;
            color: #243b55;
            margin-bottom: 5px;
        }
        
        .print-header p {
            color: #666;
            margin: 2px 0;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            text-align: center;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }
        
        .metric-number {
            font-size: 28px;
            font-weight: bold;
            color: #243b55;
            margin-bottom: 5px;
        }
        
        .metric-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        .section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #243b55;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #243b55;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }
        
        .data-table td {
            padding: 8px;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }
        
        .data-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .status-excellent { 
            background: #10b981; 
            color: white; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 9px; 
            display: inline-block;
        }
        .status-good { 
            background: #3b82f6; 
            color: white; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 9px; 
            display: inline-block;
        }
        .status-average { 
            background: #f59e0b; 
            color: white; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 9px; 
            display: inline-block;
        }
        .status-low { 
            background: #10b981; 
            color: white; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 9px; 
            display: inline-block;
        }
        .status-medium { 
            background: #f59e0b; 
            color: white; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 9px; 
            display: inline-block;
        }
        .status-high { 
            background: #ef4444; 
            color: white; 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 9px; 
            display: inline-block;
        }
        
        .print-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding: 5px;
            background: white;
        }
        
        .no-print {
            display: none;
        }
        
        @media print {
            body { 
                margin: 0; 
                font-size: 10px;
            }
            .section { 
                page-break-inside: avoid; 
                margin-bottom: 20px;
            }
            .metrics-grid {
                grid-template-columns: repeat(2, 1fr);
                margin-bottom: 20px;
            }
            .metric-number {
                font-size: 20px;
            }
            .print-controls {
                display: none !important;
            }
        }
        
        @media screen {
            body {
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
        }
        
        .print-controls {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            background: #243b55;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #1e3048;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <!-- Print Controls (only visible on screen) -->
    <div class="print-controls">
        <button class="btn" onclick="window.print()">
            🖨️ Print Report
        </button>
        <a href="{{ route('midwife.report') }}" class="btn btn-secondary">
            ← Back to Reports
        </a>
    </div>

    <!-- Print Header -->
    <div class="print-header">
        <h1>Healthcare Report</h1>
        <p><strong>{{ $report_title }} Report</strong></p>
        <p>Generated on: {{ $print_date }}</p>
        <p>Prepared by: {{ $print_user }}</p>
    </div>
    
    <!-- Summary Statistics -->
    <div class="section">
        <h2 class="section-title">Summary Statistics</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-number">{{ number_format($totalPatients) }}</div>
                <div class="metric-label">Total Patients</div>
            </div>
            <div class="metric-card">
                <div class="metric-number">{{ number_format($totalCheckups) }}</div>
                <div class="metric-label">Checkups Completed</div>
            </div>
            <div class="metric-card">
                <div class="metric-number">{{ number_format($totalVaccinations) }}</div>
                <div class="metric-label">Vaccinations Given</div>
            </div>
            <div class="metric-card">
                <div class="metric-number">{{ number_format($totalChildren) }}</div>
                <div class="metric-label">Total Children</div>
            </div>
        </div>
    </div>

    <!-- Child Immunization Statistics -->
    <div class="section">
        <h2 class="section-title">Child Immunization Statistics</h2>
        <div class="two-column">
            <div class="column">
                <table class="data-table">
                    <tr>
                        <td><strong>Immunized Girls</strong></td>
                        <td>{{ number_format($totalImmunizedGirls ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Immunized Boys</strong></td>
                        <td>{{ number_format($totalImmunizedBoys ?? 0) }}</td>
                    </tr>
                </table>
            </div>
            <div class="column">
                <table class="data-table">
                    <tr>
                        <td><strong>Upcoming Immunizations</strong></td>
                        <td>{{ number_format($upcomingImmunizations ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Upcoming Checkups</strong></td>
                        <td>{{ number_format($upcomingCheckups ?? 0) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>


    @if(isset($patientDemographics))
    <!-- Patient Demographics -->
    <div class="section">
        <h2 class="section-title">Patient Demographics by Age Group</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Age Group</th>
                    <th>Total Patients</th>
                    <th>New Patients</th>
                    <th>Immunized Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patientDemographics as $demo)
                <tr>
                    <td>{{ $demo['age_group'] }}</td>
                    <td>{{ $demo['total_patients'] }}</td>
                    <td>{{ $demo['new_patients'] }}</td>
                    <td>{{ $demo['immunized_count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Print Footer -->
    <div class="print-footer">
        Healthcare Management System | Generated: {{ $print_date }} | Report by: {{ $print_user }}
    </div>

    <script>
        // Auto-print when opened in new window with print parameter
        if (window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>