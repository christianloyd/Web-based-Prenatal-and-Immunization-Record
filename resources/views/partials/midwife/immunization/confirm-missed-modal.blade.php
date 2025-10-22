<!-- Confirm Mark as Missed Modal -->
<div id="confirmMissedModal"
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
     onclick="closeConfirmMissedModal()">

    <div class="modal-content relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6"
         onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Mark as Missed
            </h2>
            <button onclick="closeConfirmMissedModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Immunization Details -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Child:</span>
                    <div id="confirm-child-name" class="text-gray-900 mt-1"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Vaccine:</span>
                    <div id="confirm-vaccine-name" class="text-gray-900 mt-1"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Dose:</span>
                    <div id="confirm-dose" class="text-gray-900 mt-1"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Scheduled:</span>
                    <div id="confirm-schedule-date" class="text-gray-900 mt-1"></div>
                </div>
            </div>
        </div>

        <!-- Confirmation Message -->
        <p class="text-gray-700 mb-6">
            Are you sure you want to mark this immunization as missed? The parent/guardian will be notified, and you'll have the option to reschedule afterwards.
        </p>

        <!-- Form -->
        <form id="confirmMissedForm" method="POST">
            @csrf
            <input type="hidden" name="reason" value="Child did not show up">

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="button"
                        onclick="closeConfirmMissedModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition-colors flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i>
                    Mark as Missed
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentMissedImmunizationId = null;
let currentMissedImmunizationData = null;

function openConfirmMissedModal(immunization) {
    currentMissedImmunizationId = immunization.id;
    currentMissedImmunizationData = immunization;

    // Populate immunization details
    document.getElementById('confirm-child-name').textContent = immunization.child_record?.full_name || 'Unknown';
    document.getElementById('confirm-vaccine-name').textContent = immunization.vaccine?.name || immunization.vaccine_name || 'Unknown';
    document.getElementById('confirm-dose').textContent = immunization.dose || 'N/A';

    const scheduleDate = new Date(immunization.schedule_date);
    document.getElementById('confirm-schedule-date').textContent = scheduleDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Set form action
    const userRole = '{{ auth()->user()->role }}';
    const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
    document.getElementById('confirmMissedForm').action = `/${userRole}/${endpoint}/${immunization.id}/mark-missed`;

    // Show modal
    document.getElementById('confirmMissedModal').classList.remove('hidden');
}

function closeConfirmMissedModal() {
    document.getElementById('confirmMissedModal').classList.add('hidden');
    currentMissedImmunizationId = null;
    currentMissedImmunizationData = null;
}

// Close on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeConfirmMissedModal();
    }
});
</script>
