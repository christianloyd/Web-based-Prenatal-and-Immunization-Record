/* ========================================
   Cloud Backup System Module JavaScript
   ======================================== */

// Initialize with empty array - will be populated from server
let backups = [];
let restores = [];

let filteredBackups = [...backups];
let filteredRestores = [...restores];
let currentBackupProgress = null;
let activeTab = 'backup';

// Module data - will be loaded dynamically
let moduleSizes = {};
let moduleCounts = {};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing messages on page load
    hideAllMessages();

    loadRealDataCounts();
    loadBackupData();
    updateEstimatedSize();

    // Add event listeners for module selection
    const moduleCheckboxes = document.querySelectorAll('input[name="modules"]');
    moduleCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateEstimatedSize);
    });
});

// Load backup data from server
function loadBackupData() {
    return fetch(window.cloudBackupRoutes.data)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Handle error response from server
            if (data.success === false) {
                console.error('Server error loading backup data:', data.error);
                showError(data.message || 'Failed to load backup data');

                // Use empty data to prevent further errors
                backups = [];
                restores = [];
                filteredBackups = [];
                filteredRestores = [];
            } else {
                backups = data.backups || [];
                restores = data.restores || [];
                filteredBackups = [...backups];
                filteredRestores = [...restores];
            }

            if (activeTab === 'backup') {
                renderBackups();
            } else {
                renderRestores();
            }

            // Update stats
            if (data.stats) {
                updateStatsFromServer(data.stats);
            }

            return data; // Return data for chaining
        })
        .catch(error => {
            console.error('Error loading backup data:', error);
            showError('Failed to load backup data: ' + error.message);

            // Set empty data to prevent further errors
            backups = [];
            restores = [];
            filteredBackups = [];
            filteredRestores = [];

            // Still render to show empty state
            if (activeTab === 'backup') {
                renderBackups();
            } else {
                renderRestores();
            }

            throw error; // Re-throw for proper error handling
        });
}

function renderBackups() {
    const tbody = document.getElementById('backupTableBody');
    const emptyState = document.getElementById('emptyState');

    if (filteredBackups.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');

    tbody.innerHTML = filteredBackups.map(backup => {
        const statusColor = getStatusColor(backup.status);
        const moduleNames = backup.modules.map(m => formatModuleName(m)).join(', ');

        return `
            <tr class="hover:bg-gray-50">
                <td class="px-3 sm:px-6 py-4">
                    <div class="font-medium text-gray-900 text-sm sm:text-base">${backup.name}</div>
                    <div class="text-xs sm:text-sm text-gray-500">${moduleNames}</div>
                    <div class="sm:hidden text-xs text-gray-500 mt-1">${backup.type === 'full' ? 'Full' : 'Selective'} • ${backup.size}</div>
                    <div class="lg:hidden text-xs text-gray-500 mt-1">${formatDate(backup.created_at)}</div>
                    ${backup.encrypted ? '<div class="text-xs text-green-600 mt-1"><i class="fas fa-lock mr-1"></i>Encrypted</div>' : ''}
                </td>
                <td class="px-3 sm:px-6 py-4 hidden sm:table-cell">
                    <div class="text-sm text-gray-900">${backup.type === 'full' ? 'Full Backup' : 'Selective'}</div>
                    <div class="text-xs text-gray-500">SQL DUMP</div>
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 hidden md:table-cell">${backup.size}</td>
                <td class="px-3 sm:px-6 py-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${statusColor.bg} ${statusColor.text}">
                        <i class="fas ${statusColor.icon} mr-1"></i>
                        <span class="hidden sm:inline">${backup.status.charAt(0).toUpperCase() + backup.status.slice(1)}</span>
                        <span class="sm:hidden">${backup.status.charAt(0).toUpperCase()}</span>
                    </span>
                    ${backup.error ? `<div class="text-xs text-red-600 mt-1">${backup.error}</div>` : ''}
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 hidden lg:table-cell">${formatDate(backup.created_at)}</td>
                <td class="px-3 sm:px-6 py-4 text-center">
                    <div class="flex flex-col sm:flex-row justify-center items-center space-y-1 sm:space-y-0 sm:space-x-1">
                        ${backup.status === 'completed' ? `
                            <button onclick="downloadBackup(${backup.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs transition-colors w-full sm:w-auto">
                                <i class="fas fa-cloud-download-alt mr-1"></i><span class="hidden sm:inline">Download</span>
                            </button>
                            <button onclick="restoreFromBackup(${backup.id})" class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs transition-colors w-full sm:w-auto">
                                <i class="fas fa-history mr-1"></i><span class="hidden sm:inline">Restore</span>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function renderRestores() {
    const tbody = document.getElementById('restoreTableBody');
    const emptyState = document.getElementById('emptyRestoreState');

    if (filteredRestores.length === 0) {
        tbody.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');

    tbody.innerHTML = filteredRestores.map(restore => {
        const statusColor = getStatusColor(restore.status);

        return `
            <tr class="hover:bg-gray-50">
                <td class="px-3 sm:px-6 py-4">
                    <div class="font-medium text-gray-900 text-sm sm:text-base">Restored from: ${restore.backup_name}</div>
                    <div class="text-xs sm:text-sm text-gray-500">${restore.formatted_modules}</div>
                    <div class="sm:hidden text-xs text-gray-500 mt-1">${restore.restore_options}</div>
                    <div class="lg:hidden text-xs text-gray-500 mt-1">${formatDate(restore.restored_at)} • ${restore.restored_by}</div>
                </td>
                <td class="px-3 sm:px-6 py-4 hidden sm:table-cell">
                    <div class="text-sm text-gray-900">${restore.backup_name}</div>
                    <div class="text-xs text-gray-500">Backup ID: ${restore.backup_id}</div>
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 hidden md:table-cell">${restore.restore_options}</td>
                <td class="px-3 sm:px-6 py-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${statusColor.bg} ${statusColor.text}">
                        <i class="fas ${statusColor.icon} mr-1"></i>
                        <span class="hidden sm:inline">${restore.status.charAt(0).toUpperCase() + restore.status.slice(1)}</span>
                        <span class="sm:hidden">${restore.status.charAt(0).toUpperCase()}</span>
                    </span>
                    ${restore.error ? `<div class="text-xs text-red-600 mt-1">${restore.error}</div>` : ''}
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 hidden lg:table-cell">${formatDate(restore.restored_at)}</td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 hidden lg:table-cell">${restore.restored_by}</td>
            </tr>
        `;
    }).join('');
}

function switchTab(tabName) {
    // Update active tab
    activeTab = tabName;

    // Update tab buttons
    const backupTab = document.getElementById('backupHistoryTab');
    const restoreTab = document.getElementById('restoreHistoryTab');

    if (tabName === 'backup') {
        backupTab.classList.add('border-secondary', 'text-secondary');
        backupTab.classList.remove('border-transparent', 'text-gray-500');
        restoreTab.classList.add('border-transparent', 'text-gray-500');
        restoreTab.classList.remove('border-secondary', 'text-secondary');
    } else {
        restoreTab.classList.add('border-secondary', 'text-secondary');
        restoreTab.classList.remove('border-transparent', 'text-gray-500');
        backupTab.classList.add('border-transparent', 'text-gray-500');
        backupTab.classList.remove('border-secondary', 'text-secondary');
    }

    // Update tab content
    const backupContent = document.getElementById('backupHistoryContent');
    const restoreContent = document.getElementById('restoreHistoryContent');

    if (tabName === 'backup') {
        backupContent.classList.remove('hidden');
        restoreContent.classList.add('hidden');
        renderBackups();
    } else {
        restoreContent.classList.remove('hidden');
        backupContent.classList.add('hidden');
        renderRestores();
    }
}

function filterRestoreBackups() {
    const statusFilter = document.getElementById('filterRestoreStatus').value;

    filteredRestores = restores.filter(restore => {
        const matchesStatus = !statusFilter || restore.status === statusFilter;
        return matchesStatus;
    });

    renderRestores();
}

function getStatusColor(status) {
    const colors = {
        completed: { bg: 'bg-green-100', text: 'text-green-800', icon: 'fa-check-circle' },
        failed: { bg: 'bg-red-100', text: 'text-red-800', icon: 'fa-times-circle' },
        'in-progress': { bg: 'bg-blue-100', text: 'text-blue-800', icon: 'fa-spinner' }
    };
    return colors[status] || colors.completed;
}

function formatModuleName(module) {
    const names = {
        patient_records: 'Patient Records',
        prenatal_monitoring: 'Prenatal Monitoring',
        child_records: 'Child Records',
        immunization_records: 'Immunization Records',
        vaccine_management: 'Vaccine Management'
    };
    return names[module] || module;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function filterBackups() {
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;

    filteredBackups = backups.filter(backup => {
        const matchesType = !typeFilter || backup.type === typeFilter;
        const matchesStatus = !statusFilter || backup.status === statusFilter;
        return matchesType && matchesStatus;
    });

    renderBackups();
}

function openBackupModal() {
    document.getElementById('backupModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    updateEstimatedSize();
}

function closeBackupModal() {
    document.getElementById('backupModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openRestoreModal() {
    document.getElementById('restoreModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    // Refresh backup data before populating restore options to ensure latest status
    loadBackupData().then(() => {
        populateRestoreBackups();
    }).catch(() => {
        // If loading fails, still populate with existing data
        populateRestoreBackups();
    });
}

function closeRestoreModal() {
    document.getElementById('restoreModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const moduleCheckboxes = document.querySelectorAll('input[name="modules"]');

    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    updateEstimatedSize();
}

function updateEstimatedSize() {
    const selectedModules = Array.from(document.querySelectorAll('input[name="modules"]:checked'))
        .map(cb => cb.value);

    let totalSize = 0;
    selectedModules.forEach(module => {
        totalSize += moduleSizes[module] || 0;
    });

    const estimatedSizeElement = document.getElementById('estimatedSize');
    if (totalSize > 0) {
        estimatedSizeElement.textContent = '~' + totalSize.toFixed(1) + ' MB (uncompressed)';
    } else {
        estimatedSizeElement.textContent = 'Select modules to see estimate';
    }
}

function createBackup(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const selectedModules = Array.from(document.querySelectorAll('input[name="modules"]:checked'))
        .map(cb => cb.value);

    if (selectedModules.length === 0) {
        showError('Please select at least one module to backup.');
        return;
    }

    const backupData = {
        backup_name: formData.get('backup_name'),
        modules: selectedModules,
        options: Array.from(document.querySelectorAll('input[name="options"]:checked')).map(cb => cb.value)
    };

    // Get backup name for confirmation
    const backupName = backupData.backup_name || generateBackupName(selectedModules);
    const moduleNames = selectedModules.map(m => formatModuleName(m)).join(', ');

    // Use confirmation modal for backup creation
    showConfirmationModal({
        title: 'Are you sure you want to create a backup named "' + backupName + '" for the following modules: ' + moduleNames + '? This process may take several minutes.',
        type: 'info',
        confirmText: 'Yes, create backup',
        cancelText: 'Cancel',
        onConfirm: function() {
            // Close the backup modal and start the backup process
            closeBackupModal();

            // Show progress immediately
            const progressContainer = document.getElementById('backupProgress');
            progressContainer.classList.remove('hidden');
            document.getElementById('progressStatus').textContent = 'Starting backup...';
            document.getElementById('progressBar').style.width = '0%';
            document.getElementById('progressText').textContent = '0%';
            document.getElementById('progressETA').textContent = 'Calculating...';

            // Start progress simulation
            simulateBackupProgress();

            // Send request to server
            fetch(window.cloudBackupRoutes.store, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(backupData)
            })
            .then(response => response.json())
            .then(data => {
                // Stop progress simulation
                if (currentBackupProgress) {
                    clearInterval(currentBackupProgress);
                    currentBackupProgress = null;
                }

                if (data.success) {
                    // Complete the progress bar
                    document.getElementById('progressBar').style.width = '100%';
                    document.getElementById('progressText').textContent = '100%';
                    document.getElementById('progressStatus').textContent = 'Backup completed!';
                    document.getElementById('progressETA').textContent = 'Done';

                    // Hide progress after a short delay to show completion
                    setTimeout(() => {
                        progressContainer.classList.add('hidden');
                    }, 2000);

                    // Reload data to show the new backup
                    loadBackupData();

                    // Show success message
                    showSuccess('Backup created successfully!');
                } else {
                    progressContainer.classList.add('hidden');
                    showError(data.message || 'Failed to create backup');
                }
            })
            .catch(error => {
                // Stop progress simulation
                if (currentBackupProgress) {
                    clearInterval(currentBackupProgress);
                    currentBackupProgress = null;
                }

                progressContainer.classList.add('hidden');
                console.error('Error:', error);
                showError('Failed to create backup: ' + error.message);
            });
        }
    });
}

function generateBackupName(modules) {
    const timestamp = new Date().toISOString().slice(0, 16).replace('T', '_');
    if (modules.length === Object.keys(moduleSizes).length) {
        return 'Full_Backup_' + timestamp;
    } else {
        return 'Selective_Backup_' + timestamp;
    }
}

function simulateBackupProgress() {
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressStatus = document.getElementById('progressStatus');
    const progressETA = document.getElementById('progressETA');

    let progress = 10;
    const steps = [
        'Preparing backup...',
        'Connecting to database...',
        'Exporting data...',
        'Compressing backup...',
        'Encrypting backup...',
        'Uploading to Google Drive...',
        'Finalizing...'
    ];
    let currentStep = 0;

    currentBackupProgress = setInterval(() => {
        // Only animate progress if request hasn't completed yet
        if (currentBackupProgress && progress < 85) {
            progress += Math.random() * 8 + 2;
            if (progress > 85) progress = 85;

            progressBar.style.width = progress + '%';
            progressText.textContent = Math.round(progress) + '%';

            // Update status step
            const stepIndex = Math.floor((progress / 85) * steps.length);
            if (stepIndex < steps.length && stepIndex !== currentStep) {
                currentStep = stepIndex;
                progressStatus.textContent = steps[currentStep];
            }

            const remainingTime = Math.max(1, Math.round((100 - progress) / 8));
            progressETA.textContent = remainingTime > 0 ? '~' + remainingTime + 's remaining' : 'Almost done...';
        }
    }, 800);
}

function cancelBackup() {
    if (currentBackupProgress) {
        // Use confirmation modal for canceling backup
        showConfirmationModal({
            title: 'Are you sure you want to cancel the current backup process? Any progress will be lost.',
            type: 'warning',
            confirmText: 'Yes, cancel backup',
            cancelText: 'Continue backup',
            onConfirm: function() {
                clearInterval(currentBackupProgress);
                currentBackupProgress = null;
                document.getElementById('backupProgress').classList.add('hidden');
                showWarning('Backup cancelled by user.');
            }
        });
    }
}

function populateRestoreBackups() {
    const backupList = document.getElementById('backupList');
    // Filter backups to show only:
    // 1. Completed backups
    // 2. With valid size (not '0 B')
    // 3. Available locally OR downloadable from Google Drive
    const restorableBackups = backups.filter(b => {
        const isCompleted = b.status === 'completed';
        const hasValidSize = b.size && b.size !== '0 B';
        // A backup is restorable if it has either a local file path OR a Google Drive file ID
        const isRestorable = (b.storage_location === 'local') ||
                            (b.storage_location === 'google_drive' && b.google_drive_file_id);

        return isCompleted && hasValidSize && isRestorable;
    });

    if (restorableBackups.length === 0) {
        backupList.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-circle text-gray-400 text-2xl mb-2"></i>
                <p class="text-gray-600">No restorable backups available.</p>
                <p class="text-sm text-gray-500 mt-1">Create a backup or ensure Google Drive connection for cloud backups.</p>
            </div>
        `;
        return;
    }

    backupList.innerHTML = restorableBackups.map(backup => `
        <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200">
            <input type="radio" name="restore_backup" value="${backup.id}" class="mt-1 w-5 h-5 text-primary">
            <div class="flex-1">
                <div class="font-medium text-gray-900 flex items-center">
                    ${backup.name}
                    ${backup.storage_location === 'google_drive' && backup.google_drive_file_id ?
                        '<i class="fas fa-cloud ml-2 text-blue-500" title="Available from Google Drive"></i>' :
                        '<i class="fas fa-hdd ml-2 text-green-500" title="Available locally"></i>'
                    }
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full ${backup.storage_location === 'google_drive' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}">
                        ${backup.storage_location === 'google_drive' ? 'Cloud' : 'Local'}
                    </span>
                </div>
                <div class="text-sm text-gray-600">
                    ${backup.modules.map(m => formatModuleName(m)).join(', ')}
                </div>
                <div class="text-xs mt-1 text-gray-500">
                    ${formatDate(backup.created_at)} • ${backup.size} • SQL DUMP
                    ${backup.encrypted ? ' • <i class="fas fa-lock text-green-600"></i> Encrypted' : ''}
                    ${backup.storage_location === 'google_drive' ? ' • Can be downloaded for restore' : ' • Ready for immediate restore'}
                </div>
            </div>
        </label>
    `).join('');
}

function restoreData(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const backupId = parseInt(formData.get('restore_backup'));

    if (!backupId) {
        showError('Please select a backup to restore.');
        return;
    }

    const backup = backups.find(b => b.id === backupId);
    if (!backup) {
        showError('Selected backup not found.');
        return;
    }

    const restoreData = {
        backup_id: backupId,
        restore_options: Array.from(document.querySelectorAll('input[name="restore_options"]:checked')).map(cb => cb.value),
        confirm_restore: document.querySelector('input[name="confirm_restore"]').checked ? 1 : 0
    };

    // Additional confirmation for critical restore operation
    const moduleNames = backup.modules.map(m => formatModuleName(m)).join(', ');
    const restoreOptions = restoreData.restore_options.length > 0 ?
        ' with options: ' + restoreData.restore_options.join(', ') : '';

    showConfirmationModal({
        title: 'CRITICAL OPERATION: Are you absolutely sure you want to restore data from "' + backup.name + '" (' + moduleNames + ')' + restoreOptions + '? This will overwrite existing data and cannot be undone without another backup.',
        type: 'danger',
        confirmText: 'Yes, restore data',
        cancelText: 'Cancel restore',
        onConfirm: function() {
            closeRestoreModal();

            // Prevent duplicate requests
            if (window.restoreInProgress) {
                showError('Restore already in progress. Please wait...');
                return;
            }
            window.restoreInProgress = true;

            // Show progress modal similar to backup progress
            showRestoreProgressModal(backup.name);

            fetch(window.cloudBackupRoutes.restore, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(restoreData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.restore_id) {
                    // Start tracking restore progress
                    trackRestoreProgress(data.restore_id);
                } else {
                    window.restoreInProgress = false;
                    hideRestoreProgressModal();
                    showError(data.message || 'Failed to start restore');
                }
            })
            .catch(error => {
                window.restoreInProgress = false;
                hideRestoreProgressModal();
                console.error('Error:', error);
                showError('Failed to start restore: ' + error.message);
            });
        }
    });
}

function restoreFromBackup(backupId) {
    // Find the backup and populate the restore form
    const backup = backups.find(b => b.id === backupId);
    if (!backup) {
        showError('Selected backup not found.');
        return;
    }

    // Open restore modal and pre-select the backup
    openRestoreModal();
    
    // Wait for modal to open and backup list to populate
    setTimeout(() => {
        const backupRadio = document.querySelector('input[name="restore_backup"][value="' + backupId + '"]');
        if (backupRadio) {
            backupRadio.checked = true;
        }
    }, 100);
}

// Confirmation Modal Function
function showConfirmationModal(options) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('confirmationModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'confirmationModal';
        modal.className = 'hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        modal.innerHTML = '<div class="bg-white rounded-xl shadow-xl max-w-md w-full">' +
            '<div class="p-6">' +
                '<div class="flex items-center mb-4">' +
                    '<div class="flex-shrink-0">' +
                        '<i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>' +
                    '</div>' +
                    '<div class="ml-3">' +
                        '<h3 class="text-lg font-medium text-gray-900" id="confirmationTitle">Confirm Action</h3>' +
                    '</div>' +
                '</div>' +
                '<div class="mb-6">' +
                    '<p class="text-sm text-gray-600" id="confirmationMessage">Are you sure you want to proceed?</p>' +
                '</div>' +
                '<div class="flex justify-end space-x-3">' +
                    '<button type="button" id="confirmationCancel" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>' +
                    '<button type="button" id="confirmationConfirm" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Confirm</button>' +
                '</div>' +
            '</div>' +
        '</div>';
        document.body.appendChild(modal);

        // Add event listeners
        document.getElementById('confirmationCancel').addEventListener('click', function() {
            modal.classList.add('hidden');
        });

        document.getElementById('confirmationConfirm').addEventListener('click', function() {
            modal.classList.add('hidden');
            if (options.onConfirm) {
                options.onConfirm();
            }
        });
    }

    // Update modal content
    document.getElementById('confirmationTitle').textContent = options.title || 'Confirm Action';
    document.getElementById('confirmationMessage').textContent = options.message || 'Are you sure you want to proceed?';
    
    var confirmBtn = document.getElementById('confirmationConfirm');
    var cancelBtn = document.getElementById('confirmationCancel');
    
    confirmBtn.textContent = options.confirmText || 'Confirm';
    cancelBtn.textContent = options.cancelText || 'Cancel';
    
    // Update button colors based on type
    if (options.type === 'danger') {
        confirmBtn.className = 'px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors';
    } else if (options.type === 'info') {
        confirmBtn.className = 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors';
    } else {
        confirmBtn.className = 'px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors';
    }

    // Show modal
    modal.classList.remove('hidden');
}

function downloadBackup(id) {
    const backup = backups.find(b => b.id === id);
    if (backup) {
        showSuccess('Downloading "' + backup.name + '"...');
        window.open(window.cloudBackupRoutes.download.replace(':id', id), '_blank');
    }
}

function updateStats() {
    const totalBackups = backups.length;
    const successfulBackups = backups.filter(b => b.status === 'completed').length;
    const lastBackup = backups.length > 0 ? backups[0] : null;

    document.getElementById('totalBackups').textContent = totalBackups;
    document.getElementById('successfulBackups').textContent = successfulBackups;

    if (lastBackup) {
        const lastBackupTime = new Date(lastBackup.created_at);
        const now = new Date();
        const diffHours = Math.floor((now - lastBackupTime) / (1000 * 60 * 60));
        document.getElementById('lastBackup').textContent = diffHours + 'h ago';
    }
}

function updateStatsFromServer(stats) {
    // Only update stats if elements exist (they're commented out in the current UI)
    const totalElement = document.getElementById('totalBackups');
    const successfulElement = document.getElementById('successfulBackups');
    const lastElement = document.getElementById('lastBackup');
    const storageElement = document.getElementById('storageUsed');

    if (stats.total_backups !== undefined && totalElement) {
        totalElement.textContent = stats.total_backups;
    }
    if (stats.successful_backups !== undefined && successfulElement) {
        successfulElement.textContent = stats.successful_backups;
    }
    if (stats.last_backup !== undefined && lastElement) {
        lastElement.textContent = stats.last_backup;
    }
    if (stats.storage_used !== undefined && storageElement) {
        storageElement.textContent = stats.storage_used;
    }
}

// Use flowbite alert system for all notifications
function showSuccess(message) {
    // Use the global healthcare alert system
    if (window.healthcareAlert) {
        window.healthcareAlert.success(message);
    } else {
        console.log('Success:', message);
    }
}

function showError(message) {
    // Use the global healthcare alert system
    if (window.healthcareAlert) {
        window.healthcareAlert.error(message);
    } else {
        console.error('Error:', message);
    }
}

function showInfo(message) {
    // Use the global healthcare alert system
    if (window.healthcareAlert) {
        window.healthcareAlert.info(message);
    } else {
        console.log('Info:', message);
    }
}

function showWarning(message) {
    // Use the global healthcare alert system
    if (window.healthcareAlert) {
        window.healthcareAlert.warning(message);
    } else {
        console.warn('Warning:', message);
    }
}

// Function to hide all messages (no longer needed with centered overlays)
function hideAllMessages() {
    // Remove any existing alert overlays from healthcare alert system
    if (window.healthcareAlert) {
        window.healthcareAlert.removeExisting();
    }
}

// Load real database record counts
function loadRealDataCounts() {
    // Extract counts and sizes from server data - will be populated from blade template
    moduleCounts = {};
    moduleSizes = {};

    // Module info will be provided via inline script in blade template
    if (window.moduleInfo) {
        Object.keys(window.moduleInfo).forEach(module => {
            moduleCounts[module] = window.moduleInfo[module].record_count || 0;
            moduleSizes[module] = window.moduleInfo[module].size_mb || 0;
        });

        // Update UI with real counts
        const updateModuleDisplay = (moduleKey, elementId) => {
            const info = window.moduleInfo[moduleKey];
            if (info && document.getElementById(elementId)) {
                const count = info.record_count || 0;
                const size = info.size_formatted || '0 B';
                document.getElementById(elementId).textContent = count + ' records • ' + size;
            }
        };

        updateModuleDisplay('patient_records', 'patient-records-count');
        updateModuleDisplay('prenatal_monitoring', 'prenatal-records-count');
        updateModuleDisplay('child_records', 'child-records-count');
        updateModuleDisplay('immunization_records', 'immunization-records-count');
        updateModuleDisplay('vaccine_management', 'vaccine-records-count');

        console.log('Module info loaded:', window.moduleInfo);
        console.log('Module counts:', moduleCounts);
        console.log('Module sizes:', moduleSizes);
    }
}

// Format size for display
function formatSize(sizeInMB) {
    if (sizeInMB < 0.001) {
        return '< 1 KB';
    } else if (sizeInMB < 1) {
        return Math.round(sizeInMB * 1024) + ' KB';
    } else {
        return sizeInMB.toFixed(1) + ' MB';
    }
}

// Sync Google Drive backups with database
function syncGoogleDrive() {
    // Show loading message
    showInfo('Syncing with Google Drive...');

    fetch(window.cloudBackupRoutes.sync, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Successfully synced ' + (data.synced_count || 0) + ' backups from Google Drive');
            // Reload backup data to show newly synced backups
            loadBackupData();
        } else {
            showError(data.message || 'Failed to sync with Google Drive');
        }
    })
    .catch(error => {
        console.error('Error syncing Google Drive:', error);
        showError('Failed to sync with Google Drive: ' + error.message);
    });
}

// Click outside to close modals
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
        if (e.target.id === 'backupModal') {
            closeBackupModal();
        } else if (e.target.id === 'restoreModal') {
            closeRestoreModal();
        }
    }
});

// Restore Progress Functions
let restoreProgressInterval = null;

function showRestoreProgressModal(backupName) {
    const progressContainer = document.getElementById('restoreProgress');
    progressContainer.classList.remove('hidden');
    document.getElementById('restoreProgressStatus').textContent = 'Initializing restore...';
    document.getElementById('restoreProgressBar').style.width = '0%';
    document.getElementById('restoreProgressText').textContent = '0%';
    document.getElementById('restoreBackupName').textContent = 'Restoring from: ' + backupName;
}

function hideRestoreProgressModal() {
    const progressContainer = document.getElementById('restoreProgress');
    progressContainer.classList.add('hidden');
    if (restoreProgressInterval) {
        clearInterval(restoreProgressInterval);
        restoreProgressInterval = null;
    }
}

function trackRestoreProgress(restoreId) {
    const progressBar = document.getElementById('restoreProgressBar');
    const progressText = document.getElementById('restoreProgressText');
    const progressStatus = document.getElementById('restoreProgressStatus');

    // Show initial status
    showInfo('Restore operation started. Tracking progress...');

    // Poll for progress updates
    restoreProgressInterval = setInterval(() => {
        fetch(window.cloudBackupRoutes.restoreProgress.replace(':id', restoreId))
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status + ': ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                // Update progress
                const progress = data.progress || 0;
                progressBar.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';
                progressStatus.textContent = data.current_step || 'Processing...';

                // Check if completed
                if (data.status === 'completed') {
                    clearInterval(restoreProgressInterval);
                    restoreProgressInterval = null;
                    window.restoreInProgress = false;

                    // Show completion
                    progressBar.classList.remove('bg-green-600');
                    progressBar.classList.add('bg-green-500');
                    progressStatus.textContent = 'Restore completed successfully!';

                    // Show success message and reload data
                    showSuccess(data.message || 'Data restored successfully!');
                    
                    // Hide progress after delay and reload data
                    setTimeout(() => {
                        hideRestoreProgressModal();
                        loadBackupData(); // Reload data to show updated restore history
                    }, 2000);
                } else if (data.status === 'failed') {
                    clearInterval(restoreProgressInterval);
                    restoreProgressInterval = null;
                    window.restoreInProgress = false;

                    // Show error
                    progressBar.classList.remove('bg-green-600');
                    progressBar.classList.add('bg-red-600');
                    progressStatus.textContent = 'Restore failed';

                    // Show error message
                    showError(data.error || 'Restore failed. Please try again.');
                    
                    // Hide progress after delay
                    setTimeout(() => {
                        hideRestoreProgressModal();
                    }, 3000);
                } else if (data.status === 'in_progress') {
                    // Continue polling - restore is running
                    console.log('Restore progress:', progress + '% - ' + data.current_step);
                }
            })
            .catch(error => {
                console.error('Error tracking restore progress:', error);
                clearInterval(restoreProgressInterval);
                restoreProgressInterval = null;
                window.restoreInProgress = false;
                
                // Update UI to show error
                progressStatus.textContent = 'Error tracking progress';
                progressBar.classList.remove('bg-green-600');
                progressBar.classList.add('bg-red-600');
                
                showError('Failed to track restore progress: ' + error.message);
                
                // Hide progress after delay
                setTimeout(() => {
                    hideRestoreProgressModal();
                }, 3000);
            });
    }, 1000); // Poll every second
}
