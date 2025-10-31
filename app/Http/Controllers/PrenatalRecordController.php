<?php

namespace App\Http\Controllers;

use App\Models\PrenatalRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;
use App\Services\PrenatalRecordService;
use App\Http\Requests\StorePrenatalRecordRequest;
use App\Http\Requests\UpdatePrenatalRecordRequest;

class PrenatalRecordController extends Controller
{
    protected $prenatalRecordService;

    public function __construct(PrenatalRecordService $prenatalRecordService)
    {
        $this->prenatalRecordService = $prenatalRecordService;
    }
    // Display a listing of prenatal records
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        $query = PrenatalRecord::with(['patient', 'latestCheckup'])->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $prenatalRecords = $query->paginate(20)->withQueryString();

        // Get patients for the modal dropdown
        $patients = Patient::orderBy('name')->get(['id', 'name', 'formatted_patient_id', 'age']);
        
        // Get options for dropdowns
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];

        // Return appropriate view based on user role
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.index' 
            : 'bhw.prenatalrecord.index';
            
        return view($view, compact('prenatalRecords', 'patients', 'gravida_options', 'para_options'));
    }

    // Show form to create new prenatal record
    public function create()
    {
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];
        
        // Get all patients for the dropdown
        $patients = Patient::orderBy('name')->get(['id', 'name', 'formatted_patient_id', 'age']);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.create' 
            : 'bhw.prenatalrecord.create';
            
        return view($view, compact('gravida_options', 'para_options', 'patients'));
    }

    // Store new prenatal record
    public function store(StorePrenatalRecordRequest $request)
    {
        try {
            // Create prenatal record using service
            $prenatalRecord = $this->prenatalRecordService->createPrenatalRecord($request->validated());

            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalrecord.index'
                : 'bhw.prenatalrecord.index';

            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal record created successfully!');

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    // Show a single prenatal record
    public function show($id)
    {
        $prenatalRecord = PrenatalRecord::with([
            'patient.prenatalCheckups' => function($query) {
                $query->orderBy('checkup_date', 'desc');
            },
            'patient.latestCheckup'
        ])->findOrFail($id);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.show' 
            : 'bhw.prenatalrecord.show';
            
        return view($view, compact('prenatalRecord'));
    }

    // Show form to edit prenatal record
    public function edit($id)
    {
        $prenatal = PrenatalRecord::with('patient')->findOrFail($id);
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];
        
        // Get all patients for the dropdown (in case they want to reassign)
        $patients = Patient::orderBy('name')->get(['id', 'name', 'formatted_patient_id', 'age']);
        
        $view = auth()->user()->role === 'midwife' 
            ? 'midwife.prenatalrecord.edit' 
            : 'bhw.prenatalrecord.edit';
            
        return view($view, compact('prenatal', 'gravida_options', 'para_options', 'patients'));
    }

    // Update prenatal record
    public function update(UpdatePrenatalRecordRequest $request, $id)
    {
        try {
            $prenatal = PrenatalRecord::with('patient')->findOrFail($id);

            // Update prenatal record using service
            $prenatal = $this->prenatalRecordService->updatePrenatalRecord($prenatal, $request->validated());

            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalrecord.index'
                : 'bhw.prenatalrecord.index';

            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal record updated successfully!');

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    // Delete prenatal record
    public function destroy($id)
    {
        try {
            $prenatal = PrenatalRecord::findOrFail($id);
            $prenatal->delete();

            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalrecord.index'
                : 'bhw.prenatalrecord.index';

            return redirect()->route($redirectRoute)
                ->with('success', 'Prenatal record deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Error deleting prenatal record: ' . $e->getMessage());
            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalrecord.index'
                : 'bhw.prenatalrecord.index';

            return redirect()->route($redirectRoute)
                ->with('error', 'Error deleting record. Please try again.');
        }
    }

    /**
     * Complete pregnancy - Change status to completed
     * Accessible by midwife and BHW
     * Irreversible action
     */
    public function completePregnancy($id)
    {
        // Only midwife and BHW can complete pregnancies
        if (!in_array(Auth::user()->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized. Only midwives and BHWs can complete pregnancy records.');
        }

        try {
            $prenatal = PrenatalRecord::findOrFail($id);

            // Complete pregnancy using service
            $this->prenatalRecordService->completePregnancy($prenatal);

            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalrecord.index'
                : 'bhw.prenatalrecord.index';

            return redirect()->route($redirectRoute)
                ->with('success', 'Pregnancy record completed successfully. This action cannot be reversed.');

        } catch (\Exception $e) {
            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalrecord.index'
                : 'bhw.prenatalrecord.index';

            return redirect()->route($redirectRoute)
                ->with('error', $e->getMessage());
        }
    }

    // AJAX endpoint to get record data for editing
    public function getRecordData($id)
    {
        try {
            $record = PrenatalRecord::with('patient')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $record
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found'
            ], 404);
        }
    }

    // Get patients for AJAX dropdown (keep this for Select2 search)
    public function getPatients(Request $request)
    {
        $term = $request->get('q', ''); // Select2 uses 'q' parameter
        
        $patients = Patient::where('name', 'LIKE', "%{$term}%")
                           ->orWhere('formatted_patient_id', 'LIKE', "%{$term}%")
                           ->orderBy('name')
                           ->limit(10)
                           ->get(['id', 'name', 'formatted_patient_id', 'age']);
                           
        return response()->json([
            'results' => $patients->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'text' => $patient->name . ' (' . $patient->formatted_patient_id . ') - ' . $patient->age . ' years'
                ];
            })
        ]);
    }

}