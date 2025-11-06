<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\Immunization;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\PrenatalCheckup;

class DashboardController extends Controller
{
    public function index()
    {
        // Get current date for filtering
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
        $lastMonth = Carbon::now()->subMonth();

        // Statistics
        $stats = [
            'total_patients' => Patient::count(),
            'active_prenatal_records' => PrenatalRecord::where('is_active', true)->count(),
            'checkups_this_month' => PrenatalCheckup::whereMonth('checkup_date', $currentMonth)
                                                   ->whereYear('checkup_date', $currentYear)
                                                   ->count(),
            'total_children' => ChildRecord::count(),
            'upcoming_appointments' => PrenatalCheckup::where('checkup_date', '>', $currentDate)
                                                     ->where('checkup_date', '<=', $currentDate->copy()->addDays(7))
                                                     ->count(),
        ];

        // Calculate month-over-month changes
        $lastMonthCheckups = PrenatalCheckup::whereMonth('checkup_date', $lastMonth->month)
                                          ->whereYear('checkup_date', $lastMonth->year)
                                          ->count();
        $lastMonthPatients = Patient::whereMonth('created_at', $lastMonth->month)
                                  ->whereYear('created_at', $lastMonth->year)
                                  ->count();
        $lastMonthChildren = ChildRecord::whereMonth('created_at', $lastMonth->month)
                                       ->whereYear('created_at', $lastMonth->year)
                                       ->count();
        
        $stats['checkups_change'] = $stats['checkups_this_month'] - $lastMonthCheckups;
        $stats['patients_change'] = $lastMonthPatients;
        $stats['children_change'] = $lastMonthChildren;

        // Chart Data - Prenatal Checkups Per Month (last 12 months)
        $checkupsPerMonth = [];
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            $checkupsPerMonth[] = PrenatalCheckup::whereMonth('checkup_date', $date->month)
                                               ->whereYear('checkup_date', $date->year)
                                               ->count();
        }

        // Immunization Coverage Data
        $totalChildRecords = ChildRecord::count();
        $immunizationStats = $this->getImmunizationStats($totalChildRecords);

        // Most Used Vaccines - Using vaccine_name from immunizations table
        $vaccineData = collect();
        if ($totalChildRecords > 0) {
            $vaccineData = Immunization::select('vaccine_name', DB::raw('COUNT(*) as count'))
                                     ->whereNotNull('vaccine_name')
                                     ->where('vaccine_name', '!=', '')
                                     ->groupBy('vaccine_name')
                                     ->orderBy('count', 'desc')
                                     ->limit(6)
                                     ->get();
        }

        // Patient Registration Trends (last 12 months)
        $registrationData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $registrationData[] = Patient::whereMonth('created_at', $date->month)
                                       ->whereYear('created_at', $date->year)
                                       ->count();
        }

        // Recent Checkups (last 5)
        $recentCheckups = PrenatalCheckup::with(['patient', 'prenatalRecord'])
                                       ->orderBy('checkup_date', 'desc')
                                       ->limit(5)
                                       ->get()
                                       ->map(function ($checkup) {
                                           return [
                                               'patient_name' => $checkup->patient->name ?? 'Unknown',
                                               'checkup_date' => $checkup->checkup_date,
                                               'status' => $this->determineCheckupStatus($checkup),
                                               'midwife' => $checkup->conducted_by ?? 'Unknown'
                                           ];
                                       });

        // Upcoming Prenatal Checkups - only upcoming status for active pregnancies (exclude completed pregnancies)
        // OPTIMIZED: Add withCount to avoid N+1 query in determineAppointmentType
        $upcomingAppointments = PrenatalCheckup::with(['patient', 'prenatalRecord'])
                                             ->withCount(['prenatalRecord as previous_checkups_count' => function ($query) {
                                                 $query->whereDate('checkup_date', '<', today());
                                             }])
                                             ->where('status', 'Upcoming') // Specifically look for 'Upcoming' status
                                             ->where('checkup_date', '>=', Carbon::now()->toDateString()) // Future dates only
                                             ->whereHas('prenatalRecord', function($q) {
                                                 $q->where('is_active', 1)
                                                   ->where('status', '!=', 'completed');
                                             })
                                             ->orderBy('checkup_date', 'asc')
                                             ->orderBy('checkup_time', 'asc')
                                             ->limit(5)
                                             ->get()
                                             ->map(function ($appointment) {
                                                 return [
                                                     'patient_name' => $appointment->patient->name ?? 'Unknown',
                                                     'appointment_date' => $appointment->checkup_date,
                                                     'appointment_time' => $appointment->checkup_time,
                                                     'type' => $this->determineAppointmentType($appointment),
                                                     'status' => $appointment->status,
                                                     'gestational_weeks' => $appointment->gestational_age_weeks ?? $appointment->weeks_pregnant,
                                                     'midwife' => $appointment->conducted_by ?? 'Unassigned',
                                                     'notes' => $appointment->notes,
                                                     'next_visit_date' => $appointment->next_visit_date,
                                                     'next_visit_time' => $appointment->next_visit_time,
                                                     'formatted_checkup_id' => $appointment->formatted_checkup_id
                                                 ];
                                             });

        // Upcoming Immunizations - from actual scheduled immunizations (future dates only)
        $upcomingImmunizations = Immunization::with(['childRecord', 'vaccine'])
                                            ->whereHas('childRecord') // Only include immunizations with valid child records
                                            ->where('status', 'Upcoming')
                                            ->where('schedule_date', '>=', Carbon::now()->toDateString())
                                            ->orderBy('schedule_date', 'asc')
                                            ->limit(5)
                                            ->get()
                                            ->map(function ($immunization) {
                                                return [
                                                    'child_name' => $immunization->childRecord
                                                        ? ($immunization->childRecord->full_name ?: $immunization->childRecord->first_name . ' ' . $immunization->childRecord->last_name)
                                                        : 'Unknown Child',
                                                    'vaccine_name' => $immunization->vaccine_name ?? ($immunization->vaccine->name ?? 'Unknown Vaccine'),
                                                    'dose_number' => $immunization->dose ?? '1st Dose',
                                                    'due_date' => $immunization->schedule_date,
                                                    'schedule_time' => $immunization->schedule_time,
                                                    'status' => $immunization->status,
                                                    'notes' => $immunization->notes
                                                ];
                                            });

        // Prepare chart data arrays
        $charts = [
            'checkups' => [
                'labels' => $months,
                'data' => $checkupsPerMonth
            ],
            'immunization' => $immunizationStats,
            'vaccines' => [
                'labels' => $vaccineData->pluck('vaccine_name')->toArray(),
                'data' => $vaccineData->pluck('count')->toArray()
            ],
            'registration' => [
                'labels' => $months,
                'data' => $registrationData
            ]
        ];

        return view('midwife.dashboard', compact(
            'stats',
            'charts',
            'recentCheckups',
            'upcomingAppointments',
            'upcomingImmunizations'
        ));
    }

    public function bhwIndex()
    {
        // Get current date for filtering
        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;
        $currentYear = $currentDate->year;
        $lastMonth = Carbon::now()->subMonth();

        // BHW-specific statistics
        $totalMothers = Patient::count();
        $totalChildren = ChildRecord::count();
        $girlsCount = ChildRecord::where('gender', 'Female')->count();
        $boysCount = ChildRecord::where('gender', 'Male')->count();

        $stats = [
            'total_mothers' => $totalMothers,
            'total_children' => $totalChildren,
            'girls_count' => $girlsCount,
            'boys_count' => $boysCount,
            'girls_percentage' => $totalChildren > 0 ? round(($girlsCount / $totalChildren) * 100, 1) : 0,
            'boys_percentage' => $totalChildren > 0 ? round(($boysCount / $totalChildren) * 100, 1) : 0,
        ];

        // Calculate month-over-month changes
        $lastMonthMothers = Patient::whereMonth('created_at', $lastMonth->month)
                                 ->whereYear('created_at', $lastMonth->year)
                                 ->count();
        
        $stats['mothers_change'] = $lastMonthMothers;

        // Monthly Patient Registrations (last 12 months)
        $monthlyRegistrations = [];
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            $monthlyRegistrations[] = Patient::whereMonth('created_at', $date->month)
                                           ->whereYear('created_at', $date->year)
                                           ->count();
        }

        // Prenatal Status Distribution
        $activePrenatal = PrenatalRecord::where('is_active', true)->count();
        $completedPrenatal = PrenatalRecord::where('is_active', false)->count();
        $totalPrenatal = $activePrenatal + $completedPrenatal;

        $prenatalStats = [
            'active' => $totalPrenatal > 0 ? round(($activePrenatal / $totalPrenatal) * 100, 1) : 0,
            'completed' => $totalPrenatal > 0 ? round(($completedPrenatal / $totalPrenatal) * 100, 1) : 0,
        ];

        // Recent Patient Registrations (last 5)
        $recentRegistrations = Patient::orderBy('created_at', 'desc')
                                    ->limit(5)
                                    ->get()
                                    ->map(function ($patient) {
                                        return [
                                            'patient_name' => $patient->name,
                                            'registration_date' => $patient->created_at,
                                            'age' => $patient->age,
                                            'contact' => $patient->contact,
                                        ];
                                    });

        // Recent Child Records (last 5)
        $recentChildRecords = ChildRecord::orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get()
                                        ->map(function ($child) {
                                            return [
                                                'child_name' => $child->full_name,
                                                'date_of_birth' => $child->birthdate ? Carbon::parse($child->birthdate) : null,
                                                'gender' => $child->gender,
                                                'age' => $child->birthdate ? Carbon::parse($child->birthdate)->diffForHumans() : 'Unknown'
                                            ];
                                        });

        // Prepare chart data arrays for BHW
        $charts = [
            'prenatal' => $prenatalStats,
            'monthly_registrations' => [
                'labels' => $months,
                'data' => $monthlyRegistrations
            ]
        ];

        return view('bhw.dashboard', compact(
            'stats',
            'charts',
            'recentRegistrations',
            'recentChildRecords'
        ));
    }

    private function getImmunizationStats($totalChildRecords)
    {
        if ($totalChildRecords == 0) {
            return [
                'fully' => 0,
                'partially' => 0,
                'not' => 100
            ];
        }

        try {
            // Get child records with immunizations count
            $childrenWithImmunizations = Immunization::select('child_record_id', DB::raw('COUNT(DISTINCT vaccine_name) as vaccine_count'))
                                                    ->whereNotNull('vaccine_name')
                                                    ->where('vaccine_name', '!=', '')
                                                    ->groupBy('child_record_id')
                                                    ->get();
            
            $fullyImmunized = $childrenWithImmunizations->where('vaccine_count', '>=', 6)->count();
            $partiallyImmunized = $childrenWithImmunizations->where('vaccine_count', '>=', 1)->where('vaccine_count', '<', 6)->count();
            $notImmunized = $totalChildRecords - $childrenWithImmunizations->count();

            // Ensure percentages add up to 100
            $fullyPercent = $totalChildRecords > 0 ? round(($fullyImmunized / $totalChildRecords) * 100, 1) : 0;
            $partiallyPercent = $totalChildRecords > 0 ? round(($partiallyImmunized / $totalChildRecords) * 100, 1) : 0;
            $notPercent = $totalChildRecords > 0 ? round(($notImmunized / $totalChildRecords) * 100, 1) : 100;

            // Adjust if needed to ensure they add to 100
            $total = $fullyPercent + $partiallyPercent + $notPercent;
            if ($total != 100 && $totalChildRecords > 0) {
                $diff = 100 - $total;
                $notPercent += $diff;
            }

            return [
                'fully' => $fullyPercent,
                'partially' => $partiallyPercent,
                'not' => $notPercent,
            ];
        } catch (\Exception $e) {
            // If there's an error, return safe defaults
            return [
                'fully' => 0,
                'partially' => 0,
                'not' => 100
            ];
        }
    }

    private function determineCheckupStatus($checkup)
    {
        // Logic to determine checkup status based on your business rules
        $patient = $checkup->patient ?? null;
        
        if ($patient && $patient->age > 35) {
            return 'High Risk';
        }
        
        if ($checkup->notes && str_contains(strtolower($checkup->notes), 'follow')) {
            return 'Follow-up';
        }

        return 'Normal';
    }

    private function determineAppointmentType($appointment)
    {
        if (!$appointment->checkup_date) {
            return 'Regular checkup';
        }

        $daysDiff = Carbon::now()->diffInDays($appointment->checkup_date, false);
        
        if ($daysDiff < 0) {
            return 'Overdue checkup';
        }
        
        if ($daysDiff <= 1) {
            return 'Urgent';
        }

        // Check if it's first visit by using eager-loaded count (avoids N+1 query)
        $previousCheckups = $appointment->previous_checkups_count ??
                          PrenatalCheckup::where('prenatal_record_id', $appointment->prenatal_record_id)
                                       ->where('checkup_date', '<', $appointment->checkup_date)
                                       ->count();

        if ($previousCheckups == 0) {
            return 'First visit';
        }

        // Check appointment notes for specific types
        if ($appointment->notes && str_contains(strtolower($appointment->notes), 'vaccination')) {
            return 'Vaccination';
        }

        if ($appointment->notes && str_contains(strtolower($appointment->notes), 'follow')) {
            return 'Follow-up visit';
        }

        return 'Regular checkup';
    }

    private function determineImmunizationStatus($immunization)
    {
        if (!$immunization || !$immunization->childRecord) {
            return 'Unknown';
        }

        // Count total vaccines for this child
        $totalVaccines = Immunization::where('child_record_id', $immunization->child_record_id)
                                   ->whereNotNull('vaccine_name')
                                   ->where('vaccine_name', '!=', '')
                                   ->distinct('vaccine_name')
                                   ->count();

        if ($totalVaccines >= 6) {
            return 'Completed';
        } elseif ($totalVaccines >= 1) {
            return 'Partial';
        } else {
            return 'Not Started';
        }
    }

    private function getNextDueVaccine($child, $ageMonths)
    {
        // Standard vaccination schedule (in months)
        $vaccinationSchedule = [
            ['vaccine' => 'BCG', 'due_months' => 0, 'dose' => 1],
            ['vaccine' => 'Hepatitis B', 'due_months' => 0, 'dose' => 1],
            ['vaccine' => 'DPT', 'due_months' => 2, 'dose' => 1],
            ['vaccine' => 'OPV', 'due_months' => 2, 'dose' => 1],
            ['vaccine' => 'DPT', 'due_months' => 4, 'dose' => 2],
            ['vaccine' => 'OPV', 'due_months' => 4, 'dose' => 2],
            ['vaccine' => 'DPT', 'due_months' => 6, 'dose' => 3],
            ['vaccine' => 'OPV', 'due_months' => 6, 'dose' => 3],
            ['vaccine' => 'Measles', 'due_months' => 9, 'dose' => 1],
            ['vaccine' => 'MMR', 'due_months' => 12, 'dose' => 1],
        ];

        // Get existing immunizations for this child (status = 'Done' means completed)
        $existingVaccines = Immunization::where('child_record_id', $child->id)
                                      ->where('status', 'Done')
                                      ->pluck('vaccine_name')
                                      ->toArray();

        // Find the next due vaccine
        foreach ($vaccinationSchedule as $vaccine) {
            // Check if child is old enough for this vaccine
            if ($ageMonths >= $vaccine['due_months']) {
                // Check if this vaccine hasn't been given yet
                if (!in_array($vaccine['vaccine'], $existingVaccines)) {
                    $dueDate = Carbon::parse($child->birthdate)->addMonths($vaccine['due_months']);

                    return [
                        'vaccine' => $vaccine['vaccine'],
                        'dose' => $vaccine['dose'],
                        'due_date' => $dueDate,
                    ];
                }
            }
        }

        return null; // No pending vaccines
    }
}