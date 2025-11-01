<?php

declare(strict_types=1);

namespace App\Services\Menu;

/**
 * @package App\Services\Menu
 * @author Padmin D. Curtis (AI Partner OS3.0) for Fabio Cherici
 * @version 1.0.0 (NATAN_LOC)
 * @date 2025-10-31
 * @purpose Base class for all menu items with i18n, modal actions, and parameterized route support
 *
 * Menu system foundation for NATAN_LOC navigation - modular and scalable
 */
class MenuItem
{
    public string $name;
    public string $translationKey;
    public string $route;
    public ?string $icon; // Icon name (Heroicons) - will be rendered as SVG inline
    public ?string $permission;
    /** @var MenuItem[]|null */
    public ?array $children;
    public array $routeParams;
    public ?string $modalAction;
    public bool $isModalAction;

    /**
     * Constructor with translation, modal action, and parameterized route support
     *
     * @param string $translationKey The translation key
     * @param string $route The route name (or '#' for modal actions)
     * @param string|null $icon The icon key
     * @param string|null $permission The required permission
     * @param array|null $children Child menu items
     * @param string|null $modalAction The modal action attribute
     * @param array $routeParams Associative array of parameters for the route
     */
    public function __construct(
        string $translationKey,
        string $route,
        ?string $icon = null,
        ?string $permission = null,
        ?array $children = null,
        ?string $modalAction = null,
        array $routeParams = [],
    ) {
        $this->translationKey = $translationKey;
        $this->name = __($translationKey);
        $this->route = $route;
        $this->icon = $icon;
        $this->permission = $permission;
        $this->children = $children;
        $this->routeParams = $routeParams;
        $this->modalAction = $modalAction;
        $this->isModalAction = !empty($modalAction);

        // Validation
        if ($this->isModalAction && $route !== '#') {
            throw new \InvalidArgumentException(
                "Modal action items must use '#' as route. Item: {$translationKey}"
            );
        }
    }

    /**
     * Checks if this menu item has children
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * Gets the appropriate href for this menu item
     */
    public function getHref(): string
    {
        if ($this->isModalAction) {
            return '#';
        }

        try {
            return route($this->route, $this->routeParams);
        } catch (\Illuminate\Routing\Exceptions\UrlGenerationException $e) {
            // Route not defined yet - return placeholder
            \Log::warning("Route not defined: {$this->route} for menu item: {$this->translationKey}");
            return '#';
        }
    }

    /**
     * Gets the HTML attributes for this menu item
     *
     * @return array Associative array of HTML attributes
     */
    public function getHtmlAttributes(): array
    {
        $attributes = [];

        if ($this->isModalAction) {
            $attributes['data-action'] = $this->modalAction;
            $attributes['role'] = 'button';
            $attributes['aria-label'] = $this->name;
        }

        return $attributes;
    }
}

