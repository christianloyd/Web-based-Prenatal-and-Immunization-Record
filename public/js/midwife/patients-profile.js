/**
 * Midwife Patients Profile Page JavaScript
 * Handles tab switching for patient profile sections
 */

/* ============================================
   TAB MANAGEMENT
   ============================================ */

/**
 * Show specified tab content and update active state
 * @param {string} tabName - Name of the tab to show (prenatal, checkups, children)
 */
function showTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.add('hidden'));

    // Remove active state from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });

    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');

    // Add active state to selected tab button
    const activeButton = document.getElementById(tabName + '-tab');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
    activeButton.classList.add('border-blue-500', 'text-blue-600');
}

/* ============================================
   INITIALIZATION
   ============================================ */

// Initialize first tab as active
document.addEventListener('DOMContentLoaded', function() {
    showTab('prenatal');
});
