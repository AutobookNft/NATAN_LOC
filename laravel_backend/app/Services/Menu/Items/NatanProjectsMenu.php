<?php

declare(strict_types=1);

namespace App\Services\Menu\Items;

use App\Services\Menu\MenuItem;

/**
 * @package App\Services\Menu\Items
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 2.0.0 (NATAN_LOC - Projects Integration FEGI)
 * @date 2025-11-21
 * @purpose Menu item for Projects page (document management)
 */
class NatanProjectsMenu extends MenuItem
{
    public function __construct()
    {
        parent::__construct(
            translationKey: 'menu.natan_projects',
            route: 'natan.ui.projects.index',
            icon: 'folder',
            permission: null, // Progetti accessibili a tutti gli utenti autenticati del tenant
            modalAction: null // Removed modal, now navigates to projects page
        );
    }
}














