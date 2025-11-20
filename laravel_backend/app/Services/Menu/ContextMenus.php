<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Services\Menu\Items\NatanChatMenu;
use App\Services\Menu\Items\NatanDocumentsMenu;
use App\Services\Menu\Items\NatanProjectsMenu;
use App\Services\Menu\Items\NatanScrapersMenu;
use App\Services\Menu\Items\NatanEmbeddingsMenu;
use App\Services\Menu\Items\NatanAiCostsMenu;
use App\Services\Menu\Items\NatanStatisticsMenu;
use App\Services\Menu\Items\NatanBatchMenu;
use App\Services\Menu\Items\NatanTenantsMenu;
use App\Services\Menu\Items\NatanAdminConfigMenu;
use App\Services\Menu\Items\NatanUsersMenu;
use Illuminate\Support\Facades\Log;

/**
 * @package App\Services\Menu
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Provides context-aware menu groups for NATAN_LOC (PA & Enterprises)
 *
 * Modular and scalable menu system - each feature has its own MenuItem class
 */
class ContextMenus
{
    /**
     * Get menu groups for NATAN_LOC context
     *
     * @param string $context The current application context (natan.chat, infraufficio.chat, infraufficio.bacheca)
     * @return array Array of MenuGroup objects
     */
    public static function getMenusForContext(string $context): array
    {
        $menus = [];

        Log::info('🔍 NATAN Context Menus - Context detected', [
            'context' => $context,
        ]);

        // Context-aware menu structure
        switch ($context) {
            case 'natan.chat':
            case 'natan':
            case 'natan.dashboard':
            default:
                // Chat menu (main feature - shown first)
                $chatMenu = new MenuGroup(__('menu.natan_chat'), null, [
                    new NatanChatMenu(),
                ]);
                $menus[] = $chatMenu;

                // Main NATAN menu with all features organized by sections
                $mainMenu = new MenuGroup(__('menu.natan_management'), null, [
                    // Documents section
                    new NatanDocumentsMenu(),

                    // Projects section (modal in chat)
                    new NatanProjectsMenu(),

                    // Collection section
                    new NatanScrapersMenu(),
                    new NatanBatchMenu(),

                    // Intelligence section
                    new NatanEmbeddingsMenu(),
                    new NatanStatisticsMenu(),

                    // Costs section
                    new NatanAiCostsMenu(),
                ]);

                $menus[] = $mainMenu;

                // Superadmin section (for tenant management)
                $superadminMenu = new MenuGroup(__('menu.superadmin'), 'shield-check', [
                    new NatanTenantsMenu(),
                ]);
                $menus[] = $superadminMenu;

                // Admin section (for tenant-specific configurations)
                $adminMenu = new MenuGroup(__('menu.admin'), 'cog-6-tooth', [
                    new NatanUsersMenu(),
                    new NatanAdminConfigMenu(),
                ]);
                $menus[] = $adminMenu;
                break;

            case 'infraufficio.chat':
                // Infraufficio Chat context - focused on internal communication
                $chatMenu = new MenuGroup(__('menu.infraufficio_chat'), null, [
                    new NatanChatMenu(), // Reuse chat interface
                ]);
                $menus[] = $chatMenu;

                // Infraufficio specific features
                $infraufficioMenu = new MenuGroup(__('menu.infraufficio_management'), null, [
                    new NatanDocumentsMenu(),
                    new NatanProjectsMenu(),
                    new NatanStatisticsMenu(),
                ]);
                $menus[] = $infraufficioMenu;

                // Admin section (for tenant-specific configurations)
                $adminMenu = new MenuGroup(__('menu.admin'), 'cog-6-tooth', [
                    new NatanUsersMenu(),
                ]);
                $menus[] = $adminMenu;
                break;

            case 'infraufficio.bacheca':
                // Infraufficio Bacheca context - announcement board
                $bachecaMenu = new MenuGroup(__('menu.infraufficio_bacheca'), null, [
                    // Bacheca-specific features would go here
                    // For now, reuse existing features
                    new NatanDocumentsMenu(),
                    new NatanProjectsMenu(),
                ]);
                $menus[] = $bachecaMenu;

                // Management tools
                $managementMenu = new MenuGroup(__('menu.management'), null, [
                    new NatanUsersMenu(),
                    new NatanStatisticsMenu(),
                ]);
                $menus[] = $managementMenu;
                break;
        }

        return $menus;
    }
}