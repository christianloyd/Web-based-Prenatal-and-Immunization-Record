# JavaScript Modules

This directory contains modular JavaScript files extracted from Blade templates for better maintainability and organization.

## Structure

```
js/modules/
├── user-management.js      # User management module functions
└── README.md              # This file
```

## Usage

Include JavaScript modules in your Blade templates using the `@push('scripts')` directive:

```blade
@push('scripts')
    <!-- Configure Laravel routes for JavaScript -->
    <script>
        window.moduleRoutes = {
            store: '{{ route("module.store") }}',
            update: '{{ route("module.update", ":id") }}'
        };
    </script>

    <!-- Include the module -->
    <script src="{{ asset('js/modules/module-name.js') }}"></script>
@endpush
```

## Benefits

1. **Separation of Concerns**: JavaScript logic is separated from HTML/PHP
2. **Maintainability**: Easier to debug and update functionality
3. **Reusability**: Functions can be reused across multiple views
4. **Performance**: JavaScript can be cached separately
5. **Clarity**: Views are cleaner and more readable
6. **Testing**: Easier to unit test separated JavaScript

## Module Guidelines

- Each module should contain JavaScript for a specific feature/section
- Use meaningful function and variable names
- Include proper error handling and validation
- Add comments to document complex logic
- Follow consistent coding standards
- Initialize modules on `DOMContentLoaded` event

## Global Variables

When creating global variables or functions that need to be accessible via `onclick` handlers, declare them in the global scope:

```javascript
// Global variables accessible throughout the page
let currentItem = null;
let isEditMode = false;

// Global functions for onclick handlers
function openModal() {
    // Function implementation
}
```

## Route Configuration

For Laravel route integration, configure routes in a separate script block before including the module:

```javascript
window.moduleRoutes = {
    store: '{{ route("module.store") }}',
    update: '{{ route("module.update", ":id") }}',
    delete: '{{ route("module.destroy", ":id") }}'
};
```

This allows the JavaScript module to use Laravel routes dynamically.