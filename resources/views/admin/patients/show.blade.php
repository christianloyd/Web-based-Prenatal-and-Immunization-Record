@extends('layout.admin')

@section('title', 'Patient Details')
@section('page-title', 'Patient Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6 bg-gradient-to-r from-green-600 to-green-700 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">Patient Details</h1>
                    <p class="mt-1 text-green-100">{{ $patient->first_name }} {{ $patient->last_name }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('admin.patients.index') }}"
                       class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white text-green-700 hover:bg-gray-100">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Patients
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Information -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->first_name }} {{ $patient->last_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Gender</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->gender }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Age</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->age ?? 'Not specified' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->date_of_birth ? $patient->date_of_birth->format('F d, Y') : 'Not specified' }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Number</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->contact_number ? '+63' . $patient->contact_number : 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->address ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Emergency Contact</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->emergency_contact ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Emergency Contact Number</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->emergency_contact_number ? '+63' . $patient->emergency_contact_number : 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Medical Records Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Prenatal Records -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Prenatal Records</h3>
                @if($patient->prenatalRecords && $patient->prenatalRecords->count() > 0)
                    <div class="space-y-3">
                        @foreach($patient->prenatalRecords as $record)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Record #{{ $record->id }}</p>
                                <p class="text-xs text-gray-500">Created: {{ $record->created_at->format('M d, Y') }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-pink-100 text-pink-800">
                                Prenatal
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No prenatal records found.</p>
                @endif
            </div>
        </div>

        <!-- Child Records -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Child Records</h3>
                @if($patient->childRecords && $patient->childRecords->count() > 0)
                    <div class="space-y-3">
                        @foreach($patient->childRecords as $record)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $record->full_name }}</p>
                                <p class="text-xs text-gray-500">Born: {{ $record->birthdate ? $record->birthdate->format('M d, Y') : 'Not specified' }} • {{ $record->gender }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                                Child
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No child records found.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Account Details -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Details</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Registered By</dt>
                    <dd class="text-sm text-gray-900">{{ $patient->creator ? $patient->creator->name : 'Unknown' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Registration Date</dt>
                    <dd class="text-sm text-gray-900">{{ $patient->created_at->format('F d, Y \a\t g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="text-sm text-gray-900">{{ $patient->updated_at->format('F d, Y \a\t g:i A') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection