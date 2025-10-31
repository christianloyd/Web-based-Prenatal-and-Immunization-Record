<!-- Mark Prenatal Checkup as Missed Modal (Simple Confirmation) -->
<div id="markCheckupMissedModal"
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center p-4"
     onclick="closeMarkCheckupMissedModal(event)">

    <div class="modal-content relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6"
         onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                Mark as Missed
            </h2>
            <button onclick="closeMarkCheckupMissedModal(event)"
                    type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Checkup Details -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Patient:</span>
                    <div id="checkup-missed-patient-name" class="text-gray-900 mt-1"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Scheduled Date:</span>
                    <div id="checkup-missed-date" class="text-gray-900 mt-1"></div>
                </div>
                <div class="col-span-2">
                    <span class="font-semibold text-gray-700">Scheduled Time:</span>
                    <div id="checkup-missed-time" class="text-gray-900 mt-1"></div>
                </div>
            </div>
        </div>

        <!-- Confirmation Message -->
        <p class="text-gray-700 mb-6">
            Are you sure you want to mark this prenatal checkup as missed? The patient will be notified via SMS if contact information is available.
        </p>

        <!-- Form -->
        <form id="markCheckupMissedForm" method="POST">
            @csrf
            <input type="hidden" id="checkup-missed-id" name="checkup_id">
            <input type="hidden" name="reason" value="Patient did not show up">

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="button"
                        onclick="closeMarkCheckupMissedModal(event)"
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
let currentMissedCheckup = null;

function openMarkCheckupMissedModal(checkupId, patientName, checkupDate, checkupTime) {
    if (!checkupId) {
        console.error('No checkup ID provided');
        return;
    }

    currentMissedCheckup = { id: checkupId, patientName, checkupDate, checkupTime };

    // Populate checkup details
    document.getElementById('checkup-missed-id').value = checkupId;
    document.getElementById('checkup-missed-patient-name').textContent = patientName || 'Unknown';
    document.getElementById('checkup-missed-date').textContent = checkupDate || 'N/A';
    document.getElementById('checkup-missed-time').textContent = checkupTime || 'N/A';

    // Set form action
    const userRole = '{{ auth()->user()->role }}';
    document.getElementById('markCheckupMissedForm').action = `/${userRole}/prenatalcheckup/${checkupId}/mark-missed`;

    // Show modal with animation (matching the pattern from index.blade.php)
    const modal = document.getElementById('markCheckupMissedModal');
    modal.classList.remove('hidden');
    
    // Use requestAnimationFrame to ensure the DOM has updated before adding show class
    requestAnimationFrame(() => {
        modal.classList.add('show');
    });
    
    document.body.style.overflow = 'hidden';
}

function closeMarkCheckupMissedModal(event) {
    // Prevent closing if clicking inside modal content
    if (event && event.target !== event.currentTarget && !event.currentTarget.id) {
        return;
    }

    const modal = document.getElementById('markCheckupMissedModal');
    if (!modal) return;

    // Remove show class first to trigger animation
    modal.classList.remove('show');
    
    // Wait for animation to complete before hiding
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
    
    currentMissedCheckup = null;
}

// Close modal when pressing Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('markCheckupMissedModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeMarkCheckupMissedModal();
        }
    }
});
</script>