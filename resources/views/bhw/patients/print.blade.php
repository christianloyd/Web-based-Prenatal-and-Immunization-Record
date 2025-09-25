<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile - {{ $patient->name ?? ($patient->first_name . ' ' . $patient->last_name) }}</title>
    <style>
        /* A4 Print Styles with 1-inch margins */
        @page {
            size: A4;
            margin: 1in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background: white;
        }

        /* Header Styles - Compact */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            margin-bottom: 5px;
        }

        .print-date {
            font-size: 9pt;
            text-align: right;
            margin-bottom: 8px;
        }

        /* Section Styles - Reduced spacing */
        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: left;
        }

        /* Patient Info Grid - More compact */
        .patient-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
        }

        .info-item {
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 110px;
            font-size: 10pt;
        }

        .info-value {
            display: inline-block;
            font-size: 10pt;
        }

        /* Table Styles - More compact */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9pt;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }

        /* Status Badge */
        .status-badge {
            padding: 2px 6px;
            border: 1px solid #000;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .status-normal { background-color: #e6ffe6; }
        .status-monitor { background-color: #fff3cd; }
        .status-high-risk { background-color: #f8d7da; }
        .status-due { background-color: #d1ecf1; }
        .status-completed { background-color: #e2e3e5; }

        /* Page Break Controls - More flexible */
        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .avoid-break-before {
            page-break-before: avoid;
        }

        /* Print-specific utilities - Reduced spacing */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mt-1 { margin-top: 5px; }
        .mb-1 { margin-bottom: 5px; }
        .mt-2 { margin-top: 8px; }
        .mb-2 { margin-bottom: 8px; }

        /* Hide elements when printing */
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Print Date -->
    <div class="print-date">
        Printed on: {{ date('F j, Y \a\t g:i A') }}
    </div>

    <!-- Header -->
    <div class="header">
        <h1>COMPREHENSIVE PATIENT PROFILE</h1>
        <h2>Healthcare Management System</h2>
        <p>Maternal and Child Health Center</p>
    </div>

    <!-- Patient Basic Information -->
    <div class="section">
        <div class="section-title">PATIENT INFORMATION</div>
        <div class="patient-info">
            <div>
                <div class="info-item">
                    <span class="info-label">Patient ID:</span>
                    <span class="info-value">{{ $patient->formatted_patient_id ?? 'P-' . str_pad($patient->id, 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">{{ $patient->name ?? ($patient->first_name . ' ' . $patient->last_name) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Age:</span>
                    <span class="info-value">{{ $patient->age ? $patient->age . ' years old' : 'Age not specified' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date of Birth:</span>
                    <span class="info-value">
                        @if($patient->date_of_birth)
                            {{ date('F j, Y', strtotime($patient->date_of_birth)) }}
                        @elseif($patient->age)
                            {{ date('F j, Y', strtotime(now()->subYears($patient->age)->format('Y-01-01'))) }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="info-label">Contact:</span>
                    <span class="info-value">{{ $patient->contact ?? 'Contact not provided' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Emergency Contact:</span>
                    <span class="info-value">{{ $patient->emergency_contact ?? 'Emergency contact not provided' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ $patient->address ?? 'Address not provided' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Occupation:</span>
                    <span class="info-value">{{ $patient->occupation ?? 'Occupation not specified' }}</span>
                </div>
            </div>
        </div>

        @if($patient->activePrenatalRecord)
        <div class="info-item">
            <span class="info-label">Current Status:</span>
            <span class="info-value">
{{ ucfirst(str_replace('-', ' ', $patient->activePrenatalRecord->status ?? 'Unknown')) }}
            </span>
        </div>
        @endif
    </div>

    <!-- Prenatal Records -->
    @if($patient->prenatalRecords && $patient->prenatalRecords->count() > 0)
    <div class="section">
        <div class="section-title">PRENATAL RECORDS HISTORY</div>
        <table>
            <thead>
                <tr>
                    <th>Record Date</th>
                    <th>LMP</th>
                    <th>EDD</th>
                    <th>Gravida</th>
                    <th>Para</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patient->prenatalRecords as $record)
                <tr>
                    <td>{{ $record->created_at ? date('M j, Y', strtotime($record->created_at)) : 'N/A' }}</td>
                    <td>{{ $record->last_menstrual_period ? date('M j, Y', strtotime($record->last_menstrual_period)) : 'N/A' }}</td>
                    <td>{{ $record->expected_due_date ? date('M j, Y', strtotime($record->expected_due_date)) : 'N/A' }}</td>
                    <td>{{ $record->gravida ?? 'N/A' }}</td>
                    <td>{{ $record->para ?? 'N/A' }}</td>
                    <td>
{{ ucfirst(str_replace('-', ' ', $record->status ?? 'Unknown')) }}
                    </td>
                    <td>{{ $record->notes ?? 'No notes' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Prenatal Checkups -->
    @if($patient->prenatalCheckups && $patient->prenatalCheckups->count() > 0)
    <div class="section avoid-break-before">
        <div class="section-title">PRENATAL CHECKUP HISTORY</div>
        <table>
            <thead>
                <tr>
                    <th>Checkup Date</th>
                    <th>Weight (kg)</th>
                    <th>Blood Pressure</th>
                    <th>Fundal Height</th>
                    <th>FHR</th>
                    <th>Next Visit</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patient->prenatalCheckups as $checkup)
                <tr>
                    <td>{{ $checkup->checkup_date ? date('M j, Y', strtotime($checkup->checkup_date)) : 'N/A' }}</td>
                    <td>{{ $checkup->weight_kg ?? $checkup->weight ?? 'Not recorded' }}</td>
                    <td>{{ $checkup->blood_pressure ?? 'N/A' }}</td>
                    <td>{{ $checkup->fundal_height_cm ?? 'N/A' }}</td>
                    <td>{{ $checkup->fetal_heart_rate ?? 'N/A' }}</td>
                    <td>{{ $checkup->next_visit_date ? date('M j, Y', strtotime($checkup->next_visit_date)) : 'N/A' }}</td>
                    <td>
{{ ucfirst($checkup->status ?? 'Scheduled') }}
                    </td>
                </tr>
                @if($checkup->notes)
                <tr>
                    <td colspan="7"><strong>Notes:</strong> {{ $checkup->notes }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Child Records -->
    @if($patient->childRecords && $patient->childRecords->count() > 0)
    <div class="section page-break">
        <div class="section-title">CHILD RECORDS</div>
        @foreach($patient->childRecords as $child)
        <div class="no-break mb-1">
            <h3 style="font-size: 11pt; margin-bottom: 5px;">{{ $child->full_name }}</h3>
            <div class="patient-info">
                <div>
                    <div class="info-item">
                        <span class="info-label">Birth Date:</span>
                        <span class="info-value">{{ $child->birthdate ? date('F j, Y', strtotime($child->birthdate)) : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Gender:</span>
                        <span class="info-value">{{ ucfirst($child->gender ?? 'N/A') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Birth Weight:</span>
                        <span class="info-value">{{ $child->birth_weight ?? 'N/A' }} kg</span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Birth Height:</span>
                        <span class="info-value">{{ $child->birth_height ?? 'N/A' }} cm</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Current Age:</span>
                        <span class="info-value">{{ $child->birthdate ? \Carbon\Carbon::parse($child->birthdate)->age : 'N/A' }} years old</span>
                    </div>
                </div>
            </div>

            <!-- Child Immunizations -->
            @if($child->immunizations && $child->immunizations->count() > 0)
            <div class="mt-1">
                <h4 style="font-size: 10pt; margin-bottom: 4px;">Immunization History</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Vaccine</th>
                            <th>Dose</th>
                            <th>Date Scheduled/Given</th>
                            <th>Next Due Date</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($child->immunizations as $immunization)
                        <tr>
                            <td>{{ $immunization->vaccine->name ?? $immunization->vaccine_name ?? 'N/A' }}</td>
                            <td>{{ $immunization->dose ?? 'N/A' }}</td>
                            <td>{{ $immunization->schedule_date ? date('M j, Y', strtotime($immunization->schedule_date)) : 'Not scheduled' }}</td>
                            <td>
                                @if($immunization->next_due_date)
                                    {{ date('M j, Y', strtotime($immunization->next_due_date)) }}
                                @elseif($immunization->status === 'Done')
                                    Completed
                                @elseif($immunization->schedule_date)
                                    @php
                                        // Calculate approximate next due date based on typical vaccine schedules
                                        $scheduleDate = Carbon\Carbon::parse($immunization->schedule_date);
                                        $vaccineGap = [
                                            'BCG' => null, // Single dose
                                            'Hepatitis B' => 30, // 1 month gap
                                            'DPT' => 30, // 1 month gap
                                            'OPV' => 30, // 1 month gap
                                            'MMR' => null, // Single dose typically
                                            'PCV' => 60, // 2 month gap
                                            'HIB' => 30, // 1 month gap
                                            'Rotavirus' => 60, // 2 month gap
                                        ];

                                        $vaccineName = $immunization->vaccine->name ?? $immunization->vaccine_name ?? 'Unknown';
                                        $daysToAdd = $vaccineGap[$vaccineName] ?? 30; // Default 1 month

                                        if ($daysToAdd) {
                                            $nextDue = $scheduleDate->addDays($daysToAdd);
                                            echo $nextDue->format('M j, Y') . ' (Est.)';
                                        } else {
                                            echo 'Single dose';
                                        }
                                    @endphp
                                @else
                                    Not determined
                                @endif
                            </td>
                            <td>{{ ucfirst($immunization->status ?? 'Upcoming') }}</td>
                            <td>{{ $immunization->notes ?? 'No notes' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <!-- Footer -->
    <div class="section" style="margin-top: 15px; border-top: 1px solid #000; padding-top: 8px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <p style="font-size: 9pt;"><strong>Prepared by:</strong></p>
                <p style="margin-top: 15px; font-size: 9pt;">_________________________</p>
                <p style="font-size: 9pt;">{{ auth()->user()->name ?? 'Healthcare Provider' }}</p>
                <p style="font-size: 9pt;">{{ ucfirst(auth()->user()->role ?? 'Healthcare Worker') }}</p>
            </div>
            <div>
                <p style="font-size: 9pt;"><strong>Date:</strong> {{ date('F j, Y') }}</p>
                <p style="margin-top: 15px; font-size: 9pt;"><strong>Signature:</strong></p>
                <p style="margin-top: 8px; font-size: 9pt;">_________________________</p>
            </div>
        </div>
        <div class="text-center mt-1">
            <p style="font-size: 8pt; color: #666;">This document contains confidential medical information and should be handled according to privacy regulations.</p>
        </div>
    </div>

    <!-- Print JavaScript -->
    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>