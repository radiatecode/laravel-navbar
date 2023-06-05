<?php


namespace RadiateCode\LaravelNavbar\Presenter;

use RadiateCode\LaravelNavbar\Contracts\Presenter;

class NavbarPresenter implements Presenter
{
    protected array $navItems = [];

    public function __construct(array $navItems)
    {
        $this->navItems = $navItems;
    }

    /**
     * Nav tag. 
     * The whole nav component.
     *
     * @param string $navItems
     * @return string
     */
    protected function navTag(string $navItems): string
    {
        return PHP_EOL . '<nav class="mt-2">'
            . PHP_EOL . '<ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">'
            . $navItems .
            '</ul>' . PHP_EOL
            . '</nav>' . PHP_EOL;
    }

    /**
     * Header or section of navs
     *
     * @param string $name
     * @return void
     */
    protected function headerTag(string $name, array $attributes = [])
    {
        $icon = array_key_exists('icon', $attributes)
            ? '<i class="nav-header-icon ' . $attributes['icon'] . '"></i>'
            : '';

        return '<li class="nav-header">' 
        . ($icon) . $name
        . '</li>';
    }

    /**
     * Tree nav
     * 
     * [Tree nav means a nav with children nav items]
     * @param array $navItem
     * @return void
     */
    protected function treeTag(array $navItem)
    {
        $treeNav = '';
        $navIcon = $this->navIcon($navItem);
        $active = $navItem['is_active'] ? 'active' : '';
        $menuOpen = $navItem['is_active'] ? 'menu-open' : 'has-treeview';

        $treeNav = PHP_EOL . '<li class="nav-item ' . $menuOpen . '">
                        <a href="' . $navItem['url'] . '" class="nav-link ' . $active . '">
                            <i class="nav-icon ' . $navIcon . '"></i>
                            <p>
                                ' . $navItem['title'] . '
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">';

        $treeNav .= $this->nested($navItem['children']['nav-items']);

        $treeNav .= '</ul></li>' . PHP_EOL;

        return $treeNav;
    }

    /**
     * Solo nav
     * 
     * [No children nav]
     * @param array $navItem
     * @return void
     */
    protected function soloTag(array $navItem)
    {
        $navIcon = $this->navIcon($navItem);
        $active = $navItem['is_active'] ? 'active' : '';

        return PHP_EOL . '<li class="nav-item">
                    <a href="' . $navItem['url'] . '" class="nav-link ' . $active . '">
                        <i class="nav-icon ' . $navIcon . '"></i>
                        <p> ' . $navItem['title'] . ' </p>
                    </a>
                </li>' . PHP_EOL;
    }

    /**
     * Children or nested nav
     *
     * @param array $childrenItems
     * @return void
     */
    protected function nested(array $childrenItems)
    {
        return $this->nav($childrenItems);
    }

    /**
     * Nav generate
     *
     * @param array $items
     * @return void
     */
    protected function nav(array $items)
    {
        $nav = '';

        foreach ($items as $item) {
            $nav .= $this->isTreeNav($item)
                ? $this->treeTag($item)
                : $this->soloTag($item);
        }

        return $nav;
    }

    public function navbar(): string
    {
        $nav = '';

        foreach ($this->navItems as $key => $item) {
            if ($key != 'nav-items' && $item['type'] == 'header') {
                $nav .= $this->headerTag($item['title'], $item['attributes']);

                $nav .= $this->nav($item['nav-items']);
            }
        }

        return $this->navTag($nav);
    }

    /**
     * Is the nav tree or solo
     *
     * @param array $nav
     * @return boolean
     */
    protected function isTreeNav(array $nav)
    {
        return !empty($nav['children']) && !empty($nav['children']['nav-items']);
    }

    /**
     * nav icon
     *
     * @param array $navItem
     * @return string
     */
    protected function navIcon(array $navItem)
    {
        return array_key_exists('icon', $navItem['attributes'])
            ? $navItem['attributes']['icon']
            : 'fa fa-circle';
    }
}
