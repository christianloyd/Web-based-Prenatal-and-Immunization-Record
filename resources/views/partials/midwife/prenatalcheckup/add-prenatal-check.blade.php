
<!-- Add Checkup Modal -->
<div id="checkupModal" class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4" onclick="closeCheckupModal(event)">
    <div class="modal-content relative w-full max-w-3xl max-h-[90vh] bg-white rounded-xl shadow-2xl my-4 flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-calendar-plus mr-2 text-primary"></i>
                    Complete Today's Checkup & Schedule Next Visit
                </h2>
                <button type="button" onclick="closeCheckupModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <form id="checkupForm" action="{{ route('midwife.prenatalcheckup.store') }}" method="POST" class="p-6" onsubmit="return validateCheckupForm(event)">
            @csrf
            <!-- Hidden field for conducted_by -->
            <input type="hidden" name="conducted_by" value="{{ auth()->id() }}">

             
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Left Column -->
                <div class="space-y-4">
                    <!-- Basic Info -->
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center border-b border-gray-100 pb-2">
                            <svg class="w-5 h-5 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Search and Select Patient/Mother *
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           id="patient-search"
                                           placeholder="Type patient name or ID to search..."
                                           class="form-input pl-10 pr-10 @error('patient_id') error @enderror"
                                           autocomplete="off">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <div id="search-loading" class="hidden">
                                            <i class="fas fa-spinner fa-spin text-gray-400"></i>
                                        </div>
                                        <button type="button" id="clear-search" class="hidden text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Search Dropdown -->
                                    <div id="search-dropdown" class="search-dropdown">
                                        <!-- Results will be populated here -->
                                    </div>
                                </div>

                                <!-- Hidden input for selected patient ID -->
                                <input type="hidden" name="patient_id" id="selected-patient-id" value="{{ old('patient_id') }}">

                                @error('patient_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror

                                <!-- Selected Patient Display -->
                                <div id="selected-patient-display" class="selected-patient hidden">
                                    <div class="flex justify-between items-start">
                                        <div class="patient-info">
                                            <div class="patient-name" id="selected-patient-name"></div>
                                            <div class="patient-details" id="selected-patient-details"></div>
                                        </div>
                                        <button type="button" id="remove-selection" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>

                                 
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                                    <input type="date" name="checkup_date" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                        value="{{ date('Y-m-d') }}"
                                        min="{{ date('Y-m-d') }}"
                                        max="{{ date('Y-m-d') }}"
                                        required readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                                    <input type="time" name="checkup_time" class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                           value="{{ old('checkup_time', date('H:i')) }}" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Vital Signs -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-heartbeat mr-2 text-red-600"></i>Basic Measurements
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blood Pressure</label>
                                <div class="flex space-x-2">
                                    <input type="number" name="blood_pressure_systolic" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('blood_pressure_systolic') }}" placeholder="120" min="70" max="250">
                                    <span class="flex items-center text-gray-500">/</span>
                                    <input type="number" name="blood_pressure_diastolic" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('blood_pressure_diastolic') }}" placeholder="80" min="40" max="150">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight_kg" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('weight_kg') }}" placeholder="68.5" min="30" max="200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fetal Heart Rate (bpm)</label>
                                <input type="number" name="fetal_heart_rate" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('fetal_heart_rate') }}" placeholder="140" min="100" max="180">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fundal Height (cm)</label>
                                <input type="number" step="0.1" name="fundal_height_cm" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                       value="{{ old('fundal_height_cm') }}" placeholder="24" min="10" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Health Assessment -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-user-md mr-2 text-green-600"></i>Health Assessment
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Baby Movement</label>
                                <select name="baby_movement" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">-- Select Baby Movement --</option>
                                    <option value="active" {{ old('baby_movement') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="normal" {{ old('baby_movement') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="less" {{ old('baby_movement') == 'less' ? 'selected' : '' }}>Less Active</option>
                                    <option value="none" {{ old('baby_movement') == 'none' ? 'selected' : '' }}>No Movement</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Symptoms</label>
                                <textarea name="symptoms" rows="2" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Any symptoms reported by the patient...">{{ old('symptoms') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Clinical Notes</label>
                                <textarea name="notes" rows="3" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="Clinical observations, recommendations, and notes...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Next Visit -->
                    <div class="bg-gray-50 rounded-lg p-3">
                        <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-calendar mr-2 text-purple-600"></i>Next Visit
                        </h3>
                        <div class="flex items-center space-x-3 mb-3">
                            <input type="checkbox" id="scheduleNext" name="schedule_next" value="1" class="text-blue-600"
                                onchange="toggleNextVisit()" {{ old('schedule_next') ? 'checked' : '' }}>
                            <label for="scheduleNext" class="text-sm font-medium text-gray-700">Schedule next visit</label>
                        </div>

                        <div id="nextVisitFields" class="{{ old('schedule_next') ? '' : 'hidden' }} space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Next Visit Date</label>
                                    <input type="date"
                                           name="next_visit_date"
                                           id="next-visit-date"
                                           class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('next_visit_date') }}"
                                           min="{{ date('Y-m-d', strtotime('+8 days')) }}">
                                    <p class="text-xs text-gray-500 mt-1">Minimum 1 week gap required from today</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                                    <input type="time" name="next_visit_time" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                           value="{{ old('next_visit_time') }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reminder Notes</label>
                                <textarea name="next_visit_notes" rows="2" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"
                                          placeholder="What to prepare or remember for next visit...">{{ old('next_visit_notes') }}</textarea>
                            </div>
                        </div>

                        <div id="noNextVisitMessage" class="{{ old('schedule_next') ? 'hidden' : '' }}">
                            <div class="bg-gray-100 rounded-lg p-3 text-center">
                                <p class="text-gray-600 text-sm">No next visit will be scheduled.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </form>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 p-6 border-t bg-white rounded-b-xl">
            <button type="button" onclick="closeCheckupModal()" class="btn-hover px-6 py-2 border border-gray-300 rounded-lg text-gray-700">
                Cancel
            </button>
            <button type="submit" form="checkupForm" class="btn-hover bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-secondary transition-all duration-200" style="background-color: var(--primary);" onmouseover="this.style.backgroundColor='var(--secondary)'" onmouseout="this.style.backgroundColor='var(--primary)'">
                <i class="fas fa-save mr-2"></i>
                Save Checkup
            </button>
        </div>
    </div>
</div>

<script>
/**
 * Validate prenatal checkup form before submission
 */
function validateCheckupForm(event) {
    const patientId = document.getElementById('selected-patient-id')?.value;
    const checkupDate = document.querySelector('input[name="checkup_date"]')?.value;
    const checkupTime = document.querySelector('input[name="checkup_time"]')?.value;

    // Check required fields
    const errors = [];

    console.log('Validation Debug:', {
        patientId,
        checkupDate,
        checkupTime
    });

    if (!patientId || patientId.trim() === '') {
        errors.push('Please select a patient');
    }

    if (!checkupDate || checkupDate.trim() === '') {
        errors.push('Checkup date is required');
    }

    if (!checkupTime || checkupTime.trim() === '') {
        errors.push('Checkup time is required');
    }

    // Validate blood pressure - if one is filled, both must be filled
    const bpSystolic = document.querySelector('input[name="blood_pressure_systolic"]').value;
    const bpDiastolic = document.querySelector('input[name="blood_pressure_diastolic"]').value;

    if ((bpSystolic && !bpDiastolic) || (!bpSystolic && bpDiastolic)) {
        errors.push('Both systolic and diastolic blood pressure values are required if you enter blood pressure');
    }

    // Validate BP ranges if both are provided
    if (bpSystolic && bpDiastolic) {
        const systolic = parseInt(bpSystolic);
        const diastolic = parseInt(bpDiastolic);

        if (systolic < 70 || systolic > 250) {
            errors.push('Systolic blood pressure must be between 70 and 250 mmHg');
        }

        if (diastolic < 40 || diastolic > 150) {
            errors.push('Diastolic blood pressure must be between 40 and 150 mmHg');
        }

        if (systolic <= diastolic) {
            errors.push('Systolic blood pressure must be higher than diastolic blood pressure');
        }
    }

    // Validate weight if provided
    const weight = document.querySelector('input[name="weight_kg"]').value;
    if (weight) {
        const weightValue = parseFloat(weight);
        if (weightValue < 30 || weightValue > 200) {
            errors.push('Weight must be between 30 and 200 kg');
        }
    }

    // Validate fetal heart rate if provided
    const fetalHR = document.querySelector('input[name="fetal_heart_rate"]').value;
    if (fetalHR) {
        const fetalHRValue = parseInt(fetalHR);
        if (fetalHRValue < 100 || fetalHRValue > 180) {
            errors.push('Fetal heart rate must be between 100 and 180 bpm');
        }
    }

    // Validate fundal height if provided
    const fundalHeight = document.querySelector('input[name="fundal_height_cm"]').value;
    if (fundalHeight) {
        const fundalHeightValue = parseFloat(fundalHeight);
        if (fundalHeightValue < 10 || fundalHeightValue > 50) {
            errors.push('Fundal height must be between 10 and 50 cm');
        }
    }

    // Validate next visit if schedule_next is checked
    const scheduleNext = document.getElementById('scheduleNext');

    console.log('Schedule Next Debug:', {
        checkbox: scheduleNext,
        checked: scheduleNext?.checked
    });

    if (scheduleNext && scheduleNext.checked) {
        const nextVisitDate = document.querySelector('input[name="next_visit_date"]')?.value;
        const nextVisitTime = document.querySelector('input[name="next_visit_time"]')?.value;

        console.log('Next Visit Fields:', {
            nextVisitDate,
            nextVisitTime
        });

        if (!nextVisitDate || nextVisitDate.trim() === '') {
            errors.push('Next visit date is required when scheduling next visit');
        }

        if (!nextVisitTime || nextVisitTime.trim() === '') {
            errors.push('Next visit time is required when scheduling next visit');
        }

        // Check if next visit date is at least 8 days from today (only if date is provided)
        if (nextVisitDate && nextVisitDate.trim() !== '') {
            const nextDate = new Date(nextVisitDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const minDate = new Date(today);
            minDate.setDate(minDate.getDate() + 8);

            console.log('Date Comparison:', {
                nextDate,
                minDate,
                isValid: nextDate >= minDate
            });

            if (nextDate < minDate) {
                errors.push('Next visit date must be at least 8 days from today');
            }
        }
    }

    // If there are errors, show SweetAlert and prevent submission
    if (errors.length > 0) {
        event.preventDefault();

        console.error('Validation errors:', errors);

        let errorHtml = '<div class="text-left"><ul class="list-disc list-inside space-y-1">';
        errors.forEach(error => {
            errorHtml += `<li class="text-sm">${error}</li>`;
        });
        errorHtml += '</ul></div>';

        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: errorHtml,
            confirmButtonColor: '#D4A373',
            confirmButtonText: 'OK, I\'ll fix it',
            customClass: {
                popup: 'animate__animated animate__shakeX',
                container: 'swal2-container-high-z'
            },
            heightAuto: false
        });

        return false;
    }

    console.log('Form validation passed! Submitting...');

    // Show loading state while submitting
    const submitBtn = event.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving Checkup...';
    }

    return true;
}
</script>