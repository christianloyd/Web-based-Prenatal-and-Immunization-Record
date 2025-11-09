/**
 * Shared DOM Utilities
 * Common DOM manipulation and helper functions
 *
 * This module provides utility functions for common DOM operations,
 * reducing code duplication across the application.
 *
 * @module shared/utils/dom
 */

/**
 * Safely query a single element
 *
 * @param {string} selector - CSS selector
 * @param {Element|Document} context - Context element (default: document)
 * @returns {Element|null} Element or null if not found
 *
 * @example
 * const button = qs('#submitBtn');
 * const input = qs('input[name="phone"]', form);
 */
export function qs(selector, context = document) {
    return context.querySelector(selector);
}

/**
 * Safely query all elements
 *
 * @param {string} selector - CSS selector
 * @param {Element|Document} context - Context element (default: document)
 * @returns {NodeList} NodeList of elements
 *
 * @example
 * const buttons = qsAll('.action-btn');
 * const inputs = qsAll('input', form);
 */
export function qsAll(selector, context = document) {
    return context.querySelectorAll(selector);
}

/**
 * Show element by removing 'hidden' class
 *
 * @param {Element|string} element - Element or selector
 * @returns {void}
 *
 * @example
 * show('#errorMessage');
 * show(document.getElementById('modal'));
 */
export function show(element) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (el) {
        el.classList.remove('hidden');
    }
}

/**
 * Hide element by adding 'hidden' class
 *
 * @param {Element|string} element - Element or selector
 * @returns {void}
 *
 * @example
 * hide('#loadingSpinner');
 * hide(document.getElementById('modal'));
 */
export function hide(element) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (el) {
        el.classList.add('hidden');
    }
}

/**
 * Toggle element visibility
 *
 * @param {Element|string} element - Element or selector
 * @returns {void}
 *
 * @example
 * toggle('#advancedFilters');
 */
export function toggle(element) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (el) {
        el.classList.toggle('hidden');
    }
}

/**
 * Check if element is visible
 *
 * @param {Element|string} element - Element or selector
 * @returns {boolean} True if element is visible
 *
 * @example
 * if (isVisible('#modal')) {
 *     console.log('Modal is open');
 * }
 */
export function isVisible(element) {
    const el = typeof element === 'string' ? qs(element) : element;
    return el ? !el.classList.contains('hidden') : false;
}

/**
 * Add event listener with delegation support
 *
 * @param {Element|string} target - Target element or selector
 * @param {string} eventType - Event type (e.g., 'click', 'change')
 * @param {string|function} selectorOrHandler - Delegate selector or handler function
 * @param {function} handler - Handler function (if using delegation)
 * @returns {void}
 *
 * @example
 * // Direct listener
 * on('#submitBtn', 'click', () => console.log('Clicked'));
 *
 * // Delegated listener
 * on('#tableBody', 'click', '.delete-btn', (e) => {
 *     console.log('Delete clicked', e.target);
 * });
 */
export function on(target, eventType, selectorOrHandler, handler) {
    const el = typeof target === 'string' ? qs(target) : target;
    if (!el) return;

    if (typeof selectorOrHandler === 'function') {
        // Direct event listener
        el.addEventListener(eventType, selectorOrHandler);
    } else {
        // Delegated event listener
        el.addEventListener(eventType, (e) => {
            const delegateTarget = e.target.closest(selectorOrHandler);
            if (delegateTarget) {
                handler.call(delegateTarget, e);
            }
        });
    }
}

/**
 * Disable element (button, input, etc.)
 *
 * @param {Element|string} element - Element or selector
 * @param {string} loadingText - Optional loading text for buttons
 * @returns {void}
 *
 * @example
 * disable('#submitBtn', 'Saving...');
 */
export function disable(element, loadingText = null) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (!el) return;

    el.disabled = true;
    el.classList.add('opacity-50', 'cursor-not-allowed');

    if (loadingText && el.tagName === 'BUTTON') {
        el.setAttribute('data-original-text', el.innerHTML);
        el.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${loadingText}`;
    }
}

/**
 * Enable element (button, input, etc.)
 *
 * @param {Element|string} element - Element or selector
 * @returns {void}
 *
 * @example
 * enable('#submitBtn');
 */
export function enable(element) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (!el) return;

    el.disabled = false;
    el.classList.remove('opacity-50', 'cursor-not-allowed');

    if (el.hasAttribute('data-original-text')) {
        el.innerHTML = el.getAttribute('data-original-text');
        el.removeAttribute('data-original-text');
    }
}

/**
 * Set element text content
 *
 * @param {Element|string} element - Element or selector
 * @param {string} text - Text content
 * @returns {void}
 *
 * @example
 * setText('#patientName', 'John Doe');
 */
export function setText(element, text) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (el) {
        el.textContent = text;
    }
}

/**
 * Set element HTML content
 *
 * @param {Element|string} element - Element or selector
 * @param {string} html - HTML content
 * @returns {void}
 *
 * @example
 * setHtml('#results', '<p>No results found</p>');
 */
export function setHtml(element, html) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (el) {
        el.innerHTML = html;
    }
}

/**
 * Get form data as object
 *
 * @param {HTMLFormElement|string} form - Form element or selector
 * @returns {Object} Form data as key-value pairs
 *
 * @example
 * const data = getFormData('#patientForm');
 * console.log(data); // { name: 'John', phone: '09123456789' }
 */
export function getFormData(form) {
    const formEl = typeof form === 'string' ? qs(form) : form;
    if (!formEl) return {};

    const formData = new FormData(formEl);
    const data = {};

    for (const [key, value] of formData.entries()) {
        // Handle multiple values for same key (checkboxes, multi-select)
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
 * Set form data from object
 *
 * @param {HTMLFormElement|string} form - Form element or selector
 * @param {Object} data - Data object
 * @returns {void}
 *
 * @example
 * setFormData('#patientForm', {
 *     name: 'John Doe',
 *     phone: '09123456789'
 * });
 */
export function setFormData(form, data) {
    const formEl = typeof form === 'string' ? qs(form) : form;
    if (!formEl) return;

    for (const [key, value] of Object.entries(data)) {
        const input = formEl.querySelector(`[name="${key}"]`);
        if (input) {
            if (input.type === 'checkbox') {
                input.checked = value;
            } else if (input.type === 'radio') {
                const radio = formEl.querySelector(`[name="${key}"][value="${value}"]`);
                if (radio) radio.checked = true;
            } else {
                input.value = value;
            }
        }
    }
}

/**
 * Reset form and clear validation errors
 *
 * @param {HTMLFormElement|string} form - Form element or selector
 * @returns {void}
 *
 * @example
 * resetForm('#patientForm');
 */
export function resetForm(form) {
    const formEl = typeof form === 'string' ? qs(form) : form;
    if (!formEl) return;

    formEl.reset();

    // Clear validation errors
    const errorElements = formEl.querySelectorAll('[data-validation-error="true"]');
    errorElements.forEach(el => el.remove());

    // Remove error classes
    const inputs = formEl.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        input.classList.add('border-gray-300');
    });
}

/**
 * Focus first input in form or container
 *
 * @param {Element|string} container - Container element or selector
 * @param {number} delay - Delay in milliseconds before focusing
 * @returns {void}
 *
 * @example
 * focusFirstInput('#patientForm');
 * focusFirstInput('#modal', 300); // Wait 300ms before focusing
 */
export function focusFirstInput(container, delay = 0) {
    const containerEl = typeof container === 'string' ? qs(container) : container;
    if (!containerEl) return;

    const focusInput = () => {
        const input = containerEl.querySelector('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])');
        if (input) {
            input.focus();
        }
    };

    if (delay > 0) {
        setTimeout(focusInput, delay);
    } else {
        focusInput();
    }
}

/**
 * Create element with attributes and content
 *
 * @param {string} tag - HTML tag name
 * @param {Object} attributes - Element attributes
 * @param {string|Element|Array} content - Element content
 * @returns {Element} Created element
 *
 * @example
 * const button = createElement('button', {
 *     class: 'btn btn-primary',
 *     id: 'submitBtn',
 *     type: 'submit'
 * }, 'Save');
 *
 * const div = createElement('div', { class: 'container' }, [
 *     createElement('h1', {}, 'Title'),
 *     createElement('p', {}, 'Content')
 * ]);
 */
export function createElement(tag, attributes = {}, content = null) {
    const element = document.createElement(tag);

    // Set attributes
    for (const [key, value] of Object.entries(attributes)) {
        if (key === 'class') {
            element.className = value;
        } else if (key === 'dataset') {
            Object.assign(element.dataset, value);
        } else {
            element.setAttribute(key, value);
        }
    }

    // Set content
    if (content !== null) {
        if (Array.isArray(content)) {
            content.forEach(child => {
                if (typeof child === 'string') {
                    element.appendChild(document.createTextNode(child));
                } else {
                    element.appendChild(child);
                }
            });
        } else if (typeof content === 'string') {
            element.textContent = content;
        } else if (content instanceof Element) {
            element.appendChild(content);
        }
    }

    return element;
}

/**
 * Debounce function calls
 *
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {function} Debounced function
 *
 * @example
 * const searchInput = qs('#search');
 * const debouncedSearch = debounce((value) => {
 *     console.log('Searching for:', value);
 * }, 500);
 *
 * searchInput.addEventListener('input', (e) => {
 *     debouncedSearch(e.target.value);
 * });
 */
export function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Scroll to element smoothly
 *
 * @param {Element|string} element - Element or selector
 * @param {Object} options - Scroll options
 * @returns {void}
 *
 * @example
 * scrollTo('#topOfPage');
 * scrollTo('#errorSection', { behavior: 'smooth', block: 'center' });
 */
export function scrollTo(element, options = {}) {
    const el = typeof element === 'string' ? qs(element) : element;
    if (el) {
        el.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
            ...options
        });
    }
}

// Export as default
export default {
    qs,
    qsAll,
    show,
    hide,
    toggle,
    isVisible,
    on,
    disable,
    enable,
    setText,
    setHtml,
    getFormData,
    setFormData,
    resetForm,
    focusFirstInput,
    createElement,
    debounce,
    scrollTo
};
