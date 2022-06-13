<?php


namespace RadiateCode\LaravelNavbar;

use Illuminate\Support\Str;
use RadiateCode\LaravelNavbar\Contracts\NavPrepare as NavPrepareContract;

class NavPrepare implements NavPrepareContract
{
    private $menu = [];

    private $header = [];

    private $links = [];

    private $childOf = [];

    private $appendTo = [];

    private $permissions = [];

    public function addHeader(string $name): NavPrepare
    {
        $this->header = [
            'name' => Str::slug($name),
            'type' => 'header',
        ];

        return $this;
    }

    public function addNav(
        string $name,
        string $icon = 'fa fa-home'
    ): NavPrepare {
        $this->menu = [
            'name' => Str::slug($name),
            'icon' => $icon,
            'type' => 'menu',
        ];

        return $this;
    }

    /**
     * Generate menu link by route controller associate method
     *
     * @param  string  $method_name
     * @param  string|null  $title
     * @param  string|null  $icon
     * @param  array  $css_classes
     *
     * @return $this
     */
    public function addNavLink(
        string $method_name,
        string $title = null,
        string $icon = null,
        array $css_classes = []
    ): NavPrepare {
        $this->links[$method_name] = [
            'link-title' => $title,
            'link-icon' => $icon ?: 'far fa-circle nav-icon',
            'link-css-class' => $css_classes,
        ];

        return $this;
    }

    public function setPermissions(array $permissions): NavPrepare
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Menus is child of another menu
     *
     * @param  string  $name
     * @param  string  $icon
     *
     * @return $this
     */
    public function childOf(
        string $name,
        string $icon = 'fa fa-circle'
    ): NavPrepare {
        $this->childOf = [
            'name' => $name,
            'icon' => $icon,
            'type' => 'menu',
        ];

        return $this;
    }

    /**
     * Menu append to another menu
     *
     * @param  string  $controllerClass
     *
     * @return $this
     */
    public function appendTo(string $controllerClass): NavPrepare
    {
        $this->appendTo[] = $controllerClass;

        return $this;
    }

    public function hasParent(): bool
    {
        return ! empty($this->childOf);
    }

    public function hasHeader(): bool
    {
        return ! empty($this->header);
    }

    public function isAppendable(): bool
    {
        return ! empty($this->appendTo);
    }

    public function hasInconsistencyInAppend(): bool
    {
        return count($this->appendTo) != count($this->links);
    }

    public function getAppends(): array
    {
        return $this->appendTo;
    }

    public function getNav(): array
    {
        return $this->menu;
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getParent(): array
    {
        return $this->childOf;
    }

    public function getNavLinks(): array
    {
        return $this->links;
    }

    public function getMenuPermissions(): array
    {
        return $this->permissions;
    }

    public function when(
        bool $value,
        callable $callback,
        callable $default = null
    ) {
        if ($value) {
            return $callback($this) ?: $this;
        } elseif ($default) {
            return $default($this) ?: $this;
        }

        return $this;
    }
}