{{-- Flowbite Toast Notification Component --}}
{{--
Usage:
@include('components.toast-notification', [
    'type' => 'success', // success, error, warning, info
    'title' => 'New notification',
    'message' => 'Operation completed successfully',
    'user' => Auth::user()->name ?? 'System',
    'time' => 'just now'
])
--}}

@php
    $typeClasses = [
        'success' => 'border-green-200 bg-green-50 text-green-900',
        'error' => 'border-red-200 bg-red-50 text-red-900',
        'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-900',
        'info' => 'border-blue-200 bg-blue-50 text-blue-900'
    ];

    $iconClasses = [
        'success' => 'bg-green-600',
        'error' => 'bg-red-600',
        'warning' => 'bg-yellow-600',
        'info' => 'bg-blue-600'
    ];

    $icons = [
        'success' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4 4L15 3"/>',
        'error' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>',
        'warning' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 0v-4m0 4h.01"/>',
        'info' => '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1ZM8 10h2.5M8 13h2.5"/>',
    ];

    $type = $type ?? 'info';
    $title = $title ?? 'New notification';
    $message = $message ?? '';
    $user = $user ?? (Auth::user()->name ?? 'System');
    $time = $time ?? 'just now';
    $userAvatar = $userAvatar ?? null;
@endphp

{{-- Toast Container --}}
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-4"></div>

{{-- Toast Notification Template --}}
<template id="flowbite-toast-template">
    <div class="toast-notification w-full max-w-xs p-4 rounded-lg shadow-sm border {{ $typeClasses[$type] ?? $typeClasses['info'] }} transform translate-x-full opacity-0 transition-all duration-300 ease-in-out" role="alert">
        <div class="flex items-center mb-3">
            <span class="toast-title mb-1 text-sm font-semibold">{{ $title }}</span>
            <button type="button" class="toast-close ms-auto -mx-1.5 -my-1.5 bg-white justify-center items-center shrink-0 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8" aria-label="Close">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
            </button>
        </div>
        <div class="flex items-center">
            <div class="relative inline-block shrink-0">
                <div class="toast-avatar w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-sm font-medium text-gray-700 toast-avatar-text">SY</span>
                </div>
                <span class="toast-icon absolute bottom-0 right-0 inline-flex items-center justify-center w-6 h-6 {{ $iconClasses[$type] ?? $iconClasses['info'] }} rounded-full">
                    <svg class="w-3 h-3 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none">
                        {!! $icons[$type] ?? $icons['info'] !!}
                    </svg>
                    <span class="sr-only">{{ ucfirst($type) }} icon</span>
                </span>
            </div>
            <div class="ms-3 text-sm font-normal">
                <div class="toast-user text-sm font-semibold">{{ $user }}</div>
                <div class="toast-message text-sm font-normal">{{ $message }}</div>
                <span class="toast-time text-xs font-medium text-blue-600">{{ $time }}</span>
            </div>
        </div>
    </div>
</template>

<script>
// Flowbite Toast Notification System
class FlowbiteToast {
    constructor() {
        this.container = document.getElementById('toast-container');
        this.template = document.getElementById('flowbite-toast-template');
        this.activeToasts = [];
        this.typeClasses = {
            'success': 'border-green-200 bg-green-50 text-green-900',
            'error': 'border-red-200 bg-red-50 text-red-900',
            'warning': 'border-yellow-200 bg-yellow-50 text-yellow-900',
            'info': 'border-blue-200 bg-blue-50 text-blue-900'
        };
        this.iconClasses = {
            'success': 'bg-green-600',
            'error': 'bg-red-600',
            'warning': 'bg-yellow-600',
            'info': 'bg-blue-600'
        };
    }

    show(options = {}) {
        const {
            type = 'info',
            title = 'New notification',
            message = '',
            user = '{{ Auth::user()->name ?? "System" }}',
            time = 'just now',
            duration = 5000
        } = options;

        if (!this.template) {
            console.error('Toast template not found');
            return;
        }

        // Clone template
        const toastElement = this.template.content.cloneNode(true);
        const toast = toastElement.querySelector('.toast-notification');

        // Generate unique ID
        const toastId = 'toast-' + Date.now();
        toast.id = toastId;

        // Update classes for type
        toast.className = `toast-notification w-full max-w-xs p-4 rounded-lg shadow-sm border ${this.typeClasses[type] || this.typeClasses['info']} transform translate-x-full opacity-0 transition-all duration-300 ease-in-out`;

        // Update content
        toast.querySelector('.toast-title').textContent = title;
        toast.querySelector('.toast-user').textContent = user;
        toast.querySelector('.toast-message').textContent = message;
        toast.querySelector('.toast-time').textContent = time;

        // Update avatar
        const avatarText = user.substring(0, 2).toUpperCase();
        toast.querySelector('.toast-avatar-text').textContent = avatarText;

        // Update icon
        const iconSpan = toast.querySelector('.toast-icon');
        iconSpan.className = `toast-icon absolute bottom-0 right-0 inline-flex items-center justify-center w-6 h-6 ${this.iconClasses[type] || this.iconClasses['info']} rounded-full`;

        // Add to container
        this.container.appendChild(toast);

        // Set up close button
        const closeButton = toast.querySelector('.toast-close');
        closeButton.addEventListener('click', () => this.dismiss(toastId));

        // Add to active toasts
        this.activeToasts.push(toastId);

        // Position toasts
        this.positionToasts();

        // Show toast with animation
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }, 100);

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => this.dismiss(toastId), duration);
        }

        return toastId;
    }

    dismiss(toastId) {
        const toast = document.getElementById(toastId);
        if (!toast) return;

        // Hide with animation
        toast.classList.remove('translate-x-0', 'opacity-100');
        toast.classList.add('translate-x-full', 'opacity-0');

        // Remove from DOM and active list
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.activeToasts = this.activeToasts.filter(id => id !== toastId);
            this.positionToasts();
        }, 300);
    }

    positionToasts() {
        this.activeToasts.forEach((toastId, index) => {
            const toast = document.getElementById(toastId);
            if (toast) {
                const topPosition = 16 + (index * 120); // 16px base + 120px per toast
                toast.style.top = topPosition + 'px';
            }
        });
    }

    success(message, options = {}) {
        return this.show({
            type: 'success',
            title: 'Success!',
            message,
            ...options
        });
    }

    error(message, options = {}) {
        return this.show({
            type: 'error',
            title: 'Error!',
            message,
            ...options
        });
    }

    warning(message, options = {}) {
        return this.show({
            type: 'warning',
            title: 'Warning!',
            message,
            ...options
        });
    }

    info(message, options = {}) {
        return this.show({
            type: 'info',
            title: 'Info!',
            message,
            ...options
        });
    }
}

// Initialize global toast manager
window.flowbiteToast = new FlowbiteToast();

// Auto-show toasts for Laravel session messages
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        window.flowbiteToast.success('{{ addslashes(session('success')) }}');
    @endif

    @if(session('error'))
        window.flowbiteToast.error('{{ addslashes(session('error')) }}');
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            window.flowbiteToast.error('{{ addslashes($error) }}');
        @endforeach
    @endif
});

// Development helper functions (remove in production)
window.testFlowbiteToast = {
    success: (msg = 'Immunization scheduled successfully!') => window.flowbiteToast.success(msg),
    error: (msg = 'Please fill in all required fields.') => window.flowbiteToast.error(msg),
    warning: (msg = 'Low vaccine stock detected.') => window.flowbiteToast.warning(msg),
    info: (msg = 'New patient record created.') => window.flowbiteToast.info(msg),
    demo: function() {
        this.success();
        setTimeout(() => this.error(), 1000);
        setTimeout(() => this.warning(), 2000);
        setTimeout(() => this.info(), 3000);
    }
};
</script>