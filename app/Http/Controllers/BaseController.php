<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

/**
 * Base Controller with shared functionality for role-based views
 */
class BaseController extends Controller
{
    /**
     * Get the appropriate view path based on user role
     *
     * @param string $view The view name (e.g., 'patients.index')
     * @return string The complete view path (e.g., 'shared.patients.index')
     */
    protected function roleView(string $view): string
    {
        // Always return shared views
        return "shared.{$view}";
    }

    /**
     * Get the appropriate route name based on user role
     *
     * @param string $routeName The route name without role prefix (e.g., 'patients.index')
     * @return string The complete route name (e.g., 'midwife.patients.index')
     */
    protected function roleRoute(string $routeName): string
    {
        $role = Auth::user()->role ?? 'midwife';
        return "{$role}.{$routeName}";
    }

    /**
     * Get the appropriate layout based on user role
     *
     * @return string The layout name (e.g., 'layout.midwife')
     */
    protected function roleLayout(): string
    {
        $role = Auth::user()->role ?? 'midwife';
        return "layout.{$role}";
    }

    /**
     * Get the appropriate CSS path based on user role
     *
     * @param string $cssFile The CSS file name (e.g., 'patients-index.css')
     * @return string The complete CSS path
     */
    protected function roleCss(string $cssFile): string
    {
        $role = Auth::user()->role ?? 'midwife';
        return asset("css/{$role}/{$cssFile}");
    }

    /**
     * Get the appropriate JS path based on user role
     *
     * @param string $jsFile The JS file name (e.g., 'patients-index.js')
     * @return string The complete JS path
     */
    protected function roleJs(string $jsFile): string
    {
        $role = Auth::user()->role ?? 'midwife';
        return asset("js/{$role}/{$jsFile}");
    }
}
