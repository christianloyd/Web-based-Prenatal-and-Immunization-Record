/**
 * State Management Module for User Management
 * Manages global state for user operations
 */

// Global state
let currentViewUser = null;
let isEditMode = false;

/**
 * Get current view user
 * @returns {Object|null}
 */
export function getCurrentViewUser() {
    return currentViewUser;
}

/**
 * Set current view user
 * @param {Object|null} user
 */
export function setCurrentViewUser(user) {
    currentViewUser = user;
}

/**
 * Check if in edit mode
 * @returns {boolean}
 */
export function getIsEditMode() {
    return isEditMode;
}

/**
 * Set edit mode
 * @param {boolean} value
 */
export function setIsEditMode(value) {
    isEditMode = value;
}

/**
 * Reset all state
 */
export function resetState() {
    currentViewUser = null;
    isEditMode = false;
}
