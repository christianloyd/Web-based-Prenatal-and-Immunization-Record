/**
 * Shared Utilities Entry Point
 * Centralized exports for all shared utilities, configurations, and components
 *
 * This module consolidates all shared resources to eliminate code duplication
 * across BHW, Midwife, and Admin interfaces.
 *
 * @module shared
 */

// Export all utilities
export * from './utils/sweetalert.js';
export * from './utils/validation.js';
export * from './utils/api.js';
export * from './utils/dom.js';

// Export all configurations
export * from './config/routes.js';
export * from './config/permissions.js';

// Export all components
export * from './components/modal.js';
export * from './components/table.js';
export * from './components/notifications.js';
export * from './components/form.js';

// Export all pages (unified modules)
export * from './pages/patients.js';
export * from './pages/prenatalrecords.js';

// Default exports for convenience
export { default as sweetalert } from './utils/sweetalert.js';
export { default as validation } from './utils/validation.js';
export { default as api } from './utils/api.js';
export { default as dom } from './utils/dom.js';
export { default as routes } from './config/routes.js';
export { default as permissions } from './config/permissions.js';
export { default as modal } from './components/modal.js';
export { default as table } from './components/table.js';
export { default as notifications } from './components/notifications.js';
export { default as form } from './components/form.js';
export { default as patients } from './pages/patients.js';
export { default as prenatalrecords } from './pages/prenatalrecords.js';
