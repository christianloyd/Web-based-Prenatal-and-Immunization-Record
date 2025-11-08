/**
 * Patients Profile Page JavaScript
 * Handles tab switching functionality for patient profile views
 */

/**
 * Show a specific tab and hide others
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
    const selectedContent = document.getElementById(tabName + '-content');
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
    }

    // Add active state to selected tab button
    const activeButton = document.getElementById(tabName + '-tab');
    if (activeButton) {
        activeButton.classList.remove('border-transparent', 'text-gray-500');
        activeButton.classList.add('border-blue-500', 'text-blue-600');
    }
}

/**
 * Initialize the profile page
 * Show the first tab (prenatal) as active by default
 */
document.addEventListener('DOMContentLoaded', function() {
    showTab('prenatal');
});
