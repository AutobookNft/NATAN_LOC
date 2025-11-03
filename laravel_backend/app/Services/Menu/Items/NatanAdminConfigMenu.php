<?php

declare(strict_types=1);

namespace App\Services\Menu\Items;

use App\Services\Menu\MenuItem;

/**
 * @package App\Services\Menu\Items
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-11-03
 * @purpose Menu item for Tenant Configurations (under Admin section)
 */
class NatanAdminConfigMenu extends MenuItem
{
    public function __construct()
    {
        parent::__construct(
            translationKey: 'menu.admin_config',
            route: 'admin.config.index',
            icon: 'cog-6-tooth',
            permission: null // Permission checked in controller (admin, superadmin, pa_entity_admin)
        );
    }
}

