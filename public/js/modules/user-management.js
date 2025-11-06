/**
 * User Management Module JavaScript
 * Extracted from user management view for better maintainability
 * Global variables and functions for user management functionality
 */

// ====================================
// Global Variables
// ====================================
let currentViewUser = null;
let isEditMode = false;

// ====================================
// Modal Management Functions
// ====================================

/**
 * Open Add User Modal
 */
function openAddModal() {
    resetForm();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus text-[#68727A] mr-2"></i>Add User';
    document.getElementById('userForm').action = window.userManagementRoutes.store;
    document.getElementById('userId').value = '';

    // Remove method override if it exists
    removeMethodOverride();

    // Show password section for new users
    const passwordSection = document.getElementById('passwordSection');
    const passwordInput = document.getElementById('password');
    if (passwordSection && passwordInput) {
        passwordSection.style.display = 'block';
        passwordInput.required = true;
        passwordInput.placeholder = 'Enter password';
        const passwordLabel = passwordSection.querySelector('label');
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password *';
        }
    }

    // Update submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save User';
    }

    isEditMode = false;
    showModal('userModal');
}

/**
 * Open Edit User Modal
 */
function openEditUserModal(user) {
    resetForm();
    populateEditForm(user);
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-[#68727A] mr-2"></i>Edit User';
    document.getElementById('userForm').action = window.userManagementRoutes.update.replace(':id', user.id);

    // Add method override for PUT
    addMethodOverride('PUT');

    // Show password section for edit but make it optional
    const passwordSection = document.getElementById('passwordSection');
    const passwordInput = document.getElementById('password');
    if (passwordSection && passwordInput) {
        passwordSection.style.display = 'block';
        passwordInput.required = false;
        passwordInput.placeholder = 'Leave blank to keep current password';
        const passwordLabel = passwordSection.querySelector('label');
        if (passwordLabel) {
            passwordLabel.innerHTML = 'Password';
        }
    }

    // Update submit button
    const submitBtn = document.getElementById('submit-btn');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Update User';
    }

    isEditMode = true;
    showModal('userModal');
}

/**
 * Open View User Modal
 */
function openViewUserModal(user) {
    currentViewUser = user;
    populateViewModal(user);
    showModal('viewUserModal');
}

// ====================================
// User Activation/Deactivation Functions
// ====================================

function deactivateUser(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.userManagementRoutes.deactivate.replace(':id', userId);

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // Add method override for PATCH
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();
}

function activateUser(userId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.userManagementRoutes.activate.replace(':id', userId);

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);

    // Add method override for PATCH
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PATCH';
    form.appendChild(methodInput);

    document.body.appendChild(form);
    form.submit();
}

// ====================================
// Modal Show/Hide Functions
// ====================================

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`Modal with id '${modalId}' not found`);
        return;
    }

    modal.classList.remove('hidden');
    // Force reflow
    modal.offsetHeight;

    setTimeout(() => {
        modal.classList.add('show');
        if (modalId === 'viewUserModal') {
            const content = document.getElementById('viewUserModalContent');
            if (content) {
                content.classList.remove('-translate-y-10', 'opacity-0');
            }
        }
    }, 10);
    document.body.style.overflow = 'hidden';
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    modal.classList.remove('show');
    if (modalId === 'viewUserModal') {
        const content = document.getElementById('viewUserModalContent');
        if (content) {
            content.classList.add('-translate-y-10', 'opacity-0');
        }
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    hideModal('userModal');
}

function closeViewUserModal() {
    hideModal('viewUserModal');
    currentViewUser = null;
}

// ====================================
// Form Management Functions
// ====================================

function resetForm() {
    const form = document.getElementById('userForm');
    if (form) {
        form.reset();
        clearValidationErrors();
    }

    // Reset hidden fields
    const userIdInput = document.getElementById('userId');
    if (userIdInput) {
        userIdInput.value = '';
    }

    // Remove method override
    removeMethodOverride();
}

function populateEditForm(user) {
    const fields = {
        'userId': user.id,
        'name': user.name || '',
        'username': user.username || '',
        'age': user.age || '',
        'contact_number': user.contact_number || '',
        'address': user.address || '',
        'role': user.role || ''
    };

    // Populate text inputs
    Object.keys(fields).forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.value = fields[fieldId];
        }
    });

    // Set gender radio button
    setGenderRadio(user.gender);
}

function populateViewModal(user) {
    const viewFields = {
        'modalFullName': user.name || 'N/A',
        'modalGender': user.gender || 'N/A',
        'modalAge': user.age || 'N/A',
        'modalRole': user.role || 'N/A',
        'modalUsername': user.username || 'N/A',
        'modalContactNumber': user.contact_number ? '+63' + user.contact_number : 'N/A',
        'modalUserAddress': user.address || 'N/A',
        'modalStatus': user.is_active ? 'Active' : 'Inactive'
    };

    // Populate view fields
    Object.keys(viewFields).forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.textContent = viewFields[fieldId];
        }
    });

    // Update status styling in modal
    const statusElement = document.getElementById('modalStatus');
    if (statusElement) {
        statusElement.className = `text-lg font-semibold mt-1 ${user.is_active ? 'text-green-600' : 'text-red-600'}`;
    }

    // Set dates and role information
    setModalDates(user);
    setRoleInformation(user.role);
}

// ====================================
// Helper Functions
// ====================================

function setGenderRadio(gender) {
    const maleRadio = document.querySelector('input[name="gender"][value="Male"]');
    const femaleRadio = document.querySelector('input[name="gender"][value="Female"]');

    if (maleRadio) maleRadio.checked = gender === 'Male';
    if (femaleRadio) femaleRadio.checked = gender === 'Female';
}

function setModalDates(user) {
    const createdAtElement = document.getElementById('modalCreatedAt');

    if (user.created_at && createdAtElement) {
        const formattedDate = new Date(user.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        createdAtElement.textContent = formattedDate;
    }
}

function setRoleInformation(role) {
    const roleDescriptions = {
        'Midwife': 'Healthcare professional responsible for prenatal care, delivery assistance, and maternal health services. Has full system access including user management.',
        'BHW': 'Community health worker providing basic healthcare services and health education at the barangay level. Has limited system access focused on patient records.'
    };

    const accessLevels = {
        'Midwife': 'Full System Access',
        'BHW': 'Limited Access'
    };

    const descElement = document.getElementById('modalRoleDescription');
    const accessElement = document.getElementById('modalAccessLevel');

    if (descElement) {
        descElement.textContent = roleDescriptions[role] || 'No description available';
    }

    if (accessElement) {
        accessElement.textContent = accessLevels[role] || 'N/A';
    }
}

function addMethodOverride(method) {
    removeMethodOverride(); // Remove existing if any

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = method;
    methodInput.id = 'methodOverride';

    const form = document.getElementById('userForm');
    if (form) {
        form.appendChild(methodInput);
    }
}

function removeMethodOverride() {
    const methodOverride = document.getElementById('methodOverride');
    if (methodOverride) {
        methodOverride.remove();
    }
}

function clearValidationErrors() {
    // Remove error borders
    const errorElements = document.querySelectorAll('.error-border');
    errorElements.forEach(element => {
        element.classList.remove('error-border');
    });

    // Remove error messages
    const errorMessages = document.querySelectorAll('.text-red-500');
    errorMessages.forEach(element => {
        if (element.classList.contains('mt-1')) {
            element.remove();
        }
    });

    // Remove validation error container
    const errorContainer = document.querySelector('.validation-errors');
    if (errorContainer) {
        errorContainer.remove();
    }
}

// ====================================
// Phone Number Formatting Functions
// ====================================

function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');

    if (value.length > 0 && !value.startsWith('9')) {
        value = '';
    }

    if (value.length > 10) {
        value = value.substring(0, 10);
    }

    input.value = value;
}

function setupPhoneNumberFormatting() {
    const phoneInput = document.getElementById('contact_number');

    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            formatPhoneNumber(e.target);
        });

        phoneInput.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });

        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = paste.replace(/\D/g, '');

            let phoneNumber = cleaned;
            if (phoneNumber.startsWith('63')) {
                phoneNumber = phoneNumber.substring(2);
            }
            if (phoneNumber.startsWith('0')) {
                phoneNumber = phoneNumber.substring(1);
            }

            if (phoneNumber.startsWith('9') && phoneNumber.length <= 10) {
                phoneInput.value = phoneNumber;
                formatPhoneNumber(phoneInput);
            }
        });
    }
}

// ====================================
// Form Validation Functions
// ====================================

function validateForm() {
    const requiredFields = ['name', 'username', 'age', 'contact_number', 'role'];
    let isValid = true;
    const errors = [];

    clearValidationErrors();

    requiredFields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        if (input && !input.value.trim()) {
            input.classList.add('error-border');
            errors.push(`${getFieldLabel(fieldId)} is required.`);
            isValid = false;
        } else if (input) {
            input.classList.remove('error-border');
        }
    });

    // Check gender radio buttons
    const genderChecked = document.querySelector('input[name="gender"]:checked');
    if (!genderChecked) {
        errors.push('Gender is required.');
        isValid = false;
    }

    // Validate phone number format
    const phoneInput = document.getElementById('contact_number');
    if (phoneInput && phoneInput.value) {
        const phonePattern = /^9\d{9}$/;
        if (!phonePattern.test(phoneInput.value)) {
            phoneInput.classList.add('error-border');
            errors.push('Contact number must be a valid Philippine mobile number starting with 9.');
            isValid = false;
        }
    }

    // Validate age range
    const ageInput = document.getElementById('age');
    if (ageInput && ageInput.value) {
        const age = parseInt(ageInput.value);
        if (age < 18 || age > 100) {
            ageInput.classList.add('error-border');
            errors.push('Age must be between 18 and 100 years.');
            isValid = false;
        }
    }

    // Validate password
    const passwordInput = document.getElementById('password');
    if (passwordInput && passwordInput.value) {
        const password = passwordInput.value;

        // Check minimum length
        if (password.length < 8) {
            passwordInput.classList.add('error-border');
            errors.push('Password must be at least 8 characters long.');
            isValid = false;
        }

        // Check for lowercase letter
        if (!/[a-z]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one lowercase letter.');
            isValid = false;
        }

        // Check for uppercase letter
        if (!/[A-Z]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one uppercase letter.');
            isValid = false;
        }

        // Check for number
        if (!/[0-9]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one number.');
            isValid = false;
        }

        // Check for special character
        if (!/[@$!%*#?&]/.test(password)) {
            passwordInput.classList.add('error-border');
            errors.push('Password must contain at least one special character (@$!%*#?&).');
            isValid = false;
        }
    }

    // Password is required for new users
    if (!isEditMode && passwordInput && !passwordInput.value.trim()) {
        passwordInput.classList.add('error-border');
        errors.push('Password is required.');
        isValid = false;
    }

    return { isValid, errors };
}

function setupFormValidation() {
    const form = document.getElementById('userForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const validation = validateForm();
            if (!validation.isValid) {
                e.preventDefault();
                showValidationErrors(validation.errors);
                return false;
            }
        });
    }
}

function showValidationErrors(errors) {
    if (errors.length === 0) return;

    let errorContainer = document.querySelector('#userModal .validation-errors');
    if (!errorContainer) {
        errorContainer = document.createElement('div');
        errorContainer.className = 'validation-errors bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';

        const modalHeader = document.querySelector('#userModal .flex.justify-between.items-center');
        if (modalHeader) {
            modalHeader.parentNode.insertBefore(errorContainer, modalHeader.nextSibling);
        }
    }

    errorContainer.innerHTML = `
        <div class="font-medium">Please correct the following errors:</div>
        <ul class="list-disc list-inside mt-2">
            ${errors.map(error => `<li class="text-sm">${error}</li>`).join('')}
        </ul>
    `;

    const modalContent = document.querySelector('#userModal .modal-content');
    if (modalContent) {
        modalContent.scrollTop = 0;
    }
}

function getFieldLabel(fieldId) {
    const labels = {
        'name': 'Full Name',
        'username': 'Username',
        'age': 'Age',
        'contact_number': 'Contact Number',
        'role': 'Role'
    };
    return labels[fieldId] || fieldId;
}

// ====================================
// Modal Event Setup Functions
// ====================================

function setupModalEventListeners() {
    // Close modal when clicking outside
    const userModal = document.getElementById('userModal');
    if (userModal) {
        userModal.addEventListener('click', function(e) {
            if (e.target === userModal) {
                closeModal();
            }
        });
    }

    const viewUserModal = document.getElementById('viewUserModal');
    if (viewUserModal) {
        viewUserModal.addEventListener('click', function(e) {
            if (e.target === viewUserModal) {
                closeViewUserModal();
            }
        });
    }

    // ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!document.getElementById('userModal').classList.contains('hidden')) {
                closeModal();
            }
            if (!document.getElementById('viewUserModal').classList.contains('hidden')) {
                closeViewUserModal();
            }
        }
    });
}

// ====================================
// Skeleton Loading Functions
// ====================================

function showSkeletonLoaders() {
    // Hide actual content - check if elements exist first
    const statsContainer = document.getElementById('stats-container');
    const tableContent = document.getElementById('table-content');
    const statsskeleton = document.getElementById('stats-skeleton');
    const tableSkeleton = document.getElementById('table-skeleton');

    if (statsContainer) statsContainer.classList.add('hidden');
    if (tableContent) tableContent.classList.add('hidden');

    // Show skeletons
    if (statsskeleton) statsskeleton.classList.remove('hidden');
    if (tableSkeleton) tableSkeleton.classList.remove('hidden');
}

function hideSkeletonLoaders() {
    // Show actual content - check if elements exist first
    const statsContainer = document.getElementById('stats-container');
    const tableContent = document.getElementById('table-content');
    const statsskeleton = document.getElementById('stats-skeleton');
    const tableSkeleton = document.getElementById('table-skeleton');

    if (statsContainer) statsContainer.classList.remove('hidden');
    if (tableContent) tableContent.classList.remove('hidden');

    // Hide skeletons
    if (statsskeleton) statsskeleton.classList.add('hidden');
    if (tableSkeleton) tableSkeleton.classList.add('hidden');
}

// ====================================
// Initialization Function
// ====================================

function initializeUserManagement() {
    setupPhoneNumberFormatting();
    setupFormValidation();
    setupModalEventListeners();

    // Check for server-side validation errors and show modal if needed
    if (document.querySelectorAll('.error-border').length > 0 ||
        document.querySelector('#userForm .text-red-500')) {
        const userIdInput = document.getElementById('userId');
        if (userIdInput && userIdInput.value) {
            isEditMode = true;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-[#68727A] mr-2"></i>Edit User';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save mr-2"></i>Update User';
            const userId = userIdInput.value;
            document.getElementById('userForm').action = window.userManagementRoutes.update.replace(':id', userId);
            addMethodOverride('PUT');
        } else {
            isEditMode = false;
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus text-[#68727A] mr-2"></i>Add User';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save mr-2"></i>Save User';
            document.getElementById('userForm').action = window.userManagementRoutes.store;
        }
        showModal('userModal');
    }
}

// ====================================
// Document Ready Event Listener
// ====================================

document.addEventListener('DOMContentLoaded', initializeUserManagement);