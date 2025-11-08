/**
 * Child Record Create Form JavaScript
 * Handles mother selection, form validation, and form interactions
 */

// Show mother form based on selection
function showMotherForm(hasExisting) {
    document.getElementById('motherConfirmationStep').classList.add('hidden');
    document.getElementById('childRecordFormContainer').classList.remove('hidden');
    document.getElementById('motherExists').value = hasExisting ? 'yes' : 'no';

    // Update step indicator (if exists)
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    if (step1 && step2) {
        step1.classList.remove('active');
        step2.classList.add('active');
    }

    if (hasExisting) {
        document.getElementById('existingMotherSection').classList.remove('hidden');
        document.getElementById('newMotherSection').classList.add('hidden');
        // Make mother_id required
        document.getElementById('mother_id').required = true;
        // Remove required from new mother fields
        const motherNameField = document.querySelector('[name="mother_name"]');
        const motherAgeField = document.querySelector('[name="mother_age"]');
        const motherContactField = document.querySelector('[name="mother_contact"]');
        const motherAddressField = document.querySelector('[name="mother_address"]');
        if (motherNameField) motherNameField.required = false;
        if (motherAgeField) motherAgeField.required = false;
        if (motherContactField) motherContactField.required = false;
        if (motherAddressField) motherAddressField.required = false;
    } else {
        document.getElementById('newMotherSection').classList.remove('hidden');
        document.getElementById('existingMotherSection').classList.add('hidden');
        // Remove required from mother_id
        document.getElementById('mother_id').required = false;
        // Make new mother fields required
        const motherNameField = document.querySelector('[name="mother_name"]');
        const motherAgeField = document.querySelector('[name="mother_age"]');
        const motherContactField = document.querySelector('[name="mother_contact"]');
        const motherAddressField = document.querySelector('[name="mother_address"]');
        if (motherNameField) motherNameField.required = true;
        if (motherAgeField) motherAgeField.required = true;
        if (motherContactField) motherContactField.required = true;
        if (motherAddressField) motherAddressField.required = true;
    }
}

// Change mother type
function changeMotherType() {
    if (confirm('Are you sure you want to change the mother selection? This will clear the current data.')) {
        document.getElementById('childRecordFormContainer').classList.add('hidden');
        document.getElementById('motherConfirmationStep').classList.remove('hidden');
        // Update step indicator (if exists)
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        if (step1 && step2) {
            step1.classList.add('active');
            step2.classList.remove('active');
        }
        // Clear form
        document.getElementById('recordForm').reset();
        const motherDetails = document.getElementById('motherDetails');
        if (motherDetails) {
            motherDetails.classList.add('hidden');
        }
    }
}

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Show mother details when selected
    const motherSelect = document.getElementById('mother_id');
    if (motherSelect) {
        motherSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('motherName').textContent = option.dataset.name || '-';
                document.getElementById('motherAge').textContent = option.dataset.age || '-';
                document.getElementById('motherContact').textContent = option.dataset.contact || '-';
                document.getElementById('motherAddress').textContent = option.dataset.address || '-';
                document.getElementById('motherDetails').classList.remove('hidden');
            } else {
                document.getElementById('motherDetails').classList.add('hidden');
            }
        });
    }

    // Form submission with loading state
    const recordForm = document.getElementById('recordForm');
    if (recordForm) {
        recordForm.addEventListener('submit', function() {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        });
    }

    // Set max date for birthdate to today
    const birthdateInput = document.querySelector('input[name="birthdate"]');
    if (birthdateInput) {
        const today = new Date().toISOString().split('T')[0];
        birthdateInput.setAttribute('max', today);
    }
});
