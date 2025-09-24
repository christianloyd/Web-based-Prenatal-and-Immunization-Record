@extends('layout.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">User Details</h1>
                    <p class="mt-1 text-blue-100">{{ $user->name }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.users.index') }}"
                       class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white text-blue-700 hover:bg-gray-100">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Username</dt>
                            <dd class="text-sm text-gray-900">{{ $user->username }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Role</dt>
                            <dd class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->role_badge_class }}">
                                    {{ $user->role }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->status_badge_class }}">
                                    <i class="fas {{ $user->status_icon }} mr-1"></i>
                                    {{ $user->status_text }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Gender</dt>
                            <dd class="text-sm text-gray-900">{{ $user->gender }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Age</dt>
                            <dd class="text-sm text-gray-900">{{ $user->age }} years old</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Number</dt>
                            <dd class="text-sm text-gray-900">{{ $user->formatted_contact_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="text-sm text-gray-900">{{ $user->address ?: 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Summary -->
    @if(isset($activities) && count($activities) > 0)
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $activities['patients'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Patients Registered</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $activities['prenatal_records'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Prenatal Records</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">{{ $activities['child_records'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Child Records</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $activities['immunizations'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500">Immunizations Given</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Account Details -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Account Details</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="text-sm text-gray-900">{{ $user->created_at->format('F d, Y \a\t g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="text-sm text-gray-900">{{ $user->updated_at->format('F d, Y \a\t g:i A') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection