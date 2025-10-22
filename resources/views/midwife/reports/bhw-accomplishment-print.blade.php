<!DOCTYPE html>
<html>
<head>
    <title>BHW Monthly Accomplishment Report</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            background: white;
        }

        .report-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
        }

        /* Header Section */
        .report-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .report-header h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .report-info {
            margin: 10px 0;
            font-size: 10px;
        }

        .report-info-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .report-info-row span {
            flex: 1;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8px;
        }

        .data-table th {
            background: #f0f0f0;
            padding: 5px 3px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 7px;
            vertical-align: middle;
        }

        .data-table td {
            padding: 4px 3px;
            border: 1px solid #000;
            vertical-align: top;
        }

        .data-table td.metric-name {
            text-align: left;
            padding-left: 5px;
            font-size: 7.5px;
        }

        .data-table td.metric-value {
            text-align: center;
            width: 60px;
        }

        .data-table td.indent-1 {
            padding-left: 10px;
        }

        .data-table td.indent-2 {
            padding-left: 20px;
        }

        .data-table td.indent-3 {
            padding-left: 30px;
        }

        /* Section Headers */
        .section-header {
            font-weight: bold;
            background: #e0e0e0;
            padding: 6px;
            margin-top: 15px;
            border: 1px solid #000;
            font-size: 9px;
            text-transform: uppercase;
        }

        .subsection-header {
            font-weight: bold;
            background: #f5f5f5;
            padding: 4px 6px;
            font-size: 8px;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .signature-block {
            text-align: center;
            margin: 15px 0;
        }

        .signature-block .label {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 3px;
        }

        .signature-block .name {
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 3px;
            font-size: 9px;
        }

        .signature-block .title {
            font-size: 8px;
            margin-top: 2px;
        }

        /* Print Controls */
        .print-controls {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
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

        /* Page Break Handling */
        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                font-size: 8px;
            }

            .print-controls {
                display: none !important;
            }

            .report-container {
                max-width: 100%;
            }

            .signature-section {
                margin-top: 20px;
            }
        }

        @media screen {
            body {
                padding: 20px;
                background: #e0e0e0;
            }

            .report-container {
                background: white;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.2);
            }
        }

        /* Specific Column Widths */
        .col-quarterly { width: 80px; }
        .col-monthly { width: 80px; }
        .col-actual { width: 80px; }
        .col-percent { width: 80px; }
    </style>
</head>
<body>
    <!-- Print Controls (only visible on screen) -->
    <div class="print-controls">
        <button class="btn" onclick="window.print()">
            Print Report
        </button>
        <a href="{{ route('midwife.report') }}" class="btn btn-secondary">
            Back to Reports
        </a>
    </div>

    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <h1>BHW Monthly Accomplishment Report</h1>
            <div class="report-info">
                <div class="report-info-row">
                    <span>For the Month of: <strong>{{ $month ?? 'September' }}</strong></span>
                    <span>the year: <strong>{{ $year ?? '2024' }}</strong></span>
                </div>
                <div class="report-info-row">
                    <span>Barangay: <strong>{{ $barangay ?? 'Mecalong II' }}</strong></span>
                    <span>Municipality: <strong>{{ $municipality ?? 'Dumalan Hao' }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Data Tables Section -->

        <!-- 1. MATERNAL AND CHILD HEALTH -->
        <div class="section-header">1. MATERNAL AND CHILD HEALTH</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 50%;">PROGRAMS</th>
                    <th class="col-quarterly">Quarterly<br>Target<br>(AT ÷ 4)</th>
                    <th class="col-monthly">Monthly<br>Target<br>(QT ÷ 3)</th>
                    <th class="col-actual">Monthly<br>Accom.<br>(Actual No.)</th>
                    <th class="col-percent">%<br>Accom.<br>(MA ÷ MT)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="subsection-header">Prenatal Care</td>
                </tr>
                <tr>
                    <td class="metric-name">Number of pregnant women advocated for 4 prenatal visits<br>
                        <span style="font-size: 6.5px;">(at least 1 visit-first trimester 0-84days; 1 visit second trimester 85-189 days; 2 visits third trimester 190 days and more) (Total population x 2.056%)</span>
                    </td>
                    <td class="metric-value">{{ $data['prenatal']['quarterly_target'] ?? '' }}</td>
                    <td class="metric-value">{{ $data['prenatal']['monthly_target'] ?? '' }}</td>
                    <td class="metric-value">{{ $data['prenatal']['advocated'] ?? '0' }}</td>
                    <td class="metric-value">{{ $data['prenatal']['advocated_percent'] ?? '' }}</td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">Total number of pregnant tracked (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['total_tracked'] ?? '1' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">Total number of teen-age pregnant women tracked (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['teen_tracked'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">Number of teenage pregnancy 10-14 years old (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['teen_10_14'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">Number of teen age pregnancy 15-19 years old (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['teen_15_19'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">Number of pregnant women with Birth and Emergency Plan (Total population x 2.056%)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['birth_emergency_plan'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">Number of high risk pregnant women identified and referred (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['high_risk'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">Number of pregnant women identified and referred for facility based delivery (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['facility_delivery'] ?? '1' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">Number of pregnant women delivered in health facility (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['prenatal']['delivered_facility'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>

                <tr>
                    <td colspan="5" class="subsection-header">Post Partum Care</td>
                </tr>
                <tr>
                    <td class="metric-name">Number of home/follow visits of postpartum women referred for postpartum care<br>
                        <span style="font-size: 6.5px;">(Total population x 2.056%)</span>
                    </td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['postpartum']['home_visits'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
            </tbody>
        </table>

        <!-- 2. FAMILY PLANNING -->
        <div class="section-header">2. FAMILY PLANNING</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">PROGRAMS</th>
                    <th class="col-quarterly">Quarterly<br>Target<br>(AT ÷ 4)</th>
                    <th class="col-monthly">Monthly<br>Target<br>(QT ÷ 3)</th>
                    <th class="col-actual">Monthly<br>Accom.<br>(Actual No.)</th>
                    <th class="col-percent">%<br>Accom.<br>(MA ÷ MT)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="metric-name">Number of mothers/MCRA referred for FP method (Total Population x 25.854%)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['family_planning']['referred'] ?? '1' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name">Number of drop-outs followed-up (Masterlist) (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['family_planning']['dropouts'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
            </tbody>
        </table>

        <!-- 3. EXPANDED PROGRAM OF IMMUNIZATION -->
        <div class="section-header">3. EXPANDED PROGRAM OF IMMUNIZATION</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">PROGRAMS</th>
                    <th class="col-quarterly">Quarterly<br>Target<br>(AT ÷ 4)</th>
                    <th class="col-monthly">Monthly<br>Target<br>(QT ÷ 3)</th>
                    <th class="col-actual">Monthly<br>Accom.<br>(Actual No.)</th>
                    <th class="col-percent">%<br>Accom.<br>(MA ÷ MT)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="metric-name">Number of 0-12 mos. old Children followed up for immunization (Total population x 2.056%)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['immunization']['followed_up'] ?? '1' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name">Number of 0-12 months old children defaulters followed up for immunization. (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['immunization']['defaulters'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
            </tbody>
        </table>

        <!-- PAGE BREAK -->
        <div class="page-break"></div>

        <!-- 4. NUTRITION PROGRAM -->
        <div class="section-header">4. NUTRITION PROGRAM</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">PROGRAMS</th>
                    <th class="col-quarterly">Quarterly<br>Target<br>(AT ÷ 4)</th>
                    <th class="col-monthly">Monthly<br>Target<br>(QT ÷ 3)</th>
                    <th class="col-actual">Monthly<br>Accom.<br>(Actual No.)</th>
                    <th class="col-percent">%<br>Accom.<br>(MA ÷ MT)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" class="subsection-header">Growth Monitoring</td>
                </tr>
                <tr>
                    <td class="metric-name" style="font-weight: bold;">3. Operation Timbang (OPT) Jan.-March (Total Population x 10.714%)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-OPT coverage</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['coverage'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number with normal weight (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['normal'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number of underweight (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['underweight'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number of severely underweight (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['severely_underweight'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number of Stunted (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['stunted'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number of Severely Stunted (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['severely_stunted'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number of Wasted (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['wasted'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number of Severely Wasted (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['severely_wasted'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1">-Number of overweight (ACTUAL NO.)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['opt']['overweight'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>

                <tr>
                    <td class="metric-name" style="font-weight: bold;">4. Follow-up weighing</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-1" style="font-weight: bold;">a. Monthly: 0-23 months old</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Normal</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['normal'] ?? '1' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Underweight</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['underweight'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Severely underweight</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['severely_underweight'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Stunted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['stunted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Severely Stunted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['severely_stunted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Wasted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['wasted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Severely Wasted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['severely_wasted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Overweight</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_0_23']['overweight'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>

                <tr>
                    <td class="metric-name indent-1" style="font-weight: bold;">24-59 months old</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Underweight</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_24_59']['underweight'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Severely underweight</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_24_59']['severely_underweight'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Stunted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_24_59']['stunted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Severely Stunted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_24_59']['severely_stunted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Wasted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_24_59']['wasted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Severely Wasted</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['monthly_24_59']['severely_wasted'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>

                <tr>
                    <td class="metric-name indent-1" style="font-weight: bold;">b. Quarterly: 24-59 months</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Normal weight</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['quarterly_24_59']['normal'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name indent-2">-Overweight</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['quarterly_24_59']['overweight'] ?? '' }}</td>
                    <td class="metric-value"></td>
                </tr>

                <tr>
                    <td colspan="5" class="subsection-header">0-6 months old exclusively breastfeed</td>
                </tr>
                <tr>
                    <td class="metric-name">Number of 0-6 mos. old seen (Total Population x 0.171%)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['breastfeed']['seen'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name">Number of 0-6 mos. old exclusively breastfeed (Total Population x 0.171%)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['breastfeed']['exclusive'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>

                <tr>
                    <td colspan="5" class="subsection-header">Complementary feeding</td>
                </tr>
                <tr>
                    <td class="metric-name">Number of 6 mos. old started complementary feeding (Total population x 1.028)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['complementary']['started'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name">Number of Children completed the complementary feeding up 8 months<br>
                        <span style="font-size: 6.5px;">(Total population x 1.028)</span>
                    </td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['complementary']['completed'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>

                <tr>
                    <td colspan="5" class="subsection-header">Vitamin A Supplementation</td>
                </tr>
                <tr>
                    <td class="metric-name">6-11 months Given Vitamin A (Total Population x 1.028 %)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['vitamin_a']['6_11_months'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
                <tr>
                    <td class="metric-name">12-59 months given Vitamin A (Total Population x 8.658 %)</td>
                    <td class="metric-value"></td>
                    <td class="metric-value"></td>
                    <td class="metric-value">{{ $data['nutrition']['vitamin_a']['12_59_months'] ?? '0' }}</td>
                    <td class="metric-value"></td>
                </tr>
            </tbody>
        </table>

        <!-- Signature Section -->
        <div class="signature-section no-break">
            <div class="signature-grid">
                <div class="signature-block">
                    <div class="label">Prepared by:</div>
                    <div class="name">{{ $prepared_by_name ?? 'JESSA MACARAIG' }}</div>
                    <div class="title">Barangay Health Worker</div>
                    <div style="margin-top: 5px; font-size: 8px;">Signature: _______________________</div>
                </div>

                <div class="signature-block">
                    <div class="label">Reviewed by:</div>
                    <div class="name">RHM</div>
                    <div class="title">&nbsp;</div>
                </div>
            </div>

            <div class="signature-grid" style="margin-top: 20px;">
                <div class="signature-block">
                    <div class="label">Noted:</div>
                    <div class="name">{{ $noted_by_name ?? 'JANETH B. SULTAN, RM' }}</div>
                    <div class="title">Municipal BHW Coordinator</div>
                </div>

                <div class="signature-block">
                    <div class="label">Approved by:</div>
                    <div class="name">{{ $approved_by_name ?? 'PATRICK KEAN L. TOLEDO, MD' }}</div>
                    <div class="title">Municipal Health Officer</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when opened with print parameter
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
