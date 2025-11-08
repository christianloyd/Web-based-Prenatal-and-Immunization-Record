/**
 * Vaccine Management Page - JavaScript
 * Extracted from: resources/views/midwife/vaccines/index.blade.php (lines 259-464)
 * Handles modal management, form validation, and user interactions
 *
 * Configuration Notes:
 * - Uses route() Blade directive for: midwife.vaccines.index
 * - Modal IDs: vaccine-modal, view-vaccine-modal, edit-vaccine-modal
 * - Form IDs: vaccine-form, edit-vaccine-form
 */

/* ========================================
   MODAL MANAGEMENT - Add Vaccine Modal
   ======================================== */

/**
 * Opens the add vaccine modal
 * - Removes hidden class and triggers show animation
 * - Prevents body scrolling while modal is open
 * - Auto-focuses the vaccine name input field
 */
function openVaccineModal() {
    const modal = document.getElementById('vaccine-modal');
    if (!modal) return console.error('Vaccine modal not found');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        const nameInput = modal.querySelector('input[name="name"]');
        if (nameInput) nameInput.focus();
    }, 300);
}

/**
 * Closes the add vaccine modal
 * - Only closes when clicking on overlay (not bubbling clicks)
 * - Resets form if no error state is present
 * - Restores body scroll behavior
 */
function closeVaccineModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('vaccine-modal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        const form = modal.querySelector('form');
        if (form && !document.querySelector('.bg-red-100')) {
            form.reset();
        }
    }, 300);
}

/* ========================================
   MODAL MANAGEMENT - View Vaccine Modal
   ======================================== */

// Global variable to store current vaccine data for cross-modal operations
let currentVaccineData = null;

/**
 * Opens the view vaccine modal
 * - Populates all vaccine details from passed vaccine object
 * - Formats dates and dosages appropriately
 * - Displays category badges with correct styling
 * - Shows dose count with badge
 */
function openViewVaccineModal(vaccine) {
    if (!vaccine) return console.error('No vaccine data provided');

    currentVaccineData = vaccine;

    // Populate modal fields with vaccine data
    document.getElementById('viewVaccineName').textContent = vaccine.name || 'N/A';
    document.getElementById('viewVaccineCategory').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${vaccine.category_color}">${vaccine.category}</span>`;

    // Format dosage to ensure it shows ml
    const dosageText = vaccine.dosage || 'N/A';
    document.getElementById('viewVaccineDosage').textContent = dosageText !== 'N/A' && !dosageText.includes('ml') ? dosageText + ' ml' : dosageText;

    // Set dose count with badge styling
    const doseCount = vaccine.dose_count || 1;
    document.getElementById('viewVaccineDoseCount').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">${doseCount} ${doseCount == 1 ? 'Dose' : 'Doses'}</span>`;

    // Format and display expiry date
    document.getElementById('viewVaccineExpiryDate').textContent = new Date(vaccine.expiry_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('viewVaccineStorageTemp').textContent = vaccine.storage_temp || 'N/A';
    document.getElementById('viewVaccineNotes').textContent = vaccine.notes || 'No notes available';

    // Set created date
    const createdAtElement = document.getElementById('viewVaccineCreatedAt');
    if (vaccine.created_at) {
        const date = new Date(vaccine.created_at);
        createdAtElement.textContent = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } else {
        createdAtElement.textContent = 'N/A';
    }

    // Show modal with animation
    const modal = document.getElementById('view-vaccine-modal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';
}

/**
 * Closes the view vaccine modal
 * - Only closes when clicking on overlay (not bubbling clicks)
 * - Clears stored vaccine data
 * - Restores body scroll behavior
 */
function closeViewVaccineModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('view-vaccine-modal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentVaccineData = null;
    }, 300);
}

/**
 * Closes the view modal and opens the edit modal in sequence
 * - Waits for close animation to complete before opening edit modal
 * - Passes current vaccine data to edit modal
 */
function closeViewVaccineModalAndEdit() {
    if (!currentVaccineData) return;
    closeViewVaccineModal();
    setTimeout(() => {
        openEditVaccineModal(currentVaccineData);
    }, 350);
}

/* ========================================
   MODAL MANAGEMENT - Edit Vaccine Modal
   ======================================== */

/**
 * Opens the edit vaccine modal
 * - Populates form fields with vaccine data
 * - Sets form action URL with vaccine ID (replaces :id placeholder)
 * - Auto-focuses the first form field
 */
function openEditVaccineModal(vaccine) {
    if (!vaccine) return console.error('No vaccine data provided');

    const modal = document.getElementById('edit-vaccine-modal');
    const form = document.getElementById('edit-vaccine-form');
    if (!modal || !form) return console.error('Edit modal elements not found');

    // Set form action URL using data attribute with :id placeholder
    if (form.dataset.updateUrl) {
        form.action = form.dataset.updateUrl.replace(':id', vaccine.id);
    }

    // Populate form fields with vaccine data
    const fields = {
        'edit-name': vaccine.name || '',
        'edit-category': vaccine.category || '',
        'edit-dosage': vaccine.dosage || '',
        'edit-dose-count': vaccine.dose_count || '1',
        'edit-expiry-date': vaccine.expiry_date || '',
        'edit-storage-temp': vaccine.storage_temp || '',
        'edit-notes': vaccine.notes || ''
    };

    Object.entries(fields).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.value = value;
    });

    // Show modal with animation
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('show'));
    document.body.style.overflow = 'hidden';

    // Auto-focus first input field
    setTimeout(() => {
        const firstInput = document.getElementById('edit-name');
        if (firstInput) firstInput.focus();
    }, 100);
}

/**
 * Closes the edit vaccine modal
 * - Only closes when clicking on overlay (not bubbling clicks)
 * - Restores body scroll behavior
 */
function closeEditVaccineModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('edit-vaccine-modal');
    if (!modal) return;
    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

/* ========================================
   EVENT LISTENERS - Keyboard & DOM
   ======================================== */

/**
 * Close modals on Escape key press
 * - Handles all three modals: add, view, and edit
 */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeVaccineModal();
        closeViewVaccineModal();
        closeEditVaccineModal();
    }
});

/* ========================================
   FORM VALIDATION & EVENT HANDLING
   ======================================== */

/**
 * Initialize form validation and auto-dismiss alerts on DOM load
 * - Validates vaccine name, category, and expiry date
 * - Prevents form submission if validation fails
 * - Auto-hides success/error messages after 5 seconds
 * - Handles stock info updates on vaccine selection change
 */
document.addEventListener('DOMContentLoaded', function() {
    // Form validation for all vaccine forms
    const forms = document.querySelectorAll('form[id*="vaccine-form"]');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const nameInput = this.querySelector('input[name="name"]');
            const categoryInput = this.querySelector('select[name="category"]');
            const expiryInput = this.querySelector('input[name="expiry_date"]');

            // Validate vaccine name is required and not empty
            if (!nameInput || !nameInput.value.trim()) {
                e.preventDefault();
                if (nameInput) nameInput.focus();
                showError('Vaccine name is required.');
                return;
            }

            // Validate category is selected
            if (!categoryInput || !categoryInput.value) {
                e.preventDefault();
                if (categoryInput) categoryInput.focus();
                showError('Category is required.');
                return;
            }

            // Validate expiry date is in the future (if provided)
            if (expiryInput && expiryInput.value) {
                const today = new Date();
                const expiry = new Date(expiryInput.value);
                if (expiry <= today) {
                    e.preventDefault();
                    if (expiryInput) expiryInput.focus();
                    showError('Expiry date must be in the future.');
                    return;
                }
            }
        });
    });

    // Auto-hide success/error alert messages after 5 seconds
    const alerts = document.querySelectorAll('.bg-green-100[role="alert"], .bg-red-100[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Update stock info when vaccine selection changes
    const vaccineSelect = document.getElementById('vaccine_id');
    if (vaccineSelect) {
        vaccineSelect.addEventListener('change', updateStockInfo);
    }
});
