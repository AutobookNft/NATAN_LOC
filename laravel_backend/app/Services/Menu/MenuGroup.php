<?php

declare(strict_types=1);

namespace App\Services\Menu;

/**
 * @package App\Services\Menu
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Groups related menu items with i18n support
 *
 * Modular menu system for NATAN_LOC - groups menu items by functionality
 */
class MenuGroup
{
    public string $name;
    public ?string $icon;
    /** @var MenuItem[] */
    public array $items;

    /**
     * Constructor with translation support
     *
     * @param string $translatedName Already translated menu group name
     * @param string|null $iconKey Icon key for the group
     * @param array $items Array of MenuItem objects
     */
    public function __construct(string $translatedName, ?string $iconKey = null, array $items = [])
    {
        $this->name = $translatedName;
        $this->icon = $iconKey;
        $this->items = $items;
    }

    /**
     * Adds an item to the menu group
     */
    public function addItem(MenuItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Checks if the menu has visible items
     */
    public function hasVisibleItems(): bool
    {
        return !empty($this->items);
    }
}

