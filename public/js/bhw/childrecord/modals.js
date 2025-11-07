/**
 * Modals Module
 * Handles modal operations for viewing, editing, adding, and closing child records
 */

import { getCurrentRecord, setCurrentRecord } from './state.js';
import { clearValidationStates } from './validation.js';

/**
 * Open View Record Modal
 * Displays child record details in a read-only modal
 * @param {Object} record - Child record data
 */
export function openViewRecordModal(record) {
    if (!record) {
        console.error('No child record provided');
        return;
    }

    try {
        // Store current record
        setCurrentRecord(record);

        // Populate modal fields - safely handle null/undefined values
        const fieldMappings = [
            { id: 'modalChildName', value: record.full_name },
            { id: 'modalChildGender', value: record.gender },
            { id: 'modalMotherName', value: record.mother_name },
            { id: 'modalFatherName', value: record.father_name },
            { id: 'modalBirthPlace', value: record.birthplace }
        ];

        fieldMappings.forEach(field => {
            const element = document.getElementById(field.id);
            if (element) {
                element.textContent = field.value || 'N/A';
            }
        });

        // Format birth date and calculate age
        if (record.birthdate) {
            const birthDate = new Date(record.birthdate);
            const birthdateElement = document.getElementById('modalBirthDate');
            if (birthdateElement) {
                birthdateElement.textContent = birthDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            // Calculate age
            const today = new Date();
            const ageInMonths = (today.getFullYear() - birthDate.getFullYear()) * 12 + (today.getMonth() - birthDate.getMonth());
            const years = Math.floor(ageInMonths / 12);
            const months = ageInMonths % 12;

            let ageString = '';
            if (years > 0) {
                ageString = `${years} year${years > 1 ? 's' : ''}`;
                if (months > 0) {
                    ageString += ` ${months} month${months > 1 ? 's' : ''}`;
                }
            } else {
                ageString = `${months} month${months > 1 ? 's' : ''}`;
            }

            const ageElement = document.getElementById('modalChildAge');
            if (ageElement) {
                ageElement.textContent = ageString;
            }
        } else {
            const birthdateElement = document.getElementById('modalBirthDate');
            const ageElement = document.getElementById('modalChildAge');
            if (birthdateElement) birthdateElement.textContent = 'N/A';
            if (ageElement) ageElement.textContent = 'N/A';
        }

        // Birth details
        const birthWeightElement = document.getElementById('modalBirthWeight');
        if (birthWeightElement) {
            birthWeightElement.textContent = record.birth_weight ? `${record.birth_weight} kg` : 'N/A';
        }

        const birthHeightElement = document.getElementById('modalBirthHeight');
        if (birthHeightElement) {
            birthHeightElement.textContent = record.birth_height ? `${record.birth_height} cm` : 'N/A';
        }

        // Contact information - Format phone number for display
        let displayPhone = record.phone_number || 'N/A';
        if (displayPhone !== 'N/A' && displayPhone.length === 10 && displayPhone.startsWith('9')) {
            displayPhone = `+63${displayPhone}`;
        }
        const phoneElement = document.getElementById('modalPhoneNumber');
        if (phoneElement) {
            phoneElement.textContent = displayPhone;
        }

        const addressElement = document.getElementById('modalAddress');
        if (addressElement) {
            addressElement.textContent = record.address || 'N/A';
        }

        // Created date
        if (record.created_at) {
            const createdDate = new Date(record.created_at);
            const createdDateElement = document.getElementById('modalCreatedDate');
            if (createdDateElement) {
                createdDateElement.textContent = createdDate.toLocaleDateString();
            }
        } else {
            const createdDateElement = document.getElementById('modalCreatedDate');
            if (createdDateElement) {
                createdDateElement.textContent = 'N/A';
            }
        }

        // Show modal with animation
        const modal = document.getElementById('viewChildModal');
        const content = document.getElementById('viewChildModalContent');

        if (!modal || !content) {
            console.error('View modal elements not found');
            return;
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Trigger animation
        requestAnimationFrame(() => {
            content.classList.remove('-translate-y-10', 'opacity-0');
            content.classList.add('translate-y-0', 'opacity-100');
        });

    } catch (error) {
        console.error('Error opening view modal:', error);
    }
}

/**
 * Close View Record Modal
 * @param {Event} event - Click event
 */
export function closeViewChildModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('viewChildModal');
    const content = document.getElementById('viewChildModalContent');

    if (!modal || !content) return;

    content.classList.remove('translate-y-0', 'opacity-100');
    content.classList.add('-translate-y-10', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

/**
 * Open Edit Record Modal
 * Displays child record in an editable form
 * @param {Object} record - Child record data
 */
export function openEditRecordModal(record) {
    if (!record) {
        console.error('No child record provided');
        return;
    }

    const modal = document.getElementById('edit-child-modal');
    if (!modal) {
        console.error('Edit modal element not found');
        return;
    }

    const form = document.getElementById('edit-child-form');
    if (!form) {
        console.error('Edit form not found');
        return;
    }

    // Update form action with correct ID
    const updateUrl = form.dataset.updateUrl;
    if (updateUrl && record.id) {
        form.action = updateUrl.replace(':id', record.id);
    } else {
        console.error('Unable to set form action. UpdateUrl:', updateUrl, 'Record ID:', record.id);
        return;
    }

    // Store current record
    setCurrentRecord(record);

    // Format the date to "yyyy-MM-dd" for date inputs
    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    };

    // Populate form fields
    const fieldMappings = [
        { id: 'edit-record-id', value: record.id },
        { id: 'edit-child-name', value: record.full_name },
        { id: 'edit-birthdate', value: formatDate(record.birthdate) },
        { id: 'edit-birth-height', value: record.birth_height },
        { id: 'edit-birth-weight', value: record.birth_weight },
        { id: 'edit-birthplace', value: record.birthplace },
        { id: 'edit-mother-name', value: record.mother_name },
        { id: 'edit-father-name', value: record.father_name },
        { id: 'edit-address', value: record.address }
    ];

    fieldMappings.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
            element.value = field.value || '';
            element.classList.remove('error-border', 'success-border');
        }
    });

    // Set gender radio button
    const maleRadio = document.getElementById('edit-gender-male');
    const femaleRadio = document.getElementById('edit-gender-female');
    if (maleRadio && femaleRadio) {
        maleRadio.checked = record.gender === 'Male';
        femaleRadio.checked = record.gender === 'Female';
    }

    // Format phone number for editing (remove +63 prefix if present)
    let phoneValue = record.phone_number || '';
    if (phoneValue.startsWith('+63')) {
        phoneValue = phoneValue.substring(3);
    } else if (phoneValue.startsWith('63')) {
        phoneValue = phoneValue.substring(2);
    } else if (phoneValue.startsWith('0')) {
        phoneValue = phoneValue.substring(1);
    }
    const phoneInput = document.getElementById('edit-phone-number');
    if (phoneInput) {
        phoneInput.value = phoneValue;
    }

    // Clear validation states
    clearValidationStates(form);

    // Show modal with proper animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(() => {
        const nameInput = document.getElementById('edit-child-name');
        if (nameInput) nameInput.focus();
    }, 100);
}

/**
 * Close Edit Record Modal
 * @param {Event} event - Click event
 */
export function closeEditChildModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('edit-child-modal');
    if (!modal) return;

    // Remove show class to trigger fade out animation
    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Clear form if no server validation errors
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('edit-child-form');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }
        }
    }, 300);
}

/**
 * Open Add Record Modal
 * Opens modal for adding a new child record (with or without mother selection)
 */
export function openAddModal() {
    const modal = document.getElementById('recordModal');
    const motherConfirmationStep = document.getElementById('motherConfirmationStep');
    const childRecordForm = document.getElementById('childRecordForm');

    if (!modal) {
        console.error('Add modal element not found');
        return;
    }

    // Check if this modal has mother selection functionality
    const hasMotherSelection = motherConfirmationStep && childRecordForm;

    if (hasMotherSelection) {
        // Import and use mother selection reset (dynamic import to avoid circular dependency)
        import('./mother-selection.js').then(module => {
            module.resetModalState();
        }).catch(() => {
            console.warn('Mother selection module not available');
        });

        // Show confirmation step, hide form
        motherConfirmationStep.classList.remove('hidden');
        childRecordForm.classList.add('hidden');
    } else {
        // Simple modal version - reset form directly
        const form = document.getElementById('recordForm');
        if (form) {
            // Set form action dynamically
            const storeUrl = form.dataset.storeUrl || form.action;
            form.action = storeUrl;
            form.reset();

            // Clear validation states
            clearValidationStates(form);
        }

        // Set modal title if available
        const modalTitle = document.getElementById('modalTitle');
        if (modalTitle) {
            modalTitle.innerHTML = '<i class="fas fa-baby text-[#68727A] mr-2"></i>Add Child Record';
        }
    }

    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Focus first input after animation (if not using mother selection)
    if (!hasMotherSelection) {
        setTimeout(() => {
            const firstInput = document.querySelector('#recordForm input[name="first_name"]');
            if (firstInput) firstInput.focus();
        }, 300);
    }
}

/**
 * Close Add Record Modal
 * @param {Event} event - Click event
 */
export function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;

    const modal = document.getElementById('recordModal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';

        // Only reset if no validation errors from server
        if (!document.querySelector('.bg-red-100')) {
            const form = document.getElementById('recordForm');
            if (form) {
                form.reset();
                clearValidationStates(form);
            }

            // Reset modal state including mother selection (dynamic import)
            import('./mother-selection.js').then(module => {
                module.resetModalState();
            }).catch(() => {
                // Mother selection module not available, ignore
            });
        }
    }, 300);
}
