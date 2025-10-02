@extends('layout.admin')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'System Overview & Management')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-end items-center mb-6">
         
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-users text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_users'] }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="text-sm text-gray-600">
                        <span class="text-green-600">{{ $stats['active_users'] }}</span> active users
                    </div>
                </div>
            </div>
        </div>

        <!-- Midwives -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-user-md text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Midwives</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['midwives'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- BHWs -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-hands-helping text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">BHWs</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['bhws'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Patients -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-user-plus text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Patients</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_patients'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Records Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Prenatal Records -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-pink-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-file-medical text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Prenatal Records</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['prenatal_records'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Child Records -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-clipboard-list text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Child Records</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['child_records'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Immunizations -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-syringe text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Immunizations</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['immunizations'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vaccines -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-teal-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-vial text-white text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Vaccines</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['vaccines'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cloud Backup Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Backup Stats -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cloud Backup Status</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-cloud-upload-alt text-blue-500 mr-2"></i>
                            <span class="text-sm text-gray-600">Total Backups</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['cloud_backups'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-week text-green-500 mr-2"></i>
                            <span class="text-sm text-gray-600">Recent Backups (7 days)</span>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['recent_backups'] }}</span>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.cloudbackup.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-cog mr-2"></i>
                        Manage Backups
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.users.index') }}"
                       class="block w-full text-left px-4 py-3 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <div class="flex items-center">
                            <i class="fas fa-users text-blue-500 mr-3"></i>
                            <span class="text-sm font-medium text-gray-900">View All Users</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.patients.index') }}"
                       class="block w-full text-left px-4 py-3 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <div class="flex items-center">
                            <i class="fas fa-user-plus text-green-500 mr-3"></i>
                            <span class="text-sm font-medium text-gray-900">View Patients</span>
                        </div>
                    </a>
                    <a href="{{ route('admin.records.index') }}"
                       class="block w-full text-left px-4 py-3 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <div class="flex items-center">
                            <i class="fas fa-clipboard-list text-purple-500 mr-3"></i>
                            <span class="text-sm font-medium text-gray-900">View Records</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Backups -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Backups</h3>
                @if($recentBackups->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentBackups as $backup)
                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center">
                                <div class="w-2 h-2 {{ $backup->status === 'completed' ? 'bg-green-500' : ($backup->status === 'failed' ? 'bg-red-500' : 'bg-yellow-500') }} rounded-full mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $backup->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $backup->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $backup->status === 'completed' ? 'bg-green-100 text-green-800' : ($backup->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($backup->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No recent backups found.</p>
                @endif
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recently Added Users</h3>
                @if($recentUsers->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentUsers as $user)
                        <div class="flex items-center justify-between py-2">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-{{ $user->role === 'Midwife' ? 'green' : ($user->role === 'BHW' ? 'purple' : 'red') }}-500 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas {{ $user->role_icon }} text-white text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $user->role === 'Midwife' ? 'bg-green-100 text-green-800' : ($user->role === 'BHW' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800') }}">
                                {{ $user->role }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No recent users found.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection