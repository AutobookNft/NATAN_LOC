<?php

declare(strict_types=1);

namespace App\Services\Menu\Items;

use App\Services\Menu\MenuItem;

/**
 * @package App\Services\Menu\Items
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Menu item for AI Costs monitoring dashboard
 */
class NatanAiCostsMenu extends MenuItem
{
    public function __construct()
    {
        parent::__construct(
            translationKey: 'menu.natan_ai_costs',
            route: 'natan.ai-costs.dashboard',
            icon: 'currency-dollar',
            permission: null // Accessible to all NATAN users
        );
    }
}

