/**
 * Shared API Utilities
 * Standardized Axios request wrapper for all API calls
 *
 * This module provides a consistent interface for making HTTP requests
 * with built-in error handling, CSRF token management, and response formatting.
 *
 * @module shared/utils/api
 */

import { showError, showLoading, closeAlert } from './sweetalert.js';

/**
 * Get CSRF token from meta tag
 *
 * @returns {string|null} CSRF token or null if not found
 */
function getCsrfToken() {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    return tokenMeta ? tokenMeta.getAttribute('content') : null;
}

/**
 * Default request configuration
 * @type {Object}
 */
const defaultConfig = {
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    timeout: 30000 // 30 seconds
};

/**
 * Add CSRF token and merge with default config
 *
 * @param {Object} config - Request configuration
 * @returns {Object} Merged configuration with CSRF token
 */
function prepareConfig(config = {}) {
    const csrfToken = getCsrfToken();
    const headers = {
        ...defaultConfig.headers,
        ...(config.headers || {})
    };

    if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken;
    }

    return {
        ...defaultConfig,
        ...config,
        headers
    };
}

/**
 * Handle API response
 *
 * @param {Response} response - Fetch response object
 * @returns {Promise<any>} Parsed response data
 */
async function handleResponse(response) {
    const contentType = response.headers.get('content-type');
    const isJson = contentType && contentType.includes('application/json');

    let data;
    if (isJson) {
        data = await response.json();
    } else {
        data = await response.text();
    }

    if (!response.ok) {
        const error = new Error(data.message || 'An error occurred');
        error.status = response.status;
        error.data = data;
        throw error;
    }

    return data;
}

/**
 * Handle API error
 *
 * @param {Error} error - Error object
 * @param {boolean} showAlert - Whether to show error alert
 * @returns {Promise<never>} Rejected promise
 */
function handleError(error, showAlert = true) {
    console.error('[API Error]', error);

    let errorMessage = 'An unexpected error occurred';
    let errors = null;

    if (error.data) {
        errorMessage = error.data.message || errorMessage;
        errors = error.data.errors ? Object.values(error.data.errors).flat() : null;
    } else if (error.message) {
        errorMessage = error.message;
    }

    if (showAlert) {
        showError(errorMessage, errors);
    }

    return Promise.reject(error);
}

/**
 * Make a GET request
 *
 * @param {string} url - Request URL
 * @param {Object} params - Query parameters
 * @param {Object} config - Additional request configuration
 * @returns {Promise<any>} Response data
 *
 * @example
 * const patients = await get('/midwife/patients', { status: 'active', page: 1 });
 */
export async function get(url, params = {}, config = {}) {
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;

    try {
        const response = await fetch(fullUrl, prepareConfig({
            method: 'GET',
            ...config
        }));

        return await handleResponse(response);
    } catch (error) {
        return handleError(error, config.showError !== false);
    }
}

/**
 * Make a POST request
 *
 * @param {string} url - Request URL
 * @param {Object} data - Request body data
 * @param {Object} config - Additional request configuration
 * @returns {Promise<any>} Response data
 *
 * @example
 * const newPatient = await post('/midwife/patients', {
 *     name: 'John Doe',
 *     phone: '09123456789'
 * });
 */
export async function post(url, data = {}, config = {}) {
    try {
        const response = await fetch(url, prepareConfig({
            method: 'POST',
            body: JSON.stringify(data),
            ...config
        }));

        return await handleResponse(response);
    } catch (error) {
        return handleError(error, config.showError !== false);
    }
}

/**
 * Make a PUT request
 *
 * @param {string} url - Request URL
 * @param {Object} data - Request body data
 * @param {Object} config - Additional request configuration
 * @returns {Promise<any>} Response data
 *
 * @example
 * const updatedPatient = await put('/midwife/patients/123', {
 *     name: 'Jane Doe',
 *     phone: '09987654321'
 * });
 */
export async function put(url, data = {}, config = {}) {
    try {
        const response = await fetch(url, prepareConfig({
            method: 'PUT',
            body: JSON.stringify(data),
            ...config
        }));

        return await handleResponse(response);
    } catch (error) {
        return handleError(error, config.showError !== false);
    }
}

/**
 * Make a PATCH request
 *
 * @param {string} url - Request URL
 * @param {Object} data - Request body data
 * @param {Object} config - Additional request configuration
 * @returns {Promise<any>} Response data
 *
 * @example
 * const updated = await patch('/midwife/patients/123', { status: 'inactive' });
 */
export async function patch(url, data = {}, config = {}) {
    try {
        const response = await fetch(url, prepareConfig({
            method: 'PATCH',
            body: JSON.stringify(data),
            ...config
        }));

        return await handleResponse(response);
    } catch (error) {
        return handleError(error, config.showError !== false);
    }
}

/**
 * Make a DELETE request
 *
 * @param {string} url - Request URL
 * @param {Object} config - Additional request configuration
 * @returns {Promise<any>} Response data
 *
 * @example
 * await del('/midwife/patients/123');
 */
export async function del(url, config = {}) {
    try {
        const response = await fetch(url, prepareConfig({
            method: 'DELETE',
            ...config
        }));

        return await handleResponse(response);
    } catch (error) {
        return handleError(error, config.showError !== false);
    }
}

/**
 * Upload files using FormData
 *
 * @param {string} url - Request URL
 * @param {FormData} formData - FormData object with files
 * @param {Object} config - Additional request configuration
 * @param {function} onProgress - Optional progress callback
 * @returns {Promise<any>} Response data
 *
 * @example
 * const formData = new FormData();
 * formData.append('file', fileInput.files[0]);
 * formData.append('name', 'Patient Photo');
 *
 * const result = await upload('/midwife/patients/123/photo', formData, {},
 *     (progress) => console.log(`Upload progress: ${progress}%`)
 * );
 */
export async function upload(url, formData, config = {}, onProgress = null) {
    const csrfToken = getCsrfToken();

    try {
        const xhr = new XMLHttpRequest();

        // Setup progress tracking
        if (onProgress && typeof onProgress === 'function') {
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    onProgress(Math.round(percentComplete));
                }
            });
        }

        // Create promise for xhr
        const uploadPromise = new Promise((resolve, reject) => {
            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        resolve(data);
                    } catch (e) {
                        resolve(xhr.responseText);
                    }
                } else {
                    reject(new Error(`Upload failed with status ${xhr.status}`));
                }
            });

            xhr.addEventListener('error', () => {
                reject(new Error('Upload failed'));
            });

            xhr.addEventListener('abort', () => {
                reject(new Error('Upload aborted'));
            });
        });

        // Open connection and set headers
        xhr.open('POST', url);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        if (csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }

        // Send form data
        xhr.send(formData);

        return await uploadPromise;
    } catch (error) {
        return handleError(error, config.showError !== false);
    }
}

/**
 * Make request with loading indicator
 *
 * @param {function} requestFn - Async function that makes the request
 * @param {string} loadingMessage - Loading message to display
 * @returns {Promise<any>} Response data
 *
 * @example
 * const patients = await withLoading(
 *     () => get('/midwife/patients'),
 *     'Loading patients...'
 * );
 */
export async function withLoading(requestFn, loadingMessage = 'Loading...') {
    showLoading(loadingMessage);
    try {
        const result = await requestFn();
        closeAlert();
        return result;
    } catch (error) {
        closeAlert();
        throw error;
    }
}

/**
 * Batch multiple requests
 *
 * @param {Array<Promise>} requests - Array of request promises
 * @returns {Promise<Array>} Array of response data
 *
 * @example
 * const [patients, vaccines, appointments] = await batch([
 *     get('/midwife/patients'),
 *     get('/midwife/vaccines'),
 *     get('/midwife/appointments')
 * ]);
 */
export async function batch(requests) {
    try {
        return await Promise.all(requests);
    } catch (error) {
        return handleError(error);
    }
}

/**
 * Retry request on failure
 *
 * @param {function} requestFn - Async function that makes the request
 * @param {number} maxRetries - Maximum number of retries
 * @param {number} delay - Delay between retries in milliseconds
 * @returns {Promise<any>} Response data
 *
 * @example
 * const data = await retry(
 *     () => get('/midwife/patients/123'),
 *     3,  // retry 3 times
 *     1000  // wait 1 second between retries
 * );
 */
export async function retry(requestFn, maxRetries = 3, delay = 1000) {
    let lastError;

    for (let i = 0; i <= maxRetries; i++) {
        try {
            return await requestFn();
        } catch (error) {
            lastError = error;
            if (i < maxRetries) {
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }

    return handleError(lastError);
}

// Export as default
export default {
    get,
    post,
    put,
    patch,
    del,
    delete: del, // Alias for del
    upload,
    withLoading,
    batch,
    retry
};
