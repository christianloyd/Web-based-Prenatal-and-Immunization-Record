{{-- Reusable Table Skeleton Component --}}
@props([
    'id' => 'table-skeleton',
    'rows' => 5,
    'columns' => 6,
    'showStats' => true,
    'statsId' => 'stats-skeleton'
])

{{-- Stats Skeleton (if enabled) --}}
@if($showStats)
<div id="{{ $statsId }}" class="hidden flex flex-wrap gap-4 mb-6">
    @for($i = 0; $i < 4; $i++)
    <div class="bg-white p-4 rounded-lg shadow-sm border animate-pulse min-w-[200px]">
        <div class="h-8 bg-gray-200 rounded w-16 mb-2"></div>
        <div class="h-4 bg-gray-200 rounded w-20"></div>
    </div>
    @endfor
</div>
@endif

{{-- Table Skeleton --}}
<div id="{{ $id }}" class="hidden bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <!-- Header Skeleton -->
    <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
        <div class="flex justify-between items-center">
            <div class="flex space-x-4 animate-pulse">
                @for($i = 0; $i < $columns; $i++)
                <div class="h-4 bg-gray-200 rounded w-{{ [16, 20, 12, 14, 16, 24][$i % 6] }}"></div>
                @endfor
            </div>
        </div>
    </div>
    
    <!-- Rows Skeleton -->
    <div class="divide-y divide-gray-200">
        @for($row = 0; $row < $rows; $row++)
        <div class="px-4 py-3 animate-pulse">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    {{-- Avatar/Icon placeholder --}}
                    <div class="h-10 w-10 bg-gray-200 rounded-full"></div>
                    {{-- Content placeholders --}}
                    @for($col = 0; $col < $columns - 1; $col++)
                    <div class="space-y-1">
                        <div class="h-4 bg-gray-200 rounded w-{{ [24, 16, 20, 28, 12, 32][$col % 6] }}"></div>
                        @if($col === 0)
                        <div class="h-3 bg-gray-200 rounded w-16"></div>
                        @endif
                    </div>
                    @endfor
                </div>
                {{-- Actions placeholder --}}
                <div class="flex space-x-2">
                    <div class="h-8 bg-gray-200 rounded w-16"></div>
                    <div class="h-8 bg-gray-200 rounded w-16"></div>
                    <div class="h-8 bg-gray-200 rounded w-20"></div>
                </div>
            </div>
        </div>
        @endfor
    </div>
    
    {{-- Pagination skeleton --}}
    <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
        <div class="flex justify-between items-center animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-32"></div>
            <div class="flex space-x-2">
                <div class="h-8 bg-gray-200 rounded w-20"></div>
                <div class="h-8 bg-gray-200 rounded w-8"></div>
                <div class="h-8 bg-gray-200 rounded w-8"></div>
                <div class="h-8 bg-gray-200 rounded w-20"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* Skeleton Animation Styles */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.hidden {
    display: none !important;
}
</style>