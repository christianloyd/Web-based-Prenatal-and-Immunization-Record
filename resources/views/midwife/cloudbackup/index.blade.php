@extends('layout.midwife')
@section('title', 'Cloud Backup System')
@section('page-title', 'Cloud Backup System')
@section('page-subtitle', 'Secure backup and restore for your medical records')

@push('styles')
    <link href="{{ asset('css/midwife/cloudbackup-index.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Success/Error Messages -->
    @include('components.flowbite-alert')

    <!-- Global Confirmation Modal -->
    @include('components.confirmation-modal')
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
                <button onclick="syncGoogleDrive()" class="bg-green-600 hover:bg-green-700 text-white px-3 sm:px-4 py-2 rounded-lg font-medium flex items-center justify-center space-x-2 transition-colors text-sm sm:text-base">
                    <i class="fas fa-sync-alt w-4 h-4"></i>
                    <span class="hidden sm:inline">Sync Drive</span>
                    <span class="sm:hidden">Sync</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards 
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-blue-100">
                    <i class="fas fa-database text-2xl sm:text-3xl text-blue-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-sm sm:text-base font-medium text-gray-600 truncate">Total Backups</p>
                    <p id="totalBackups" class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900">{{ $stats['total_backups'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-green-100">
                    <i class="fas fa-check-circle text-2xl sm:text-3xl text-green-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-sm sm:text-base font-medium text-gray-600 truncate">Successful</p>
                    <p id="successfulBackups" class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900">{{ $stats['successful_backups'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-yellow-100">
                    <i class="fas fa-clock text-2xl sm:text-3xl text-yellow-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-sm sm:text-base font-medium text-gray-600 truncate">Last Backup</p>
                    <p id="lastBackup" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 truncate">{{ $stats['last_backup'] ?? 'Never' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="p-2 sm:p-3 rounded-lg bg-purple-100">
                    <i class="fas fa-hdd text-2xl sm:text-3xl text-purple-600"></i>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0">
                    <p class="text-sm sm:text-base font-medium text-gray-600 truncate">Storage Used</p>
                    <p id="storageUsed" class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 truncate">{{ $stats['storage_used'] ?? '0 MB' }}</p>
                </div>
            </div>
        </div>
    </div>-->


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

    <!-- Active Restore Progress -->
    <div id="restoreProgress" class="hidden bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="animate-spin">
                    <i class="fas fa-cloud-download-alt text-2xl text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Restore in Progress</h3>
                    <p id="restoreProgressStatus" class="text-sm text-gray-600">Initializing restore...</p>
                </div>
            </div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="restoreProgressBar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
        <div class="flex justify-between text-sm mt-2 text-gray-600">
            <span id="restoreProgressText">0%</span>
            <span id="restoreBackupName" class="text-xs"></span>
        </div>
    </div>

    <!-- History Tabs -->
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-4 sm:px-6" aria-label="Tabs">
                <button id="backupHistoryTab" class="tab-button border-b-2 border-secondary text-secondary py-4 px-1 text-sm font-medium" onclick="switchTab('backup')">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                    Backup History
                </button>
                <button id="restoreHistoryTab" class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 py-4 px-1 text-sm font-medium" onclick="switchTab('restore')">
                    <i class="fas fa-cloud-download-alt mr-2"></i>
                    Restore History
                </button>
            </nav>
        </div>

        <!-- Backup History Tab Content -->
        <div id="backupHistoryContent" class="tab-content">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Backup Operations</h3>
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

        <!-- Restore History Tab Content -->
        <div id="restoreHistoryContent" class="tab-content hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4 sm:mb-0">Restore Operations</h3>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                        <select id="filterRestoreStatus" class="w-full sm:w-auto px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" onchange="filterRestoreBackups()">
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
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restore Details</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Source Backup</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Options</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Date</th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">User</th>
                        </tr>
                    </thead>
                    <tbody id="restoreTableBody" class="bg-white divide-y divide-gray-200">
                        <!-- Dynamic restore entries will be populated here -->
                    </tbody>
                </table>
            </div>

            <!-- Empty State for Restores -->
            <div id="emptyRestoreState" class="hidden text-center py-12">
                <i class="fas fa-cloud-download-alt text-4xl mb-4 text-gray-300"></i>
                <h3 class="text-lg font-medium mb-2 text-gray-900">No restore operations found</h3>
                <p class="mb-6 text-gray-600">Restore operations will appear here when you restore data from backups.</p>
            </div>
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
<!-- Cloud Backup Routes Configuration -->
<script>
    window.cloudBackupRoutes = {
        data: '{{ route("midwife.cloudbackup.data") }}',
        store: '{{ route("midwife.cloudbackup.store") }}',
        restore: '{{ route("midwife.cloudbackup.restore") }}',
        download: '{{ route("midwife.cloudbackup.download", ":id") }}',
        sync: '{{ route("midwife.cloudbackup.sync") }}',
        restoreProgress: '{{ route("midwife.cloudbackup.restore-progress", ":id") }}'
    };

    // Module information from server
    window.moduleInfo = @json($moduleInfo ?? []);
    window.stats = @json($stats ?? []);
</script>

<!-- Include Cloud Backup Module JavaScript -->
<script src="{{ asset('js/midwife/cloudbackup-index.js') }}"></script>

@endsection