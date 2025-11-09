/**
 * Filter Management Module
 * Handles status filtering, search, and advanced filters
 *
 * @module midwife/immunization/filters
 */

import { ImmunizationState } from './state';
import { refreshTable } from './table';

/**
 * Initializes filter event listeners
 *
 * @param {ImmunizationState} state - Application state instance
 * @returns {void}
 */
export function initializeFilters(state) {
    initializeStatusFilters(state);
    initializeSearchFilter(state);
    initializeAdvancedFilters(state);

    console.log('[Immunization] Filters initialized');
}

/**
 * Initializes status filter tabs
 *
 * @private
 * @param {ImmunizationState} state - Application state
 * @returns {void}
 */
function initializeStatusFilters(state) {
    const statusButtons = document.querySelectorAll('[data-status-filter]');

    statusButtons.forEach(button => {
        button.addEventListener('click', () => {
            const status = button.dataset.statusFilter;

            // Update state
            state.setStatus(status);

            // Update UI - active state
            statusButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Refresh table
            refreshTable(state);

            console.log(`[Immunization] Status filter changed to: ${status}`);
        });
    });
}

/**
 * Initializes search filter
 *
 * @private
 * @param {ImmunizationState} state - Application state
 * @returns {void}
 */
function initializeSearchFilter(state) {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const clearButton = document.getElementById('clearSearch');

    if (!searchInput) {
        console.warn('[Immunization] Search input not found');
        return;
    }

    // Search on button click
    if (searchButton) {
        searchButton.addEventListener('click', () => performSearch(state, searchInput));
    }

    // Search on Enter key
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch(state, searchInput);
        }
    });

    // Clear search
    if (clearButton) {
        clearButton.addEventListener('click', () => {
            searchInput.value = '';
            state.setSearchTerm(null);
            refreshTable(state);
            console.log('[Immunization] Search cleared');
        });
    }
}

/**
 * Performs search operation
 *
 * @private
 * @param {ImmunizationState} state - Application state
 * @param {HTMLInputElement} searchInput - Search input element
 * @returns {void}
 */
function performSearch(state, searchInput) {
    const searchTerm = searchInput.value.trim();

    if (searchTerm.length === 0) {
        state.setSearchTerm(null);
    } else if (searchTerm.length < 2) {
        console.warn('[Immunization] Search term too short (minimum 2 characters)');
        return;
    } else {
        state.setSearchTerm(searchTerm);
    }

    refreshTable(state);
    console.log(`[Immunization] Search performed: ${searchTerm || 'cleared'}`);
}

/**
 * Initializes advanced filters (vaccine, date range)
 *
 * @private
 * @param {ImmunizationState} state - Application state
 * @returns {void}
 */
function initializeAdvancedFilters(state) {
    const vaccineFilter = document.getElementById('vaccineFilter');
    const dateFromFilter = document.getElementById('dateFromFilter');
    const dateToFilter = document.getElementById('dateToFilter');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');

    // Apply filters button
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', () => {
            const filters = {};

            if (vaccineFilter && vaccineFilter.value) {
                filters.vaccine = vaccineFilter.value;
            }

            if (dateFromFilter && dateFromFilter.value) {
                filters.dateFrom = dateFromFilter.value;
            }

            if (dateToFilter && dateToFilter.value) {
                filters.dateTo = dateToFilter.value;
            }

            // Validate date range
            if (filters.dateFrom && filters.dateTo) {
                if (new Date(filters.dateFrom) > new Date(filters.dateTo)) {
                    alert('Invalid date range: "From" date must be before "To" date');
                    return;
                }
            }

            state.updateFilters(filters);
            refreshTable(state);

            console.log('[Immunization] Advanced filters applied:', filters);
        });
    }

    // Reset filters button
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', () => {
            if (vaccineFilter) vaccineFilter.value = '';
            if (dateFromFilter) dateFromFilter.value = '';
            if (dateToFilter) dateToFilter.value = '';

            state.resetFilters();
            refreshTable(state);

            console.log('[Immunization] Filters reset');
        });
    }
}

/**
 * Gets current filter summary for display
 *
 * @param {ImmunizationState} state - Application state
 * @returns {string} Human-readable filter summary
 */
export function getFilterSummary(state) {
    const parts = [];

    if (state.currentStatus !== 'all') {
        parts.push(`Status: ${state.currentStatus}`);
    }

    if (state.searchTerm) {
        parts.push(`Search: "${state.searchTerm}"`);
    }

    if (state.filters.vaccine) {
        parts.push(`Vaccine: ${state.filters.vaccine}`);
    }

    if (state.filters.dateFrom || state.filters.dateTo) {
        const dateRange = [
            state.filters.dateFrom || '...',
            state.filters.dateTo || '...'
        ].join(' to ');
        parts.push(`Dates: ${dateRange}`);
    }

    return parts.length > 0 ? parts.join(' | ') : 'All records';
}
