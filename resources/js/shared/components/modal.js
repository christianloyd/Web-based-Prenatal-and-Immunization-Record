/**
 * Shared Modal Component
 * Reusable modal management for all application pages
 *
 * This module provides a standardized way to manage modals across the application,
 * eliminating duplicate modal handling code.
 *
 * @module shared/components/modal
 */

import { qs, show, hide, on, focusFirstInput, resetForm } from '../utils/dom.js';

/**
 * Modal class for managing modal dialogs
 */
export class Modal {
    /**
     * Create a Modal instance
     *
     * @param {string|HTMLElement} modalElement - Modal element or selector
     * @param {Object} options - Modal configuration options
     * @param {boolean} options.closeOnEscape - Close modal on ESC key (default: true)
     * @param {boolean} options.closeOnBackdrop - Close modal on backdrop click (default: true)
     * @param {function} options.onOpen - Callback when modal opens
     * @param {function} options.onClose - Callback when modal closes
     * @param {number} options.focusDelay - Delay before focusing first input (default: 300ms)
     */
    constructor(modalElement, options = {}) {
        this.modal = typeof modalElement === 'string' ? qs(modalElement) : modalElement;

        if (!this.modal) {
            console.error('[Modal] Modal element not found:', modalElement);
            return;
        }

        this.options = {
            closeOnEscape: true,
            closeOnBackdrop: true,
            onOpen: null,
            onClose: null,
            focusDelay: 300,
            ...options
        };

        this.isOpen = false;
        this.form = this.modal.querySelector('form');

        this.initialize();
    }

    /**
     * Initialize modal event listeners
     * @private
     */
    initialize() {
        // Close button listeners
        const closeButtons = this.modal.querySelectorAll('[data-modal-close]');
        closeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.close();
            });
        });

        // Backdrop click listener
        if (this.options.closeOnBackdrop) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.close();
                }
            });
        }

        // ESC key listener
        if (this.options.closeOnEscape) {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });
        }
    }

    /**
     * Open the modal
     *
     * @param {Object} data - Optional data to populate form
     * @returns {void}
     *
     * @example
     * const modal = new Modal('#patientModal');
     * modal.open({ name: 'John Doe', phone: '09123456789' });
     */
    open(data = null) {
        if (this.isOpen) return;

        // Reset form if exists
        if (this.form) {
            resetForm(this.form);
        }

        // Populate form with data
        if (data && this.form) {
            this.populateForm(data);
        }

        // Show modal
        this.modal.classList.remove('hidden');

        // Add animation class after a tick
        requestAnimationFrame(() => {
            this.modal.classList.add('show');
        });

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Focus first input
        if (this.form) {
            focusFirstInput(this.form, this.options.focusDelay);
        }

        this.isOpen = true;

        // Call onOpen callback
        if (this.options.onOpen && typeof this.options.onOpen === 'function') {
            this.options.onOpen(this);
        }
    }

    /**
     * Close the modal
     *
     * @returns {void}
     *
     * @example
     * modal.close();
     */
    close() {
        if (!this.isOpen) return;

        // Remove animation class
        this.modal.classList.remove('show');

        // Hide modal after animation
        setTimeout(() => {
            this.modal.classList.add('hidden');
        }, 300);

        // Restore body scroll
        document.body.style.overflow = '';

        this.isOpen = false;

        // Call onClose callback
        if (this.options.onClose && typeof this.options.onClose === 'function') {
            this.options.onClose(this);
        }
    }

    /**
     * Toggle modal open/close
     *
     * @returns {void}
     */
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    /**
     * Populate form with data
     * @private
     *
     * @param {Object} data - Form data
     * @returns {void}
     */
    populateForm(data) {
        if (!this.form) return;

        for (const [key, value] of Object.entries(data)) {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = value;
                } else if (input.type === 'radio') {
                    const radio = this.form.querySelector(`[name="${key}"][value="${value}"]`);
                    if (radio) radio.checked = true;
                } else {
                    input.value = value || '';
                }
            }
        }
    }

    /**
     * Get form data
     *
     * @returns {Object} Form data as key-value pairs
     *
     * @example
     * const data = modal.getFormData();
     * console.log(data); // { name: 'John', phone: '09123456789' }
     */
    getFormData() {
        if (!this.form) return {};

        const formData = new FormData(this.form);
        const data = {};

        for (const [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }

        return data;
    }

    /**
     * Reset form in modal
     *
     * @returns {void}
     */
    reset() {
        if (this.form) {
            resetForm(this.form);
        }
    }
}

/**
 * Create and manage multiple modals
 */
export class ModalManager {
    constructor() {
        this.modals = new Map();
    }

    /**
     * Register a modal
     *
     * @param {string} name - Modal identifier
     * @param {string|HTMLElement} modalElement - Modal element or selector
     * @param {Object} options - Modal options
     * @returns {Modal} Modal instance
     *
     * @example
     * const manager = new ModalManager();
     * manager.register('add', '#addPatientModal');
     * manager.register('edit', '#editPatientModal');
     */
    register(name, modalElement, options = {}) {
        const modal = new Modal(modalElement, options);
        this.modals.set(name, modal);
        return modal;
    }

    /**
     * Get a registered modal
     *
     * @param {string} name - Modal identifier
     * @returns {Modal|null} Modal instance or null
     *
     * @example
     * const modal = manager.get('add');
     * modal.open();
     */
    get(name) {
        return this.modals.get(name) || null;
    }

    /**
     * Open a registered modal
     *
     * @param {string} name - Modal identifier
     * @param {Object} data - Optional data to populate
     * @returns {void}
     *
     * @example
     * manager.open('edit', { id: 123, name: 'John Doe' });
     */
    open(name, data = null) {
        const modal = this.get(name);
        if (modal) {
            modal.open(data);
        } else {
            console.warn(`[ModalManager] Modal "${name}" not found`);
        }
    }

    /**
     * Close a registered modal
     *
     * @param {string} name - Modal identifier
     * @returns {void}
     */
    close(name) {
        const modal = this.get(name);
        if (modal) {
            modal.close();
        }
    }

    /**
     * Close all modals
     *
     * @returns {void}
     */
    closeAll() {
        this.modals.forEach(modal => modal.close());
    }
}

/**
 * Simple modal functions for backward compatibility
 */

/**
 * Open a modal by selector
 *
 * @param {string} selector - Modal selector
 * @param {Object} data - Optional data to populate
 * @returns {void}
 *
 * @example
 * openModal('#patientModal', { name: 'John' });
 */
export function openModal(selector, data = null) {
    const modal = qs(selector);
    if (!modal) return;

    const modalInstance = new Modal(modal);
    modalInstance.open(data);
}

/**
 * Close a modal by selector
 *
 * @param {string} selector - Modal selector
 * @returns {void}
 *
 * @example
 * closeModal('#patientModal');
 */
export function closeModal(selector) {
    const modal = qs(selector);
    if (!modal) return;

    modal.classList.remove('show');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
    document.body.style.overflow = '';
}

// Export as default
export default {
    Modal,
    ModalManager,
    openModal,
    closeModal
};
