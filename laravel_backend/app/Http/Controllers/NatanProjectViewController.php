<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * @Oracode Controller: NATAN Project Views
 * 🎯 Purpose: Render Blade views for project management
 * 
 * @package App\Http\Controllers
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC - Projects Frontend)
 * @date 2025-11-21
 * @purpose Render project views (Blade templates)
 * 
 * Note: Auth middleware is applied in routes (web.php)
 *       Laravel 11 removed middleware() method from base Controller
 */
class NatanProjectViewController extends Controller
{
    /**
     * Display projects list page
     * 
     * GET /natan/projects
     */
    public function index(): View
    {
        return view('natan.projects.index');
    }

    /**
     * Display project detail page
     * 
     * GET /natan/projects/{id}
     */
    public function show(int $id): View
    {
        return view('natan.projects.show', [
            'projectId' => $id,
        ]);
    }
}

