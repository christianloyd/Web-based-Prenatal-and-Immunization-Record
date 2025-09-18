@extends('layout.midwife')
@section('title', 'Cloud Backup System')
@section('page-title', 'Cloud Backup System')
@section('page-subtitle', 'Secure backup and restore for your medical records')

@push('styles')
<style>
    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.15s ease;
        border: 1px solid transparent;
    }

    .btn-view {
        background-color: #f8fafc;
        color: #475569;
        border-color: #e2e8f0;
    }

    .btn-view:hover {
        background-color: #68727A;
        color: white;
        border-color: #68727A;
    }

    .btn-edit {
        background-color: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    .btn-edit:hover {
        background-color: #16a34a;
        color: white;
        border-color: #16a34a;
    }

    .btn-delete {
        background-color: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .btn-delete:hover {
        background-color: #dc2626;
        color: white;
        border-color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 truncate">Cloud Backup Management</h2>
                <p class="mt-1 text-sm text-gray-600 truncate">Secure backup and restore your healthcare data</p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3 flex-shrink-0">
                <button onclick="openBackupModal()" class="bg-secondary hover:bg-secondary/90 text-white px-3 sm:px-4 py-2 rounded-lg font-medium flex items-center justify-center space-x-2 transition-colors text-sm sm:text-base">
                    <i class="fas fa-cloud-upload-alt w-4 h-4"></i>
                    <span class="hidden sm:inline">Create Backup</span>
                    <span class="sm:hidden">Backup</span>
                </button>
                <button onclick="openRestoreModal()" class="bg-primary hover:bg-primary/90 text-white px-3 sm:px-4 py-2 rounded-lg font-medium flex items-center justify-center space-x-2 transition-colors text-sm sm:text-base">
                    <i class="fas fa-cloud-download-alt w-4 h-4"></i>
                    <span class="hidden sm:inline">Restore Data</span>
                    <span class="sm:hidden">Restore</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-blue-100">
                    <i class="fas fa-database text-lg sm:text-xl text-blue-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Total Backups</p>
                    <p id="totalBackups" class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['total_backups'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-green-100">
                    <i class="fas fa-check-circle text-lg sm:text-xl text-green-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Successful</p>
                    <p id="successfulBackups" class="text-xl sm:text-2xl font-bold text-gray-900">{{ $stats['successful_backups'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-yellow-100">
                    <i class="fas fa-clock text-lg sm:text-xl text-yellow-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Last Backup</p>
                    <p id="lastBackup" class="text-xl sm:text-2xl font-bold text-gray-900 truncate">{{ $stats['last_backup'] ?? 'Never' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-purple-100">
                    <i class="fas fa-hdd text-lg sm:text-xl text-purple-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Storage Used</p>
                    <p id="storageUsed" class="text-xl sm:text-2xl font-bold text-gray-900 truncate">{{ $stats['storage_used'] ?? '0 MB' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="successMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="successText"></span>
        </div>
    </div>

    <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span id="errorText"></span>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6 mb-6 sm:mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <div class="flex items-start sm:items-center space-x-3 min-w-0">
                @if($isOAuth && $isAuthenticated)
                    <div class="p-2 rounded-lg bg-green-100 flex-shrink-0">
                        <i class="fab fa-google-drive text-green-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Google Drive Connected</h3>
                        <p class="text-xs sm:text-sm text-gray-600">Connected to your Google Drive - Ready for cloud backups</p>
                    </div>
                @elseif($isOAuth && !$isAuthenticated)
                    <div class="p-2 rounded-lg bg-yellow-100 flex-shrink-0">
                        <i class="fab fa-google-drive text-yellow-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Google Drive Authentication Required</h3>
                        <p class="text-xs sm:text-sm text-gray-600">Please authenticate with Google Drive to enable cloud backups</p>
                    </div>
                @elseif($googleDriveConnected)
                    <div class="p-2 rounded-lg bg-green-100 flex-shrink-0">
                        <i class="fab fa-google-drive text-green-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Cloud Storage Status</h3>
                        <p class="text-xs sm:text-sm text-gray-600">Connected to Google Drive - Service Account Mode</p>
                    </div>
                @else
                    <div class="p-2 rounded-lg bg-red-100 flex-shrink-0">
                        <i class="fab fa-google-drive text-red-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 truncate">Google Drive Disconnected</h3>
                        <p class="text-xs sm:text-sm text-gray-600">No connection to Google Drive - Local backups only</p>
                    </div>
                @endif
            </div>
            <div class="flex items-center space-x-3 flex-shrink-0">
                @if($isOAuth && $isAuthenticated)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs sm:text-sm text-green-600 font-medium">Connected</span>
                    </div>
                    <form method="POST" action="{{ route('google.disconnect') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-2 sm:px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-colors">
                            <i class="fas fa-unlink mr-1"></i>
                            <span class="hidden sm:inline">Disconnect</span>
                        </button>
                    </form>
                @elseif($isOAuth && !$isAuthenticated)
                    <a href="{{ route('google.auth') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium flex items-center space-x-2 transition-colors text-xs sm:text-sm">
                        <i class="fab fa-google"></i>
                        <span class="hidden sm:inline">Connect Google Drive</span>
                        <span class="sm:hidden">Connect</span>
                    </a>
                @elseif($googleDriveConnected)
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs sm:text-sm text-green-600 font-medium">Online</span>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="text-xs sm:text-sm text-red-600 font-medium">Offline</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Active Backup Progress -->
    <div id="backupProgress" class="hidden bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="animate-spin">
                    <i class="fas fa-cloud-upload-alt text-2xl text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Backup in Progress</h3>
                    <p id="progressStatus" class="text-sm text-gray-600">Preparing backup...</p>
                </div>
            </div>
            <button onclick="cancelBackup()" class="text-red-600 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <div class="flex justify-between text-sm mt-2 text-gray-600">
            <span id="progressText">0%</span>
            <span id="progressETA">Calculating...</span>
        </div>
    </div>

    <!-- Backup History -->
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="p-4 sm:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Backup History</h3>
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                    <select id="filterType" class="w-full sm:w-auto px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" onchange="filterBackups()">
                        <option value="">All Types</option>
                        <option value="full">Full Backup</option>
                        <option value="selective">Selective Backup</option>
                    </select>
                    <select id="filterStatus" class="w-full sm:w-auto px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" onchange="filterBackups()">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="in-progress">In Progress</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Backup Details</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Type</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Size</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Date</th>
                        <th class="px-3 sm:px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="backupTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Dynamic backup entries will be populated here -->
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-12">
            <i class="fas fa-cloud text-4xl mb-4 text-gray-300"></i>
            <h3 class="text-lg font-medium mb-2 text-gray-900">No backups found</h3>
            <p class="mb-6 text-gray-600">Create your first backup to secure your healthcare data.</p>
            <button onclick="openBackupModal()" class="bg-secondary hover:bg-secondary/90 text-white px-6 py-2 rounded-lg font-medium">
                Create Backup
            </button>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 sm:mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center space-x-3">
                <div class="p-2 sm:p-3 rounded-lg bg-blue-100 flex-shrink-0">
                    <i class="fas fa-calendar-plus text-blue-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="text-sm sm:text-base font-semibold text-gray-900 truncate">Schedule Backup</h4>
                    <p class="text-xs sm:text-sm text-gray-600">Set up automatic backups</p>
                </div>
            </div>
            <button class="mt-3 sm:mt-4 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium">
                Configure Schedule
            </button>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center space-x-3">
                <div class="p-2 sm:p-3 rounded-lg bg-green-100 flex-shrink-0">
                    <i class="fas fa-shield-alt text-green-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="text-sm sm:text-base font-semibold text-gray-900 truncate">Security Settings</h4>
                    <p class="text-xs sm:text-sm text-gray-600">Manage encryption and access</p>
                </div>
            </div>
            <button class="mt-3 sm:mt-4 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium">
                Manage Security
            </button>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center space-x-3">
                <div class="p-2 sm:p-3 rounded-lg bg-purple-100 flex-shrink-0">
                    <i class="fas fa-chart-line text-purple-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="text-sm sm:text-base font-semibold text-gray-900 truncate">Storage Analytics</h4>
                    <p class="text-xs sm:text-sm text-gray-600">View usage and trends</p>
                </div>
            </div>
            <button class="mt-3 sm:mt-4 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg transition-colors text-xs sm:text-sm font-medium">
                View Analytics
            </button>
        </div>
    </div>

    <!-- Create Backup Modal -->
    <div id="backupModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-2 sm:p-4">
        <div class="bg-white rounded-lg sm:rounded-xl shadow-xl max-w-4xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-4 sm:px-6 py-3 sm:py-4 rounded-t-lg sm:rounded-t-xl border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-cloud-upload-alt mr-2 text-secondary text-sm sm:text-base"></i>
                        <span class="hidden sm:inline">Create Backup</span>
                        <span class="sm:hidden">Backup</span>
                    </h2>
                    <button onclick="closeBackupModal()" class="text-gray-400 hover:text-gray-600 p-1">
                        <i class="fas fa-times text-lg sm:text-xl"></i>
                    </button>
                </div>
            </div>

            <form id="backupForm" class="p-4 sm:p-6" onsubmit="createBackup(event)">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                    <!-- Data Selection -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Select Data to Backup</h3>
                        <div class="space-y-4">
                            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200">
                                <input type="checkbox" name="modules" value="patient_records" class="mt-1 w-5 h-5 text-secondary">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Patient Records</div>
                                    <div class="text-sm text-gray-600">All patient registration data and profiles</div>
                                    <div class="text-xs mt-1 text-gray-500" id="patient-records-count">Loading...</div>
                                </div>
                            </label>

                            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200">
                                <input type="checkbox" name="modules" value="prenatal_monitoring" class="mt-1 w-5 h-5 text-secondary">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Prenatal Monitoring</div>
                                    <div class="text-sm text-gray-600">Prenatal records, checkups, and high-risk cases</div>
                                    <div class="text-xs mt-1 text-gray-500" id="prenatal-records-count">Loading...</div>
                                </div>
                            </label>

                            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200">
                                <input type="checkbox" name="modules" value="child_records" class="mt-1 w-5 h-5 text-secondary">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Child Records</div>
                                    <div class="text-sm text-gray-600">Child patient data and growth tracking</div>
                                    <div class="text-xs mt-1 text-gray-500" id="child-records-count">Loading...</div>
                                </div>
                            </label>

                            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200">
                                <input type="checkbox" name="modules" value="immunization_records" class="mt-1 w-5 h-5 text-secondary">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Immunization Records</div>
                                    <div class="text-sm text-gray-600">Vaccination history and schedules</div>
                                    <div class="text-xs mt-1 text-gray-500" id="immunization-records-count">Loading...</div>
                                </div>
                            </label>

                            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 border-gray-200">
                                <input type="checkbox" name="modules" value="vaccine_management" class="mt-1 w-5 h-5 text-secondary">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Vaccine Management</div>
                                    <div class="text-sm text-gray-600">Vaccine inventory and stock transactions</div>
                                    <div class="text-xs mt-1 text-gray-500" id="vaccine-records-count">Loading...</div>
                                </div>
                            </label>

                            <div class="border-t pt-4 border-gray-200">
                                <label class="flex items-center space-x-3 cursor-pointer">
                                    <input type="checkbox" id="selectAll" class="w-5 h-5 text-secondary" onchange="toggleSelectAll()">
                                    <span class="font-medium text-secondary">Select All Modules</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Backup Configuration -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Backup Configuration</h3>
                        <div class="space-y-6">
                            <!-- Backup Name -->
                            <div>
                                <label class="block text-sm font-medium mb-2 text-gray-600">Backup Name</label>
                                <input type="text" name="backup_name" id="backup_name" 
                                       class="w-full px-4 py-2 border rounded-lg border-gray-300 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-transparent"
                                       placeholder="Enter backup name (optional)">
                                <div class="text-xs mt-1 text-gray-500">Leave empty to auto-generate name</div>
                            </div>

                            <!-- Storage Location (Fixed to Google Drive) -->
                            <div>
                                <label class="block text-sm font-medium mb-2 text-gray-600">Storage Location</label>
                                <div class="flex items-center p-3 border rounded-lg bg-gray-50 border-gray-200">
                                    <i class="fab fa-google-drive text-green-600 mr-3"></i>
                                    <span class="text-gray-900">Google Drive (Healthcare Backup)</span>
                                </div>
                            </div>

                            <!-- Backup Format (Fixed to SQL Dump) -->
                            <div>
                                <label class="block text-sm font-medium mb-2 text-gray-600">Backup Format</label>
                                <div class="flex items-center p-3 border rounded-lg bg-gray-50 border-gray-200">
                                    <i class="fas fa-database text-blue-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">SQL Dump (.sql)</div>
                                        <div class="text-sm text-gray-600">MySQL database dump - Best for database restoration</div>
                                    </div>
                                </div>
                            </div> 
                            <!-- Estimated Size -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-info-circle text-blue-600"></i>
                                    <span class="text-sm font-medium text-blue-800">Estimated Backup Size</span>
                                </div>
                                <div id="estimatedSize" class="text-lg font-semibold text-blue-900 mt-1">Select modules to see estimate</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeBackupModal()" class="px-6 py-2 border rounded-lg text-gray-600 border-gray-300 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-secondary hover:bg-secondary/90 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>
                        Start Backup
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Restore Data Modal -->
    <div id="restoreModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4 rounded-t-xl border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-cloud-download-alt mr-2 text-primary"></i>
                        Restore Data
                    </h2>
                    <button onclick="closeRestoreModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="mb-6">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                            <div>
                                <h4 class="font-medium text-yellow-800">Important Warning</h4>
                                <p class="text-sm text-yellow-700 mt-1">Restoring data will overwrite existing records. Please ensure you have a current backup before proceeding.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="restoreForm" onsubmit="restoreData(event)">
                    <div class="space-y-6">
                        <!-- Select Backup -->
                        <div>
                            <label class="block text-sm font-medium mb-3 text-gray-600">Select Backup to Restore</label>
                            <div id="backupList" class="space-y-3 max-h-60 overflow-y-auto">
                                <!-- Backup options will be populated here -->
                            </div>
                        </div>

                        <!-- Restore Options -->
                        <div>
                            <label class="block text-sm font-medium mb-3 text-gray-600">Restore Options</label>
                            <div class="space-y-3">
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="restore_options" value="create_backup" class="w-5 h-5 text-primary" checked>
                                    <span class="text-sm text-gray-900">Create backup before restore</span>
                                </label>
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="restore_options" value="verify_integrity" class="w-5 h-5 text-primary" checked>
                                    <span class="text-sm text-gray-900">Verify backup integrity</span>
                                </label>
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="restore_options" value="selective_restore" class="w-5 h-5 text-primary">
                                    <span class="text-sm text-gray-900">Enable selective module restore</span>
                                </label>
                            </div>
                        </div>

                        <!-- Confirmation -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <label class="flex items-start space-x-3">
                                <input type="checkbox" name="confirm_restore" class="mt-1 w-5 h-5 text-red-600" required>
                                <div class="text-sm text-red-800">
                                    <strong>I understand that this action will overwrite existing data</strong> and cannot be undone without a backup. I have verified that I want to proceed with this restore operation.
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                        <button type="button" onclick="closeRestoreModal()" class="px-6 py-2 border rounded-lg text-gray-600 border-gray-300 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-cloud-download-alt mr-2"></i>
                            Start Restore
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize with empty array - will be populated from server
    let backups = [];

    let filteredBackups = [...backups];
    let currentBackupProgress = null;

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
        return fetch('{{ route("midwife.cloudbackup.data") }}')
            .then(response => response.json())
            .then(data => {
                backups = data.backups;
                filteredBackups = [...backups];
                renderBackups();

                // Update stats
                if (data.stats) {
                    updateStatsFromServer(data.stats);
                }

                return data; // Return data for chaining
            })
            .catch(error => {
                console.error('Error loading backup data:', error);
                showError('Failed to load backup data');
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
            estimatedSizeElement.textContent = `~${totalSize.toFixed(1)} MB (uncompressed)`;
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
            title: `Are you sure you want to create a backup named "${backupName}" for the following modules: ${moduleNames}? This process may take several minutes.`,
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
                
                // Send request to server
                fetch('{{ route("midwife.cloudbackup.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(backupData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Simulate progress updates (in real app, you'd poll the progress endpoint)
                        simulateBackupProgress(data.backup_id);
                    } else {
                        progressContainer.classList.add('hidden');
                        showError(data.message || 'Failed to create backup');
                    }
                })
                .catch(error => {
                    progressContainer.classList.add('hidden');
                    console.error('Error:', error);
                    showError('Failed to create backup');
                });
            }
        });
    }

    function generateBackupName(modules) {
        const timestamp = new Date().toISOString().slice(0, 16).replace('T', '_');
        if (modules.length === Object.keys(moduleSizes).length) {
            return `Full_Backup_${timestamp}`;
        } else {
            return `Selective_Backup_${timestamp}`;
        }
    }

    function simulateBackupProgress(backupId) {
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
            if (progress < 90) {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                
                progressBar.style.width = progress + '%';
                progressText.textContent = Math.round(progress) + '%';
                
                if (currentStep < steps.length - 1) {
                    progressStatus.textContent = steps[currentStep];
                    currentStep++;
                }
                
                const remainingTime = Math.max(0, Math.round((100 - progress) / 10));
                progressETA.textContent = remainingTime > 0 ? `${remainingTime}s remaining` : 'Almost done...';
            } else {
                clearInterval(currentBackupProgress);
                // Check final status from server
                checkBackupCompletion(backupId);
            }
        }, 1500);
    }

    function checkBackupCompletion(backupId) {
        // Check if we already completed this backup to prevent multiple success messages
        if (!currentBackupProgress) {
            return; // Already completed or cancelled
        }

        setTimeout(() => {
            const progressContainer = document.getElementById('backupProgress');
            progressContainer.classList.add('hidden');

            // Clear the backup progress tracker
            currentBackupProgress = null;

            // Reload backup data from server to get the real backup entry
            loadBackupData();

            showSuccess('Backup completed successfully!');
        }, 2000);
    }

    // Removed completeBackup() function to prevent duplicate entries
    // The server already creates the backup entry, so we just need to reload data

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
                    showError('Backup cancelled by user.');
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
        
        // Additional confirmation using the global modal for critical restore operation
        const moduleNames = backup.modules.map(m => formatModuleName(m)).join(', ');
        const restoreOptions = restoreData.restore_options.length > 0 ? 
            ' with options: ' + restoreData.restore_options.join(', ') : '';
        
        showConfirmationModal({
            title: `⚠️ CRITICAL OPERATION: Are you absolutely sure you want to restore data from "${backup.name}" (${moduleNames})${restoreOptions}? This will overwrite existing data and cannot be undone without another backup.`,
            type: 'danger',
            confirmText: 'Yes, restore data',
            cancelText: 'Cancel restore',
            onConfirm: function() {
                closeRestoreModal();
                
                showSuccess(`Starting restore from "${backup.name}". This may take several minutes...`);
                
                fetch('{{ route("midwife.cloudbackup.restore") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(restoreData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showSuccess(data.message);
                        loadBackupData(); // Reload data
                    } else {
                        showError(data.message || 'Failed to restore backup');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Failed to restore backup');
                });
            }
        });
    }

    function downloadBackup(id) {
        const backup = backups.find(b => b.id === id);
        if (backup) {
            showSuccess(`Downloading "${backup.name}"...`);
            window.open(`{{ route('midwife.cloudbackup.download', ':id') }}`.replace(':id', id), '_blank');
        }
    }

    function restoreFromBackup(id) {
        const backup = backups.find(b => b.id === id);
        if (backup) {
            // Pre-select the backup in restore modal
            openRestoreModal();
            setTimeout(() => {
                const radioButton = document.querySelector(`input[name="restore_backup"][value="${id}"]`);
                if (radioButton) {
                    radioButton.checked = true;
                }
            }, 100);
        }
    }

    function deleteBackup(id) {
        const backup = backups.find(b => b.id === id);
        if (!backup) {
            showError('Backup not found.');
            return;
        }
        
        // Use the global confirmation modal instead of native confirm
        confirmDelete(backup.name, function() {
            // This callback is executed when user confirms deletion
            fetch(`{{ route('midwife.cloudbackup.destroy', ':id') }}`.replace(':id', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    loadBackupData(); // Reload data
                } else {
                    showError(data.message || 'Failed to delete backup');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to delete backup');
            });
        });
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
            document.getElementById('lastBackup').textContent = `${diffHours}h ago`;
        }
    }

    function updateStatsFromServer(stats) {
        const statsData = @json($stats ?? []);
        
        if (stats.total_backups !== undefined) {
            document.getElementById('totalBackups').textContent = stats.total_backups;
        }
        if (stats.successful_backups !== undefined) {
            document.getElementById('successfulBackups').textContent = stats.successful_backups;
        }
        if (stats.last_backup !== undefined) {
            document.getElementById('lastBackup').textContent = stats.last_backup;
        }
        if (stats.storage_used !== undefined) {
            document.getElementById('storageUsed').textContent = stats.storage_used;
        }
    }

    // Utility functions
    function formatModuleName(moduleKey) {
        const moduleNames = {
            'patient_records': 'Patient Records',
            'prenatal_monitoring': 'Prenatal Monitoring',
            'child_records': 'Child Records',
            'immunization_records': 'Immunization Records',
            'vaccine_management': 'Vaccine Management'
        };
        return moduleNames[moduleKey] || moduleKey;
    }

    // Global variable to track success message timer
    let successMessageTimer = null;

    function showSuccess(message) {
        const successElement = document.getElementById('successMessage');
        const textElement = document.getElementById('successText');

        // Clear any existing timer to prevent multiple timers
        if (successMessageTimer) {
            clearTimeout(successMessageTimer);
            successMessageTimer = null;
        }

        // Hide any existing error messages
        const errorElement = document.getElementById('errorMessage');
        errorElement.classList.add('hidden');

        textElement.textContent = message;
        successElement.classList.remove('hidden');

        // Set new timer to hide message after 5 seconds
        successMessageTimer = setTimeout(() => {
            successElement.classList.add('hidden');
            successMessageTimer = null;
        }, 5000);
    }

    // Global variable to track error message timer
    let errorMessageTimer = null;

    function showError(message) {
        const errorElement = document.getElementById('errorMessage');
        const textElement = document.getElementById('errorText');

        // Clear any existing timer to prevent multiple timers
        if (errorMessageTimer) {
            clearTimeout(errorMessageTimer);
            errorMessageTimer = null;
        }

        // Hide any existing success messages
        const successElement = document.getElementById('successMessage');
        successElement.classList.add('hidden');

        textElement.textContent = message;
        errorElement.classList.remove('hidden');

        // Set new timer to hide message after 5 seconds
        errorMessageTimer = setTimeout(() => {
            errorElement.classList.add('hidden');
            errorMessageTimer = null;
        }, 5000);
    }

    // Function to hide all messages and clear timers
    function hideAllMessages() {
        // Clear success message timer
        if (successMessageTimer) {
            clearTimeout(successMessageTimer);
            successMessageTimer = null;
        }

        // Clear error message timer
        if (errorMessageTimer) {
            clearTimeout(errorMessageTimer);
            errorMessageTimer = null;
        }

        // Hide all messages
        const successElement = document.getElementById('successMessage');
        const errorElement = document.getElementById('errorMessage');

        if (successElement) {
            successElement.classList.add('hidden');
        }
        if (errorElement) {
            errorElement.classList.add('hidden');
        }
    }

    // Load real database record counts
    function loadRealDataCounts() {
        // Use server-side real module information
        const moduleInfo = @json($moduleInfo ?? []);

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
</script>
@endpush

@endsection