# Duplicate Code Analysis - BHW vs Midwife

## Overview

This document identifies duplicate code between the BHW and Midwife JavaScript folders and provides a consolidation strategy.

**Total Duplicate Code Estimated:** ~2,000-2,500 lines across 8 files

---

## High-Priority Duplicates (>90% Similar)

### 1. sweetalert-handler.js
**Location:**
- `public/js/bhw/sweetalert-handler.js` (222 lines)
- `public/js/midwife/sweetalert-handler.js` (same)

**Difference:** Only comments differ ("BHW" vs "Midwife" in header)

**Consolidation Strategy:**
Move to: `resources/js/shared/utils/sweetalert.js`

**Savings:** 222 lines

```javascript
// BEFORE (bhw/sweetalert-handler.js)
/**
 * BHW SweetAlert Handler
 * Reusable SweetAlert functions for all BHW pages
 */

// BEFORE (midwife/sweetalert-handler.js)
/**
 * Midwife SweetAlert Handler
 * Reusable SweetAlert functions for all Midwife pages
 */

// AFTER (shared/utils/sweetalert.js)
/**
 * SweetAlert Utility Functions
 * Shared across all roles (BHW, Midwife, Admin)
 *
 * @module shared/utils/sweetalert
 */
export function showSuccess(title, message) { /* ... */ }
export function showError(title, message) { /* ... */ }
export function showConfirm(title, message) { /* ... */ }
```

---

### 2. patients-index.js
**Location:**
- `public/js/bhw/patients-index.js` (472 lines)
- `public/js/midwife/patients-index.js` (521 lines)

**Similarity:** ~85% identical

**Differences:**
- Route URLs (`bhw.patients.*` vs `midwife.patients.*`)
- Some BHW-specific features disabled
- Minor UI differences

**Consolidation Strategy:**
Move shared logic to: `resources/js/shared/patients/index.js`
Keep role-specific overrides in respective folders.

**Estimated Savings:** ~400 lines

```javascript
// AFTER (shared/patients/index.js)
/**
 * Shared Patient Management Logic
 *
 * @module shared/patients
 */
export class PatientManager {
    constructor(config) {
        this.routes = config.routes;
        this.permissions = config.permissions;
    }

    initializeTable() { /* shared logic */ }
    handleSearch() { /* shared logic */ }
    handleDelete(id) { /* shared logic */ }
}

// AFTER (bhw/patients/index.js)
import { PatientManager } from '@shared/patients';

const manager = new PatientManager({
    routes: {
        index: '/bhw/patients',
        store: '/bhw/patients',
        // ...
    },
    permissions: {
        canCreate: true,
        canDelete: false, // BHW cannot delete
    }
});

// AFTER (midwife/patients/index.js)
import { PatientManager } from '@shared/patients';

const manager = new PatientManager({
    routes: {
        index: '/midwife/patients',
        store: '/midwife/patients',
        // ...
    },
    permissions: {
        canCreate: true,
        canDelete: true, // Midwife can delete
    }
});
```

---

### 3. prenatalrecord-index.js
**Location:**
- `public/js/bhw/prenatalrecord-index.js` (289 lines)
- `public/js/midwife/prenatalrecord-index.js` (566 lines)

**Similarity:** ~70% identical

**Differences:**
- Midwife version has more features (completion workflow, advanced filtering)
- Different routes
- Permission checks

**Consolidation Strategy:**
Extract shared table management, search, and modal logic to:
`resources/js/shared/prenatalrecord/base.js`

**Estimated Savings:** ~200 lines

---

### 4. dashboard.js
**Location:**
- `public/js/bhw/dashboard.js` (338 lines)
- `public/js/midwife/dashboard.js` (similar)

**Similarity:** ~75% identical

**Differences:**
- Different statistics displayed
- Different API endpoints
- Different charts

**Consolidation Strategy:**
Extract shared dashboard utilities to:
`resources/js/shared/dashboard/charts.js`
`resources/js/shared/dashboard/stats.js`

**Estimated Savings:** ~250 lines

---

### 5. prenatalrecord-create.js
**Location:**
- `public/js/bhw/prenatalrecord-create.js` (269 lines)
- `public/js/midwife/prenatalrecord-create.js` (438 lines)

**Similarity:** ~80% identical

**Differences:**
- Form validation logic mostly same
- Different submit endpoints
- Midwife has additional fields

**Consolidation Strategy:**
Extract to: `resources/js/shared/prenatalrecord/forms.js`

**Estimated Savings:** ~210 lines

---

### 6. patients-profile.js
**Location:**
- `public/js/bhw/patients-profile.js`
- `public/js/midwife/patients-profile.js`

**Similarity:** ~90% identical

**Differences:**
- Routes only

**Consolidation Strategy:**
Move to: `resources/js/shared/patients/profile.js`

**Estimated Savings:** ~180 lines

---

### 7. reports-print.js
**Location:**
- `public/js/bhw/reports-print.js`
- Similar functionality in midwife

**Consolidation Strategy:**
Move to: `resources/js/shared/reports/print.js`

**Estimated Savings:** ~100 lines

---

### 8. childrecord-index.js
**Location:**
- `public/js/bhw/childrecord-index.js` (928 lines)
- `public/js/midwife/childrecord-index.js` (1,253 lines)

**Similarity:** ~60% identical

**Differences:**
- Midwife version significantly more complex
- Different permission levels
- Additional features for midwife

**Consolidation Strategy:**
Extract shared core logic to:
`resources/js/shared/childrecord/table.js`
`resources/js/shared/childrecord/modals.js`
`resources/js/shared/childrecord/validation.js`

**Estimated Savings:** ~550 lines

---

## Consolidation Implementation Plan

### Phase 1: Create Shared Utilities (Week 1)

```
resources/js/shared/
├── utils/
│   ├── sweetalert.js          ← consolidate from bhw & midwife
│   ├── validation.js          ← extract common validation
│   ├── api.js                 ← extract AJAX patterns
│   ├── dom.js                 ← extract DOM manipulation
│   └── formatters.js          ← extract data formatters
```

**Files to consolidate:**
1. ✅ sweetalert-handler.js → shared/utils/sweetalert.js

**Actions:**
```bash
# Create shared utilities
mkdir -p resources/js/shared/utils

# Move and refactor sweetalert
# (combine bhw & midwife versions, remove role-specific comments)
```

### Phase 2: Create Shared Components (Week 2)

```
resources/js/shared/
├── components/
│   ├── Modal.js               ← generic modal management
│   ├── DataTable.js           ← table management
│   ├── SearchBar.js           ← search functionality
│   ├── FilterPanel.js         ← filtering logic
│   └── FormValidator.js       ← form validation
```

**Files to consolidate:**
2. ✅ Modal logic from multiple files
3. ✅ Table logic from patients-index, prenatalrecord-index, childrecord-index

### Phase 3: Create Shared Domain Logic (Week 3)

```
resources/js/shared/
├── patients/
│   ├── index.js               ← PatientManager class
│   ├── profile.js             ← profile view logic
│   └── search.js              ← patient search logic
├── prenatalrecord/
│   ├── forms.js               ← form handling
│   ├── table.js               ← table management
│   └── validation.js          ← validation rules
└── childrecord/
    ├── table.js               ← table management
    ├── modals.js              ← modal logic
    └── validation.js          ← validation rules
```

**Files to consolidate:**
4. ✅ patients-index.js → shared/patients/index.js
5. ✅ patients-profile.js → shared/patients/profile.js
6. ✅ prenatalrecord-create.js → shared/prenatalrecord/forms.js
7. ✅ prenatalrecord-index.js → shared/prenatalrecord/table.js
8. ✅ childrecord-index.js → shared/childrecord/*

### Phase 4: Update Role-Specific Files (Week 4)

Convert BHW and Midwife files to use shared modules:

```javascript
// BEFORE: bhw/patients-index.js (472 lines of duplicate code)
function initializePatientTable() {
    // 400 lines of code...
}

// AFTER: bhw/patients/index.js (72 lines, uses shared code)
import { PatientManager } from '@shared/patients';
import { showSuccess, showError } from '@shared/utils/sweetalert';

const manager = new PatientManager({
    routes: BHW_ROUTES,
    permissions: BHW_PERMISSIONS
});

manager.initialize();
```

---

## Configuration-Based Approach

To handle route differences, use a configuration object:

```javascript
// resources/js/bhw/config.js
export const BHW_ROUTES = {
    patients: {
        index: '/bhw/patients',
        store: '/bhw/patients',
        update: (id) => `/bhw/patients/${id}`,
        destroy: (id) => `/bhw/patients/${id}`,
        profile: (id) => `/bhw/patients/${id}/profile`,
    },
    prenatalrecord: {
        index: '/bhw/prenatalrecord',
        store: '/bhw/prenatalrecord',
        // ...
    },
};

export const BHW_PERMISSIONS = {
    patients: {
        canCreate: true,
        canUpdate: true,
        canDelete: false, // BHW cannot delete
        canExport: false,
    },
};

// resources/js/midwife/config.js
export const MIDWIFE_ROUTES = {
    patients: {
        index: '/midwife/patients',
        store: '/midwife/patients',
        update: (id) => `/midwife/patients/${id}`,
        destroy: (id) => `/midwife/patients/${id}`,
        profile: (id) => `/midwife/patients/${id}/profile`,
    },
    // ...
};

export const MIDWIFE_PERMISSIONS = {
    patients: {
        canCreate: true,
        canUpdate: true,
        canDelete: true, // Midwife can delete
        canExport: true,
    },
};
```

---

## Expected Results

### Before Consolidation
- **Total JS Files:** 36 files
- **Total Lines:** ~15,000 lines
- **Duplicate Code:** ~2,500 lines (17%)
- **Maintainability:** Low (changes must be made in 2 places)

### After Consolidation
- **Total JS Files:** 25 files (11 consolidated into shared)
- **Total Lines:** ~12,500 lines (2,500 lines removed)
- **Duplicate Code:** <5% (only minor role-specific differences)
- **Maintainability:** High (changes made once in shared code)

---

## File-by-File Consolidation Checklist

### High Priority (Do First)
- [ ] sweetalert-handler.js → shared/utils/sweetalert.js (222 lines saved)
- [ ] patients-profile.js → shared/patients/profile.js (180 lines saved)
- [ ] reports-print.js → shared/reports/print.js (100 lines saved)

### Medium Priority
- [ ] patients-index.js → shared/patients/index.js (400 lines saved)
- [ ] prenatalrecord-create.js → shared/prenatalrecord/forms.js (210 lines saved)
- [ ] prenatalrecord-index.js → shared/prenatalrecord/table.js (200 lines saved)

### Low Priority (More Complex)
- [ ] dashboard.js → shared/dashboard/* (250 lines saved)
- [ ] childrecord-index.js → shared/childrecord/* (550 lines saved)

---

## Implementation Example

### sweetalert-handler.js Consolidation

**Step 1:** Create shared file

```javascript
// resources/js/shared/utils/sweetalert.js
/**
 * SweetAlert Utility Functions
 * Shared alert, confirm, and notification helpers
 *
 * @module shared/utils/sweetalert
 */

export function showSuccess(title, message = '') {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
    });
}

export function showError(title, message = '') {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#EF4444',
    });
}

export function showConfirm(title, message, confirmText = 'Yes') {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
    });
}
```

**Step 2:** Update BHW files

```javascript
// bhw/patients-index.js
import { showSuccess, showError, showConfirm } from '@shared/utils/sweetalert';

// Replace all SwalHelper.success() with showSuccess()
// Replace all SwalHelper.error() with showError()
// Replace all SwalHelper.confirm() with showConfirm()
```

**Step 3:** Update Midwife files

```javascript
// midwife/patients-index.js
import { showSuccess, showError, showConfirm } from '@shared/utils/sweetalert';

// Same replacements as BHW
```

**Step 4:** Delete duplicate files

```bash
rm public/js/bhw/sweetalert-handler.js
rm public/js/midwife/sweetalert-handler.js
```

---

## Testing Strategy

After consolidation, test:

1. **BHW Pages:**
   - ✅ All alerts/confirms work
   - ✅ All CRUD operations function
   - ✅ Permissions respected (cannot delete)

2. **Midwife Pages:**
   - ✅ All alerts/confirms work
   - ✅ All CRUD operations function
   - ✅ Full permissions work

3. **Edge Cases:**
   - ✅ Modal overlays
   - ✅ Form validation
   - ✅ AJAX error handling

---

## Migration Timeframe

**Total Estimated Time:** 3-4 weeks

- **Week 1:** Create shared utilities (5 files)
- **Week 2:** Create shared components (5 files)
- **Week 3:** Create shared domain logic (8 files)
- **Week 4:** Update role-specific files, test, deploy

**Lines of Code Saved:** ~2,500 lines
**Maintenance Effort Reduction:** ~50%
**Bundle Size Reduction:** ~25-30%

---

**Last Updated:** 2025-11-09
**Priority:** High
**Status:** Ready for Implementation
