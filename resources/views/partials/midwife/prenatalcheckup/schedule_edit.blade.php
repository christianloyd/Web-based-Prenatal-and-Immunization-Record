<!-- Edit Schedule Modal (Simple Scheduling Only) -->
<div id="scheduleEditModal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="closeScheduleEditModal(event)">
    <div class="modal-content relative w-full max-w-lg max-h-[80vh] bg-white rounded-xl shadow-2xl my-4 flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    Edit Appointment Schedule
                </h2>
                <button type="button" onclick="closeScheduleEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <form id="scheduleEditForm" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Patient Info Display -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6z"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">Patient:</span>
                    <span id="schedule-edit-patient-display" class="ml-2 text-sm text-gray-900 font-semibold">Loading...</span>
                </div>
            </div>

            <!-- Scheduling Fields -->
            <div class="bg-gray-50 rounded-lg p-3">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-calendar mr-2 text-purple-600"></i>Schedule Details
                </h3>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Date *</label>
                        <input type="date" name="next_visit_date" id="schedule-edit-date"
                               class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                               min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Time *</label>
                        <input type="time" name="next_visit_time" id="schedule-edit-time"
                               class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="next_visit_notes" id="schedule-edit-notes" rows="2"
                                  class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                  placeholder="Reminder notes for next visit..."></textarea>
                    </div>
                </div>
            </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-4 border-t bg-white rounded-b-xl">
            <button type="button" onclick="closeScheduleEditModal()"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all duration-200">
                Cancel
            </button>
            <button type="submit" form="scheduleEditForm"
                    class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-all duration-200 flex items-center" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-save mr-2"></i>
                Update Schedule
            </button>
        </div>
    </div>
</div>

<script>
let currentScheduleEditId = null;

function openScheduleEditModal(checkupId) {
    currentScheduleEditId = checkupId;

    // Show modal with loading state
    const modal = document.getElementById('scheduleEditModal');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.add('show'), 10);
    document.body.style.overflow = 'hidden';

    // Show loading state
    document.getElementById('schedule-edit-patient-display').textContent = 'Loading...';

    // Fetch checkup data
    fetchCheckupData(checkupId)
        .then(data => {
            console.log('Fetched data for schedule edit:', data);
            populateScheduleEditModal(data);
        })
        .catch(error => {
            console.error('Error fetching checkup data:', error);
            alert('Error loading appointment details. Please try again.');
            closeScheduleEditModal();
        });
}

function closeScheduleEditModal() {
    const modal = document.getElementById('scheduleEditModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentScheduleEditId = null;
    }, 300);
}

function populateScheduleEditModal(checkup) {
    // Set form action
    const form = document.getElementById('scheduleEditForm');
    form.action = `/midwife/prenatalcheckup/${checkup.id}/schedule`;

    // Display patient info
    let patient = checkup.patient || checkup.prenatal_record?.patient;
    if (patient) {
        document.getElementById('schedule-edit-patient-display').textContent =
            `${patient.name || patient.first_name + ' ' + patient.last_name} (${patient.formatted_patient_id || 'N/A'})`;
    } else {
        document.getElementById('schedule-edit-patient-display').textContent = 'Unknown Patient';
    }

    // Populate schedule fields - prioritize next_visit_date over current appointment
    if (checkup.next_visit_date) {
        let date = checkup.next_visit_date;
        if (typeof date === 'string' && date.includes('T')) {
            date = date.split('T')[0];
        }
        document.getElementById('schedule-edit-date').value = date;
    } else if (checkup.appointment?.appointment_date) {
        document.getElementById('schedule-edit-date').value = checkup.appointment.appointment_date;
    } else if (checkup.checkup_date) {
        let date = checkup.checkup_date;
        if (typeof date === 'string' && date.includes('T')) {
            date = date.split('T')[0];
        }
        document.getElementById('schedule-edit-date').value = date;
    }

    if (checkup.next_visit_time) {
        let time = checkup.next_visit_time;
        if (time.length > 5) {
            time = time.substring(0, 5);
        }
        document.getElementById('schedule-edit-time').value = time;
    } else if (checkup.appointment?.appointment_time) {
        let time = checkup.appointment.appointment_time;
        if (time.length > 5) {
            time = time.substring(0, 5);
        }
        document.getElementById('schedule-edit-time').value = time;
    } else {
        document.getElementById('schedule-edit-time').value = '09:00'; // Default time
    }

    document.getElementById('schedule-edit-notes').value = checkup.next_visit_notes || '';
}


// Form submission with loading state
document.getElementById('scheduleEditForm').addEventListener('submit', function(e) {
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Updating...';
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('scheduleEditModal').classList.contains('hidden')) {
        closeScheduleEditModal();
    }
});

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.id === 'scheduleEditModal') {
        closeScheduleEditModal();
        e.stopPropagation();
    }
});
</script>