/**
 * Mother Selection Module
 * Handles mother selection workflow for adding new child records
 */

import { getIsExistingMother, setIsExistingMother } from './state.js';
import { clearValidationStates } from './validation.js';

/**
 * Show Mother Form
 * Displays appropriate form section based on mother selection
 * @param {boolean} motherExists - True if existing mother, false if new mother
 */
export function showMotherForm(motherExists) {
    const confirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherExistsInput = document.getElementById('motherExists');
    const contactDetailsSection = document.getElementById('contactDetailsSection');
    const motherAddressField = document.getElementById('motherAddressField');

    // Hide confirmation step and show form
    confirmationStep.classList.add('hidden');
    childRecordForm.classList.remove('hidden');

    // Store the choice
    setIsExistingMother(motherExists);
    if (motherExistsInput) {
        motherExistsInput.value = motherExists ? 'yes' : 'no';
    }

    // Handle Contact Details section visibility
    if (contactDetailsSection) {
        if (motherExists) {
            // Show contact details section for existing mother (auto-fill)
            contactDetailsSection.style.display = 'block';
        } else {
            // Hide contact details section for new mother (manual input only)
            contactDetailsSection.style.display = 'none';
        }
    }

    // Handle Mother Address field visibility (in Birth Details section)
    if (motherAddressField) {
        if (motherExists) {
            // Hide mother address field for existing mother
            motherAddressField.classList.add('hidden');
        } else {
            // Show mother address field for new mother
            motherAddressField.classList.remove('hidden');
        }
    }

    if (motherExists) {
        // Show existing mother selection
        existingMotherSection.classList.remove('hidden');
        newMotherSection.classList.add('hidden');
        updateRequiredFields(true);

        // Clear new mother fields
        clearNewMotherFields();
    } else {
        // Show new mother input
        existingMotherSection.classList.add('hidden');
        newMotherSection.classList.remove('hidden');
        updateRequiredFields(false);

        // Clear existing mother selection
        clearExistingMotherSelection();

        // Clear the child contact fields since we'll use mother's
        const phoneNumber = document.getElementById('phone_number');
        const address = document.getElementById('address');
        if (phoneNumber) {
            phoneNumber.value = '';
            phoneNumber.required = false;
        }
        if (address) {
            address.value = '';
        }
    }
}

/**
 * Change Mother Type
 * Returns to mother selection confirmation step
 */
export function changeMotherType() {
    const confirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');

    // Show confirmation step and hide form
    confirmationStep.classList.remove('hidden');
    childRecordForm.classList.add('hidden');

    // Reset form sections
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherExists = document.getElementById('motherExists');

    if (existingMotherSection) existingMotherSection.classList.add('hidden');
    if (newMotherSection) newMotherSection.classList.add('hidden');
    if (motherExists) motherExists.value = '';
}

/**
 * Go Back to Confirmation
 * Returns from form to confirmation step
 */
export function goBackToConfirmation() {
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');

    if (!motherConfirmationStep || !childRecordForm) return;

    childRecordForm.classList.add('hidden');
    motherConfirmationStep.classList.remove('hidden');

    // Reset sections
    resetMotherSections();
}

/**
 * Setup Mother Selection Handler
 * Attaches event listener to mother dropdown for auto-filling contact details
 */
export function setupMotherSelection() {
    const motherSelect = document.getElementById('mother_id');
    if (!motherSelect) return;

    motherSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const motherDetails = document.getElementById('motherDetails');

        if (!motherDetails) return;

        if (this.value && selectedOption.dataset.name) {
            // Show mother details
            const motherName = document.getElementById('motherName');
            const motherAge = document.getElementById('motherAge');
            const motherContact = document.getElementById('motherContact');
            const motherAddress = document.getElementById('motherAddress');

            if (motherName) motherName.textContent = selectedOption.dataset.name || '-';
            if (motherAge) motherAge.textContent = selectedOption.dataset.age || '-';
            if (motherContact) motherContact.textContent = selectedOption.dataset.contact || '-';
            if (motherAddress) motherAddress.textContent = selectedOption.dataset.address || '-';

            motherDetails.classList.remove('hidden');

            // Auto-fill contact details from mother info
            const phoneInput = document.getElementById('phone_number');
            const addressInput = document.getElementById('address');

            if (phoneInput && selectedOption.dataset.contact) {
                let contact = selectedOption.dataset.contact;
                // Format for phone input (convert +63 to 09 format)
                if (contact.startsWith('+639')) {
                    contact = '0' + contact.substring(3);
                } else if (contact.startsWith('639')) {
                    contact = '0' + contact.substring(2);
                } else if (contact.startsWith('+63')) {
                    contact = '0' + contact.substring(3);
                } else if (contact.startsWith('63')) {
                    contact = '0' + contact.substring(2);
                }
                phoneInput.value = contact;
                phoneInput.readOnly = true;
                phoneInput.classList.add('bg-gray-100');
                phoneInput.required = true;
            }

            if (addressInput && selectedOption.dataset.address) {
                addressInput.value = selectedOption.dataset.address;
                addressInput.readOnly = true;
                addressInput.classList.add('bg-gray-100');
            }

        } else {
            motherDetails.classList.add('hidden');
            // Clear and enable contact inputs
            const phoneInput = document.getElementById('phone_number');
            const addressInput = document.getElementById('address');

            if (phoneInput) {
                phoneInput.value = '';
                phoneInput.readOnly = false;
                phoneInput.classList.remove('bg-gray-100');
            }

            if (addressInput) {
                addressInput.value = '';
                addressInput.readOnly = false;
                addressInput.classList.remove('bg-gray-100');
            }
        }
    });
}

/**
 * Update Required Fields
 * Toggles required attribute based on mother type selection
 * @param {boolean} isExisting - True if existing mother, false if new mother
 */
export function updateRequiredFields(isExisting) {
    const motherIdSelect = document.getElementById('mother_id');
    const motherNameInput = document.getElementById('mother_name');
    const motherAgeInput = document.getElementById('mother_age');
    const motherContactInput = document.getElementById('mother_contact');
    const motherAddressInput = document.getElementById('mother_address');

    if (isExisting) {
        // Existing mother - require selection
        if (motherIdSelect) motherIdSelect.setAttribute('required', 'required');
        if (motherNameInput) motherNameInput.removeAttribute('required');
        if (motherAgeInput) motherAgeInput.removeAttribute('required');
        if (motherContactInput) motherContactInput.removeAttribute('required');
        if (motherAddressInput) motherAddressInput.removeAttribute('required');
    } else {
        // New mother - require manual inputs
        if (motherIdSelect) motherIdSelect.removeAttribute('required');
        if (motherNameInput) motherNameInput.setAttribute('required', 'required');
        if (motherAgeInput) motherAgeInput.setAttribute('required', 'required');
        if (motherContactInput) motherContactInput.setAttribute('required', 'required');
        if (motherAddressInput) motherAddressInput.setAttribute('required', 'required');
    }
}

/**
 * Clear Existing Mother Selection
 * Resets mother dropdown and hides mother details
 */
export function clearExistingMotherSelection() {
    const motherSelect = document.getElementById('mother_id');
    if (motherSelect) {
        motherSelect.value = '';
        motherSelect.dispatchEvent(new Event('change'));
    }
}

/**
 * Clear New Mother Fields
 * Clears all new mother input fields and validation states
 */
export function clearNewMotherFields() {
    const fields = ['mother_name', 'mother_age', 'mother_contact', 'mother_address'];
    fields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            field.value = '';
            field.classList.remove('error-border', 'success-border');
        }
    });
}

/**
 * Reset Modal State
 * Resets entire modal to initial state
 */
export function resetModalState() {
    const form = document.getElementById('recordForm');
    if (form) {
        form.reset();
        clearValidationStates(form);
    }

    // Reset mother selection sections if they exist
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherDetails = document.getElementById('motherDetails');

    if (existingMotherSection) existingMotherSection.classList.add('hidden');
    if (newMotherSection) newMotherSection.classList.add('hidden');
    if (motherDetails) motherDetails.classList.add('hidden');

    // Clear mother exists flag
    const motherExistsInput = document.getElementById('motherExists');
    if (motherExistsInput) {
        motherExistsInput.value = '';
    }

    setIsExistingMother(false);
}

/**
 * Reset Mother Sections
 * Hides mother selection sections and clears fields
 */
export function resetMotherSections() {
    const existingMotherSection = document.getElementById('existingMotherSection');
    const newMotherSection = document.getElementById('newMotherSection');
    const motherDetails = document.getElementById('motherDetails');

    if (existingMotherSection) existingMotherSection.classList.add('hidden');
    if (newMotherSection) newMotherSection.classList.add('hidden');
    if (motherDetails) motherDetails.classList.add('hidden');

    clearExistingMotherSelection();
    clearNewMotherFields();
}

/**
 * Setup Form Submission Handler
 * Handles form submission for new mothers (copies contact details)
 */
export function setupFormSubmission() {
    const recordForm = document.getElementById('recordForm');
    if (!recordForm) return;

    recordForm.addEventListener('submit', function(e) {
        const motherExists = document.getElementById('motherExists')?.value;

        if (motherExists === 'no') {
            // Copy mother's contact details to child's contact details
            const motherContact = document.getElementById('mother_contact')?.value || '';
            const motherAddress = document.getElementById('mother_address')?.value || '';

            // Clean phone number (safely format)
            let cleanContact = motherContact;
            if (cleanContact) {
                if (cleanContact.startsWith('+639')) {
                    cleanContact = cleanContact.substring(3);
                } else if (cleanContact.startsWith('639')) {
                    cleanContact = cleanContact.substring(2);
                } else if (cleanContact.startsWith('+63')) {
                    cleanContact = cleanContact.substring(3);
                } else if (cleanContact.startsWith('63')) {
                    cleanContact = cleanContact.substring(2);
                } else if (cleanContact.startsWith('09')) {
                    cleanContact = cleanContact.substring(1);
                }
            }

            const phoneNumber = document.getElementById('phone_number');
            const address = document.getElementById('address');

            if (phoneNumber) {
                phoneNumber.value = cleanContact;
                phoneNumber.required = true;
            }

            if (address) {
                address.value = motherAddress;
            }
        }
    });
}
