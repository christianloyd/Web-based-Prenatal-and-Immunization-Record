<!-- View Prenatal Checkup Modal -->
<style>
    /* Status Badge Styles */
    .status-done {
        background-color: #10b981;
        color: white;
    }

    .status-upcoming {
        background-color: #dbeafe;
        color: #1d4ed8;
    }
</style>

<div id="viewCheckupModal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
     role="dialog" aria-modal="true" onclick="closeViewCheckupModal(event)">

    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl my-8" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <svg class="w-6 h-6 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"/>
                    </svg>
                    Prenatal Checkup Details
                </h3>
                <button type="button" onclick="closeViewCheckupModal()"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-6">

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Left Column - Patient & Checkup Info -->
            <div class="space-y-6">

                <!-- Basic Information -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-primary mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        Checkup Information
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Checkup ID</label>
                            <p class="text-gray-900 font-medium" id="view-checkup-id">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Patient Name</label>
                            <p class="text-gray-900 font-medium" id="view-patient-name">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Patient ID</label>
                            <p class="text-gray-900 font-medium" id="view-patient-id">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Date</label>
                            <p class="text-gray-900 font-medium" id="view-checkup-date">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Time</label>
                            <p class="text-gray-900 font-medium" id="view-checkup-time">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Status</label>
                            <p class="text-gray-900 font-medium" id="view-status">-</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-gray-600">Conducted By</label>
                            <p class="text-gray-900 font-medium" id="view-conducted-by">-</p>
                        </div>
                    </div>
                </div>

                <!-- Medical Measurements -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        Medical Measurements
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Weight</label>
                            <p class="text-gray-900 font-medium" id="view-weight">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Blood Pressure</label>
                            <p class="text-gray-900 font-medium" id="view-blood-pressure">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Fetal Heart Rate</label>
                            <p class="text-gray-900 font-medium" id="view-fetal-heart-rate">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Fundal Height</label>
                            <p class="text-gray-900 font-medium" id="view-fundal-height">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Medical Info -->
            <div class="space-y-6">

                <!-- Health Assessment -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Health Assessment
                    </h4>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Symptoms</label>
                            <p class="text-gray-900 leading-relaxed bg-gray-50 p-3 rounded-md" id="view-symptoms">No symptoms reported</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Clinical Notes</label>
                            <p class="text-gray-900 leading-relaxed bg-gray-50 p-3 rounded-md" id="view-notes">No notes recorded</p>
                        </div>
                    </div>
                </div>

                <!-- Appointment Information -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200" id="view-appointment-info" style="display: none;">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        Appointment Details
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Appointment ID</label>
                            <p class="text-gray-900 font-medium" id="view-appointment-id">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Type</label>
                            <p class="text-gray-900 font-medium" id="view-appointment-type">-</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-gray-600">Status</label>
                            <p class="text-gray-900 font-medium" id="view-appointment-status">-</p>
                        </div>
                    </div>
                </div>

                <!-- Next Appointment -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200" id="view-next-appointment" style="display: none;">
                    <h4 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                        <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        Next Appointment
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Next Visit Date</label>
                            <p class="text-gray-900 font-medium" id="view-next-visit-date">-</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Next Visit Time</label>
                            <p class="text-gray-900 font-medium" id="view-next-visit-time">-</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-gray-600">Reminder Notes</label>
                            <p class="text-gray-900 leading-relaxed bg-gray-50 p-3 rounded-md" id="view-next-visit-notes">No reminder notes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>

        <!-- Footer -->
        <div class="flex flex-col sm:flex-row justify-between space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t mt-6 px-6 pb-6">
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <button type="button" onclick="editCheckupFromView()"
                        class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-all duration-200 inline-flex items-center justify-center"
                        id="edit-from-view-btn" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Checkup
                </button>
                <button type="button" onclick="viewPrenatalRecord()"
                        class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-all duration-200 inline-flex items-center justify-center"
                        id="view-prenatal-record-btn" style="display: none; background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                    <i class="fas fa-file-medical mr-2"></i>
                    View Prenatal Record
                </button>
            </div>
            <button type="button" onclick="closeViewCheckupModal()"
                    class="px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    let currentViewCheckupId = null;
    let currentViewCheckupData = null;

    function openViewCheckupModal(checkupId) {
        currentViewCheckupId = checkupId;

        // Fetch checkup data
        fetchCheckupData(checkupId)
            .then(data => {
                currentViewCheckupData = data;
                populateViewModal(data);

                const modal = document.getElementById('viewCheckupModal');
                modal.classList.remove('hidden');
                setTimeout(() => modal.classList.add('show'), 10);
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                console.error('Error fetching checkup data:', error);
                alert('Error loading checkup details. Please try again.');
            });
    }

    function closeViewCheckupModal() {
        const modal = document.getElementById('viewCheckupModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            currentViewCheckupId = null;
            currentViewCheckupData = null;
        }, 300);
    }

    function populateViewModal(checkup) {
        // Basic Information
        document.getElementById('view-checkup-id').textContent = checkup.formatted_checkup_id || `PC-${String(checkup.id).padStart(3, '0')}`;
        document.getElementById('view-patient-name').textContent = checkup.patient?.name || '-';
        document.getElementById('view-patient-id').textContent = checkup.patient?.formatted_patient_id || '-';
        document.getElementById('view-checkup-date').textContent = checkup.formatted_checkup_date || '-';
        document.getElementById('view-checkup-time').textContent = checkup.formatted_checkup_time || '-';

        // Status badge
        const statusElement = document.getElementById('view-status');
        const statusIcon = checkup.status === 'done' ? 'fa-check' : 'fa-clock';
        statusElement.innerHTML = `<span class="px-2 py-1 rounded-full text-xs font-semibold status-${checkup.status}"><i class="fas ${statusIcon} mr-1"></i>${checkup.status_text || checkup.status}</span>`;

        document.getElementById('view-conducted-by').textContent = checkup.conducted_by?.name || '-';

        // Medical Measurements
        document.getElementById('view-weight').textContent = checkup.weight_kg ? `${checkup.weight_kg} kg` : '-';

        // Blood pressure
        let bpText = '-';
        if (checkup.blood_pressure_systolic && checkup.blood_pressure_diastolic) {
            bpText = `${checkup.blood_pressure_systolic}/${checkup.blood_pressure_diastolic} mmHg`;
        } else if (checkup.bp_high && checkup.bp_low) {
            bpText = `${checkup.bp_high}/${checkup.bp_low} mmHg`;
        }
        document.getElementById('view-blood-pressure').textContent = bpText;

        document.getElementById('view-fetal-heart-rate').textContent = (checkup.fetal_heart_rate || checkup.baby_heartbeat) ? `${checkup.fetal_heart_rate || checkup.baby_heartbeat} bpm` : '-';
        document.getElementById('view-fundal-height').textContent = (checkup.fundal_height_cm || checkup.belly_size) ? `${checkup.fundal_height_cm || checkup.belly_size} cm` : '-';

        // Health Assessment
        document.getElementById('view-symptoms').textContent = checkup.symptoms || 'No symptoms reported';
        document.getElementById('view-notes').textContent = checkup.notes || 'No notes recorded';

        // Appointment Information
        if (checkup.appointment) {
            document.getElementById('view-appointment-info').style.display = 'block';
            document.getElementById('view-appointment-id').textContent = checkup.appointment.formatted_appointment_id || '-';
            document.getElementById('view-appointment-type').textContent = checkup.appointment.type_text || checkup.appointment.type || '-';

            const appointmentStatusElement = document.getElementById('view-appointment-status');
            appointmentStatusElement.innerHTML = `<span class="px-2 py-1 rounded-full text-xs font-semibold status-${checkup.appointment.status}">${checkup.appointment.status_text || checkup.appointment.status}</span>`;
        }

        // Next Appointment
        if (checkup.next_visit_date) {
            document.getElementById('view-next-appointment').style.display = 'block';
            document.getElementById('view-next-visit-date').textContent = checkup.formatted_next_visit_date || checkup.next_visit_date || '-';
            document.getElementById('view-next-visit-time').textContent = checkup.formatted_next_visit_time || checkup.next_visit_time || '-';
            document.getElementById('view-next-visit-notes').textContent = checkup.next_visit_notes || 'No reminder notes';
        }

        // Show/hide buttons based on status
        const editBtn = document.getElementById('edit-from-view-btn');
        const prenatalRecordBtn = document.getElementById('view-prenatal-record-btn');

        if (checkup.status === 'completed') {
            editBtn.style.display = 'none';
        } else {
            editBtn.style.display = 'inline-flex';
        }

        if (checkup.prenatal_record_id) {
            prenatalRecordBtn.style.display = 'inline-flex';
            prenatalRecordBtn.href = `/midwife/prenatalrecord/${checkup.prenatal_record_id}`;
        }
    }

    function editCheckupFromView() {
        closeViewCheckupModal();
        setTimeout(() => {
            if (currentViewCheckupId) {
                openEditCheckupModal(currentViewCheckupId);
            }
        }, 300);
    }

    function viewPrenatalRecord() {
        if (currentViewCheckupData && currentViewCheckupData.prenatal_record_id) {
            window.open(`/midwife/prenatalrecord/${currentViewCheckupData.prenatal_record_id}`, '_blank');
        }
    }

    async function fetchCheckupData(checkupId) {
        const response = await fetch(`/midwife/prenatalcheckup/${checkupId}/data`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (!response.ok) {
            throw new Error('Failed to fetch checkup data');
        }
        return await response.json();
    }

    // Close modal when clicking outside
    function closeViewCheckupModal(event) {
        if (event && event.target !== event.currentTarget) {
            return;
        }

        const modal = document.getElementById('viewCheckupModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            currentViewCheckupId = null;
            currentViewCheckupData = null;
        }, 300);
    }
</script>