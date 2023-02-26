<?php

namespace RadiateCode\LaravelNavbar;

use Closure;
use Illuminate\Support\Str;

class Nav
{
    protected array $nav = [];

    protected array $navWithHeader = [];

    protected string $header_key = '';

    public static function make(): Nav
    {
        return new self();
    }

    public function header($name, Closure $closure, array $attributes = []): Nav
    {
        $nav = new Nav();

        $closure($nav);

        $navItems = $nav->render();

        // if no nav-items then why bother to create header in first place
        if (empty($navItems['nav-items'])) {
            return $this;
        }

        $this->navWithHeader[Str::slug($name)] = array_merge([
            'title' => $name,
            'attributes' => $attributes,
            'type' => 'header'
        ], $navItems);

        return $this;
    }

    public function add(string $title, string $url, ?array $attributes = null, ?callable $configure = null): Nav
    {
        $childrenItems = [];

        if ($configure) {
            $children = new Children();

            $configure($children);

            $childrenItems = $children->render();
        }

        // if no children then why bother to create the nav
        if (array_key_exists('nav-items', $childrenItems) && empty($childrenItems['nav-items'])) {
            return $this;
        }

        $this->nav[] = [
            'title' => $title,
            'url' => $url,
            'attributes' => $attributes,
            'type' => 'menu',
            'children' => $childrenItems
        ];

        return $this;
    }

    public function addIf($condition, string $title, string $url, array $attributes = [], ?callable $configure = null): self
    {
        $condition = is_callable($condition) ? $condition() : $condition;

        if ($condition) {
            $this->add($title, $url, $attributes, $configure);
        }

        return $this;
    }

    public function render()
    {
        return array_merge($this->navWithHeader, ['nav-items' => $this->nav]);
    }
}
