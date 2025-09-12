{{-- Reusable Refresh Data Button Component --}}
@props([
    'id' => 'refresh-btn',
    'text' => 'Refresh Data',
    'function' => 'refreshDataWithSkeleton'
])

<button id="{{ $id }}" onclick="{{ $function }}()" 
    class="btn-minimal px-4 py-2 bg-gray-600 text-white rounded-lg font-medium flex items-center space-x-2 hover:bg-gray-700 transition-all duration-200">
    <i class="fas fa-sync-alt text-sm"></i>
    <span>{{ $text }}</span>
</button>

<style>
/* Refresh Button Styles */
.btn-minimal {
    transition: all 0.15s ease;
    border: 1px solid transparent;
}

.btn-minimal:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-minimal:disabled {
    opacity: 0.75;
    cursor: not-allowed;
    transform: none;
}

.fa-spin {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>