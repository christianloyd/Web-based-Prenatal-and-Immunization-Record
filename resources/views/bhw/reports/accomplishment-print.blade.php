<!DOCTYPE html>
<html>
<head>
    <title>BHW Monthly Accomplishment Report</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Include accomplishment report print CSS --}}
    <link rel="stylesheet" href="{{ asset('css/bhw/reports-accomplishment-print.css') }}">
</head>
<body>
    <!-- Print Controls (only visible on screen) -->
    <div class="print-controls">
        <button class="btn" onclick="window.print()">
            Print Report
        </button>
        <a href="{{ route('bhw.report') }}" class="btn btn-secondary">
            Back to Reports
        </a>
    </div>

    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <h1>BHW Monthly Accomplishment Report</h1>
            <div class="report-info">
                <div class="report-info-row">
                    <span>For the Month of: <strong>{{ $month ?? now()->format('F') }}</strong></span>
                    <span>the year: <strong>{{ $year ?? now()->format('Y') }}</strong></span>
                </div>
                <div class="report-info-row">
                    <span>Barangay: <strong>{{ $barangay ?? 'Mecolong' }}</strong></span>
                    <span>Municipality: <strong>{{ $municipality ?? 'Dumalinao' }}</strong></span>
                </div>
                <div class="report-info-row">
                    <span>Prepared by: <strong>{{ auth()->user()->name ?? 'BHW' }}</strong></span>
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
                    <div class="name">{{ $prepared_by_name ?? auth()->user()->name ?? 'BHW NAME' }}</div>
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

    {{-- Include reports print JavaScript --}}
    <script src="{{ asset('js/bhw/reports-print.js') }}"></script>
</body>
</html>
