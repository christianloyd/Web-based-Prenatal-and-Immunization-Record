@extends('layout.bhw')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-subtitle', 'Monitor patient care and health records')



@section('content')
<!-- Dashboard Section -->
<div id="dashboard-section" class="section-content">
    <!-- Stats Cards Grid -->
    <!-- TODO: Replace with DaisyUI stats component -->
    <!-- Original: grid with gap-6 mb-8 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Pregnancies Card -->
        <!-- TODO: Replace with DaisyUI stat card -->
        <!-- Original: bg-white p-6 rounded-lg shadow-sm border -->
        <div class="bg-white p-6 rounded-lg shadow-sm border stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Active Pregnancies</p>
                    <p class="text-3xl font-bold text-primary">{{ $stats['active_pregnancies'] ?? 24 }}</p>
                </div>
                <!-- TODO: Replace with DaisyUI avatar or icon -->
                <!-- Original: bg-primary bg-opacity-10 p-3 rounded-full -->
                <div class="bg-primary bg-opacity-10 p-3 rounded-full">
                    <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Babies Born Card -->
        <!-- TODO: Replace with DaisyUI stat card -->
        <!-- Original: bg-white p-6 rounded-lg shadow-sm border -->
        <div class="bg-white p-6 rounded-lg shadow-sm border stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Babies Born</p>
                    <p class="text-3xl font-bold text-primary">{{ $stats['babies_born'] ?? 156 }}</p>
                </div>
                <!-- TODO: Replace with DaisyUI avatar or icon -->
                <!-- Original: bg-primary bg-opacity-10 p-3 rounded-full -->
                <div class="bg-primary bg-opacity-10 p-3 rounded-full">
                    <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Vaccinations Due Card -->
        <!-- TODO: Replace with DaisyUI stat card with alert styling -->
        <!-- Original: bg-white p-6 rounded-lg shadow-sm border -->
        <div class="bg-white p-6 rounded-lg shadow-sm border stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Vaccinations Due</p>
                    <p class="text-3xl font-bold text-red-500">{{ $stats['vaccinations_due'] ?? 8 }}</p>
                </div>
                <!-- TODO: Replace with DaisyUI alert icon -->
                <!-- Original: bg-red-100 p-3 rounded-full -->
                <div class="bg-red-100 p-3 rounded-full">
                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Appointments Today Card -->
        <!-- TODO: Replace with DaisyUI stat card -->
        <!-- Original: bg-white p-6 rounded-lg shadow-sm border -->
        <div class="bg-white p-6 rounded-lg shadow-sm border stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Appointments Today</p>
                    <p class="text-3xl font-bold text-primary">{{ $stats['appointments_today'] ?? 12 }}</p>
                </div>
                <!-- TODO: Replace with DaisyUI calendar icon -->
                <!-- Original: bg-primary bg-opacity-10 p-3 rounded-full -->
                <div class="bg-primary bg-opacity-10 p-3 rounded-full">
                    <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <!-- TODO: Replace with DaisyUI card grid -->
    <!-- Original: grid with gap-6 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Appointments Card -->
        <!-- TODO: Replace with DaisyUI card -->
        <!-- Original: bg-white rounded-lg shadow-sm border -->
        <div class="bg-white rounded-lg shadow-sm border">
            <!-- TODO: Replace with DaisyUI card-title -->
            <!-- Original: p-6 border-b -->
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Recent Appointments</h3>
            </div>
            <!-- TODO: Replace with DaisyUI card-body -->
            <!-- Original: p-6 -->
            <div class="p-6">
                <!-- TODO: Replace with DaisyUI list group -->
                <!-- Original: space-y-4 -->
                <div class="space-y-4" id="recent-appointments">
                    @forelse($recent_appointments ?? [] as $appointment)
                        <!-- TODO: Replace with DaisyUI list item -->
                        <!-- Original: flex items-center justify-between p-3 bg-gray-50 rounded-lg -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">{{ $appointment['patient_name'] }}</p>
                                <p class="text-sm text-gray-600">{{ $appointment['description'] }}</p>
                            </div>
                            <!-- TODO: Replace with DaisyUI badge -->
                            <!-- Original: text-sm text-primary font-medium -->
                            <span class="text-sm text-primary font-medium">{{ $appointment['time'] }}</span>
                        </div>
                    @empty
                        <!-- Default appointments when no data -->
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Sarah Williams</p>
                                <p class="text-sm text-gray-600">28 weeks - Routine checkup</p>
                            </div>
                            <span class="text-sm text-primary font-medium">10:00 AM</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Maria Rodriguez</p>
                                <p class="text-sm text-gray-600">36 weeks - Final checkup</p>
                            </div>
                            <span class="text-sm text-primary font-medium">2:30 PM</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-800">Emma Thompson</p>
                                <p class="text-sm text-gray-600">12 weeks - First visit</p>
                            </div>
                            <span class="text-sm text-primary font-medium">4:00 PM</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming Vaccinations Card -->
        <!-- TODO: Replace with DaisyUI card -->
        <!-- Original: bg-white rounded-lg shadow-sm border -->
        <div class="bg-white rounded-lg shadow-sm border">
            <!-- TODO: Replace with DaisyUI card-title -->
            <!-- Original: p-6 border-b -->
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Upcoming Vaccinations</h3>
            </div>
            <!-- TODO: Replace with DaisyUI card-body -->
            <!-- Original: p-6 -->
            <div class="p-6">
                <!-- TODO: Replace with DaisyUI list group -->
                <!-- Original: space-y-4 -->
                <div class="space-y-4" id="upcoming-vaccinations">
                    @forelse($upcoming_vaccinations ?? [] as $vaccination)
                        <!-- TODO: Replace with DaisyUI alert item -->
                        <!-- Original: flex with dynamic color classes -->
                        <div class="flex items-center justify-between p-3 bg-{{ $vaccination['status_color'] }}-50 rounded-lg border border-{{ $vaccination['status_color'] }}-200">
                            <div>
                                <p class="font-medium text-gray-800">{{ $vaccination['baby_name'] }}</p>
                                <p class="text-sm text-{{ $vaccination['status_color'] }}-600">{{ $vaccination['vaccines'] }}</p>
                            </div>
                            <span class="text-sm text-{{ $vaccination['status_color'] }}-600 font-medium">{{ $vaccination['due_date'] }}</span>
                        </div>
                    @empty
                        <!-- Default vaccinations when no data -->
                        <!-- TODO: Replace with DaisyUI alert -->
                        <!-- Original: bg-red-50 rounded-lg border border-red-200 -->
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                            <div>
                                <p class="font-medium text-gray-800">Baby Johnson</p>
                                <p class="text-sm text-red-600">2 months - DPT, Polio, Hib</p>
                            </div>
                            <span class="text-sm text-red-600 font-medium">Tomorrow</span>
                        </div>
                        <!-- TODO: Replace with DaisyUI warning alert -->
                        <!-- Original: bg-yellow-50 rounded-lg border border-yellow-200 -->
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div>
                                <p class="font-medium text-gray-800">Baby Martinez</p>
                                <p class="text-sm text-yellow-600">4 months - DPT, Polio, Hib</p>
                            </div>
                            <span class="text-sm text-yellow-600 font-medium">3 days</span>
                        </div>
                        <!-- TODO: Replace with DaisyUI info alert -->
                        <!-- Original: bg-blue-50 rounded-lg border border-blue-200 -->
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div>
                                <p class="font-medium text-gray-800">Baby Chen</p>
                                <p class="text-sm text-blue-600">6 months - DPT, Polio, Hib</p>
                            </div>
                            <span class="text-sm text-blue-600 font-medium">1 week</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Dashboard-specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dashboard functionality
        console.log('Dashboard loaded');
        
        // Fixed: Removed unwanted hover event listeners that caused background changes
        // TODO: Replace with DaisyUI component interactions
        
        // Optional: Add click handlers for cards if needed
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('click', function() {
                // Handle card click if needed
                console.log('Card clicked:', this);
            });
        });
    });
</script>
@endpush