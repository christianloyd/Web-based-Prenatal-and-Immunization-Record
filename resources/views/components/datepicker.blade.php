@props([
    'name',
    'id' => null,
    'required' => false,
    'placeholder' => 'Choose your date',
    'value' => null,
    'minDate' => null,
    'maxDate' => null
])

<div class="relative">
    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        <i class="fas fa-calendar-alt text-gray-400"></i>
    </div>
    <input datepicker datepicker-autohide datepicker-format="yyyy-mm-dd" 
           type="text" 
           name="{{ $name }}" 
           id="{{ $id ?? $name }}"
           value="{{ $value }}"
           @if($required) required @endif
           @if($minDate) datepicker-min-date="{{ $minDate }}" @endif
           @if($maxDate) datepicker-max-date="{{ $maxDate }}" @endif
           placeholder="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'w-full pl-10 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary cursor-pointer']) }}>
</div>

@pushOnce('styles')
<style>
    /* Flowbite Datepicker Overrides - Global */
    .datepicker {
        z-index: 100000 !important;
    }
    .datepicker [class*="bg-blue-"] {
        background-color: #D4A373 !important;
        color: white !important;
    }
    .datepicker [class*="text-blue-"] {
        color: #D4A373 !important;
    }
    .datepicker [class*="hover:bg-gray-"]:hover {
        background-color: #FEFAE0 !important;
        color: #B8956A !important;
    }
</style>
@endPushOnce

@pushOnce('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/datepicker.min.js"></script>
@endPushOnce
