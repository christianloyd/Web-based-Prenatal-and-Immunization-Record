<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Vaccine;
use App\Models\User;
// use App\Models\StockTransaction; // Removed - no longer needed
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Notifications\HealthcareNotification;

class VaccineController extends Controller
{
    public function index(Request $request)
    {
        $query = Vaccine::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        // Category filter
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        
        // Stock status filter
        if ($request->filled('stock_status')) {
            $query->byStockStatus($request->stock_status);
        }
        
        $vaccines = $query->orderBy('name')->paginate(10)->appends($request->all());
        
        // Get stats
        $stats = [
            'total' => Vaccine::count(),
            'in_stock' => Vaccine::whereRaw('current_stock > min_stock')->count(),
            'low_stock' => Vaccine::lowStock()->count(),
            'out_of_stock' => Vaccine::outOfStock()->count()
        ];
        
        // Get categories for filter dropdown
        $categories = Vaccine::distinct('category')->pluck('category')->sort();
        
        return view('midwife.vaccines.index', compact('vaccines', 'stats', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:vaccines,name',
            'category' => 'required|string|in:Routine Immunization,COVID-19,Seasonal,Travel',
            'dosage' => 'required|string|max:255',
            'dose_count' => 'required|integer|min:1|max:5',
            'expiry_date' => 'required|date|after:today',
            'storage_temp' => 'required|string|in:2-8°C,15-25°C',
            'notes' => 'nullable|string|max:1000'
        ], [
            'name.required' => 'Vaccine name is required.',
            'name.unique' => 'This vaccine name already exists.',
            'category.required' => 'Category is required.',
            'category.in' => 'Please select a valid category.',
            'dosage.required' => 'Dosage is required.',
            'dose_count.required' => 'Number of doses is required.',
            'dose_count.min' => 'Number of doses must be at least 1.',
            'dose_count.max' => 'Number of doses cannot exceed 5.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.after' => 'Expiry date must be in the future.',
            'storage_temp.required' => 'Storage temperature is required.',
            'storage_temp.in' => 'Please select a valid storage temperature.'
        ]);

        try {
            DB::beginTransaction();
            
            $vaccine = Vaccine::create([
                'name' => $request->name,
                'category' => $request->category,
                'dosage' => $request->dosage,
                'dose_count' => $request->dose_count,
                'current_stock' => $request->initial_stock ?? 0,
                'min_stock' => $request->min_stock ?? 10,
                'expiry_date' => $request->expiry_date,
                'storage_temp' => $request->storage_temp,
                'notes' => $request->notes
            ]);

            // Send notification to all healthcare workers
            $this->notifyHealthcareWorkers(
                'New Vaccine Added',
                "New vaccine '{$vaccine->name}' has been added to the system with {$vaccine->current_stock} units in stock",
                'success',
                route('midwife.vaccines.index'),
                ['vaccine_id' => $vaccine->id, 'action' => 'vaccine_created']
            );
            
            DB::commit();
            
            return redirect()->route('midwife.vaccines.index')
                           ->with('success', 'Vaccine added successfully!');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'Error adding vaccine. Please try again.');
        }
    }

    public function update(Request $request, Vaccine $vaccine)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:vaccines,name,' . $vaccine->id,
            'category' => 'required|string|in:Routine Immunization,COVID-19,Seasonal,Travel',
            'dosage' => 'required|string|max:255',
            'dose_count' => 'required|integer|min:1|max:5',
            'expiry_date' => 'required|date|after:today',
            'storage_temp' => 'required|string|in:2-8°C,15-25°C',
            'notes' => 'nullable|string|max:1000'
        ], [
            'name.required' => 'Vaccine name is required.',
            'name.unique' => 'This vaccine name already exists.',
            'category.required' => 'Category is required.',
            'category.in' => 'Please select a valid category.',
            'dosage.required' => 'Dosage is required.',
            'dose_count.required' => 'Number of doses is required.',
            'dose_count.min' => 'Number of doses must be at least 1.',
            'dose_count.max' => 'Number of doses cannot exceed 5.',
            'expiry_date.required' => 'Expiry date is required.',
            'expiry_date.after' => 'Expiry date must be in the future.',
            'storage_temp.required' => 'Storage temperature is required.',
            'storage_temp.in' => 'Please select a valid storage temperature.'
        ]);

        try {
            $vaccine->update([
                'name' => $request->name,
                'category' => $request->category,
                'dosage' => $request->dosage,
                'dose_count' => $request->dose_count,
                'expiry_date' => $request->expiry_date,
                'storage_temp' => $request->storage_temp,
                'notes' => $request->notes
            ]);
            
            return redirect()->route('midwife.vaccines.index')
                           ->with('success', 'Vaccine updated successfully!');
                           
        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'Error updating vaccine. Please try again.');
        }
    }

    public function show(Vaccine $vaccine)
    {
        $vaccine->load(['stockTransactions' => function($query) {
            $query->latest()->limit(10);
        }]);
        
        return response()->json($vaccine);
    }

    public function stockTransaction(Request $request)
    {
        $request->validate([
            'vaccine_id' => 'required|exists:vaccines,id',
            'transaction_type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255'
        ], [
            'vaccine_id.required' => 'Please select a vaccine.',
            'vaccine_id.exists' => 'Selected vaccine does not exist.',
            'transaction_type.required' => 'Please select transaction type.',
            'transaction_type.in' => 'Invalid transaction type.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'reason.required' => 'Reason is required.',
            'reason.max' => 'Reason is too long.'
        ]);

        try {
            DB::beginTransaction();
            
            $vaccine = Vaccine::findOrFail($request->vaccine_id);
            
            // Check if stock out quantity is available
            if ($request->transaction_type === 'out' && $request->quantity > $vaccine->current_stock) {
                return back()->with('error', "Insufficient stock. Available: {$vaccine->current_stock} units");
            }
            
            $vaccine->updateStock($request->quantity, $request->transaction_type, $request->reason);

            // Send notification for stock updates
            $actionType = $request->transaction_type === 'in' ? 'restocked' : 'used';
            $this->notifyHealthcareWorkers(
                'Vaccine Stock Updated',
                "Vaccine '{$vaccine->name}' has been {$actionType}. Current stock: {$vaccine->current_stock} units",
                $request->transaction_type === 'in' ? 'success' : 'warning',
                route('midwife.vaccines.index'),
                ['vaccine_id' => $vaccine->id, 'action' => 'stock_updated', 'transaction_type' => $request->transaction_type]
            );

            DB::commit();
            
            $action = $request->transaction_type === 'in' ? 'added to' : 'removed from';
            return redirect()->route('midwife.vaccines.index')
                           ->with('success', "{$request->quantity} units {$action} {$vaccine->name} successfully!");
                           
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing stock transaction. Please try again.');
        }
    }

    public function getVaccinesForStock()
    {
        $vaccines = Vaccine::select('id', 'name')
                          ->orderBy('name')
                          ->get()
                          ->map(function ($vaccine) {
                              return [
                                  'id' => $vaccine->id,
                                  'text' => $vaccine->name
                              ];
                          });
        
        return response()->json($vaccines);
    }

    /**
     * Send notification to all healthcare workers
     */
    private function notifyHealthcareWorkers($title, $message, $type = 'info', $actionUrl = null, $data = [])
    {
        // Get all healthcare workers (midwives and BHWs)
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])
            ->where('id', '!=', Auth::id()) // Exclude the current user
            ->get();

        foreach ($healthcareWorkers as $worker) {
            $worker->notify(new HealthcareNotification(
                $title,
                $message,
                $type,
                $actionUrl,
                array_merge($data, ['notified_by' => Auth::user()->name])
            ));

            // Clear notification cache for the recipient
            Cache::forget("unread_notifications_count_{$worker->id}");
            Cache::forget("recent_notifications_{$worker->id}");
        }
    }
}