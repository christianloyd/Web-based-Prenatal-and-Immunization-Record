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
    return fetch('/admin/cloudbackup/data')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
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
            } else {
                backups = data.backups || [];
                restores = data.restores || [];
                filteredBackups = [...backups];
                filteredRestores = [...restores];

                // Render appropriate data based on active tab
                if (activeTab === 'backup') {
                    renderBackups();
                } else {
                    renderRestores();
                }

                return data; // Return data for chaining
            }
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
                        <button onclick="deleteBackup(${backup.id})" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs transition-colors w-full sm:w-auto">
                            <i class="fas fa-trash-alt mr-1"></i><span class="hidden sm:inline">Delete</span>
                        </button>
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
                    <div class="font-medium text-gray-900 text-sm sm:text-base">${restore.backup_name || 'Unknown'}</div>
                    <div class="text-xs sm:text-sm text-gray-500">${restore.modules || 'All modules'}</div>
                    <div class="sm:hidden text-xs text-gray-500 mt-1">${restore.options || 'Standard'} • ${restore.size}</div>
                    <div class="md:hidden text-xs text-gray-500 mt-1">${formatDate(restore.created_at)}</div>
                </td>
                <td class="px-3 sm:px-6 py-4 hidden sm:table-cell text-sm text-gray-900">${restore.backup_name || 'N/A'}</td>
                <td class="px-3 sm:px-6 py-4 hidden md:table-cell text-sm text-gray-900">${restore.options || 'Standard'}</td>
                <td class="px-3 sm:px-6 py-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${statusColor.bg} ${statusColor.text}">
                        <i class="fas ${statusColor.icon} mr-1"></i>
                        <span class="hidden sm:inline">${restore.status.charAt(0).toUpperCase() + restore.status.slice(1)}</span>
                        <span class="sm:hidden">${restore.status.charAt(0).toUpperCase()}</span>
                    </span>
                    ${restore.error ? `<div class="text-xs text-red-600 mt-1">${restore.error}</div>` : ''}
                </td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 hidden lg:table-cell">${formatDate(restore.created_at)}</td>
                <td class="px-3 sm:px-6 py-4 text-sm text-gray-900 hidden lg:table-cell">${restore.user_name || 'System'}</td>
            </tr>
        `;
    }).join('');
}

// Tab switching
function switchTab(tab) {
    activeTab = tab;

    // Update tab buttons
    document.getElementById('backupHistoryTab').classList.toggle('border-secondary', tab === 'backup');
    document.getElementById('backupHistoryTab').classList.toggle('text-secondary', tab === 'backup');
    document.getElementById('backupHistoryTab').classList.toggle('border-transparent', tab !== 'backup');
    document.getElementById('backupHistoryTab').classList.toggle('text-gray-500', tab !== 'backup');

    document.getElementById('restoreHistoryTab').classList.toggle('border-secondary', tab === 'restore');
    document.getElementById('restoreHistoryTab').classList.toggle('text-secondary', tab === 'restore');
    document.getElementById('restoreHistoryTab').classList.toggle('border-transparent', tab !== 'restore');
    document.getElementById('restoreHistoryTab').classList.toggle('text-gray-500', tab !== 'restore');

    // Show/hide content
    document.getElementById('backupHistoryContent').classList.toggle('hidden', tab !== 'backup');
    document.getElementById('restoreHistoryContent').classList.toggle('hidden', tab !== 'restore');

    // Render appropriate data
    if (tab === 'backup') {
        renderBackups();
    } else {
        renderRestores();
    }
}

// Filter functions
function filterBackups() {
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;

    filteredBackups = backups.filter(backup => {
        return (typeFilter === '' || backup.type === typeFilter) &&
               (statusFilter === '' || backup.status === statusFilter);
    });

    renderBackups();
}

function filterRestoreBackups() {
    const statusFilter = document.getElementById('filterRestoreStatus').value;

    filteredRestores = restores.filter(restore => {
        return statusFilter === '' || restore.status === statusFilter;
    });

    renderRestores();
}

// Modal functions
function openBackupModal() {
    document.getElementById('backupModal').classList.remove('hidden');
    document.getElementById('backupModal').classList.add('flex');
    updateEstimatedSize();
}

function closeBackupModal() {
    document.getElementById('backupModal').classList.add('hidden');
    document.getElementById('backupModal').classList.remove('flex');
    resetBackupForm();
}

function openRestoreModal() {
    document.getElementById('restoreModal').classList.remove('hidden');
    document.getElementById('restoreModal').classList.add('flex');
    loadBackupList();
}

function closeRestoreModal() {
    document.getElementById('restoreModal').classList.add('hidden');
    document.getElementById('restoreModal').classList.remove('flex');
    document.getElementById('restoreForm').reset();
}

// Backup form functions
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const moduleCheckboxes = document.querySelectorAll('input[name="modules"]');

    moduleCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });

    updateEstimatedSize();
}

function updateEstimatedSize() {
    const selectedModules = Array.from(document.querySelectorAll('input[name="modules"]:checked')).map(cb => cb.value);
    let totalSize = 0;
    let totalRecords = 0;

    selectedModules.forEach(module => {
        if (moduleSizes[module]) {
            totalSize += moduleSizes[module];
        }
        if (moduleCounts[module]) {
            totalRecords += moduleCounts[module];
        }
    });

    const sizeText = formatSize(totalSize);
    const recordText = totalRecords.toLocaleString();

    document.getElementById('estimatedSize').textContent = `${recordText} records • ${sizeText}`;
}

function resetBackupForm() {
    document.getElementById('backupForm').reset();
    document.getElementById('selectAll').checked = false;
    updateEstimatedSize();
}

// Restore functions
function loadBackupList() {
    const backupList = document.getElementById('backupList');
    backupList.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading backups...</div>';

    // Filter only completed backups
    const completedBackups = backups.filter(backup => backup.status === 'completed');

    if (completedBackups.length === 0) {
        backupList.innerHTML = '<div class="text-center py-4 text-gray-500">No completed backups available for restore</div>';
        return;
    }

    backupList.innerHTML = completedBackups.map(backup => {
        const moduleNames = backup.modules.map(m => formatModuleName(m)).join(', ');
        return `
            <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200">
                <input type="radio" name="backup_id" value="${backup.id}" class="mt-1 w-5 h-5 text-primary" required>
                <div class="flex-1">
                    <div class="font-medium text-gray-900">${backup.name}</div>
                    <div class="text-sm text-gray-600">${moduleNames}</div>
                    <div class="text-xs text-gray-500 mt-1">${backup.size} • ${formatDate(backup.created_at)}</div>
                </div>
            </label>
        `;
    }).join('');
}

// Backup creation
function createBackup(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const selectedModules = Array.from(document.querySelectorAll('input[name="modules"]:checked')).map(cb => cb.value);
    const backupName = document.getElementById('backup_name').value;

    if (selectedModules.length === 0) {
        showError('Please select at least one module to backup');
        return;
    }

    // Update button state
    const submitBtn = document.getElementById('backupSubmitBtn');
    const submitText = document.getElementById('backupSubmitText');
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    submitText.textContent = 'Creating Backup...';

    // Show progress
    showBackupProgress();

    // Send request
    fetch('/admin/cloudbackup/store', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            modules: selectedModules,
            backup_name: backupName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            closeBackupModal();
            hideBackupProgress();
            loadBackupData();
        } else {
            showError(data.message || 'Failed to create backup');
            resetBackupButton();
            hideBackupProgress();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to create backup');
        resetBackupButton();
        hideBackupProgress();
    });
}

function showBackupProgress() {
    document.getElementById('backupProgress').classList.remove('hidden');
}

function hideBackupProgress() {
    document.getElementById('backupProgress').classList.add('hidden');
}

// Restore functions
function restoreData(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const backupId = formData.get('backup_id');

    if (!backupId) {
        showError('Please select a backup to restore');
        return;
    }

    if (!document.querySelector('input[name="confirm_restore"]:checked')) {
        showError('Please confirm that you understand the risks');
        return;
    }

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Restoring...';

    fetch('/admin/cloudbackup/restore', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            backup_id: backupId,
            options: Array.from(formData.getAll('restore_options'))
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            closeRestoreModal();
            loadBackupData();
        } else {
            showError(data.message || 'Failed to restore data');
        }
        submitBtn.disabled = false;
        submitBtn.textContent = 'Start Restore';
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to restore data');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Start Restore';
    });
}

// Utility functions
function getStatusColor(status) {
    switch (status) {
        case 'completed':
            return { bg: 'bg-green-100', text: 'text-green-800', icon: 'fa-check-circle' };
        case 'failed':
            return { bg: 'bg-red-100', text: 'text-red-800', icon: 'fa-times-circle' };
        case 'in-progress':
            return { bg: 'bg-blue-100', text: 'text-blue-800', icon: 'fa-spinner fa-spin' };
        default:
            return { bg: 'bg-gray-100', text: 'text-gray-800', icon: 'fa-question-circle' };
    }
}

function formatModuleName(module) {
    const names = {
        'patient_records': 'Patient Records',
        'prenatal_monitoring': 'Prenatal Monitoring',
        'child_records': 'Child Records',
        'immunization_records': 'Immunization Records',
        'vaccine_management': 'Vaccine Management'
    };
    return names[module] || module;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
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
    // Use server-side real module information
    const moduleInfo = JSON.parse(document.querySelector('[data-module-info]')?.getAttribute('data-module-info') || '{}');

    // Extract counts and sizes from server data
    moduleCounts = {};
    moduleSizes = {};

    Object.keys(moduleInfo).forEach(module => {
        moduleCounts[module] = moduleInfo[module].record_count || 0;
        moduleSizes[module] = moduleInfo[module].size_mb || 0;
    });

    // Update UI with real counts
    const updateModuleDisplay = (moduleKey, elementId) => {
        const info = moduleInfo[moduleKey];
        if (info && document.getElementById(elementId)) {
            const count = info.record_count || 0;
            const size = info.size_formatted || '0 B';
            document.getElementById(elementId).textContent = `${count} records • ${size}`;
        }
    };

    updateModuleDisplay('patient_records', 'patient-records-count');
    updateModuleDisplay('prenatal_monitoring', 'prenatal-records-count');
    updateModuleDisplay('child_records', 'child-records-count');
    updateModuleDisplay('immunization_records', 'immunization-records-count');
    updateModuleDisplay('vaccine_management', 'vaccine-records-count');

    console.log('Module info loaded:', moduleInfo);
    console.log('Module counts:', moduleCounts);
    console.log('Module sizes:', moduleSizes);
}

// Format size for display
function formatSize(sizeInMB) {
    if (sizeInMB < 0.001) {
        return '< 1 KB';
    } else if (sizeInMB < 1) {
        return `${Math.round(sizeInMB * 1024)} KB`;
    } else {
        return `${sizeInMB.toFixed(1)} MB`;
    }
}

// Reset backup button state
function resetBackupButton() {
    const submitBtn = document.getElementById('backupSubmitBtn');
    const submitText = document.getElementById('backupSubmitText');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        submitText.textContent = 'Start Backup';
    }
}

// Sync Google Drive backups
function syncGoogleDrive() {
    showInfo('Syncing Google Drive backups...');

    fetch('/admin/cloudbackup/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            loadBackupData(); // Reload backup data
        } else {
            showError(data.message || 'Failed to sync Google Drive');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to sync Google Drive');
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
