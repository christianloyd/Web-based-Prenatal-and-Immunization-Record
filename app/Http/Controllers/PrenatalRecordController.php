<?php

namespace App\Http\Controllers;

use App\Models\PrenatalRecord;
use App\Models\Patient;
use App\Models\User;
use App\Repositories\Contracts\PrenatalRecordRepositoryInterface;
use App\Repositories\Contracts\PatientRepositoryInterface;
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

class PrenatalRecordController extends BaseController
{
    protected $prenatalRecordService;
    protected $prenatalRecordRepository;
    protected $patientRepository;

    public function __construct(
        PrenatalRecordService $prenatalRecordService,
        PrenatalRecordRepositoryInterface $prenatalRecordRepository,
        PatientRepositoryInterface $patientRepository
    ) {
        $this->prenatalRecordService = $prenatalRecordService;
        $this->prenatalRecordRepository = $prenatalRecordRepository;
        $this->patientRepository = $patientRepository;
    }
    // Display a listing of prenatal records
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['bhw', 'midwife'])) {
            abort(403, 'Unauthorized access');
        }

        // Use repository for search and filter
        $perPage = 10;

        $prenatalRecords = $this->prenatalRecordRepository->searchAndFilter(
            $request->filled('search') ? $request->search : null,
            $request->filled('status') ? $request->status : null,
            $perPage
        )->withQueryString();

        // Get patients for the modal dropdown using repository
        $patients = $this->patientRepository->all();

        // Get options for dropdowns
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];

        // Use shared view for both roles
        return view($this->roleView('prenatalrecord.index'), compact('prenatalRecords', 'patients', 'gravida_options', 'para_options'));
    }

    // Show form to create new prenatal record
    public function create()
    {
        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];

        // Get all patients for the dropdown using repository
        $patients = $this->patientRepository->all();

        return view($this->roleView('prenatalrecord.create'), compact('gravida_options', 'para_options', 'patients'));
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
        $prenatalRecord = $this->prenatalRecordRepository->findWithRelations($id, [
            'patient.prenatalCheckups' => function($query) {
                $query->orderBy('checkup_date', 'desc');
            },
            'patient.latestCheckup'
        ]);

        if (!$prenatalRecord) {
            abort(404, 'Prenatal record not found');
        }

        return view($this->roleView('prenatalrecord.show'), compact('prenatalRecord'));
    }

    // Show form to edit prenatal record
    public function edit($id)
    {
        $prenatal = $this->prenatalRecordRepository->findWithRelations($id, ['patient']);

        if (!$prenatal) {
            abort(404, 'Prenatal record not found');
        }

        $gravida_options = [1 => 'G1', 2 => 'G2', 3 => 'G3', 4 => 'G4', 5 => 'G5+'];
        $para_options = [0 => 'P0', 1 => 'P1', 2 => 'P2', 3 => 'P3', 4 => 'P4+'];

        // Get all patients for the dropdown (in case they want to reassign) using repository
        $patients = $this->patientRepository->all();

        return view($this->roleView('prenatalrecord.edit'), compact('prenatal', 'gravida_options', 'para_options', 'patients'));
    }

    // Update prenatal record
    public function update(UpdatePrenatalRecordRequest $request, $id)
    {
        try {
            $prenatal = $this->prenatalRecordRepository->findWithRelations($id, ['patient']);

            if (!$prenatal) {
                abort(404, 'Prenatal record not found');
            }

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
            $prenatal = $this->prenatalRecordRepository->find($id);

            if (!$prenatal) {
                abort(404, 'Prenatal record not found');
            }

            $this->prenatalRecordRepository->delete($id);

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
    public function completePregnancy(Request $request, $id)
    {
        // Only midwife and BHW can complete pregnancies
        if (!in_array(Auth::user()->role, ['midwife', 'bhw'])) {
            abort(403, 'Unauthorized. Only midwives and BHWs can complete pregnancy records.');
        }

        try {
            $prenatal = $this->prenatalRecordRepository->find($id);

            if (!$prenatal) {
                abort(404, 'Prenatal record not found');
            }

            // Complete pregnancy using service
            $this->prenatalRecordService->completePregnancy($prenatal);

            // Return JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pregnancy record completed successfully. This action cannot be reversed.'
                ]);
            }

            $redirectRoute = Auth::user()->role === 'midwife'
                ? 'midwife.prenatalrecord.index'
                : 'bhw.prenatalrecord.index';

            return redirect()->route($redirectRoute)
                ->with('success', 'Pregnancy record completed successfully. This action cannot be reversed.');

        } catch (\Exception $e) {
            // Return JSON error for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

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
            $record = $this->prenatalRecordRepository->findWithRelations($id, ['patient']);

            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }

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

        // Use repository to search patients with filters and limit
        $patients = $this->patientRepository->searchWithFilters($term, [], 10);

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