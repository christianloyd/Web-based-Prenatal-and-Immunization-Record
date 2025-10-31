<!-- Mark as Missed Modal -->
<div id="markMissedModal"
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeMarkMissedModal(event)">

    <div class="modal-content relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6"
         onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Mark Immunization as Missed
            </h2>
            <button onclick="closeMarkMissedModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Immunization Details -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Child:</span>
                    <span id="missed-child-name" class="text-gray-900 ml-2"></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Vaccine:</span>
                    <span id="missed-vaccine-name" class="text-gray-900 ml-2"></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Dose:</span>
                    <span id="missed-dose" class="text-gray-900 ml-2"></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Scheduled:</span>
                    <span id="missed-schedule-date" class="text-gray-900 ml-2"></span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="markMissedForm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" id="missed-immunization-id" name="immunization_id">

            <!-- Reason -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Missing <span class="text-red-500">*</span>
                </label>
                <select id="missed-reason"
                        name="reason"
                        required
                        class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">Select a reason...</option>
                    <option value="Child was sick">Child was sick</option>
                    <option value="Family didn't come">Family didn't come</option>
                    <option value="No transportation">No transportation</option>
                    <option value="Vaccine not available">Vaccine not available</option>
                    <option value="Weather conditions">Weather conditions</option>
                    <option value="Family emergency">Family emergency</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Additional Notes
                </label>
                <textarea id="missed-notes"
                          name="notes"
                          rows="3"
                          class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                          placeholder="Provide additional details..."></textarea>
            </div>

            <!-- Reschedule Option -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <label class="flex items-start cursor-pointer mb-3">
                    <input type="checkbox"
                           id="missed-reschedule-checkbox"
                           class="mt-1 h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                    <span class="ml-3 text-sm font-semibold text-gray-700">
                        Would you like to reschedule this immunization?
                    </span>
                </label>

                <!-- Reschedule Fields (hidden by default) -->
                <div id="reschedule-fields" class="hidden mt-4 space-y-3 pl-7">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Date</label>
                            <input type="date"
                                   id="missed-reschedule-date"
                                   name="reschedule_date"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="form-input w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Time</label>
                            <input type="time"
                                   id="missed-reschedule-time"
                                   name="reschedule_time"
                                   class="form-input w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <label class="flex items-start cursor-pointer">
                    <input type="checkbox"
                           id="missed-confirm-checkbox"
                           required
                           class="mt-1 h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                    <span class="ml-3 text-sm text-gray-700">
                        I confirm that this immunization was missed.
                        The parent/guardian will be notified via SMS if contact information is available.
                    </span>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button type="button"
                        onclick="closeMarkMissedModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        id="missed-submit-btn"
                        class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors flex items-center">
                    <i class="fas fa-times mr-2"></i>
                    Mark as Missed
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentMissedImmunization = null;

function openMarkMissedModal(immunization) {
    if (!immunization) {
        console.error('No immunization data provided');
        return;
    }

    currentMissedImmunization = immunization;

    // Populate immunization details
    document.getElementById('missed-immunization-id').value = immunization.id;
    document.getElementById('missed-child-name').textContent = immunization.child_record?.full_name || 'Unknown';
    document.getElementById('missed-vaccine-name').textContent = immunization.vaccine?.name || immunization.vaccine_name || 'Unknown';
    document.getElementById('missed-dose').textContent = immunization.dose || 'N/A';
    document.getElementById('missed-schedule-date').textContent = new Date(immunization.schedule_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Set form action
    const userRole = '{{ auth()->user()->role }}';
    const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
    document.getElementById('markMissedForm').action = `/${userRole}/${endpoint}/${immunization.id}/mark-missed`;

    // Reset form
    document.getElementById('markMissedForm').reset();
    document.getElementById('missed-immunization-id').value = immunization.id; // Restore after reset
    document.getElementById('missed-confirm-checkbox').checked = false;
    document.getElementById('missed-reschedule-checkbox').checked = false;
    document.getElementById('reschedule-fields').classList.add('hidden');

    // Show modal
    const modal = document.getElementById('markMissedModal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        document.getElementById('missed-reason').focus();
    }, 300);
}

function closeMarkMissedModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('markMissedModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('markMissedForm').reset();
        currentMissedImmunization = null;
    }, 300);
}

// Toggle reschedule fields
document.getElementById('missed-reschedule-checkbox').addEventListener('change', function() {
    const rescheduleFields = document.getElementById('reschedule-fields');
    const dateInput = document.getElementById('missed-reschedule-date');
    const timeInput = document.getElementById('missed-reschedule-time');

    if (this.checked) {
        rescheduleFields.classList.remove('hidden');
        dateInput.required = true;
        timeInput.required = true;
    } else {
        rescheduleFields.classList.add('hidden');
        dateInput.required = false;
        timeInput.required = false;
        dateInput.value = '';
        timeInput.value = '';
    }
});

// Handle form submission
document.getElementById('markMissedForm').addEventListener('submit', function(e) {
    if (!document.getElementById('missed-confirm-checkbox').checked) {
        e.preventDefault();
        alert('Please confirm by checking the checkbox');
        return false;
    }

    // Show loading state
    const submitBtn = document.getElementById('missed-submit-btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

    // Let the form submit normally
    return true;
});
</script>
