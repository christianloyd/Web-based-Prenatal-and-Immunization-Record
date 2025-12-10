@if($childRecords->count() > 0)
    <div class="table-wrapper">
        <table class="w-full table-container">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <!--<th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Child ID</th>-->
                    <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'child_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                            Child Name <i class="fas fa-sort ml-1 text-gray-400"></i>
                        </a>
                    </th>
                    <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Gender</th>
                    <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'birthdate', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="flex items-center hover:text-gray-800">
                            Birth Date <i class="fas fa-sort ml-1 text-gray-400"></i>
                        </a>
                    </th>
                    <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Mother's Name</th>
                    {{-- <th class="px-2 sm:px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap hide-mobile">Phone Number</th> --}}
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($childRecords as $record)
                <tr class="table-row-hover">
                    <!--<td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                        <div class="font-medium text-blue-600">{{ $record->formatted_child_id ?? 'CH-001' }}</div>
                    </td>-->
                    <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                        <div class="font-medium text-gray-900">{{ $record->full_name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500 sm:hidden">{{ $record->mother_name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ ($record->gender ?? '') === 'Male' ? 'gender-badge-male' : 'gender-badge-female' }}">
                            {{ $record->gender ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-2 sm:px-4 py-3 text-gray-700 whitespace-nowrap">
                        <div class="text-sm sm:text-base">{{ $record->birthdate ? $record->birthdate->format('M j, Y') : 'N/A' }}</div>
                        {{-- <div class="text-xs text-gray-500 sm:hidden">{{ $record->phone_number ?? 'N/A' }}</div> --}}
                    </td>
                    <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                        {{ $record->mother_name ?? 'N/A' }}
                    </td>
                    {{-- <td class="px-2 sm:px-4 py-3 text-gray-700 hide-mobile">
                        {{ $record->phone_number ?? 'N/A' }}
                    </td> --}}
                    <td class="px-2 sm:px-4 py-3 whitespace-nowrap">
                        <div class="action-buttons flex flex-col sm:flex-row sm:justify-center space-y-2 sm:space-y-0 sm:space-x-2">
                            <a href="{{ route(auth()->user()->role . '.childrecord.show', $record->id) }}" class="btn-action btn-view inline-flex items-center justify-center">
                                <i class="fas fa-eye mr-1"></i><span class="hidden sm:inline">View</span>
                            </a>
                            <a href="#" onclick='openEditRecordModal(@json($record->toArray()))' class="btn-action btn-edit inline-flex items-center justify-center">
                                <i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">Edit</span>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @php
        $paginator = $childRecords;
        $currentPage = $paginator->currentPage();
        $lastPage = max(1, $paginator->lastPage());
    @endphp

    <div class="px-6 py-3 border-top border-gray-200 flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-sm text-gray-600">
        <div>
            Showing
            <span class="font-medium">{{ $paginator->firstItem() ?? ($paginator->count() ? 1 : 0) }}</span>
            to
            <span class="font-medium">{{ $paginator->lastItem() ?? $paginator->count() }}</span>
            of
            <span class="font-medium">{{ $paginator->total() }}</span>
            results
        </div>

        <nav class="inline-flex items-center gap-1" role="navigation" aria-label="Pagination">
            @php $prevDisabled = $paginator->onFirstPage(); @endphp
            <a
                href="{{ $prevDisabled ? '#' : $paginator->previousPageUrl() }}"
                class="pagination-btn {{ $prevDisabled ? 'disabled' : '' }}"
                aria-disabled="{{ $prevDisabled ? 'true' : 'false' }}"
                aria-label="Previous page"
            >
                <i class="fas fa-chevron-left"></i>
            </a>

            @for ($page = 1; $page <= $lastPage; $page++)
                <a
                    href="{{ $paginator->url($page) }}"
                    class="pagination-btn {{ $page === $currentPage ? 'active' : '' }}"
                    aria-current="{{ $page === $currentPage ? 'page' : 'false' }}"
                >
                    {{ $page }}
                </a>
            @endfor

            @php $nextDisabled = !$paginator->hasMorePages(); @endphp
            <a
                href="{{ $nextDisabled ? '#' : $paginator->nextPageUrl() }}"
                class="pagination-btn {{ $nextDisabled ? 'disabled' : '' }}"
                aria-disabled="{{ $nextDisabled ? 'true' : 'false' }}"
                aria-label="Next page"
            >
                <i class="fas fa-chevron-right"></i>
            </a>
        </nav>
    </div>
@else
    <!-- Empty State -->
    <div class="text-center py-16 px-4">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-baby text-gray-400 text-xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No child records found</h3>
        <p class="text-gray-500 mb-6 max-w-sm mx-auto">
            @if(request()->hasAny(['search', 'gender']))
                No records match your search criteria. Try adjusting your filters.
            @else
                Get started by adding your first child record.
            @endif
        </p>
        <button onclick="openAddModal()" class="btn-minimal btn-primary-clean px-6 py-3 rounded-lg font-medium inline-flex items-center">
            <i class="fas fa-plus mr-2"></i>Add Child Record
        </button>
    </div>
@endif
