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
