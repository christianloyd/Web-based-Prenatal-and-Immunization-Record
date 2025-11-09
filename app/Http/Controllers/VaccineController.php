<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vaccine;
use App\Repositories\Contracts\VaccineRepositoryInterface;
use App\Services\VaccineService;
use App\Http\Requests\StoreVaccineRequest;
use App\Http\Requests\UpdateVaccineRequest;
use App\Http\Requests\StockTransactionRequest;
use App\Utils\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VaccineController extends Controller
{
    protected $vaccineRepository;
    protected $vaccineService;

    /**
     * Constructor - Inject Vaccine Repository and Service
     */
    public function __construct(VaccineRepositoryInterface $vaccineRepository, VaccineService $vaccineService)
    {
        $this->vaccineRepository = $vaccineRepository;
        $this->vaccineService = $vaccineService;
    }

    /**
     * Check if user is authorized to access vaccine management
     */
    private function checkAuthorization()
    {
        if (!auth()->check()) {
            abort(401, 'Unauthorized access. Please login.');
        }

        if (!in_array(auth()->user()->role, ['midwife', 'admin'])) {
            abort(403, 'Forbidden. Only midwives and admins can access vaccine management.');
        }
    }
    
    public function index(Request $request)
    {
        $this->checkAuthorization();

        // Build filters array
        $filters = [];
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        if ($request->filled('category')) {
            $filters['category'] = $request->category;
        }
        if ($request->filled('stock_status')) {
            $filters['stock_status'] = $request->stock_status;
        }

        // Use repository to get paginated vaccines
        $vaccines = $this->vaccineRepository->getAllPaginated($filters, 10);

        // Get stats using service
        $stats = $this->vaccineService->getInventoryStats();

        // Get categories for filter dropdown
        $categories = $this->vaccineRepository->getCategories();

        return view('midwife.vaccines.index', compact('vaccines', 'stats', 'categories'));
    }
    
    /**
     * Show the form for creating a new vaccine
     */
    public function create()
    {
        $this->checkAuthorization();
        
        return view('midwife.vaccines.create');
    }

    public function store(StoreVaccineRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                // Validation is handled by StoreVaccineRequest
                // Create vaccine using service (handles notifications)
                $vaccine = $this->vaccineService->createVaccine($request->validated());

                if ($request->ajax()) {
                    return ResponseHelper::success($vaccine, 'Vaccine added successfully!');
                }

                return redirect()->route('midwife.vaccines.index')
                    ->with('success', 'Vaccine added successfully!');

            } catch (\Exception $e) {
                Log::error('Vaccine creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->validated()
                ]);

                if ($request->ajax()) {
                    return ResponseHelper::error($e->getMessage(), [], 500);
                }

                return back()->withInput()
                    ->with('error', 'Error adding vaccine. Please try again.');
            }
        });
    }

    public function update(UpdateVaccineRequest $request, Vaccine $vaccine)
    {
        return DB::transaction(function () use ($request, $vaccine) {
            try {
                // Validation is handled by UpdateVaccineRequest
                // Update vaccine using repository
                $updatedVaccine = $this->vaccineRepository->update($vaccine->id, $request->validated());

                if ($request->ajax()) {
                    return ResponseHelper::success($updatedVaccine, 'Vaccine updated successfully!');
                }

                return redirect()->route('midwife.vaccines.index')
                    ->with('success', 'Vaccine updated successfully!');

            } catch (\Exception $e) {
                Log::error('Vaccine update failed', [
                    'vaccine_id' => $vaccine->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->validated()
                ]);

                if ($request->ajax()) {
                    return ResponseHelper::error($e->getMessage(), [], 500);
                }

                return back()->withInput()
                    ->with('error', 'Error updating vaccine. Please try again.');
            }
        });
    }

    public function show(Vaccine $vaccine)
    {
        $this->checkAuthorization();

        // Use repository to get vaccine with relations if needed
        $vaccine = $this->vaccineRepository->find($vaccine->id);

        return response()->json($vaccine);
    }

    public function stockTransaction(StockTransactionRequest $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $validated = $request->validated();
                $transactionType = $validated['transaction_type'];

                // Use service to handle stock transaction (includes stock check and notifications)
                if ($transactionType === 'in') {
                    $this->vaccineService->addStock(
                        $validated['vaccine_id'],
                        $validated['quantity'],
                        null, // batch_number (can be extended)
                        null, // expiry_date (can be extended)
                        $validated['reason']
                    );
                    $action = 'added to';
                } else {
                    $this->vaccineService->removeStock(
                        $validated['vaccine_id'],
                        $validated['quantity'],
                        $validated['reason']
                    );
                    $action = 'removed from';
                }

                $vaccine = $this->vaccineRepository->find($validated['vaccine_id']);

                if ($request->ajax()) {
                    return ResponseHelper::success(
                        $vaccine,
                        "{$validated['quantity']} units {$action} {$vaccine->name} successfully!"
                    );
                }

                return redirect()->route('midwife.vaccines.index')
                    ->with('success', "{$validated['quantity']} units {$action} {$vaccine->name} successfully!");

            } catch (\Exception $e) {
                Log::error('Stock transaction failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'input' => $request->validated()
                ]);

                if ($request->ajax()) {
                    return ResponseHelper::error($e->getMessage(), [], 500);
                }

                return back()->with('error', $e->getMessage());
            }
        });
    }

    public function getVaccinesForStock()
    {
        $vaccines = $this->vaccineRepository->all(['id', 'name'])
            ->sortBy('name')
            ->map(function ($vaccine) {
                return [
                    'id' => $vaccine->id,
                    'text' => $vaccine->name
                ];
            })->values();

        return response()->json($vaccines);
    }
}