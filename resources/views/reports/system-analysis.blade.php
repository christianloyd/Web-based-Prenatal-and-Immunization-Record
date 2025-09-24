<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report_title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
        }

        .header .subtitle {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 10px;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            color: #34495e;
            font-size: 18px;
            margin-bottom: 15px;
            border-left: 4px solid #3498db;
            padding-left: 10px;
            background-color: #ecf0f1;
            padding: 10px;
        }

        .subsection-title {
            color: #2c3e50;
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .status-complete {
            color: #27ae60;
            font-weight: bold;
        }

        .status-incomplete {
            color: #e74c3c;
            font-weight: bold;
        }

        .status-warning {
            color: #f39c12;
            font-weight: bold;
        }

        .module-card {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #27ae60;
        }

        .module-card.incomplete {
            border-left-color: #e74c3c;
            background-color: #ffe6e6;
        }

        .module-header {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .feature-list {
            margin: 10px 0;
            padding-left: 15px;
        }

        .feature-list li {
            margin-bottom: 5px;
            font-size: 10px;
        }

        .summary-stats {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        .stat-item {
            display: inline-block;
            margin: 0 20px;
            text-align: center;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .priority-high {
            background-color: #ffe6e6;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin: 10px 0;
        }

        .priority-medium {
            background-color: #fff3cd;
            border-left: 4px solid #f39c12;
            padding: 15px;
            margin: 10px 0;
        }

        .priority-low {
            background-color: #e6f3ff;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 10px 0;
        }

        .tech-spec-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .tech-spec-table th,
        .tech-spec-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }

        .tech-spec-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .recommendation-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .recommendation-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #3498db;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        ul {
            margin: 5px 0;
            padding-left: 20px;
        }

        li {
            margin-bottom: 3px;
        }

        .completion-bar {
            width: 100%;
            height: 20px;
            background-color: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .completion-fill {
            height: 100%;
            background-color: #27ae60;
            width: {{ $system_completion }};
            text-align: center;
            line-height: 20px;
            color: white;
            font-weight: bold;
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $report_title }}</h1>
        <div class="subtitle">
            Generated on {{ $generated_date }}<br>
            System Analysis & Progress Report
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="section">
        <div class="section-title">Executive Summary</div>
        <div class="summary-stats">
            <div class="stat-item">
                <span class="stat-number">{{ $system_completion }}</span>
                <span class="stat-label">System Complete</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $modules_completed }}/{{ $modules_total }}</span>
                <span class="stat-label">Modules Complete</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">Laravel 11.x</span>
                <span class="stat-label">Framework</span>
            </div>
        </div>

        <div class="completion-bar">
            <div class="completion-fill">{{ $system_completion }} Complete</div>
        </div>

        <p>This comprehensive web-based Healthcare Management System is designed for managing prenatal care, child records, and immunization tracking. Built specifically for healthcare workers including midwives and Barangay Health Workers (BHW), the system provides complete healthcare workflow management with robust data security and cloud backup capabilities.</p>
    </div>

    <!-- System Overview -->
    <div class="section">
        <div class="section-title">System Overview</div>

        <div class="subsection-title">Purpose & Scope</div>
        <ul>
            <li>Comprehensive prenatal care monitoring and management</li>
            <li>Child health records and immunization tracking</li>
            <li>Patient registration and profile management</li>
            <li>Healthcare worker coordination and reporting</li>
            <li>Data backup, synchronization, and SMS notifications</li>
        </ul>

        <div class="subsection-title">Target Users</div>
        <ul>
            <li><strong>Midwives:</strong> Full system access with administrative capabilities</li>
            <li><strong>Barangay Health Workers (BHW):</strong> Limited access for patient registration and basic care monitoring</li>
        </ul>
    </div>

    <!-- Completed Modules -->
    <div class="section page-break">
        <div class="section-title">Completed Modules ({{ $modules_completed }}/{{ $modules_total }})</div>

        @foreach($completed_modules as $module)
        <div class="module-card">
            <div class="module-header">
                <span class="status-complete">✅</span> {{ $module['name'] }} - {{ $module['completion'] }} Complete
            </div>
            <div class="subsection-title">Key Features Implemented:</div>
            <ul class="feature-list">
                @foreach($module['features'] as $feature)
                <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>

    <!-- Remaining Modules -->
    <div class="section">
        <div class="section-title">Remaining Modules</div>

        @foreach($remaining_modules as $module)
        <div class="module-card incomplete">
            <div class="module-header">
                <span class="status-incomplete">⚠️</span> {{ $module['name'] }} - {{ $module['completion'] }} Complete
            </div>
            <div class="subsection-title">Requirements for Completion:</div>
            <ul class="feature-list">
                @foreach($module['requirements'] as $requirement)
                <li>{{ $requirement }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>

    <!-- Priority Tasks -->
    <div class="section page-break">
        <div class="section-title">Priority Tasks & Timeline</div>

        @foreach($priority_tasks as $task)
        <div class="priority-{{ strtolower($task['priority']) }}">
            <div class="subsection-title">{{ $task['priority'] }} Priority: {{ $task['task'] }}</div>
            <p><strong>Estimated Time:</strong> {{ $task['estimated_time'] }}</p>
            <div class="subsection-title">Requirements:</div>
            <ul>
                @foreach($task['requirements'] as $requirement)
                <li>{{ $requirement }}</li>
                @endforeach
            </ul>
        </div>
        @endforeach
    </div>

    <!-- System Strengths -->
    <div class="section">
        <div class="section-title">System Strengths & Achievements</div>
        <ul>
            @foreach($system_strengths as $strength)
            <li>{{ $strength }}</li>
            @endforeach
        </ul>
    </div>

    <!-- Technical Specifications -->
    <div class="section page-break">
        <div class="section-title">Technical Specifications</div>

        <table class="tech-spec-table">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Technology/Version</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Backend Framework</td>
                    <td>{{ $technical_specs['framework'] }}</td>
                    <td>Core application framework</td>
                </tr>
                <tr>
                    <td>Programming Language</td>
                    <td>{{ $technical_specs['php_version'] }}</td>
                    <td>Server-side programming</td>
                </tr>
                <tr>
                    <td>Database</td>
                    <td>{{ $technical_specs['database'] }}</td>
                    <td>Data storage and management</td>
                </tr>
                <tr>
                    <td>Frontend</td>
                    <td>{{ $technical_specs['frontend'] }}</td>
                    <td>User interface and styling</td>
                </tr>
            </tbody>
        </table>

        <div class="subsection-title">Third-Party Integrations:</div>
        <ul>
            @foreach($technical_specs['integrations'] as $integration)
            <li>{{ $integration }}</li>
            @endforeach
        </ul>
    </div>

    <!-- Recommendations -->
    <div class="section">
        <div class="section-title">Recommendations & Next Steps</div>

        <div class="recommendation-box">
            <div class="recommendation-title">Immediate Actions (Next 1-2 weeks)</div>
            <ul>
                @foreach($recommendations['immediate'] as $action)
                <li>{{ $action }}</li>
                @endforeach
            </ul>
        </div>

        <div class="recommendation-box">
            <div class="recommendation-title">Short-term Goals (Next 1-2 months)</div>
            <ul>
                @foreach($recommendations['short_term'] as $goal)
                <li>{{ $goal }}</li>
                @endforeach
            </ul>
        </div>

        <div class="recommendation-box">
            <div class="recommendation-title">Long-term Vision (Next 3-6 months)</div>
            <ul>
                @foreach($recommendations['long_term'] as $vision)
                <li>{{ $vision }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Conclusion -->
    <div class="section">
        <div class="section-title">Conclusion</div>
        <p>The Healthcare Management System represents a comprehensive solution for modern healthcare administration, particularly tailored for maternal and child health services. With {{ $system_completion }} completion, the system demonstrates strong technical implementation and thorough healthcare workflow integration.</p>

        <p><strong>Key Achievements:</strong> Eight out of nine core modules are fully operational, providing complete healthcare management capabilities with robust technical foundation and user-centric design.</p>

        <p><strong>Critical Next Step:</strong> Complete SMS integration to achieve 100% system functionality. This final component is essential for patient communication and appointment management.</p>

        <p><strong>Final Assessment:</strong> The system is ready to deliver significant value to healthcare providers and communities with the completion of SMS integration and final testing.</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Healthcare Management System Analysis Report</strong></p>
        <p>Generated on {{ $generated_date }} | System Version: Laravel 11.x</p>
        <p>Analysis Completion: 100% | Overall System Readiness: {{ $system_completion }} Complete</p>
        <p style="margin-top: 15px; font-style: italic;">
            This report represents a comprehensive analysis of the healthcare management system.<br>
            Regular updates and reviews are recommended as the system evolves.
        </p>
    </div>
</body>
</html>