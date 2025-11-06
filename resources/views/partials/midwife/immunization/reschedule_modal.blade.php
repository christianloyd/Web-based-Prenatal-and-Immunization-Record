<!-- Reschedule Modal -->
<div id="rescheduleModal"
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
     onclick="closeImmunizationRescheduleModal(event)">

    <div class="modal-content relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6"
         onclick="event.stopPropagation()">

        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-calendar-plus text-blue-500 mr-2"></i>
                Reschedule Immunization
            </h2>
            <button onclick="closeImmunizationRescheduleModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Immunization Details -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Child:</span>
                    <div id="reschedule-child-name" class="text-gray-900 mt-1"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Vaccine:</span>
                    <div id="reschedule-vaccine-name" class="text-gray-900 mt-1"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Dose:</span>
                    <div id="reschedule-dose" class="text-gray-900 mt-1"></div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Original Date:</span>
                    <div id="reschedule-original-date" class="text-gray-900 mt-1"></div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="rescheduleForm" method="POST" class="space-y-4">
            @csrf

            <p class="text-sm text-gray-600 mb-4">
                Please select a new date and time for this immunization appointment.
            </p>

            <!-- New Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    New Schedule Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       id="reschedule-date"
                       name="schedule_date"
                       required
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- New Time -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Time (Optional)
                </label>
                <input type="time"
                       id="reschedule-time"
                       name="schedule_time"
                       class="form-input w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="button"
                        onclick="closeImmunizationRescheduleModal()"
                        class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center justify-center">
                    <i class="fas fa-calendar-check mr-2"></i>
                    Reschedule
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRescheduleImmunization = null;

function openImmunizationRescheduleModal(immunization) {
    console.log('Opening reschedule modal with data:', immunization);
    console.log('Child record:', immunization.child_record);
    console.log('Vaccine:', immunization.vaccine);
    console.log('Vaccine name:', immunization.vaccine_name);
    console.log('Dose:', immunization.dose);

    if (!immunization) {
        console.error('No immunization data provided');
        return;
    }

    // Set both local and global variable for compatibility
    currentRescheduleImmunization = immunization;
    window.currentRescheduleImmunization = immunization;

    // Populate immunization details
    const childNameEl = document.getElementById('reschedule-child-name');
    const vaccineNameEl = document.getElementById('reschedule-vaccine-name');
    const doseEl = document.getElementById('reschedule-dose');
    const originalDateEl = document.getElementById('reschedule-original-date');

    // Get child name - handle both nested object and direct property
    let childName = 'Unknown';
    if (immunization.child_record && immunization.child_record.full_name) {
        childName = immunization.child_record.full_name;
    }
    console.log('Setting child name to:', childName);
    if (childNameEl) childNameEl.textContent = childName;

    // Get vaccine name - handle both nested object and direct property
    let vaccineName = 'Unknown';
    if (immunization.vaccine && immunization.vaccine.name) {
        vaccineName = immunization.vaccine.name;
    } else if (immunization.vaccine_name) {
        vaccineName = immunization.vaccine_name;
    }
    console.log('Setting vaccine name to:', vaccineName);
    if (vaccineNameEl) vaccineNameEl.textContent = vaccineName;

    // Get dose
    let dose = 'N/A';
    if (immunization.dose) {
        dose = immunization.dose;
    }
    console.log('Setting dose to:', dose);
    if (doseEl) doseEl.textContent = dose;

    if (originalDateEl) {
        const scheduleDate = new Date(immunization.schedule_date);
        const formattedDate = scheduleDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        console.log('Setting original date to:', formattedDate);
        originalDateEl.textContent = formattedDate;
    }

    // Reset form
    const form = document.getElementById('rescheduleForm');
    if (form) form.reset();

    // Show modal
    const modal = document.getElementById('rescheduleModal');
    if (modal) {
        modal.classList.remove('hidden');
        requestAnimationFrame(() => modal.classList.add('show'));
        document.body.style.overflow = 'hidden';

        // Focus on date input after a small delay
        setTimeout(() => {
            const dateInput = document.getElementById('reschedule-date');
            if (dateInput) dateInput.focus();
        }, 300);
    } else {
        console.error('Reschedule modal not found');
    }
}

function closeImmunizationRescheduleModal(event) {
    if (event && event.target !== event.currentTarget && arguments.length > 0) return;

    const modal = document.getElementById('rescheduleModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentRescheduleImmunization = null;
        window.currentRescheduleImmunization = null;

        const form = document.getElementById('rescheduleForm');
        if (form) form.reset();
    }, 300);
}

// Handle reschedule form submission
document.addEventListener('DOMContentLoaded', function() {
    const rescheduleForm = document.getElementById('rescheduleForm');
    if (rescheduleForm) {
        console.log('Reschedule form found, attaching submit handler');
        rescheduleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Reschedule form submitted');
            console.log('currentRescheduleImmunization:', currentRescheduleImmunization);

            const userRole = '{{ auth()->user()->role }}';
            const endpoint = userRole === 'bhw' ? 'immunizations' : 'immunization';

            // Get immunization ID - try currentRescheduleImmunization first, then window global
            const immunizationId = currentRescheduleImmunization?.id || window.currentRescheduleImmunization?.id;

            console.log('Immunization ID:', immunizationId);

            if (!immunizationId) {
                console.error('No immunization selected for rescheduling');
                if (window.healthcareAlert) {
                    window.healthcareAlert.error('No immunization selected for rescheduling');
                } else {
                    alert('No immunization selected for rescheduling');
                }
                return;
            }

            this.action = `/${userRole}/${endpoint}/${immunizationId}/reschedule`;

            console.log('Form action URL:', this.action);
            console.log('Submitting form...');
            this.submit();
        });
    } else {
        console.error('Reschedule form not found!');
    }
});

// Close on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('rescheduleModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeImmunizationRescheduleModal();
        }
    }
});
</script>
