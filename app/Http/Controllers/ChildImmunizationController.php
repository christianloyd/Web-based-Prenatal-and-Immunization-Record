<?php

namespace App\Http\Controllers;

use App\Models\ChildRecord;
use App\Models\ChildImmunization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChildImmunizationController extends Controller
{
    /**
     * Store a newly created immunization record.
     */
    public function store(Request $request, ChildRecord $childRecord)
    {
        // Check authorization
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }
            abort(401, 'Authentication required');
        }

        $user = Auth::user();
        
        // Authorize roles
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        // Validate the request
        $validated = $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'vaccine_description' => 'nullable|string|max:1000',
            'vaccination_date' => 'required|date|before_or_equal:today',
            'administered_by' => 'required|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'next_due_date' => 'nullable|string|max:100',
        ], [
            'vaccine_name.required' => 'Vaccine name is required.',
            'vaccination_date.required' => 'Vaccination date is required.',
            'vaccination_date.before_or_equal' => 'Vaccination date cannot be in the future.',
            'administered_by.required' => 'Administered by field is required.',
        ]);

        try {
            // Create the immunization record
            $immunization = ChildImmunization::create([
                'child_record_id' => $childRecord->id,
                'vaccine_name' => $validated['vaccine_name'],
                'vaccine_description' => $validated['vaccine_description'],
                'vaccination_date' => $validated['vaccination_date'],
                'administered_by' => $validated['administered_by'],
                'batch_number' => $validated['batch_number'],
                'notes' => $validated['notes'],
                'next_due_date' => $validated['next_due_date'],
            ]);

            // Send SMS notification to parent/guardian about completed vaccination
            $child = ChildRecord::with('mother')->find($childRecord->id);
            if ($child && $child->mother) {
                $mother = $child->mother;
                $contactNumber = $mother->contact;

                if ($contactNumber) {
                    try {
                        $formattedDate = Carbon::parse($validated['vaccination_date'])->format('M j, Y');
                        $childName = $child->full_name;
                        $vaccineName = $validated['vaccine_name'];
                        $motherName = $mother->name;
                        $senderName = config('services.iprog.sender_name');

                        // Build confirmation message
                        $message = "Hi {$motherName}, your child {$childName}'s {$vaccineName} vaccination completed on {$formattedDate}.";

                        // Add next due date if available
                        if (!empty($validated['next_due_date'])) {
                            $message .= " Next dose due: {$validated['next_due_date']}.";
                        }

                        $message .= " Thank you!";

                        // Only add sender name if it fits within 160 chars
                        if (strlen($message . " - " . $senderName) <= 160) {
                            $message .= " - {$senderName}";
                        }

                        // Dispatch SMS job to background queue
                        SendSmsJob::dispatch(
                            $contactNumber,
                            $message,
                            'immunization_completed',
                            $motherName,
                            'ChildImmunization',
                            $immunization->id
                        );

                        Log::info('SMS job dispatched for child immunization', [
                            'child_id' => $child->id,
                            'immunization_id' => $immunization->id,
                            'phone' => $contactNumber,
                            'message_length' => strlen($message)
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to dispatch SMS job for child immunization: ' . $e->getMessage());
                        // Don't fail the immunization creation if SMS dispatch fails
                    }
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Immunization record added successfully!',
                    'data' => $immunization
                ], 201);
            }

            $redirectRoute = $user->role === 'bhw' 
                ? 'bhw.childrecord.show' 
                : 'midwife.childrecord.show';

            return redirect()->route($redirectRoute, $childRecord->id)
                           ->with('success', 'Immunization record added successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating immunization record: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error adding immunization record. Please try again.'
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error adding immunization record. Please try again.']);
        }
    }

    /**
     * Update the specified immunization record.
     */
    public function update(Request $request, ChildRecord $childRecord, ChildImmunization $immunization)
    {
        // Check authorization
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }
            abort(401, 'Authentication required');
        }

        $user = Auth::user();
        
        // Authorize roles
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        // Ensure the immunization belongs to the child record
        if ($immunization->child_record_id !== $childRecord->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Immunization record not found for this child.'
                ], 404);
            }
            abort(404, 'Immunization record not found for this child.');
        }

        // Validate the request
        $validated = $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'vaccine_description' => 'nullable|string|max:1000',
            'vaccination_date' => 'required|date|before_or_equal:today',
            'administered_by' => 'required|string|max:255',
            'batch_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'next_due_date' => 'nullable|string|max:100',
        ]);

        try {
            $immunization->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Immunization record updated successfully!',
                    'data' => $immunization->fresh()
                ]);
            }

            $redirectRoute = $user->role === 'bhw' 
                ? 'bhw.childrecord.show' 
                : 'midwife.childrecord.show';

            return redirect()->route($redirectRoute, $childRecord->id)
                           ->with('success', 'Immunization record updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error updating immunization record: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating immunization record. Please try again.'
                ], 500);
            }

            return back()->withInput()->withErrors(['error' => 'Error updating immunization record. Please try again.']);
        }
    }

    /**
     * Remove the specified immunization record.
     */
    public function destroy(Request $request, ChildRecord $childRecord, ChildImmunization $immunization)
    {
        // Check authorization
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.'
                ], 401);
            }
            abort(401, 'Authentication required');
        }

        $user = Auth::user();
        
        // Authorize roles
        if (!in_array($user->role, ['midwife', 'bhw'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        }

        // Ensure the immunization belongs to the child record
        if ($immunization->child_record_id !== $childRecord->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Immunization record not found for this child.'
                ], 404);
            }
            abort(404, 'Immunization record not found for this child.');
        }

        try {
            $vaccineName = $immunization->vaccine_name;
            $immunization->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Immunization record for {$vaccineName} has been deleted successfully!"
                ]);
            }

            $redirectRoute = $user->role === 'bhw' 
                ? 'bhw.childrecord.show' 
                : 'midwife.childrecord.show';

            return redirect()->route($redirectRoute, $childRecord->id)
                           ->with('success', "Immunization record for {$vaccineName} has been deleted successfully!");

        } catch (\Exception $e) {
            \Log::error('Error deleting immunization record: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting immunization record. Please try again.'
                ], 500);
            }

            $redirectRoute = $user->role === 'bhw' 
                ? 'bhw.childrecord.show' 
                : 'midwife.childrecord.show';

            return redirect()->route($redirectRoute, $childRecord->id)
                           ->withErrors(['error' => 'Error deleting immunization record. Please try again.']);
        }
    }
}