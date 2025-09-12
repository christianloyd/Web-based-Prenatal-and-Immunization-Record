# Global Confirmation Modal Usage Guide

The global confirmation modal component is now available throughout your healthcare application for consistent user confirmations.

## Features

- **Single Modal Instance**: One modal that can be reused across the entire application
- **Multiple Types**: Support for different confirmation types (danger, warning, info, success)
- **Consistent Styling**: Matches your application's design system
- **Keyboard Support**: ESC key and click-outside-to-close functionality
- **Customizable**: Custom messages, button texts, and callbacks

## How to Use

### Basic Usage

```javascript
// Show a basic confirmation
showConfirmationModal({
    title: 'Are you sure you want to delete this record?',
    type: 'danger',
    confirmText: 'Yes, delete it',
    cancelText: 'Cancel',
    onConfirm: function() {
        // Delete the record
        console.log('Record deleted');
    }
});
```

### Convenience Functions

Instead of calling `showConfirmationModal` directly, you can use these convenience functions:

#### Delete Confirmation
```javascript
confirmDelete('Patient Record #123', function() {
    deletePatientRecord(123);
});
```

#### Deactivate Confirmation
```javascript
confirmDeactivate('John Doe', function() {
    deactivateUser(userId);
});
```

#### Activate Confirmation
```javascript
confirmActivate('John Doe', function() {
    activateUser(userId);
});
```

#### Submit Confirmation
```javascript
confirmSubmit('Are you sure you want to submit this form?', function() {
    document.getElementById('myForm').submit();
});
```

#### Save Confirmation
```javascript
confirmSave('Save changes to patient record?', function() {
    savePatientRecord();
});
```

## Integration Examples

### 1. Delete Patient Record
```html
<button onclick="confirmDelete('{{ $patient->full_name }}', function() { deletePatient({{ $patient->id }}) })" 
        class="btn-delete">
    Delete Patient
</button>
```

### 2. Delete Child Record
```html
<button onclick="confirmDelete('Child Record for {{ $child->full_name }}', function() { deleteChildRecord({{ $child->id }}) })" 
        class="btn-delete">
    Delete Record
</button>
```

### 3. Delete Prenatal Checkup
```html
<button onclick="confirmDelete('Prenatal Checkup on {{ $checkup->checkup_date }}', function() { deletePrenatalCheckup({{ $checkup->id }}) })" 
        class="btn-delete">
    Delete Checkup
</button>
```

### 4. Delete Immunization Record
```html
<button onclick="confirmDelete('{{ $immunization->vaccine->vaccine_name }} vaccination', function() { deleteImmunization({{ $immunization->id }}) })" 
        class="btn-delete">
    Delete Vaccination
</button>
```

### 5. Delete Notification
```html
<button onclick="confirmDelete('this notification', function() { deleteNotification('{{ $notification->id }}') })" 
        class="text-red-600 hover:text-red-800">
    Delete
</button>
```

### 6. Form Submission Confirmation
```html
<button type="button" onclick="confirmSubmit('Submit patient registration?', function() { document.getElementById('patientForm').submit(); })" 
        class="btn-primary">
    Register Patient
</button>
```

### 7. Backup Operations
```html
<button onclick="confirmSave('Start backup process? This may take a few minutes.', function() { startBackup() })" 
        class="btn-primary">
    Start Backup
</button>
```

## Custom Confirmation Types

### Custom Messages and Styling
```javascript
showConfirmationModal({
    title: 'Archive this patient record?',
    type: 'warning', // Options: 'danger', 'warning', 'info', 'success'
    confirmText: 'Yes, archive it',
    cancelText: 'Keep active',
    onConfirm: function() {
        archivePatientRecord(patientId);
    },
    onCancel: function() {
        console.log('Archive cancelled');
    }
});
```

## Replacing Existing Confirm() Dialogs

### Before (Old way)
```javascript
if (confirm('Are you sure you want to delete this record?')) {
    deleteRecord();
}
```

### After (Global modal way)
```javascript
confirmDelete('this record', function() {
    deleteRecord();
});
```

## Available Confirmation Types

| Type | Color | Use Case |
|------|--------|----------|
| `danger` | Red | Deletions, destructive actions |
| `warning` | Yellow | Deactivations, important warnings |
| `success` | Green | Activations, confirmations |
| `info` | Blue | General confirmations, submissions |

## Implementation in Your Views

The global confirmation modal is automatically included in:
- `layout/midwife.blade.php`
- `layout/bhw.blade.php`

You can use the confirmation functions in any view that extends these layouts.

## Best Practices

1. **Use descriptive messages**: Instead of "Are you sure?", use "Delete patient John Doe?"
2. **Choose appropriate types**: Use `danger` for deletions, `warning` for deactivations
3. **Clear button text**: "Yes, delete it" is better than "OK"
4. **Provide context**: Include what will be deleted/changed in the message
5. **Handle errors**: Include error handling in your confirmation callbacks

## Migration Guide

To replace existing confirmation modals in your application:

1. Remove the old modal HTML
2. Replace `data-modal-target` and `data-modal-toggle` attributes with `onclick` handlers
3. Use the appropriate convenience function (`confirmDelete`, `confirmActivate`, etc.)
4. Test the functionality to ensure callbacks work correctly

The global confirmation modal is now ready to use throughout your healthcare application!