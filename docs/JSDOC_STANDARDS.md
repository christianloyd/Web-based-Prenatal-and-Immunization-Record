# JSDoc Documentation Standards

## Overview

This document defines the JSDoc documentation standards for the Web-based Prenatal and Immunization Record system.

---

## Why JSDoc?

- **IntelliSense Support:** Enable auto-completion in VS Code and other IDEs
- **Type Safety:** Catch type errors before runtime
- **Self-Documentation:** Code documents itself
- **Better Collaboration:** Team members understand code faster
- **Easier Maintenance:** Know what functions do without reading implementation

---

## Basic Function Documentation

### Template

```javascript
/**
 * Brief description of what the function does
 *
 * Optional longer description providing more context,
 * usage examples, or important notes.
 *
 * @param {Type} paramName - Parameter description
 * @param {Type} [optionalParam] - Optional parameter description
 * @param {Type} [optionalParam=default] - Optional with default value
 * @returns {Type} Description of return value
 * @throws {ErrorType} Description of when error is thrown
 * @example
 * // Example usage
 * const result = functionName(arg1, arg2);
 */
function functionName(paramName, optionalParam = 'default') {
    // Implementation
    return result;
}
```

### Real Example

```javascript
/**
 * Formats a Philippine phone number to standard format
 *
 * Accepts various input formats and converts to +63XXXXXXXXXX format.
 * Handles formats with or without country code, with or without leading zero.
 *
 * @param {string} phoneNumber - Raw phone number input
 * @param {boolean} [includeCountryCode=true] - Whether to include +63 prefix
 * @returns {string} Formatted phone number
 * @throws {Error} If phone number is invalid
 * @example
 * // Returns: "+639123456789"
 * formatPhoneNumber("09123456789");
 *
 * @example
 * // Returns: "9123456789"
 * formatPhoneNumber("09123456789", false);
 */
function formatPhoneNumber(phoneNumber, includeCountryCode = true) {
    if (!phoneNumber) {
        throw new Error('Phone number is required');
    }

    const cleaned = phoneNumber.replace(/\D/g, '');

    if (cleaned.startsWith('0')) {
        cleaned = cleaned.substring(1);
    }

    if (includeCountryCode) {
        return `+63${cleaned}`;
    }

    return cleaned;
}
```

---

## Parameter Types

### Basic Types

```javascript
/**
 * @param {string} name - String parameter
 * @param {number} age - Number parameter
 * @param {boolean} isActive - Boolean parameter
 * @param {null} value - Explicitly null
 * @param {undefined} value - Explicitly undefined
 */
```

### Arrays

```javascript
/**
 * @param {Array} items - Generic array
 * @param {string[]} names - Array of strings
 * @param {number[]} ids - Array of numbers
 * @param {Object[]} records - Array of objects
 * @param {Array<Patient>} patients - Array of Patient objects
 */
```

### Objects

```javascript
/**
 * @param {Object} options - Generic object
 * @param {Object} patient - Patient object
 * @param {Object} patient.id - Patient ID
 * @param {string} patient.name - Patient name
 * @param {number} patient.age - Patient age
 */

// OR use @typedef for complex objects (recommended)

/**
 * @typedef {Object} Patient
 * @property {number} id - Patient ID
 * @property {string} name - Patient name
 * @property {number} age - Patient age
 * @property {string} [email] - Optional email
 */

/**
 * @param {Patient} patient - Patient object
 */
```

### Functions

```javascript
/**
 * @param {Function} callback - Callback function
 * @param {(error: Error, data: Object) => void} callback - Callback with signature
 */
```

### Union Types

```javascript
/**
 * @param {string|number} id - Can be string or number
 * @param {Patient|null} patient - Patient object or null
 * @param {('pending'|'active'|'completed')} status - One of specific strings
 */
```

### Optional Parameters

```javascript
/**
 * @param {string} required - Required parameter
 * @param {string} [optional] - Optional parameter
 * @param {number} [withDefault=10] - Optional with default value
 */
function example(required, optional, withDefault = 10) {
    // ...
}
```

---

## Return Types

```javascript
/**
 * @returns {void} - Function returns nothing
 */

/**
 * @returns {string} Description of return value
 */

/**
 * @returns {Promise<User>} Resolves with User object
 */

/**
 * @returns {Promise<void>} Promise with no return value
 */
```

---

## Classes

### Class Documentation

```javascript
/**
 * Immunization State Management Class
 *
 * Manages application state for immunization index page.
 * Provides methods for updating filters, selections, and loading state.
 *
 * @class
 * @example
 * const state = new ImmunizationState();
 * state.setStatus('upcoming');
 */
class ImmunizationState {
    /**
     * Creates an ImmunizationState instance
     *
     * @constructor
     */
    constructor() {
        /** @type {string} Current active status filter */
        this.currentStatus = 'all';

        /** @type {Object|null} Selected immunization data */
        this.selectedImmunization = null;
    }

    /**
     * Updates the current status filter
     *
     * @param {string} status - New status value ('all', 'upcoming', 'done', 'missed')
     * @returns {void}
     * @example
     * state.setStatus('upcoming');
     */
    setStatus(status) {
        this.currentStatus = status;
    }
}
```

---

## Modules

### ES6 Module Documentation

```javascript
/**
 * Modal Management Module
 *
 * Handles all modal interactions for immunization management including
 * opening, closing, and populating modals with data.
 *
 * @module midwife/immunization/modals
 * @requires @shared/utils/validation
 * @requires @shared/utils/dom
 */

import { clearValidationStates } from '@shared/utils/validation';
import { focusFirstInput } from '@shared/utils/dom';

/**
 * Opens the Add Immunization modal
 *
 * @returns {void}
 */
export function openAddModal() {
    // Implementation
}
```

---

## TypeDefs for Complex Objects

```javascript
/**
 * Patient object definition
 *
 * @typedef {Object} Patient
 * @property {number} id - Unique patient identifier
 * @property {string} name - Patient full name
 * @property {number} age - Patient age in years
 * @property {string} contact - Contact phone number
 * @property {string} address - Residential address
 * @property {Date} created_at - Record creation date
 */

/**
 * Immunization record definition
 *
 * @typedef {Object} Immunization
 * @property {number} id - Immunization ID
 * @property {number} child_record_id - Child record foreign key
 * @property {number} vaccine_id - Vaccine foreign key
 * @property {string} vaccine_name - Vaccine name
 * @property {string} dose - Dose number (e.g., "Dose 1")
 * @property {string} schedule_date - Scheduled date (YYYY-MM-DD)
 * @property {string} schedule_time - Scheduled time (HH:MM)
 * @property {('Upcoming'|'Done'|'Missed')} status - Current status
 * @property {string} [notes] - Optional notes
 */

/**
 * Creates a new immunization record
 *
 * @param {Object} data - Immunization data
 * @returns {Promise<Immunization>} Created immunization record
 */
async function createImmunization(data) {
    // Implementation
}
```

---

## Common Patterns

### API Functions

```javascript
/**
 * Fetches immunization records from the server
 *
 * @async
 * @param {Object} filters - Query filters
 * @param {string} [filters.status] - Filter by status
 * @param {string} [filters.search] - Search term
 * @returns {Promise<Object>} Response object
 * @returns {boolean} Response.success - Whether request succeeded
 * @returns {Immunization[]} Response.data - Array of immunization records
 * @returns {string} [Response.message] - Optional message
 * @throws {Error} If network request fails
 * @example
 * const response = await fetchImmunizations({ status: 'upcoming' });
 * console.log(response.data); // Array of immunizations
 */
async function fetchImmunizations(filters = {}) {
    // Implementation
}
```

### Event Handlers

```javascript
/**
 * Handles form submission for adding new immunization
 *
 * @param {Event} event - Form submit event
 * @returns {Promise<void>}
 * @fires immunization:created - When immunization is successfully created
 */
async function handleSubmit(event) {
    event.preventDefault();
    // Implementation
}
```

### Callbacks

```javascript
/**
 * Success callback signature
 *
 * @callback SuccessCallback
 * @param {Object} data - Success data
 * @returns {void}
 */

/**
 * Error callback signature
 *
 * @callback ErrorCallback
 * @param {Error} error - Error object
 * @returns {void}
 */

/**
 * Performs async operation with callbacks
 *
 * @param {SuccessCallback} onSuccess - Called on success
 * @param {ErrorCallback} onError - Called on error
 * @returns {void}
 */
function performOperation(onSuccess, onError) {
    // Implementation
}
```

---

## Deprecation

```javascript
/**
 * Old function that should no longer be used
 *
 * @deprecated Since version 2.0. Use {@link newFunction} instead.
 * @param {string} param - Parameter
 * @returns {string} Result
 */
function oldFunction(param) {
    return newFunction(param);
}
```

---

## Links and References

```javascript
/**
 * Processes patient data
 *
 * @see {@link https://docs.example.com/patient-api|Patient API Docs}
 * @see {@link formatPhoneNumber} for phone formatting
 * @param {Patient} patient - Patient object
 * @returns {void}
 */
```

---

## TODO and FIXME

```javascript
/**
 * Calculates delivery date estimate
 *
 * @todo Implement Naegele's Rule calculation
 * @fixme Handle edge cases for irregular cycles
 * @param {Date} lastPeriod - Last menstrual period date
 * @returns {Date} Estimated delivery date
 */
```

---

## Best Practices

### 1. Be Descriptive but Concise

```javascript
// ❌ BAD - Too vague
/**
 * Does something
 * @param {Object} data
 */

// ✅ GOOD - Clear and specific
/**
 * Validates immunization schedule date against child's birthdate
 * @param {Date} scheduleDate - Proposed immunization date
 * @param {Date} birthdate - Child's date of birth
 * @returns {boolean} True if schedule date is valid
 */
```

### 2. Document All Public Functions

```javascript
// ✅ GOOD - Public function documented
/**
 * Opens the immunization modal
 * @returns {void}
 */
export function openModal() {
    // Implementation
}

// ✅ GOOD - Private function can be simpler
/**
 * @private
 */
function helperFunction() {
    // Implementation
}
```

### 3. Use @example for Complex Functions

```javascript
/**
 * Filters immunization records by multiple criteria
 *
 * @param {Immunization[]} records - Array of immunization records
 * @param {Object} filters - Filter criteria
 * @returns {Immunization[]} Filtered records
 * @example
 * const filtered = filterImmunizations(allRecords, {
 *     status: 'upcoming',
 *     vaccine: 'BCG',
 *     dateFrom: '2025-01-01'
 * });
 */
```

### 4. Document Thrown Errors

```javascript
/**
 * Deletes an immunization record
 *
 * @param {number} id - Immunization ID
 * @returns {Promise<void>}
 * @throws {Error} If immunization not found
 * @throws {Error} If user lacks permission
 * @throws {Error} If immunization is already completed
 */
```

---

## Recommended VSCode Extensions

1. **ESLint** - Linting and error detection
2. **Prettier** - Code formatting
3. **Document This** - Automatic JSDoc generation
4. **IntelliSense for CSS class names** - Tailwind support

---

## Quick Reference

```javascript
// Function
/** @param {Type} name - Description */
/** @returns {Type} Description */
/** @throws {Error} Description */

// Variables
/** @type {Type} */
const variable = value;

// Objects
/** @typedef {Object} Name */
/** @property {Type} prop - Description */

// Arrays
/** @type {Type[]} */

// Optional
/** @param {Type} [optional] */

// Union
/** @param {Type1|Type2} name */

// Module
/** @module path/to/module */

// Example
/** @example code */

// See also
/** @see {@link reference} */
```

---

## Migration Checklist

- [ ] Add JSDoc to all exported functions
- [ ] Add @module tags to all modules
- [ ] Create @typedef for complex objects
- [ ] Add @example to complex functions
- [ ] Document @throws for error cases
- [ ] Add @param and @returns to all functions
- [ ] Run ESLint to check JSDoc compliance
- [ ] Update as code changes

---

**Last Updated:** 2025-11-09
**Status:** Standard Adopted
**Compliance:** Required for all new code, recommended for legacy code
