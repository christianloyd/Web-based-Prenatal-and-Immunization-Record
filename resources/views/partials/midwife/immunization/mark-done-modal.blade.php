<!-- Mark as Done Confirmation Modal - COMPLETELY REVISED -->
<div id="markDoneModal"
     style="display: none; position: fixed; inset: 0; z-index: 9999; background-color: rgba(17, 24, 39, 0.5); overflow-y: auto; width: 100%; height: 100%;"
     onclick="if(event.target === this) closeMarkDoneModal()">

    <div style="display: flex; align-items: center; justify-content: center; min-height: 100%; padding: 1rem;">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md"
             onclick="event.stopPropagation()">
            <div class="text-center">
                <!-- Success Icon -->
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                </div>

                <!-- Modal Title -->
                <h3 class="text-xl font-bold text-gray-900 mb-2">Mark Immunization as Complete?</h3>

                <!-- Immunization Details -->
                <p class="text-gray-600 mb-2">
                    You are about to mark this immunization as completed:
                </p>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4 text-left">
                    <p class="text-sm text-gray-700">
                        <strong>Child:</strong> <span id="done-child-name">Loading...</span><br>
                        <strong>Vaccine:</strong> <span id="done-vaccine-name">Loading...</span><br>
                        <strong>Dose:</strong> <span id="done-dose">Loading...</span>
                    </p>
                </div>

                <!-- Confirmation Question -->
                <p class="text-gray-700 font-medium mb-6">
                    Are you sure you want to proceed?
                </p>

                <!-- Form -->
                <form id="markDoneForm" method="POST" action="">
                    @csrf
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeMarkDoneModal()"
                                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 transition-colors font-medium">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                            <i class="fas fa-check-circle mr-2"></i>Yes, Mark as Done
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Opens the Mark as Done modal - COMPLETELY REVISED
 */
function openMarkDoneModal(immunizationData) {
    console.log('Opening mark done modal:', immunizationData);

    if (!immunizationData) {
        console.error('No immunization data provided');
        alert('Error: No immunization data provided');
        return;
    }

    // Get the modal element
    const modal = document.getElementById('markDoneModal');
    if (!modal) {
        console.error('markDoneModal element not found in DOM');
        alert('Error: Modal element not found');
        return;
    }

    console.log('Mark done modal found, modal element:', modal);

    // Set immunization details in modal
    const childNameElement = document.getElementById('done-child-name');
    const vaccineNameElement = document.getElementById('done-vaccine-name');
    const doseElement = document.getElementById('done-dose');

    if (childNameElement) {
        childNameElement.textContent = immunizationData.child_record?.full_name || 'N/A';
        console.log('Set child name:', childNameElement.textContent);
    }

    if (vaccineNameElement) {
        vaccineNameElement.textContent = immunizationData.vaccine_name || immunizationData.vaccine?.name || 'N/A';
        console.log('Set vaccine name:', vaccineNameElement.textContent);
    }

    if (doseElement) {
        doseElement.textContent = immunizationData.dose || 'N/A';
        console.log('Set dose:', doseElement.textContent);
    }

    // Set form action
    const form = document.getElementById('markDoneForm');
    if (form) {
        const userRole = document.body.getAttribute('data-user-role') || 'midwife';
        const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';
        form.action = `/${userRole}/${endpoint}/${immunizationData.id}/complete`;
        console.log('Form action set to:', form.action);
    }

    // CRITICAL FIX: Show modal using inline style (highest specificity)
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    console.log('Modal should be visible now');
    console.log('Modal display style:', modal.style.display);
    console.log('Modal computed display:', window.getComputedStyle(modal).display);
}

/**
 * Closes the Mark as Done modal
 */
function closeMarkDoneModal() {
    console.log('Closing mark done modal');
    
    const modal = document.getElementById('markDoneModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
        console.log('Modal closed');
    }
}

// Escape key handler
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('markDoneModal');
        if (modal && modal.style.display === 'block') {
            console.log('Escape key pressed, closing modal');
            closeMarkDoneModal();
        }
    }
});
</script>