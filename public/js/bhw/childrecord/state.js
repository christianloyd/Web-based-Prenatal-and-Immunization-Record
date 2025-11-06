/**
 * State Management Module
 * Manages global state for child record operations
 */

// Global state
let currentRecord = null;
let isExistingMother = false;

/**
 * Get current record
 * @returns {Object|null}
 */
export function getCurrentRecord() {
    return currentRecord;
}

/**
 * Set current record
 * @param {Object|null} record
 */
export function setCurrentRecord(record) {
    currentRecord = record;
}

/**
 * Check if using existing mother
 * @returns {boolean}
 */
export function getIsExistingMother() {
    return isExistingMother;
}

/**
 * Set existing mother flag
 * @param {boolean} value
 */
export function setIsExistingMother(value) {
    isExistingMother = value;
}

/**
 * Reset all state
 */
export function resetState() {
    currentRecord = null;
    isExistingMother = false;
}
