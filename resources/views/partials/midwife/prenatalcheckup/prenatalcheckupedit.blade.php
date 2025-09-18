<!-- Edit Prenatal Checkup Modal -->
<div id="editCheckupModal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="closeEditCheckupModal(event)">
    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl my-8" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                    </svg>
                    Edit Prenatal Checkup
                </h2>
                <button type="button" onclick="closeEditCheckupModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <form id="editCheckupForm" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Patient *</label>
                                <div class="p-3 bg-gray-100 rounded-lg">
                                    <span id="edit-patient-display" class="text-sm text-gray-900">Loading...</span>
                                </div>
                                <input type="hidden" name="patient_id" id="edit-patient-id">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                                    <input type="date" name="checkup_date" id="edit-checkup-date" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                                    <input type="time" name="checkup_time" id="edit-checkup-time" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                            Medical Measurements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure</label>
                                <div class="flex space-x-2">
                                    <input type="number" name="blood_pressure_systolic" id="edit-bp-systolic" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                           placeholder="120" min="50" max="300">
                                    <span class="flex items-center text-gray-500">/</span>
                                    <input type="number" name="blood_pressure_diastolic" id="edit-bp-diastolic" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                           placeholder="80" min="30" max="200">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight_kg" id="edit-weight" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                       placeholder="68.5" min="30" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fetal Heart Rate (bpm)</label>
                                <input type="number" name="fetal_heart_rate" id="edit-fetal-heart-rate" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                       placeholder="140" min="100" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fundal Height (cm)</label>
                                <input type="number" step="0.1" name="fundal_height_cm" id="edit-fundal-height" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                       placeholder="24" min="0" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Health Assessment -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Health Assessment
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Symptoms</label>
                                <textarea name="symptoms" id="edit-symptoms" rows="3" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                          placeholder="Any symptoms reported by the patient..."></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clinical Notes</label>
                                <textarea name="notes" id="edit-notes" rows="4" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                          placeholder="Clinical observations, recommendations, and notes..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Status
                        </h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Checkup Status</label>
                            <select name="status" id="edit-status" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="upcoming">Upcoming</option>
                                <option value="done">Done</option>
                            </select>
                        </div>
                    </div>

                    <!-- Next Visit -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            Next Visit
                        </h3>
                        <div class="flex items-center space-x-3 mb-4">
                            <input type="checkbox" id="editScheduleNext" name="schedule_next" value="1" class="text-primary"
                                   onchange="toggleEditNextVisit()">
                            <label for="editScheduleNext" class="text-sm font-medium text-gray-700">Update next visit</label>
                        </div>

                        <div id="editNextVisitFields" class="hidden space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Next Visit Date</label>
                                    <input type="date" name="next_visit_date" id="edit-next-visit-date" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                    <input type="time" name="next_visit_time" id="edit-next-visit-time" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Notes</label>
                                <textarea name="next_visit_notes" id="edit-next-visit-notes" rows="2" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                          placeholder="What to prepare or remember for next visit..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                <button type="button" onclick="closeEditCheckupModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-all duration-200">
                    Cancel
                </button>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary transition-all duration-200 flex items-center" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                    <i class="fas fa-save mr-2"></i>
                    Update Checkup
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentEditCheckupId = null;
    let currentEditCheckupData = null;

    function openEditCheckupModal(checkupId) {
        currentEditCheckupId = checkupId;

        // Show modal with loading state
        const modal = document.getElementById('editCheckupModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.add('show'), 10);
        document.body.style.overflow = 'hidden';

        // Show loading state in patient display
        document.getElementById('edit-patient-display').textContent = 'Loading patient data...';

        // Fetch checkup data
        fetchCheckupData(checkupId)
            .then(data => {
                console.log('Successfully fetched checkup data for ID:', checkupId, data);
                currentEditCheckupData = data;
                populateEditModal(data);
            })
            .catch(error => {
                console.error('Error fetching checkup data:', error);
                alert('Error loading checkup details. Please try again.');
                closeEditCheckupModal();
            });
    }

    function closeEditCheckupModal() {
        const modal = document.getElementById('editCheckupModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            currentEditCheckupId = null;
            currentEditCheckupData = null;
        }, 300);
    }

    function populateEditModal(checkup) {
        console.log('Populating edit modal with data:', checkup);

        // Set form action
        const form = document.getElementById('editCheckupForm');
        form.action = `/midwife/prenatalcheckup/${checkup.id}`;

        // Basic Information - handle both direct patient and patient via prenatal record
        let patient = checkup.patient || checkup.prenatal_record?.patient;
        if (patient) {
            document.getElementById('edit-patient-display').textContent = `${patient.name || patient.first_name + ' ' + patient.last_name} (${patient.formatted_patient_id || 'N/A'})`;
        } else {
            document.getElementById('edit-patient-display').textContent = 'Unknown Patient';
        }
        document.getElementById('edit-patient-id').value = checkup.patient_id || '';

        // Parse date for input field
        if (checkup.appointment?.appointment_date) {
            document.getElementById('edit-checkup-date').value = checkup.appointment.appointment_date;
        } else if (checkup.checkup_date) {
            // Handle different date formats
            let date = checkup.checkup_date;
            if (typeof date === 'string' && date.includes('T')) {
                date = date.split('T')[0];
            }
            document.getElementById('edit-checkup-date').value = date;
        }

        // Parse time for input field
        if (checkup.appointment?.appointment_time) {
            let time = checkup.appointment.appointment_time;
            if (time.length > 5) {
                time = time.substring(0, 5); // Take only HH:MM
            }
            document.getElementById('edit-checkup-time').value = time;
        } else if (checkup.checkup_time) {
            let time = checkup.checkup_time;
            if (time.length > 5) {
                time = time.substring(0, 5);
            }
            document.getElementById('edit-checkup-time').value = time;
        }

        // Medical Measurements
        console.log('Setting medical measurements:', {
            weight: checkup.weight_kg || checkup.weight,
            bp_systolic: checkup.blood_pressure_systolic || checkup.bp_high,
            bp_diastolic: checkup.blood_pressure_diastolic || checkup.bp_low,
            fetal_heart_rate: checkup.fetal_heart_rate || checkup.baby_heartbeat,
            fundal_height: checkup.fundal_height_cm || checkup.belly_size
        });

        // Set values with null checking
        const setFieldValue = (fieldId, value) => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.value = value || '';
            } else {
                console.warn(`Field not found: ${fieldId}`);
            }
        };

        setFieldValue('edit-weight', checkup.weight_kg || checkup.weight);
        setFieldValue('edit-bp-systolic', checkup.blood_pressure_systolic || checkup.bp_high);
        setFieldValue('edit-bp-diastolic', checkup.blood_pressure_diastolic || checkup.bp_low);
        setFieldValue('edit-fetal-heart-rate', checkup.fetal_heart_rate || checkup.baby_heartbeat);
        setFieldValue('edit-fundal-height', checkup.fundal_height_cm || checkup.belly_size);

        // Health Assessment
        setFieldValue('edit-symptoms', checkup.symptoms);
        setFieldValue('edit-notes', checkup.notes);

        // Status
        setFieldValue('edit-status', checkup.status || 'pending');

        // Next Visit
        const nextVisitDate = checkup.next_visit_date;
        const nextVisitTime = checkup.next_visit_time;
        const nextVisitNotes = checkup.next_visit_notes;

        if (nextVisitDate || nextVisitTime || nextVisitNotes) {
            document.getElementById('editScheduleNext').checked = true;
            document.getElementById('editNextVisitFields').classList.remove('hidden');

            if (nextVisitDate) {
                let date = nextVisitDate;
                if (typeof date === 'string' && date.includes('T')) {
                    date = date.split('T')[0];
                }
                document.getElementById('edit-next-visit-date').value = date;
            }

            if (nextVisitTime) {
                let time = nextVisitTime;
                if (time.length > 5) {
                    time = time.substring(0, 5);
                }
                document.getElementById('edit-next-visit-time').value = time;
            }

            document.getElementById('edit-next-visit-notes').value = nextVisitNotes || '';
        }
    }

    function toggleEditNextVisit() {
        const checkbox = document.getElementById('editScheduleNext');
        const fields = document.getElementById('editNextVisitFields');

        if (checkbox.checked) {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
    }

    // Fetch checkup data function
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

    // Form submission with loading state
    document.getElementById('editCheckupForm').addEventListener('submit', function(e) {
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Updating...';
    });

    // Close modal when clicking outside - more specific handling
    document.addEventListener('click', function(e) {
        if (e.target.id === 'editCheckupModal') {
            closeEditCheckupModal();
            e.stopPropagation();
        }
    });
</script>