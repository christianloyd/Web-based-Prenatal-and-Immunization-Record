<!-- Mark as Done Modal -->
<div id="markDoneModal"
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeMarkDoneModal(event)">

    <div class="modal-content relative w-full max-w-2xl bg-white rounded-xl shadow-2xl p-6"
         onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                Mark Immunization as Completed
            </h2>
            <button onclick="closeMarkDoneModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Immunization Details -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Child:</span>
                    <span id="done-child-name" class="text-gray-900 ml-2"></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Vaccine:</span>
                    <span id="done-vaccine-name" class="text-gray-900 ml-2"></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Dose:</span>
                    <span id="done-dose" class="text-gray-900 ml-2"></span>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Scheduled:</span>
                    <span id="done-schedule-date" class="text-gray-900 ml-2"></span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="markDoneForm" class="space-y-4">
            <input type="hidden" id="done-immunization-id" name="immunization_id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Batch Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Batch Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="done-batch-number"
                           name="batch_number"
                           required
                           class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Enter vaccine batch number">
                </div>

                <!-- Administered By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Administered By <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="done-administered-by"
                           name="administered_by"
                           required
                           value="{{ auth()->user()->name }}"
                           class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Healthcare worker name">
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notes
                </label>
                <textarea id="done-notes"
                          name="notes"
                          rows="3"
                          class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          placeholder="Any observations or additional notes..."></textarea>
            </div>

            <!-- Confirmation Checkbox -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <label class="flex items-start cursor-pointer">
                    <input type="checkbox"
                           id="done-confirm-checkbox"
                           required
                           class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="ml-3 text-sm text-gray-700">
                        I confirm that this immunization has been successfully administered.
                        This action will update the child's immunization record and cannot be easily undone.
                    </span>
                </label>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button type="button"
                        onclick="closeMarkDoneModal()"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        id="done-submit-btn"
                        class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors flex items-center">
                    <i class="fas fa-check mr-2"></i>
                    Confirm Completion
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentDoneImmunization = null;

function openMarkDoneModal(immunization) {
    if (!immunization) {
        console.error('No immunization data provided');
        return;
    }

    currentDoneImmunization = immunization;

    // Populate immunization details
    document.getElementById('done-immunization-id').value = immunization.id;
    document.getElementById('done-child-name').textContent = immunization.child_record?.full_name || 'Unknown';
    document.getElementById('done-vaccine-name').textContent = immunization.vaccine?.name || immunization.vaccine_name || 'Unknown';
    document.getElementById('done-dose').textContent = immunization.dose || 'N/A';
    document.getElementById('done-schedule-date').textContent = new Date(immunization.schedule_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Reset form
    document.getElementById('markDoneForm').reset();
    document.getElementById('done-administered-by').value = '{{ auth()->user()->name }}';
    document.getElementById('done-confirm-checkbox').checked = false;

    // Show modal
    const modal = document.getElementById('markDoneModal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        document.getElementById('done-batch-number').focus();
    }, 300);
}

function closeMarkDoneModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('markDoneModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        document.getElementById('markDoneForm').reset();
        currentDoneImmunization = null;
    }, 300);
}

// Handle form submission
document.getElementById('markDoneForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('done-submit-btn');
    const originalBtnText = submitBtn.innerHTML;

    if (!document.getElementById('done-confirm-checkbox').checked) {
        alert('Please confirm by checking the checkbox');
        return;
    }

    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

        const formData = {
            immunization_id: document.getElementById('done-immunization-id').value,
            batch_number: document.getElementById('done-batch-number').value,
            administered_by: document.getElementById('done-administered-by').value,
            notes: document.getElementById('done-notes').value,
            status: 'Done'
        };

        const userRole = '{{ auth()->user()->role }}';
        const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
        const response = await fetch(`/${userRole}/${endpoint}/quick-update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            // Show success message
            if (window.healthcareAlert) {
                window.healthcareAlert.success(result.message || 'Immunization marked as completed successfully!');
            }

            // Close modal and reload page
            closeMarkDoneModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(result.message || 'Failed to update status');
        }

    } catch (error) {
        console.error('Error:', error);
        if (window.healthcareAlert) {
            window.healthcareAlert.error(error.message || 'Failed to mark as completed. Please try again.');
        } else {
            alert('Error: ' + error.message);
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});
</script>
