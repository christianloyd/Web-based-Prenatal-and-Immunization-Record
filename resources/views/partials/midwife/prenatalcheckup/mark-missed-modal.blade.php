<script>
/**
 * Open SweetAlert to mark prenatal checkup as missed
 * @param {number} checkupId - The ID of the checkup to mark as missed
 * @param {string} patientName - The name of the patient
 * @param {string} checkupDate - The scheduled checkup date
 * @param {string} checkupTime - The scheduled checkup time
 */
function openMarkCheckupMissedModal(checkupId, patientName, checkupDate, checkupTime) {
    if (!checkupId) {
        showError('No checkup ID provided');
        return;
    }

    // Build the confirmation HTML with checkup details
    const detailsHtml = `
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4 text-left">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="font-semibold text-gray-700">Patient:</span>
                    <div class="text-gray-900 mt-1">${patientName || 'Unknown'}</div>
                </div>
                <div>
                    <span class="font-semibold text-gray-700">Scheduled Date:</span>
                    <div class="text-gray-900 mt-1">${checkupDate || 'N/A'}</div>
                </div>
                <div class="col-span-2">
                    <span class="font-semibold text-gray-700">Scheduled Time:</span>
                    <div class="text-gray-900 mt-1">${checkupTime || 'N/A'}</div>
                </div>
            </div>
        </div>
        <p class="text-gray-700 text-sm">
            Are you sure you want to mark this prenatal checkup as missed? The patient will be notified via SMS if contact information is available.
        </p>
    `;

    Swal.fire({
        icon: 'warning',
        title: 'Mark as Missed',
        html: detailsHtml,
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#374151',
        confirmButtonText: '<i class="fas fa-times mr-2"></i>Yes, Mark as Missed',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        iconColor: '#F59E0B',
        customClass: {
            icon: 'swal2-icon-warning-custom',
            cancelButton: 'swal2-cancel-custom'
        },
        didOpen: () => {
            // Make the warning icon more visible
            const icon = document.querySelector('.swal2-icon.swal2-warning');
            if (icon) {
                icon.style.borderColor = '#F59E0B';
                icon.style.color = '#F59E0B';
            }
            // Make exclamation mark solid, bold and clear like the image
            const exclamation = document.querySelector('.swal2-icon.swal2-warning .swal2-icon-content');
            if (exclamation) {
                exclamation.style.fontWeight = '900';
                exclamation.style.fontSize = '5em';
                exclamation.style.color = '#F59E0B';
                exclamation.style.textShadow = '0 0 2px #F59E0B';
                exclamation.style.letterSpacing = '0';
                exclamation.style.lineHeight = '1';
            }
            // Style the X mark (top line of !)
            const xMark = document.querySelector('.swal2-icon.swal2-warning [class$="x-mark"]');
            if (xMark) {
                xMark.style.backgroundColor = '#F59E0B';
            }
            // Make Cancel button more visible with darker color
            const cancelBtn = document.querySelector('.swal2-cancel');
            if (cancelBtn) {
                cancelBtn.style.backgroundColor = '#374151';
                cancelBtn.style.color = '#FFFFFF';
                cancelBtn.style.fontWeight = '500';
                cancelBtn.style.border = 'none';
            }
        },
        preConfirm: () => {
            // Create and submit form
            const userRole = '{{ auth()->user()->role }}';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/${userRole}/prenatalcheckup/${checkupId}/mark-missed`;

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Add reason
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = 'Patient did not show up';
            form.appendChild(reasonInput);

            // Add to body and submit
            document.body.appendChild(form);
            form.submit();

            // Return false to keep the loading state
            return false;
        }
    });
}
</script>