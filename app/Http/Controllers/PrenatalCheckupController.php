<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PrenatalCheckup;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrenatalCheckupController extends Controller
{
    /**
     * Display a listing of patients and their checkups
     */
    // index method
public function index()
{
    if (auth()->user()->role !== 'midwife') {
        abort(403, 'Unauthorized access');
    }

    $patients = Patient::withActivePrenatal()
                      ->with(['latestCheckup', 'activePrenatalRecord'])
                      ->orderBy('name')
                      ->get();

    return view('midwife.prenatalcheckup.index', compact('patients'));
}

    /**
     * Get active patients for dropdown (AJAX)
     */
    public function getActivePatients()
    {
        $activePatients = Patient::withActivePrenatal()
                                ->orderBy('name')
                                ->get(['id', 'name', 'formatted_patient_id', 'age']);

        return response()->json($activePatients);
    }

    /**
     * Store a newly created checkup
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'checkup_date' => 'required|date',
            'checkup_time' => 'required',
            'weeks_pregnant' => 'nullable|string',
            'bp_high' => 'nullable|integer|min:50|max:300',
            'bp_low' => 'nullable|integer|min:30|max:200',
            'weight' => 'nullable|numeric|min:30|max:200',
            'baby_heartbeat' => 'nullable|integer|min:100|max:200',
            'belly_size' => 'nullable|numeric|min:0|max:50',
            'baby_movement' => 'nullable|in:active,normal,less',
            'swelling' => 'nullable|array',
            'notes' => 'nullable|string',
            'next_visit_date' => 'nullable|date|after:today',
            'next_visit_time' => 'nullable',
            'next_visit_notes' => 'nullable|string',
        ]);
    
        // Calculate weeks pregnant CORRECTLY (only weeks, no days) from patient's prenatal record
        $patient = Patient::with('activePrenatalRecord')->find($request->patient_id);
        if ($patient && $patient->activePrenatalRecord) {
            $lmp = $patient->activePrenatalRecord->last_menstrual_period;
            $checkupDate = Carbon::parse($request->checkup_date);
            
            // Calculate total days between LMP and checkup date
            $totalDays = Carbon::parse($lmp)->diffInDays($checkupDate);
            
            // Convert to whole weeks only (no decimals)
            $weeks = intval($totalDays / 7);
            
            // Format as weeks only (no days)
            $validatedData['weeks_pregnant'] = $weeks == 1 ? "1 week" : "{$weeks} weeks";
        }
    
        // Handle swelling array
        if ($request->has('swelling')) {
            $validatedData['swelling'] = $request->swelling;
        }
    
        // Handle next visit scheduling
        $scheduleNext = $request->has('schedule_next') && $request->schedule_next;
        if (!$scheduleNext) {
            unset($validatedData['next_visit_date']);
            unset($validatedData['next_visit_time']);
            unset($validatedData['next_visit_notes']);
        }
    
        try {
            $checkup = PrenatalCheckup::create($validatedData);
            
            return redirect()->route('midwife.prenatalcheckup.index')
                            ->with('success', 'Prenatal checkup saved successfully!');
                            
        } catch (\Exception $e) {
            \Log::error('Error saving prenatal checkup: ' . $e->getMessage());
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Error saving checkup. Please try again.');
        }
    }
    /**
     * Display the specified checkup
     */
    public function show(PrenatalCheckup $checkup)
{
    if (auth()->user()->role !== 'midwife') {
        abort(403, 'Unauthorized access');
    }

    $checkup->load(['patient', 'patient.activePrenatalRecord']);

    $patientCheckups = PrenatalCheckup::where('patient_id', $checkup->patient_id)
                                    ->completed()
                                    ->orderBy('checkup_date', 'desc')
                                    ->get();

    return view('midwife.prenatalcheckup.show', compact('checkup', 'patientCheckups'));
}


    /**
     * Show patient details with all checkups
     */
    public function showPatient(Patient $patient)
    {
        $patient->load([
            'prenatalCheckups' => function($query) {
                $query->completed()->orderBy('checkup_date', 'desc');
            },
            'activePrenatalRecord'
        ]);

        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalcheckup.patient' 
            : 'bhw.prenatalcheckup.patient';
            
        return view($view, compact('patient'));
    }

    /**
     * Update the specified checkup
     */
    public function update(Request $request, PrenatalCheckup $checkup)
{
    $validatedData = $request->validate([
        'checkup_date' => 'required|date',
        'checkup_time' => 'required',
        'weeks_pregnant' => 'nullable|string',
        'bp_high' => 'nullable|integer|min:50|max:300',
        'bp_low' => 'nullable|integer|min:30|max:200',
        'weight' => 'nullable|numeric|min:30|max:200',
        'baby_heartbeat' => 'nullable|integer|min:100|max:200',
        'belly_size' => 'nullable|numeric|min:0|max:50',
        'baby_movement' => 'nullable|in:active,normal,less',
        'swelling' => 'nullable|array',
        'notes' => 'nullable|string',
        'next_visit_date' => 'nullable|date|after:today',
        'next_visit_time' => 'nullable',
        'next_visit_notes' => 'nullable|string',
    ]);

    // Recalculate weeks pregnant based on updated checkup date
    $patient = Patient::with('activePrenatalRecord')->find($checkup->patient_id);
    if ($patient && $patient->activePrenatalRecord) {
        $lmp = $patient->activePrenatalRecord->last_menstrual_period;
        $checkupDate = Carbon::parse($request->checkup_date);
        
        // Calculate total days between LMP and checkup date
        $totalDays = Carbon::parse($lmp)->diffInDays($checkupDate);
        
        // Convert to whole weeks only (no decimals)
        $weeks = intval($totalDays / 7);
        
        // Format as weeks only (no days)
        $validatedData['weeks_pregnant'] = $weeks == 1 ? "1 week" : "{$weeks} weeks";
    }

    if ($request->has('swelling')) {
        $validatedData['swelling'] = $request->swelling;
    }

    $checkup->update($validatedData);

    return response()->json([
        'success' => true,
        'message' => 'Checkup updated successfully!',
        'checkup' => $checkup->load('patient')
    ]);
}

    /**
     * Delete checkup
     */
    public function destroy(PrenatalCheckup $checkup)
    {
        try {
            $checkup->delete();
            
            $redirectRoute = auth()->user()->role === 'midwife' 
                ? 'midwife.prenatalcheckup.index' 
                : 'bhw.prenatalcheckup.index';
                
            return redirect()->route($redirectRoute)
                ->with('success', 'Checkup deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error deleting checkup: ' . $e->getMessage());
            
            $redirectRoute = auth()->user()->role === 'midwife' 
                ? 'midwife.prenatalcheckup.index' 
                : 'bhw.prenatalcheckup.index';
                
            return redirect()->route($redirectRoute)
                ->with('error', 'Error deleting checkup. Please try again.');
        }
    }
}