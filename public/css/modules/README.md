# CSS Modules

This directory contains modular CSS files extracted from Blade templates for better maintainability and organization.

## Structure

```
css/modules/
├── user-management.css     # User management module styles
└── README.md              # This file
```

## Usage

Include CSS modules in your Blade templates using the `@push('styles')` directive:

```blade
@push('styles')
    <link href="{{ asset('css/modules/user-management.css') }}" rel="stylesheet">
@endpush
```

## Benefits

1. **Separation of Concerns**: CSS is separated from HTML/PHP logic
2. **Maintainability**: Easier to update and debug styles
3. **Reusability**: Styles can be reused across multiple views
4. **Performance**: CSS can be cached separately
5. **Clarity**: Views are cleaner and more readable

## Module Guidelines

- Each module should contain styles for a specific feature/section
- Use descriptive class names with consistent naming conventions
- Include responsive styles within the same module
- Add comments to document complex styles
- Follow existing project color schemes and spacing