<?php

declare(strict_types=1);

namespace App\Services\Menu\Items;

use App\Services\Menu\MenuItem;

/**
 * @package App\Services\Menu\Items
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Menu item for Users Management (Admin section)
 */
class NatanUsersMenu extends MenuItem
{
    public function __construct()
    {
        parent::__construct(
            translationKey: 'menu.users',
            route: 'users.index',
            icon: 'users',
            permission: null // Permission checked in controller (admin, superadmin, pa_entity_admin)
        );
    }
}

