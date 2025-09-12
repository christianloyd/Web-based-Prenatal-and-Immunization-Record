{{-- Update Button Skeleton Component --}}
@props([
    'label' => 'Update',
    'icon' => 'fas fa-sync-alt',
    'size' => 'medium', // small, medium, large
    'variant' => 'primary', // primary, secondary, success, warning, danger
    'loading' => false,
    'disabled' => false,
    'action' => '#',
    'method' => 'POST',
    'confirm' => false,
    'confirmMessage' => 'Are you sure you want to update this item?',
    'type' => 'button' // button, submit, link
])

@php
    $sizeClasses = [
        'small' => 'px-3 py-1.5 text-sm',
        'medium' => 'px-4 py-2 text-base',
        'large' => 'px-6 py-3 text-lg'
    ];
    
    $variantClasses = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white border-blue-600',
        'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white border-gray-600',
        'success' => 'bg-green-600 hover:bg-green-700 text-white border-green-600',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white border-yellow-600',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white border-red-600'
    ];
    
    $classes = 'inline-flex items-center justify-center font-medium rounded-lg border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    $classes .= ' ' . ($sizeClasses[$size] ?? $sizeClasses['medium']);
    $classes .= ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']);
    
    if ($disabled || $loading) {
        $classes .= ' opacity-50 cursor-not-allowed';
    }
@endphp

@if($type === 'link')
    <a href="{{ $action }}" 
       class="{{ $classes }} {{ $attributes->get('class', '') }}"
       @if($disabled) onclick="return false;" @endif
       @if($confirm) onclick="return confirm('{{ $confirmMessage }}')" @endif
       {{ $attributes->except(['class']) }}>
        
        @if($loading)
            <i class="fas fa-spinner fa-spin mr-2"></i>
        @else
            <i class="{{ $icon }} mr-2"></i>
        @endif
        
        {{ $loading ? 'Updating...' : $label }}
    </a>
    
@elseif($type === 'submit' || $method !== 'GET')
    <form method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action }}" style="display: inline;">
        @if($method !== 'GET' && $method !== 'POST')
            @method($method)
        @endif
        @if($method !== 'GET')
            @csrf
        @endif
        
        <button type="submit" 
                class="{{ $classes }} {{ $attributes->get('class', '') }}"
                @if($disabled || $loading) disabled @endif
                @if($confirm) onclick="return confirm('{{ $confirmMessage }}')" @endif
                {{ $attributes->except(['class']) }}>
            
            @if($loading)
                <i class="fas fa-spinner fa-spin mr-2"></i>
            @else
                <i class="{{ $icon }} mr-2"></i>
            @endif
            
            {{ $loading ? 'Updating...' : $label }}
        </button>
    </form>
    
@else
    <button type="button" 
            class="{{ $classes }} {{ $attributes->get('class', '') }}"
            @if($disabled || $loading) disabled @endif
            @if($confirm) onclick="return confirm('{{ $confirmMessage }}')" @endif
            {{ $attributes->except(['class']) }}>
        
        @if($loading)
            <i class="fas fa-spinner fa-spin mr-2"></i>
        @else
            <i class="{{ $icon }} mr-2"></i>
        @endif
        
        {{ $loading ? 'Updating...' : $label }}
    </button>
@endif