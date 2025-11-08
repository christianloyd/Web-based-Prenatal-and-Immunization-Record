# Child Record Name Validation Fix

## Issue Summary

**Problem:** Child records were accepting numbers in names (e.g., "2342" could be entered as a child's name)

**Root Cause:** Missing regex validation in ChildRecord request validation files

---

## Solution Implemented

Applied the same strict name validation used in `PatientController` to prevent numbers and special characters in names.

### Validation Pattern Applied

```php
'regex:/^[a-zA-Z\s\.\-\']+$/'
```

**This pattern allows:**
- ✅ Letters (a-z, A-Z)
- ✅ Spaces
- ✅ Dots (.)
- ✅ Hyphens (-)
- ✅ Apostrophes (')

**This pattern blocks:**
- ❌ Numbers (0-9)
- ❌ Special characters (@, #, $, %, etc.)
- ❌ Other invalid characters

---

## Files Modified

### 1. **app/Http/Requests/StoreChildRecordRequest.php**

**Updated Fields:**
- `first_name` - Added regex validation
- `middle_name` - Added regex validation
- `last_name` - Added regex validation
- `father_name` - Added regex validation
- `mother_name` - Added regex validation (when creating new mother)

**Before:**
```php
'first_name' => 'required|string|max:255|min:2',
'middle_name' => 'nullable|string|max:255',
'last_name' => 'required|string|max:255|min:2',
```

**After:**
```php
'first_name' => [
    'required',
    'string',
    'min:2',
    'max:255',
    'regex:/^[a-zA-Z\s\.\-\']+$/'
],
'middle_name' => [
    'nullable',
    'string',
    'max:255',
    'regex:/^[a-zA-Z\s\.\-\']+$/'
],
'last_name' => [
    'required',
    'string',
    'min:2',
    'max:255',
    'regex:/^[a-zA-Z\s\.\-\']+$/'
],
```

**Added Error Messages:**
```php
'first_name.regex' => 'First name should only contain letters, spaces, dots, hyphens, and apostrophes.',
'middle_name.regex' => 'Middle name should only contain letters, spaces, dots, hyphens, and apostrophes.',
'last_name.regex' => 'Last name should only contain letters, spaces, dots, hyphens, and apostrophes.',
'father_name.regex' => 'Father\'s name should only contain letters, spaces, dots, hyphens, and apostrophes.',
'mother_name.regex' => 'Mother\'s name should only contain letters, spaces, dots, hyphens, and apostrophes.',
```

---

### 2. **app/Http/Requests/UpdateChildRecordRequest.php**

**Updated Fields:**
- `child_name` - Added regex validation

**Before:**
```php
'child_name' => 'required|string|max:255|min:2',
```

**After:**
```php
'child_name' => [
    'required',
    'string',
    'min:2',
    'max:255',
    'regex:/^[a-zA-Z\s\.\-\']+$/'
],
```

**Added Error Message:**
```php
'child_name.regex' => 'Child name should only contain letters, spaces, dots, hyphens, and apostrophes.',
```

---

## Validation Coverage

| Field | Store (Create) | Update | Regex Applied |
|-------|---------------|---------|---------------|
| **Child first_name** | ✅ | N/A | ✅ |
| **Child middle_name** | ✅ | N/A | ✅ |
| **Child last_name** | ✅ | N/A | ✅ |
| **Child full name** | N/A | ✅ | ✅ |
| **Father name** | ✅ | N/A | ✅ |
| **Mother name** (new) | ✅ | N/A | ✅ |
| **Mother** (existing) | Skipped (from patient table) | N/A | Already validated |

---

## Examples

### ✅ Valid Names (Will Be Accepted)

- "Maria Clara"
- "John Paul"
- "Mary-Ann"
- "O'Brien"
- "Jean-Pierre"
- "St. John"
- "Ana Maria dela Cruz"

### ❌ Invalid Names (Will Be Rejected)

- "2342" ❌ Numbers only
- "John123" ❌ Contains numbers
- "Maria@" ❌ Contains special character
- "Test#123" ❌ Contains numbers and special characters
- "User_123" ❌ Contains underscore and numbers
- "Name$" ❌ Contains dollar sign

---

## Error Messages Shown to User

When validation fails, users will see clear, helpful error messages:

### For Child Names:
- "First name should only contain letters, spaces, dots, hyphens, and apostrophes."
- "Middle name should only contain letters, spaces, dots, hyphens, and apostrophes."
- "Last name should only contain letters, spaces, dots, hyphens, and apostrophes."
- "Child name should only contain letters, spaces, dots, hyphens, and apostrophes."

### For Parent Names:
- "Father's name should only contain letters, spaces, dots, hyphens, and apostrophes."
- "Mother's name should only contain letters, spaces, dots, hyphens, and apostrophes."

---

## Testing Checklist

### Create Child Record (Store):
- [ ] Try entering "123" as first name → Should show validation error
- [ ] Try entering "Test@123" as last name → Should show validation error
- [ ] Try entering "2342" as middle name → Should show validation error
- [ ] Enter valid name "Maria Clara" → Should save successfully
- [ ] Enter name with hyphen "Mary-Ann" → Should save successfully
- [ ] Enter name with apostrophe "O'Brien" → Should save successfully

### Update Child Record:
- [ ] Try changing child name to "123456" → Should show validation error
- [ ] Try changing child name to "Test@#$" → Should show validation error
- [ ] Change child name to "Maria dela Cruz" → Should update successfully

### Father Name:
- [ ] Try entering "Father123" → Should show validation error
- [ ] Enter "John Smith" → Should save successfully

### New Mother (when not selecting existing):
- [ ] Try entering "Mother@123" → Should show validation error
- [ ] Enter "Maria Santos" → Should save successfully

---

## Consistency with Patient Validation

This validation now matches the **exact same pattern** used in `PatientController`:

| Validation | PatientController | ChildRecordController |
|------------|-------------------|----------------------|
| Regex Pattern | `/^[a-zA-Z\s\.\-\']+$/` | `/^[a-zA-Z\s\.\-\']+$/` ✅ |
| Min Length | 2 characters | 2 characters ✅ |
| Max Length | 50 characters | 255 characters |
| Allows Numbers | ❌ No | ❌ No ✅ |
| Allows Letters | ✅ Yes | ✅ Yes ✅ |
| Allows Spaces | ✅ Yes | ✅ Yes ✅ |
| Allows Hyphens | ✅ Yes | ✅ Yes ✅ |
| Allows Apostrophes | ✅ Yes | ✅ Yes ✅ |

---

## Benefits

✅ **Data Integrity**
- No more numeric "names" in the database
- Clean, valid name data

✅ **Consistency**
- Same validation rules across Patient and Child records
- Predictable behavior

✅ **User Experience**
- Clear error messages
- Immediate feedback on invalid input

✅ **Security**
- Prevents potential injection attempts
- Validates data at request level

---

## Related Validation Rules

### Phone Number Validation (Already Implemented)
```php
'regex:/^(\+63|0)[0-9]{10}$/'
```
- Ensures Philippine phone format
- Examples: +639123456789 or 09123456789

### Age Validation (Already Implemented)
```php
'integer|min:15|max:50'
```
- Mother's age must be between 15-50 years

---

## Notes

### Why This Pattern?

The regex `/^[a-zA-Z\s\.\-\']+$/` was chosen because it:

1. **Matches Real Names**: Handles common name patterns like:
   - Multiple words: "Maria Clara"
   - Hyphens: "Jean-Pierre"
   - Apostrophes: "O'Brien"
   - Titles: "St. John"

2. **Blocks Invalid Input**:
   - Numbers (preventing "123" as a name)
   - Special characters (preventing "@#$%")
   - Emoji and unicode symbols

3. **Balances Security and Usability**:
   - Strict enough to prevent invalid data
   - Flexible enough to handle real-world names

### Database Impact

No database migration needed - this is a validation-only change at the application level.

---

**Date:** 2025-11-04
**Status:** ✅ Fixed - Ready for Testing
**Pattern Source:** Copied from PatientController validation (lines 74, 81, 117, 122)
