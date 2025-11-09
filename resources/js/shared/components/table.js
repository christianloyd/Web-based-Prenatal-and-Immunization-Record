/**
 * Shared Table Component
 * Reusable DataTable wrapper with common configurations
 *
 * This module provides a standardized way to initialize and manage DataTables
 * across the application with role-based defaults.
 *
 * @module shared/components/table
 */

/**
 * Default DataTable configuration
 * @type {Object}
 */
const defaultConfig = {
    responsive: true,
    autoWidth: false,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    language: {
        search: 'Search:',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty: 'Showing 0 to 0 of 0 entries',
        infoFiltered: '(filtered from _MAX_ total entries)',
        zeroRecords: 'No matching records found',
        emptyTable: 'No data available in table',
        paginate: {
            first: 'First',
            last: 'Last',
            next: 'Next',
            previous: 'Previous'
        }
    },
    dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4"ip>',
    ordering: true,
    searching: true,
    processing: true
};

/**
 * DataTable wrapper class
 */
export class DataTable {
    /**
     * Create a DataTable instance
     *
     * @param {string|HTMLElement} tableElement - Table element or selector
     * @param {Object} config - DataTable configuration
     * @param {Object} options - Additional options
     * @param {function} options.onInit - Callback after initialization
     * @param {function} options.onDraw - Callback after table draw
     */
    constructor(tableElement, config = {}, options = {}) {
        this.element = typeof tableElement === 'string'
            ? document.querySelector(tableElement)
            : tableElement;

        if (!this.element) {
            console.error('[DataTable] Table element not found:', tableElement);
            return;
        }

        this.config = { ...defaultConfig, ...config };
        this.options = options;
        this.table = null;

        this.initialize();
    }

    /**
     * Initialize DataTable
     * @private
     */
    initialize() {
        // Check if DataTables library is loaded
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('[DataTable] DataTables library not loaded');
            return;
        }

        // Add draw callback if provided
        if (this.options.onDraw) {
            this.config.drawCallback = () => {
                this.options.onDraw(this);
            };
        }

        // Add init complete callback
        if (this.options.onInit) {
            this.config.initComplete = () => {
                this.options.onInit(this);
            };
        }

        // Initialize DataTable
        this.table = $(this.element).DataTable(this.config);

        console.log('[DataTable] Initialized:', this.element.id || 'table');
    }

    /**
     * Reload table data
     *
     * @param {boolean} resetPaging - Reset to first page (default: false)
     * @returns {void}
     *
     * @example
     * table.reload(); // Keep current page
     * table.reload(true); // Reset to first page
     */
    reload(resetPaging = false) {
        if (this.table) {
            this.table.ajax.reload(null, resetPaging);
        }
    }

    /**
     * Clear table and redraw
     *
     * @returns {void}
     */
    clear() {
        if (this.table) {
            this.table.clear().draw();
        }
    }

    /**
     * Add a row to the table
     *
     * @param {Array|Object} data - Row data
     * @param {boolean} redraw - Redraw table after adding (default: true)
     * @returns {void}
     *
     * @example
     * table.addRow({ name: 'John', age: 30, phone: '09123456789' });
     */
    addRow(data, redraw = true) {
        if (this.table) {
            this.table.row.add(data);
            if (redraw) {
                this.table.draw();
            }
        }
    }

    /**
     * Update a row in the table
     *
     * @param {number|Node} row - Row index or node
     * @param {Array|Object} data - New row data
     * @returns {void}
     *
     * @example
     * table.updateRow(0, { name: 'Jane', age: 25 });
     */
    updateRow(row, data) {
        if (this.table) {
            this.table.row(row).data(data).draw();
        }
    }

    /**
     * Remove a row from the table
     *
     * @param {number|Node} row - Row index or node
     * @returns {void}
     *
     * @example
     * table.removeRow(0);
     */
    removeRow(row) {
        if (this.table) {
            this.table.row(row).remove().draw();
        }
    }

    /**
     * Search table
     *
     * @param {string} query - Search query
     * @returns {void}
     *
     * @example
     * table.search('John Doe');
     */
    search(query) {
        if (this.table) {
            this.table.search(query).draw();
        }
    }

    /**
     * Filter by column
     *
     * @param {number} columnIndex - Column index
     * @param {string} value - Filter value
     * @returns {void}
     *
     * @example
     * table.filterColumn(2, 'Active');
     */
    filterColumn(columnIndex, value) {
        if (this.table) {
            this.table.column(columnIndex).search(value).draw();
        }
    }

    /**
     * Get all data from table
     *
     * @returns {Array} Array of row data
     */
    getData() {
        if (this.table) {
            return this.table.rows().data().toArray();
        }
        return [];
    }

    /**
     * Get selected rows
     *
     * @param {string} selector - Row selector (default: '.selected')
     * @returns {Array} Array of selected row data
     *
     * @example
     * const selected = table.getSelected();
     */
    getSelected(selector = '.selected') {
        if (this.table) {
            return this.table.rows(selector).data().toArray();
        }
        return [];
    }

    /**
     * Destroy DataTable instance
     *
     * @returns {void}
     */
    destroy() {
        if (this.table) {
            this.table.destroy();
            this.table = null;
        }
    }
}

/**
 * Create a simple DataTable
 *
 * @param {string} selector - Table selector
 * @param {Object} config - DataTable configuration
 * @returns {DataTable} DataTable instance
 *
 * @example
 * const table = createDataTable('#patientsTable', {
 *     ajax: '/midwife/patients/data',
 *     columns: [
 *         { data: 'name' },
 *         { data: 'phone' },
 *         { data: 'address' }
 *     ]
 * });
 */
export function createDataTable(selector, config = {}) {
    return new DataTable(selector, config);
}

/**
 * Create a DataTable with AJAX data source
 *
 * @param {string} selector - Table selector
 * @param {string} ajaxUrl - AJAX endpoint URL
 * @param {Array} columns - Column definitions
 * @param {Object} extraConfig - Additional configuration
 * @returns {DataTable} DataTable instance
 *
 * @example
 * const table = createAjaxDataTable(
 *     '#patientsTable',
 *     '/midwife/patients/data',
 *     [
 *         { data: 'name', title: 'Name' },
 *         { data: 'phone', title: 'Phone' },
 *         { data: 'actions', title: 'Actions', orderable: false }
 *     ]
 * );
 */
export function createAjaxDataTable(selector, ajaxUrl, columns, extraConfig = {}) {
    const config = {
        ajax: {
            url: ajaxUrl,
            dataSrc: 'data',
            error: function(xhr, error, code) {
                console.error('[DataTable] AJAX Error:', error, code);
            }
        },
        columns: columns,
        ...extraConfig
    };

    return new DataTable(selector, config);
}

/**
 * Create a server-side DataTable
 *
 * @param {string} selector - Table selector
 * @param {string} ajaxUrl - AJAX endpoint URL
 * @param {Array} columns - Column definitions
 * @param {Object} extraConfig - Additional configuration
 * @returns {DataTable} DataTable instance
 *
 * @example
 * const table = createServerSideDataTable(
 *     '#patientsTable',
 *     '/midwife/patients/data',
 *     [
 *         { data: 'name', name: 'name' },
 *         { data: 'phone', name: 'phone' },
 *         { data: 'created_at', name: 'created_at' }
 *     ]
 * );
 */
export function createServerSideDataTable(selector, ajaxUrl, columns, extraConfig = {}) {
    const config = {
        processing: true,
        serverSide: true,
        ajax: {
            url: ajaxUrl,
            type: 'GET',
            error: function(xhr, error, code) {
                console.error('[DataTable] Server-side Error:', error, code);
            }
        },
        columns: columns,
        ...extraConfig
    };

    return new DataTable(selector, config);
}

/**
 * Common column render functions
 */
export const columnRenderers = {
    /**
     * Render date column
     * @param {string} data - Date string
     * @returns {string} Formatted date
     */
    date: function(data) {
        if (!data) return '-';
        const date = new Date(data);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    /**
     * Render datetime column
     * @param {string} data - Datetime string
     * @returns {string} Formatted datetime
     */
    datetime: function(data) {
        if (!data) return '-';
        const date = new Date(data);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Render status badge
     * @param {string} data - Status value
     * @returns {string} HTML badge
     */
    status: function(data) {
        const colors = {
            active: 'green',
            inactive: 'gray',
            pending: 'yellow',
            completed: 'blue',
            cancelled: 'red'
        };
        const color = colors[data?.toLowerCase()] || 'gray';
        return `<span class="px-2 py-1 text-xs rounded-full bg-${color}-100 text-${color}-800">${data}</span>`;
    },

    /**
     * Render phone number
     * @param {string} data - Phone number
     * @returns {string} Formatted phone
     */
    phone: function(data) {
        if (!data) return '-';
        // Format as 0912-345-6789
        return data.replace(/(\d{4})(\d{3})(\d{4})/, '$1-$2-$3');
    },

    /**
     * Render action buttons
     * @param {Object} options - Button options
     * @returns {function} Render function
     */
    actions: function(options = {}) {
        return function(data, type, row) {
            const buttons = [];
            const id = row.id || row.DT_RowId;

            if (options.view) {
                buttons.push(`<button class="btn-view text-blue-600 hover:text-blue-800 mr-2" data-id="${id}">
                    <i class="fas fa-eye"></i>
                </button>`);
            }

            if (options.edit) {
                buttons.push(`<button class="btn-edit text-green-600 hover:text-green-800 mr-2" data-id="${id}">
                    <i class="fas fa-edit"></i>
                </button>`);
            }

            if (options.delete) {
                buttons.push(`<button class="btn-delete text-red-600 hover:text-red-800" data-id="${id}">
                    <i class="fas fa-trash"></i>
                </button>`);
            }

            return buttons.join('');
        };
    }
};

// Export as default
export default {
    DataTable,
    createDataTable,
    createAjaxDataTable,
    createServerSideDataTable,
    columnRenderers
};
