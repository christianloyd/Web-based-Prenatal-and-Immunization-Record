/**
 * Immunization State Management
 * Centralized state for immunization index page
 *
 * @module midwife/immunization/state
 */

/**
 * ImmunizationState class
 * Manages application state for immunization management
 */
export class ImmunizationState {
    constructor() {
        /** @type {string} Current active status filter */
        this.currentStatus = 'all';

        /** @type {string|null} Current search term */
        this.searchTerm = null;

        /** @type {object} Current filter values */
        this.filters = {
            status: 'all',
            vaccine: null,
            dateFrom: null,
            dateTo: null,
        };

        /** @type {number|null} Currently selected immunization ID */
        this.selectedImmunizationId = null;

        /** @type {object | null} Currently selected immunization data */
        this.selectedImmunization = null;

        /** @type {boolean} Loading state */
        this.isLoading = false;

        /** @type {Array} List of available vaccines */
        this.vaccines = [];

        /** @type {Array} List of child records */
        this.childRecords = [];
    }

    /**
     * Updates the current status filter
     * @param {string} status - New status value
     * @returns {void}
     */
    setStatus(status) {
        this.currentStatus = status;
        this.filters.status = status;
    }

    /**
     * Updates search term
     * @param {string|null} term - Search term
     * @returns {void}
     */
    setSearchTerm(term) {
        this.searchTerm = term;
    }

    /**
     * Updates filter values
     * @param {object} newFilters - Filter values to update
     * @returns {void}
     */
    updateFilters(newFilters) {
        this.filters = { ...this.filters, ...newFilters };
    }

    /**
     * Selects an immunization
     * @param {number} id - Immunization ID
     * @param {object} data - Immunization data
     * @returns {void}
     */
    selectImmunization(id, data) {
        this.selectedImmunizationId = id;
        this.selectedImmunization = data;
    }

    /**
     * Clears selected immunization
     * @returns {void}
     */
    clearSelection() {
        this.selectedImmunizationId = null;
        this.selectedImmunization = null;
    }

    /**
     * Sets loading state
     * @param {boolean} loading - Loading state
     * @returns {void}
     */
    setLoading(loading) {
        this.isLoading = loading;
    }

    /**
     * Sets available vaccines
     * @param {Array} vaccines - Vaccine list
     * @returns {void}
     */
    setVaccines(vaccines) {
        this.vaccines = vaccines;
    }

    /**
     * Sets child records
     * @param {Array} children - Child record list
     * @returns {void}
     */
    setChildRecords(children) {
        this.childRecords = children;
    }

    /**
     * Resets all filters to default
     * @returns {void}
     */
    resetFilters() {
        this.currentStatus = 'all';
        this.searchTerm = null;
        this.filters = {
            status: 'all',
            vaccine: null,
            dateFrom: null,
            dateTo: null,
        };
    }

    /**
     * Gets current query parameters for API requests
     * @returns {URLSearchParams}
     */
    getQueryParams() {
        const params = new URLSearchParams();

        if (this.filters.status && this.filters.status !== 'all') {
            params.append('status', this.filters.status);
        }

        if (this.searchTerm) {
            params.append('search', this.searchTerm);
        }

        if (this.filters.vaccine) {
            params.append('vaccine', this.filters.vaccine);
        }

        if (this.filters.dateFrom) {
            params.append('date_from', this.filters.dateFrom);
        }

        if (this.filters.dateTo) {
            params.append('date_to', this.filters.dateTo);
        }

        return params;
    }
}
