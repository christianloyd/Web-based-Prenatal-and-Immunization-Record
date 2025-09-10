
@extends('layout.midwife')
@section('title', 'Cloud Backup System')
@section('page-title', 'Cloud Backup System')
@section('page-subtitle', 'Secure backup and restore for your medical records')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    
    * {
        font-family: 'Inter', sans-serif;
    }
    
    .btn-hover {
        transition: all 0.2s ease;
    }
    
    .btn-hover:hover {
        transform: translateY(-1px);
    }
    
    .input-focus {
        transition: all 0.2s ease;
    }
    
    .input-focus:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-success { background-color: #d1fae5; color: #065f46; }
    .status-warning { background-color: #fef3c7; color: #92400e; }
    .status-error { background-color: #fee2e2; color: #991b1b; }
    .status-info { background-color: #dbeafe; color: #1e40af; }
    
    .modal-backdrop {
        backdrop-filter: blur(4px);
    }
    
    .page {
        display: none;
    }
    
    .page.active {
        display: block;
    }
    
    .progress-bar {
        transition: width 0.3s ease;
    }
    
    .cloud-icon {
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    .backup-item {
        transition: all 0.2s ease;
    }
    
    .backup-item:hover {
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }
    
    .storage-meter {
        background: linear-gradient(90deg, #10b981 0%, #f59e0b 70%, #ef4444 90%);
        border-radius: 9999px;
        height: 8px;
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>

<!-- Dashboard Page -->
<div id="dashboardPage" class="page active">
    <!-- Quick Actions -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-3">
            <button onclick="showPage('dashboard')" class="btn-hover bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </button>
            <button onclick="showPage('backups')" class="btn-hover bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-history mr-2"></i>Backup History
            </button>
            <button onclick="showPage('settings')" class="btn-hover bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-cog mr-2"></i>Settings
            </button>
        </div>
        <button onclick="openBackupModal()" class="btn-hover bg-green-600 text-white px-6 py-2 rounded-lg font-medium">
            <i class="fas fa-cloud-upload-alt mr-2"></i>Create Backup
        </button>
    </div>

    <!-- Storage Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Storage Usage -->
        <div class="lg:col-span-2 bg-white rounded-lg border p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Storage Usage</h3>
                    <p class="text-sm text-gray-600">Monitor your cloud storage consumption</p>
                </div>
                <div class="cloud-icon text-3xl text-blue-500">
                    <i class="fas fa-cloud"></i>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">Used Storage</span>
                    <span class="text-sm text-gray-600">2.4 GB of 10 GB</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="storage-meter h-3 rounded-full" style="width: 24%"></div>
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-green-600">7.6 GB</p>
                        <p class="text-xs text-gray-600">Available</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-600">2.4 GB</p>
                        <p class="text-xs text-gray-600">Used</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-purple-600">156</p>
                        <p class="text-xs text-gray-600">Files</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="space-y-4">
            <div class="bg-white rounded-lg border p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-100">
                        <i class="fas fa-check-circle text-xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Last Backup</p>
                        <p class="text-lg font-semibold text-gray-800">2 hours ago</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100">
                        <i class="fas fa-shield-alt text-xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Security</p>
                        <p class="text-lg font-semibold text-gray-800">Encrypted</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg border p-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-purple-100">
                        <i class="fas fa-sync-alt text-xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Auto Backup</p>
                        <p class="text-lg font-semibold text-gray-800">Daily</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Backups -->
    <div class="bg-white rounded-lg border">
        <div class="border-b px-6 py-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Recent Backups</h3>
                <button onclick="showPage('backups')" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                <!-- Backup Item -->
                <div class="backup-item border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-database text-xl text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Full System Backup</h4>
                                <p class="text-sm text-gray-600">Patient records, checkup data, and system settings</p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>Feb 20, 2024 at 2:30 PM
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-hdd mr-1"></i>245 MB
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="status-badge status-success">
                                <i class="fas fa-check mr-1"></i>Completed
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="downloadBackup('backup1')" class="btn-hover text-blue-600 hover:text-blue-700 p-2" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button onclick="restoreBackup('backup1')" class="btn-hover text-green-600 hover:text-green-700 p-2" title="Restore">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Item -->
                <div class="backup-item border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-xl text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Patient Records Only</h4>
                                <p class="text-sm text-gray-600">All patient information and medical histories</p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>Feb 19, 2024 at 11:45 PM
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-hdd mr-1"></i>128 MB
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="status-badge status-success">
                                <i class="fas fa-check mr-1"></i>Completed
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="downloadBackup('backup2')" class="btn-hover text-blue-600 hover:text-blue-700 p-2" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button onclick="restoreBackup('backup2')" class="btn-hover text-green-600 hover:text-green-700 p-2" title="Restore">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Item -->
                <div class="backup-item border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-sync-alt text-xl text-yellow-600 animate-spin"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Scheduled Backup</h4>
                                <p class="text-sm text-gray-600">Automatic daily backup in progress</p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>Feb 20, 2024 at 11:59 PM
                                    </span>
                                    <div class="w-32 bg-gray-200 rounded-full h-2">
                                        <div class="progress-bar bg-yellow-500 h-2 rounded-full" style="width: 65%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">65%</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="status-badge status-warning">
                                <i class="fas fa-clock mr-1"></i>In Progress
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup History Page -->
<div id="backupsPage" class="page">
    <!-- Navigation -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-3">
            <button onclick="showPage('dashboard')" class="btn-hover bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </button>
            <button onclick="showPage('backups')" class="btn-hover bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-history mr-2"></i>Backup History
            </button>
            <button onclick="showPage('settings')" class="btn-hover bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-cog mr-2"></i>Settings
            </button>
        </div>
        <button onclick="openBackupModal()" class="btn-hover bg-green-600 text-white px-6 py-2 rounded-lg font-medium">
            <i class="fas fa-cloud-upload-alt mr-2"></i>Create Backup
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Type</label>
                <select class="input-focus px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option>All Backups</option>
                    <option>Full System</option>
                    <option>Patient Records</option>
                    <option>Settings Only</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select class="input-focus px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option>Last 7 days</option>
                    <option>Last 30 days</option>
                    <option>Last 3 months</option>
                    <option>All time</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select class="input-focus px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option>All Status</option>
                    <option>Completed</option>
                    <option>In Progress</option>
                    <option>Failed</option>
                </select>
            </div>
            <div class="flex-1"></div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
                <input type="text" placeholder="Search backups..." class="input-focus px-3 py-2 border border-gray-300 rounded-lg text-sm w-64">
            </div>
        </div>
    </div>

    <!-- Backup List -->
    <div class="bg-white rounded-lg border">
        <div class="border-b px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-800">All Backups</h3>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                <!-- Backup Items -->
                <div class="backup-item border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-database text-xl text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Full System Backup</h4>
                                <p class="text-sm text-gray-600">Complete backup including all patient records, settings, and system data</p>
                                <div class="flex items-center space-x-6 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>February 20, 2024 at 2:30 PM
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-hdd mr-1"></i>245 MB
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>Duration: 3m 45s
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="status-badge status-success">
                                <i class="fas fa-check mr-1"></i>Completed
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="downloadBackup('backup1')" class="btn-hover text-blue-600 hover:text-blue-700 p-2" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button onclick="restoreBackup('backup1')" class="btn-hover text-green-600 hover:text-green-700 p-2" title="Restore">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button onclick="deleteBackup('backup1')" class="btn-hover text-red-600 hover:text-red-700 p-2" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="backup-item border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-xl text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Patient Records Backup</h4>
                                <p class="text-sm text-gray-600">All patient information, medical histories, and checkup records</p>
                                <div class="flex items-center space-x-6 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>February 19, 2024 at 11:45 PM
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-hdd mr-1"></i>128 MB
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>Duration: 1m 23s
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="status-badge status-success">
                                <i class="fas fa-check mr-1"></i>Completed
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="downloadBackup('backup2')" class="btn-hover text-blue-600 hover:text-blue-700 p-2" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button onclick="restoreBackup('backup2')" class="btn-hover text-green-600 hover:text-green-700 p-2" title="Restore">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button onclick="deleteBackup('backup2')" class="btn-hover text-red-600 hover:text-red-700 p-2" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="backup-item border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-xl text-red-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Failed Backup Attempt</h4>
                                <p class="text-sm text-gray-600">Backup failed due to insufficient storage space</p>
                                <div class="flex items-center space-x-6 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>February 18, 2024 at 3:15 AM
                                    </span>
                                    <span class="text-xs text-red-500">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Error: Storage full
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="status-badge status-error">
                                <i class="fas fa-times mr-1"></i>Failed
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="retryBackup('backup3')" class="btn-hover text-orange-600 hover:text-orange-700 p-2" title="Retry">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <button onclick="deleteBackup('backup3')" class="btn-hover text-red-600 hover:text-red-700 p-2" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="backup-item border rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cog text-xl text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Settings Backup</h4>
                                <p class="text-sm text-gray-600">System configuration and user preferences</p>
                                <div class="flex items-center space-x-6 mt-2">
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>February 17, 2024 at 9:20 AM
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-hdd mr-1"></i>2.3 MB
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>Duration: 12s
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="status-badge status-success">
                                <i class="fas fa-check mr-1"></i>Completed
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="downloadBackup('backup4')" class="btn-hover text-blue-600 hover:text-blue-700 p-2" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button onclick="restoreBackup('backup4')" class="btn-hover text-green-600 hover:text-green-700 p-2" title="Restore">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <button onclick="deleteBackup('backup4')" class="btn-hover text-red-600 hover:text-red-700 p-2" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6 pt-6 border-t">
                <p class="text-sm text-gray-600">Showing 1-4 of 12 backups</p>
                <div class="flex space-x-2">
                    <button class="btn-hover px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">Previous</button>
                    <button class="btn-hover px-3 py-2 bg-blue-600 text-white rounded-lg text-sm">1</button>
                    <button class="btn-hover px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">2</button>
                    <button class="btn-hover px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">3</button>
                    <button class="btn-hover px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Settings Page -->
<div id="settingsPage" class="page">
    <!-- Navigation -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-3">
            <button onclick="showPage('dashboard')" class="btn-hover bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </button>
            <button onclick="showPage('backups')" class="btn-hover bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-history mr-2"></i>Backup History
            </button>
            <button onclick="showPage('settings')" class="btn-hover bg-blue-600 text-white px-4 py-2 rounded-lg font-medium">
                <i class="fas fa-cog mr-2"></i>Settings
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Automatic Backup Settings -->
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-sync-alt mr-2 text-blue-600"></i>Automatic Backup
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Enable Auto Backup</p>
                        <p class="text-sm text-gray-600">Automatically create backups on schedule</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Frequency</label>
                    <select class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="daily" selected>Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Time</label>
                    <input type="time" value="23:59" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Retention Period</label>
                    <select class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="7">Keep for 7 days</option>
                        <option value="30" selected>Keep for 30 days</option>
                        <option value="90">Keep for 90 days</option>
                        <option value="365">Keep for 1 year</option>
                    </select>
                </div>
            </div>

            <button onclick="saveAutoBackupSettings()" class="btn-hover bg-blue-600 text-white px-4 py-2 rounded-lg font-medium mt-6">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </div>

        <!-- Security Settings -->
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-shield-alt mr-2 text-green-600"></i>Security Settings
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Encrypt Backups</p>
                        <p class="text-sm text-gray-600">Protect your data with encryption</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Encryption Level</label>
                    <select class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="aes128">AES-128</option>
                        <option value="aes256" selected>AES-256 (Recommended)</option>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Two-Factor Authentication</p>
                        <p class="text-sm text-gray-600">Require 2FA for restore operations</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Password</label>
                    <input type="password" placeholder="Enter backup password" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <button onclick="saveSecuritySettings()" class="btn-hover bg-green-600 text-white px-4 py-2 rounded-lg font-medium mt-6">
                <i class="fas fa-shield-alt mr-2"></i>Update Security
            </button>
        </div>

        <!-- Storage Settings -->
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-cloud mr-2 text-purple-600"></i>Storage Settings
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cloud Provider</label>
                    <select class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="aws">Amazon S3</option>
                        <option value="google" selected>Google Drive</option>
                        <option value="dropbox">Dropbox</option>
                        <option value="onedrive">OneDrive</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Storage Location</label>
                    <input type="text" value="/Backups/MidwifeSystem/" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Compress Backups</p>
                        <p class="text-sm text-gray-600">Reduce file size with compression</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    </label>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Storage Usage</p>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>2.4 GB used</span>
                        <span>7.6 GB available</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: 24%"></div>
                    </div>
                </div>
            </div>

            <button onclick="saveStorageSettings()" class="btn-hover bg-purple-600 text-white px-4 py-2 rounded-lg font-medium mt-6">
                <i class="fas fa-cloud mr-2"></i>Update Storage
            </button>
        </div>

        <!-- Notification Settings -->
        <div class="bg-white rounded-lg border p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-bell mr-2 text-yellow-600"></i>Notifications
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Backup Completion</p>
                        <p class="text-sm text-gray-600">Notify when backups complete</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Backup Failures</p>
                        <p class="text-sm text-gray-600">Alert on backup errors</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-800">Storage Warnings</p>
                        <p class="text-sm text-gray-600">Warn when storage is low</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Notifications</label>
                    <input type="email" placeholder="admin@barangay.gov.ph" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <button onclick="saveNotificationSettings()" class="btn-hover bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium mt-6">
                <i class="fas fa-bell mr-2"></i>Save Notifications
            </button>
        </div>
    </div>
</div>

<!-- Create Backup Modal -->
<div id="backupModal" class="hidden fixed inset-0 bg-black bg-opacity-50 modal-backdrop z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full">
        <div class="border-b px-6 py-4">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">
                    <i class="fas fa-cloud-upload-alt mr-2 text-green-600"></i>
                    Create New Backup
                </h2>
                <button onclick="closeBackupModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form class="p-6" onsubmit="createBackup(event)">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Backup Type</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="backup_type" value="full" class="text-green-600" checked>
                            <div class="ml-3">
                                <p class="font-medium text-gray-800">Full System Backup</p>
                                <p class="text-sm text-gray-600">Complete backup including all data and settings</p>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="backup_type" value="patients" class="text-green-600">
                            <div class="ml-3">
                                <p class="font-medium text-gray-800">Patient Records Only</p>
                                <p class="text-sm text-gray-600">Backup patient information and medical records</p>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="backup_type" value="settings" class="text-green-600">
                            <div class="ml-3">
                                <p class="font-medium text-gray-800">Settings Only</p>
                                <p class="text-sm text-gray-600">Backup system configuration and preferences</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Name</label>
                    <input type="text" name="backup_name" placeholder="e.g., Weekly Backup - Feb 2024" class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                    <textarea name="backup_description" rows="3" placeholder="Add notes about this backup..." class="input-focus w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-medium text-gray-800 mb-3">Backup Options</h4>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="compress" class="text-green-600" checked>
                            <span class="ml-2 text-sm text-gray-700">Compress backup file</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="encrypt" class="text-green-600" checked>
                            <span class="ml-2 text-sm text-gray-700">Encrypt backup with password</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="verify" class="text-green-600">
                            <span class="ml-2 text-sm text-gray-700">Verify backup integrity after creation</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                <button type="button" onclick="closeBackupModal()" class="btn-hover px-6 py-2 border border-gray-300 rounded-lg text-gray-700">
                    Cancel
                </button>
                <button type="submit" class="btn-hover bg-green-600 text-white px-6 py-2 rounded-lg font-medium">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                    Create Backup
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Page navigation
    function showPage(pageName) {
        // Hide all pages
        document.querySelectorAll('.page').forEach(page => {
            page.classList.remove('active');
        });
        
        // Show selected page
        document.getElementById(pageName + 'Page').classList.add('active');
    }

    // Modal functions
    function openBackupModal() {
        document.getElementById('backupModal').classList.remove('hidden');
    }

    function closeBackupModal() {
        document.getElementById('backupModal').classList.add('hidden');
    }

    // Backup actions
    function createBackup(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        const backupData = Object.fromEntries(formData.entries());
        
        console.log('Creating backup:', backupData);
        
        showSuccess('Backup creation started! You will be notified when complete.');
        closeBackupModal();
        
        // Simulate backup progress
        setTimeout(() => {
            showSuccess('Backup completed successfully!');
        }, 3000);
    }

    function downloadBackup(backupId) {
        showSuccess('Downloading backup... Please wait.');
        console.log('Downloading backup:', backupId);
    }

    function restoreBackup(backupId) {
        if (confirm('Are you sure you want to restore from this backup? This will overwrite current data.')) {
            showSuccess('Restore process started. This may take a few minutes.');
            console.log('Restoring backup:', backupId);
        }
    }

    function deleteBackup(backupId) {
        if (confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
            showSuccess('Backup deleted successfully.');
            console.log('Deleting backup:', backupId);
        }
    }

    function retryBackup(backupId) {
        showSuccess('Retrying backup creation...');
        console.log('Retrying backup:', backupId);
    }

    // Settings functions
    function saveAutoBackupSettings() {
        showSuccess('Auto backup settings saved successfully!');
    }

    function saveSecuritySettings() {
        showSuccess('Security settings updated successfully!');
    }

    function saveStorageSettings() {
        showSuccess('Storage settings saved successfully!');
    }

    function saveNotificationSettings() {
        showSuccess('Notification preferences updated!');
    }

    // Utility functions
    function showSuccess(message) {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50';
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 4000);
    }

    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.id === 'backupModal') {
            closeBackupModal();
        }
    });

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Set default backup name with current date
        const now = new Date();
        const defaultName = `Backup - ${now.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        })}`;
        
        const nameInput = document.querySelector('input[name="backup_name"]');
        if (nameInput && !nameInput.value) {
            nameInput.value = defaultName;
        }
    });
</script>
@endsection
