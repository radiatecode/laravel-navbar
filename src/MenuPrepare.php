<?php


namespace RadiateCode\LaravelNavbar;

use Illuminate\Support\Str;
use RadiateCode\LaravelNavbar\Contracts\MenuPrepare as MenuPrepareContract;

class MenuPrepare implements MenuPrepareContract
{
    private $menu = [];

    private $links = [];

    private $childOf = '';

    private $appendTo = [];

    public function addMenu(string $name, string $icon = 'fa fa-home'): MenuPrepare
    {
        $this->menu = [
            'name' => Str::slug($name),
            'icon' => $icon
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
    public function linkByMethod(
        string $method_name,
        string $title = null,
        string $icon = null,
        array $css_classes = []
    ): MenuPrepare {
        $this->links[$method_name] = [
            'link-title' => $title,
            'link-icon' => $icon ?: 'far fa-circle nav-icon',
            'link-css-class' => $css_classes,
        ];

        return $this;
    }

    /**
     * Menus is child of another menu
     *
     * @param string $controllerClass
     *
     * @return $this
     */
    public function childOf(string $controllerClass): MenuPrepare
    {
        $this->childOf = $controllerClass;

        return $this;
    }

    /**
     * Menu append to another menu
     *
     * @param string $controllerClass
     *
     * @return $this
     */
    public function appendTo(string $controllerClass): MenuPrepare
    {
        $this->appendTo[] = $controllerClass;

        return $this;
    }

    public function hasParent(): bool
    {
        return ! empty($this->childOf);
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

    public function getMenu(): array
    {
        return $this->menu;
    }

    public function getParent(): string
    {
        return $this->childOf;
    }

    public function getLinks(): array
    {
        return $this->links;
    }
}
