# View Consolidation Refactoring Guide

## 📋 Overview

This guide documents the refactoring of duplicate role-based views (Midwife and BHW) into shared views. The goal is to reduce code duplication, improve maintainability, and make the codebase more scalable.

## ✅ What's Been Completed

### 1. **BaseController with Helper Methods** ✓
**File**: `app/Http/Controllers/BaseController.php`

Created a base controller with helper methods for role-aware functionality:
- `roleView($view)` - Returns shared view path
- `roleRoute($routeName)` - Returns role-prefixed route name
- `roleLayout()` - Returns role-specific layout
- `roleCss($cssFile)` - Returns role-specific CSS path
- `roleJs($jsFile)` - Returns role-specific JS path

### 2. **Custom Blade Directives** ✓
**File**: `app/Providers/AppServiceProvider.php`

Added custom Blade directives for role-based content:
- `@midwife` / `@endmidwife` - Show content only to midwives
- `@bhw` / `@endbhw` - Show content only to BHWs
- `@roleRoute('route.name')` - Generate role-prefixed routes
- `@roleCss('file.css')` - Generate role-specific CSS paths
- `@roleJs('file.js')` - Generate role-specific JS paths

### 3. **Shared Views Directory Structure** ✓
Created organized directory structure:
```
resources/views/
├── shared/
│   ├── patients/
│   │   └── index.blade.php ✓
│   ├── prenatalrecord/
│   └── childrecord/
└── partials/
    └── shared/
        ├── patient/
        │   ├── patient_add.blade.php ✓
        │   ├── patient_view.blade.php (placeholder)
        │   └── patient_edit.blade.php (placeholder)
        ├── prenatalrecord/
        └── childrecord/
```

### 4. **Patient Views Consolidated** ✓
**Files**:
- `resources/views/shared/patients/index.blade.php` - Unified patient listing
- `resources/views/partials/shared/patient/patient_add.blade.php` - Unified add modal

### 5. **PatientController Updated** ✓
**File**: `app/Http/Controllers/PatientController.php`

Changes:
- Extended `BaseController` instead of `Controller`
- Replaced role-specific views with `$this->roleView('patients.index')`
- Removed duplicate view selection logic

## 📝 How It Works

### Before (Duplicate Code):
```php
// Controller
$view = auth()->user()->role === 'midwife'
    ? 'midwife.patients.index'
    : 'bhw.patients.index';
return view($view, compact('patients'));

// View (midwife/patients/index.blade.php)
@extends('layout.midwife')
<form action="{{ route('midwife.patients.index') }}">
<link rel="stylesheet" href="{{ asset('css/midwife/patients-index.css') }}">

// View (bhw/patients/index.blade.php) - Almost identical!
@extends('layout.bhw')
<form action="{{ route('bhw.patients.index') }}">
<link rel="stylesheet" href="{{ asset('css/bhw/patients-index.css') }}">
```

### After (Shared Code):
```php
// Controller
return view($this->roleView('patients.index'), compact('patients'));

// Single Shared View (shared/patients/index.blade.php)
@extends('layout.' . auth()->user()->role)
<form action="@roleRoute('patients.index')">
<link rel="stylesheet" href="@roleCss('patients-index.css')">
```

## 🚀 Step-by-Step Implementation Guide

### For Prenatal Records

#### Step 1: Create Shared Prenatal Record Views

```bash
# Create the index view
cp resources/views/midwife/prenatalrecord/index.blade.php \
   resources/views/shared/prenatalrecord/index.blade.php
```

#### Step 2: Update the Shared View

Edit `resources/views/shared/prenatalrecord/index.blade.php`:

```blade
{{-- Change layout --}}
- @extends('layout.midwife')
+ @extends('layout.' . auth()->user()->role)

{{-- Change CSS paths --}}
- <link rel="stylesheet" href="{{ asset('css/midwife/prenatalrecord.css') }}">
+ <link rel="stylesheet" href="@roleCss('prenatalrecord.css')">

{{-- Change routes --}}
- action="{{ route('midwife.prenatalrecord.index') }}"
+ action="@roleRoute('prenatalrecord.index')"

- href="{{ route('midwife.prenatalrecord.create') }}"
+ href="@roleRoute('prenatalrecord.create')"

{{-- Change partials --}}
- @include('partials.midwife.prenatalrecord.prenataladd')
+ @include('partials.shared.prenatalrecord.prenataladd')

{{-- Change scripts --}}
- <script src="{{ asset('js/midwife/prenatalrecord-index.js') }}"></script>
+ <script src="@roleJs('prenatalrecord-index.js')"></script>
```

#### Step 3: Create Shared Partials

```bash
# Copy modal partials
cp resources/views/partials/midwife/prenatalrecord/prenataladd.blade.php \
   resources/views/partials/shared/prenatalrecord/prenataladd.blade.php
```

Edit the partial and replace:
```blade
- action="{{ route('midwife.prenatalrecord.store') }}"
+ action="@roleRoute('prenatalrecord.store')"
```

#### Step 4: Update PrenatalRecordController

```php
// app/Http/Controllers/PrenatalRecordController.php

// Change 1: Extend BaseController
- class PrenatalRecordController extends Controller
+ class PrenatalRecordController extends BaseController

// Change 2: Update index method
public function index(Request $request)
{
    // ... existing logic ...

-   $view = auth()->user()->role === 'midwife'
-       ? 'midwife.prenatalrecord.index'
-       : 'bhw.prenatalrecord.index';
-   return view($view, compact('records'));
+   return view($this->roleView('prenatalrecord.index'), compact('records'));
}

// Change 3: Update create method
public function create()
{
    // ... existing logic ...

-   $view = auth()->user()->role === 'midwife'
-       ? 'midwife.prenatalrecord.create'
-       : 'bhw.prenatalrecord.create';
-   return view($view, compact('patients'));
+   return view($this->roleView('prenatalrecord.create'), compact('patients'));
}

// Repeat for show(), edit(), etc.
```

### For Child Records

Follow the same pattern as Prenatal Records:

1. Copy `midwife/childrecord/index.blade.php` → `shared/childrecord/index.blade.php`
2. Replace role-specific code with @role directives
3. Copy partials from `partials/midwife/childrecord/` → `partials/shared/childrecord/`
4. Update `ChildRecordController` to extend `BaseController`
5. Replace view selection logic with `$this->roleView()`

## 🔍 Search & Replace Patterns

Use these patterns to quickly update views:

### In Blade Views:
```bash
# Layout
Find:    @extends('layout.midwife')
Replace: @extends('layout.' . auth()->user()->role)

# CSS
Find:    {{ asset('css/midwife/
Replace: @roleCss('

Find:    {{ asset('css/bhw/
Replace: @roleCss('

# JS
Find:    {{ asset('js/midwife/
Replace: @roleJs('

Find:    {{ asset('js/bhw/
Replace: @roleJs('

# Routes
Find:    {{ route('midwife.
Replace: @roleRoute('

Find:    {{ route('bhw.
Replace: @roleRoute('

# Partials
Find:    @include('partials.midwife.
Replace: @include('partials.shared.

Find:    @include('partials.bhw.
Replace: @include('partials.shared.
```

### In Controllers:
```php
// Find this pattern:
$view = auth()->user()->role === 'midwife'
    ? 'midwife.MODULE.ACTION'
    : 'bhw.MODULE.ACTION';
return view($view, compact('data'));

// Replace with:
return view($this->roleView('MODULE.ACTION'), compact('data'));
```

## 📦 Benefits of This Refactoring

### 1. **Reduced Code Duplication**
- Before: 2 identical views per page (92 files × 2 = 184 view files)
- After: 1 shared view per page (92 files)
- **Result**: 50% reduction in view files

### 2. **Easier Maintenance**
- Bug fixes need to be applied only once
- UI changes are consistent across roles
- Less chance of views drifting apart

### 3. **Scalable for New Roles**
- Adding a new role (e.g., "admin") requires:
  - Creating new layout file
  - Creating role-specific CSS/JS files
  - No view duplication needed!

### 4. **Better Testing**
- Test one view instead of multiple
- Consistent behavior across roles
- Role-specific features can be tested with `@midwife` / `@bhw` directives

## ⚠️ Important Notes

### 1. **Role-Specific Features**
Use conditional directives for features that differ between roles:

```blade
@midwife
    <button class="btn-delete">Delete Patient</button>
@endmidwife

@bhw
    <p class="text-gray-500">Contact midwife to delete patients</p>
@endbhw
```

### 2. **JavaScript Considerations**
Role-specific JS files are still loaded separately:
- `js/midwife/patients-index.js`
- `js/bhw/patients-index.js`

If the JS files are identical, consider creating:
- `js/shared/patients-index.js`

And loading it with:
```blade
<script src="{{ asset('js/shared/patients-index.js') }}"></script>
```

### 3. **Testing After Consolidation**

Test both roles thoroughly:
```bash
# Login as Midwife
- Navigate to patients page
- Create a patient
- Edit a patient
- View patient profile
- Check all modals work
- Verify CSS loads correctly
- Verify JS functions work

# Login as BHW
- Repeat all tests above
- Verify permissions work correctly
- Check that BHW-specific restrictions work
```

### 4. **Blade Cache**
After making changes, clear the Blade cache:
```bash
php artisan view:clear
php artisan config:clear
```

## 📊 Consolidation Checklist

Use this checklist to track progress:

### Patient Module
- [x] patients/index.blade.php
- [ ] patients/create.blade.php
- [ ] patients/show.blade.php
- [ ] patients/edit.blade.php
- [ ] patients/profile.blade.php
- [x] partials/patient/patient_add.blade.php
- [ ] partials/patient/patient_view.blade.php
- [ ] partials/patient/patient_edit.blade.php
- [x] PatientController updated

### Prenatal Record Module
- [ ] prenatalrecord/index.blade.php
- [ ] prenatalrecord/create.blade.php
- [ ] prenatalrecord/show.blade.php
- [ ] prenatalrecord/edit.blade.php
- [ ] partials/prenatalrecord/prenataladd.blade.php
- [ ] partials/prenatalrecord/prenatalview.blade.php
- [ ] partials/prenatalrecord/prenataledit.blade.php
- [ ] PrenatalRecordController updated

### Child Record Module
- [ ] childrecord/index.blade.php
- [ ] childrecord/create.blade.php
- [ ] childrecord/show.blade.php
- [ ] childrecord/table.blade.php
- [ ] partials/childrecord/childadd.blade.php
- [ ] partials/childrecord/childview.blade.php
- [ ] partials/childrecord/childedit.blade.php
- [ ] ChildRecordController updated

### Prenatal Checkup Module (Midwife Only)
Note: This is midwife-only, may not need consolidation

### Reports Module
- [ ] reports/index.blade.php
- [ ] reports/print.blade.php
- [ ] reports/accomplishment-print.blade.php
- [ ] ReportController updated

## 🔧 Troubleshooting

### Issue: "View not found"
**Solution**: Clear view cache
```bash
php artisan view:clear
```

### Issue: "Undefined method roleView()"
**Solution**: Make sure controller extends `BaseController`:
```php
class YourController extends BaseController
```

### Issue: "Call to undefined function roleRoute()"
**Solution**: Clear config cache and check AppServiceProvider:
```bash
php artisan config:clear
```

### Issue: CSS/JS not loading
**Solution**: Check that role-specific files exist:
```
css/midwife/MODULE.css
css/bhw/MODULE.css
js/midwife/MODULE.js
js/bhw/MODULE.js
```

## 📚 Additional Resources

- Laravel Blade Documentation: https://laravel.com/docs/11.x/blade
- Custom Blade Directives: https://laravel.com/docs/11.x/blade#custom-if-statements
- Service Providers: https://laravel.com/docs/11.x/providers

## ✨ Next Steps

1. **Complete Remaining Modules** - Use this guide to consolidate prenatal records and child records
2. **Test Thoroughly** - Test with both midwife and BHW accounts
3. **Remove Old Views** - Once confirmed working, delete `midwife/` and `bhw/` view folders
4. **Update Documentation** - Document any role-specific features
5. **Code Review** - Have team review the refactored code

## 📞 Questions?

If you encounter issues or need clarification:
1. Check this guide first
2. Review the example files (patients module)
3. Test with the working example before proceeding

---

**Author**: Claude Code Refactoring
**Date**: November 2025
**Version**: 1.0
