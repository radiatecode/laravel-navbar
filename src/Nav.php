<?php

namespace RadiateCode\LaravelNavbar;

use Closure;
use Illuminate\Support\Str;
use RadiateCode\LaravelNavbar\Html\NavBuilder;

class Nav
{
    protected array $nav = [];

    protected string $header_key = '';

    protected $checkActive = false;

    public function __construct()
    {
        $this->checkActive = config('navbar.enable-nav-active');
    }

    public static function make(): Nav
    {
        return new self();
    }

    /**
     * Nav header or section
     * [Note: it is used to group certain navs]
     *
     * @param string $name
     * @param Closure $closure
     * @param array $attributes
     * @return Nav
     */
    public function header(string $name, Closure $closure, array $attributes = []): Nav
    {
        $nav = new Nav();

        $closure($nav);

        $navItems = $nav->render();

        // if no nav-items then why bother to create header in first place
        if (empty($navItems)) {
            return $this;
        }

        $this->nav[Str::slug($name)] = [
            'title' => $name,
            'attributes' => $attributes,
            'type' => 'header',
            'nav-items' => $navItems
        ];

        return $this;
    }

    /**
     * Add nav item with it's attributes and it's children (if any)
     *
     * @param string $title
     * @param string $url
     * @param array|null $attributes
     * @param callable|null $configure
     * @return Nav
     */
    public function add(string $title, string $url, ?array $attributes = null, ?callable $children = null): Nav
    {
        $childrenItems = [];

        $hasNoChildren = false;

        $isActive = false;

        // active a nav
        if ($this->checkActive && request()->getUri() === $url) {
            $isActive = true;
        }

        if ($children) {
            $childrenNav = new Children();

            $children($childrenNav);

            $childrenItems = $childrenNav->render();

            $hasNoChildren = empty($childrenItems);

            // active parent nav if any child nav is active
            $isActive = $this->isChildrenActive($childrenItems);
        }

        // if no children then why bother to create the nav
        if ($hasNoChildren) {
            return $this;
        }

        $navKey = Str::slug(strtolower($title));

        $this->nav[$navKey] = [
            'title' => $title,
            'url' => $url,
            'attributes' => $attributes,
            'is_active' => $isActive,
            'type' => 'menu',
            'children' => $childrenItems
        ];

        return $this;
    }

    /**
     * Condinally add nav with it's attributes and it's children (if any)
     *
     * @param string|Closure $condition
     * @param string $title
     * @param string $url
     * @param array $attributes
     * @param callable|null $configure
     * @return self
     */
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
        return $this->nav;
    }

    protected function isChildrenActive($items)
    {
        if (!$this->checkActive) {
            return false;
        }

        foreach ($items as $key => $item) {
            if ($item['is_active']) {
                return true;
            }
        }

        return false;
    }
}
