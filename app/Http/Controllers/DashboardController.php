<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\PrenatalCheckup;
use App\Models\Immunization;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        
        $stats['checkups_change'] = $stats['checkups_this_month'] - $lastMonthCheckups;
        $stats['patients_change'] = $lastMonthPatients;

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

        // Upcoming Appointments (next 7 days)
        $upcomingAppointments = PrenatalCheckup::with(['patient', 'prenatalRecord'])
                                             ->where('checkup_date', '>', Carbon::now())
                                             ->where('checkup_date', '<=', Carbon::now()->addDays(7))
                                             ->orderBy('checkup_date', 'asc')
                                             ->limit(5)
                                             ->get()
                                             ->map(function ($appointment) {
                                                 return [
                                                     'patient_name' => $appointment->patient->name ?? 'Unknown',
                                                     'appointment_date' => $appointment->checkup_date,
                                                     'type' => $this->determineAppointmentType($appointment),
                                                     'midwife' => $appointment->conducted_by ?? 'Unassigned'
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
            'upcomingAppointments'
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

        // Check if it's first visit by counting previous checkups
        $previousCheckups = PrenatalCheckup::where('prenatal_record_id', $appointment->prenatal_record_id)
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
}