# Midwife SweetAlert Implementation Guide

## ✅ What's Been Set Up

I've created a comprehensive SweetAlert system for all Midwife pages!

### Files Created:

1. **`public/js/midwife/sweetalert-handler.js`** - Reusable SweetAlert functions
2. **Updated `resources/views/layout/midwife.blade.php`** - Includes SweetAlert globally
3. **Added `resources/views/components/sweetalert-flash.blade.php`** - Flash message component (shared with BHW)

---

## 🎯 Available Functions

These functions are now available on ALL Midwife pages:

### 1. Success Message
```javascript
showSuccess('Patient registered successfully!', () => {
    window.location.reload();
});
```

### 2. Error Message
```javascript
showError('An error occurred', ['Field 1 is required', 'Field 2 is invalid']);
```

### 3. Confirmation Dialog
```javascript
showConfirmation(
    'Are you sure?',
    'This action cannot be undone',
    () => {
        // User clicked "Yes"
        console.log('Confirmed!');
    },
    () => {
        // User clicked "Cancel" (optional)
        console.log('Cancelled');
    }
);
```

### 4. Delete Confirmation
```javascript
showDeleteConfirmation('Patient "John Doe"', () => {
    // Proceed with deletion
    deletePatient(id);
});
```

### 5. Loading State
```javascript
showLoading('Deleting patient...');
// Do something...
closeAlert(); // Close when done
```

### 6. AJAX Form Handler
```javascript
const form = document.getElementById('my-form');
handleAjaxSubmit(
    form,
    'Operation completed successfully!',
    () => {
        window.location.reload();
    },
    'Processing...'
);
```

---

## 📋 Implementation Examples

### Example 1: Add/Create with Modal

```javascript
document.getElementById('add-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (data.success) {
            // Close modal first
            closeModal();

            // Show success after modal closes
            setTimeout(() => {
                showSuccess(data.message, () => {
                    window.location.reload();
                });
            }, 400);
        } else {
            const errors = data.errors ? Object.values(data.errors).flat() : null;
            showError(data.message, errors);
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        showError('An unexpected error occurred');
    });
});
```

### Example 2: Edit/Update with Modal

```javascript
document.getElementById('edit-form').addEventListener('submit', function(e) {
    e.preventDefault();

    handleAjaxSubmit(
        this,
        'Record updated successfully!',
        () => {
            closeEditModal();
            setTimeout(() => {
                window.location.reload();
            }, 400);
        },
        'Updating...'
    );
});
```

### Example 3: Delete with Confirmation

```javascript
function deleteRecord(id, name) {
    showDeleteConfirmation(name, () => {
        showLoading('Deleting...');

        fetch(`/midwife/records/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            closeAlert();

            if (data.success) {
                showSuccess(data.message, () => {
                    window.location.reload();
                });
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            closeAlert();
            showError('Failed to delete record');
        });
    });
}
```

### Example 4: Mark as Done

```javascript
function markAsDone(id) {
    showConfirmation(
        'Mark as Done?',
        'Are you sure you want to mark this as done?',
        () => {
            fetch(`/midwife/records/${id}/done`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message, () => {
                        window.location.reload();
                    });
                } else {
                    showError(data.message);
                }
            });
        }
    );
}
```

### Example 5: Reschedule with Date Input

```javascript
function rescheduleCheckup(id, currentDate) {
    Swal.fire({
        title: 'Reschedule Checkup',
        html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-2">New Date</label>
                <input type="date" id="new-date" class="swal2-input w-full" value="${currentDate}">
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#D4A373',
        confirmButtonText: 'Reschedule',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const newDate = document.getElementById('new-date').value;
            if (!newDate) {
                Swal.showValidationMessage('Please select a date');
            }
            return newDate;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading('Rescheduling...');

            fetch(`/midwife/checkup/${id}/reschedule`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ new_date: result.value })
            })
            .then(response => response.json())
            .then(data => {
                closeAlert();
                if (data.success) {
                    showSuccess(data.message, () => {
                        window.location.reload();
                    });
                } else {
                    showError(data.message);
                }
            });
        }
    });
}
```

---

## 🚀 Quick Start for Each Page

### Step 1: Remove Old Alert Code

Remove any old `alert()` or flowbite alert code:

```javascript
// OLD - Remove this
alert('Patient registered successfully!');

// NEW - Use this
showSuccess('Patient registered successfully!');
```

### Step 2: Convert Form Submissions

```javascript
// Add this to your form submit handler
e.preventDefault(); // Prevent default form submission

// Then handle with AJAX + SweetAlert
```

### Step 3: Add Delete Confirmations

```html
<!-- In your Blade file -->
<button onclick="deleteRecord({{ $record->id }}, '{{ $record->name }}')">
    Delete
</button>
```

```javascript
// In your JavaScript
function deleteRecord(id, name) {
    showDeleteConfirmation(name, () => {
        // Your delete logic here
    });
}
```

---

## 📝 Pages to Update

### 🔄 Priority Pages:

1. **Patient Registration** (`midwife/patients/index.blade.php`)
   - Add patient
   - Edit patient
   - Delete patient

2. **Prenatal Records** (`midwife/prenatalrecord/index.blade.php`)
   - Add record
   - Edit record
   - Mark as complete
   - Delete record

3. **Prenatal Checkup** (`midwife/prenatalcheckup/index.blade.php`)
   - Schedule checkup
   - Reschedule
   - Mark as done

4. **Child Records** (`midwife/childrecord/index.blade.php`)
   - Add record
   - Edit record
   - Delete record

5. **Immunization** (`midwife/immunization/index.blade.php`)
   - Schedule immunization
   - Reschedule
   - Mark as done
   - Mark as missed

6. **Vaccines** (`midwife/vaccines/index.blade.php`)
   - Add vaccine
   - Edit vaccine
   - Delete vaccine

7. **User Management** (`midwife/user/index.blade.php`)
   - Add user
   - Edit user
   - Delete user
   - Reset password

8. **Cloud Backup** (`midwife/cloudbackup/index.blade.php`)
   - Backup confirmation
   - Restore confirmation

---

## 🎨 Customization

### Change Button Colors

Edit `public/js/midwife/sweetalert-handler.js`:

```javascript
const SwalConfig = {
    confirmButtonColor: '#YOUR_COLOR', // Change this
    cancelButtonColor: '#6B7280',
};
```

### Change Animation

```javascript
showClass: {
    popup: 'animate__animated animate__fadeInDown animate__faster'
},
hideClass: {
    popup: 'animate__animated animate__fadeOutUp animate__faster'
}
```

---

## 🔧 Laravel Controller Changes

Your controllers should return JSON for AJAX requests:

```php
public function store(Request $request)
{
    try {
        // Your logic here...
        $record = Record::create($validatedData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Record created successfully!',
                'data' => $record
            ]);
        }

        return redirect()->back()->with('success', 'Record created successfully!');

    } catch (\Exception $e) {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'errors' => []
            ], 500);
        }

        return redirect()->back()->with('error', 'An error occurred');
    }
}
```

---

## 📚 Best Practices

1. **Always close modals before showing success**
   ```javascript
   closeModal();
   setTimeout(() => showSuccess('Done!'), 400);
   ```

2. **Use loading states for async operations**
   ```javascript
   showLoading('Processing...');
   // ... do something
   closeAlert();
   ```

3. **Provide clear error messages**
   ```javascript
   showError('Failed to save', ['Name is required', 'Age must be a number']);
   ```

4. **Always reload or update UI after success**
   ```javascript
   showSuccess('Saved!', () => {
       window.location.reload();
   });
   ```

---

## 🐛 Troubleshooting

### Issue 1: "showSuccess is not defined"
**Solution**: Make sure the page has loaded. Wrap in `DOMContentLoaded` or put script at end of page.

### Issue 2: Modal doesn't close before SweetAlert
**Solution**: Add `setTimeout()` delay of 400ms after closing modal.

### Issue 3: Button stays disabled
**Solution**: Always restore button state in `.catch()` block.

---

## 🎯 Migration from flowbite-alert

Based on the components analysis, there are **17 files** on the Midwife side using `flowbite-alert`:

### High Priority:
1. midwife/patients/index.blade.php
2. midwife/prenatalrecord/index.blade.php
3. midwife/childrecord/index.blade.php
4. midwife/prenatalcheckup/index.blade.php
5. midwife/immunization/index.blade.php

### Medium Priority:
6. midwife/prenatalrecord/create.blade.php
7. midwife/prenatalrecord/show.blade.php
8. midwife/childrecord/create.blade.php
9. midwife/childrecord/show.blade.php
10. midwife/vaccines/index.blade.php

### Low Priority:
11. midwife/dashboard.blade.php
12. midwife/user/index.blade.php
13. midwife/reports/index.blade.php
14. midwife/cloudbackup/index.blade.php

---

## ✨ Comparison with BHW Implementation

Both BHW and Midwife sides now have:
- ✅ Same SweetAlert handler with identical functions
- ✅ Same button color (#D4A373)
- ✅ Same animations and styling
- ✅ Same flash message component
- ✅ Consistent UX across the entire application

---

## 🔗 Related Documentation

- [BHW SweetAlert Implementation Guide](BHW_SWEETALERT_IMPLEMENTATION.md)
- [Components Analysis](COMPONENTS_ANALYSIS.md)
- [SweetAlert2 Official Docs](https://sweetalert2.github.io/)

---

**Ready to implement? Start with Patient Registration page!** 🚀
