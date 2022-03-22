<?php


namespace RadiateCode\LaravelNavbar;

use RadiateCode\LaravelNavbar\Contracts\MenuPrepare as MenuPrepareContract;

class MenuPrepare implements MenuPrepareContract
{
    private $menu = '';

    private $links = [];

    private $childOf = '';

    private $appendTo = [];

    public function addMenu(string $name): MenuPrepare
    {
        $this->menu = $name;

        return $this;
    }

    public function linkAssocMethod(string $method_name,string $title = null,string $icon = null,array $css_classes = []): MenuPrepare
    {
        $this->links[$method_name] = [
            'link-title' => $title,
            'link-icon' => $icon,
            'link-css-class' => $css_classes
        ];

        return $this;
    }

    public function childOf($controllerClass): MenuPrepare
    {
        $this->childOf = $controllerClass;

        return $this;
    }

    public function appendTo($controllerClass): MenuPrepare
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

    public function getMenu(): string
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
