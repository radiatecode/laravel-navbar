<?php


namespace RadiateCode\LaravelNavbar\Presenter;


use Illuminate\Support\Facades\Cache;
use RadiateCode\LaravelNavbar\Contracts\Presenter;

class NavBarPresenter implements Presenter
{
    protected array $navItems = [];

    public function __construct(array $navItems)
    {
        $this->navItems = $navItems;
    }

    /**
     * Navigation
     *
     * @param string $ul
     * @return string
     */
    protected function navTag(string $ul)
    {
        return PHP_EOL . '<nav class="mt-2">' . $ul . '</nav>' . PHP_EOL;
    }

    /**
     * Navigation Items
     *
     * @param string $navItems
     * @return string
     */
    protected function ulTag(string $navItems)
    {
        return PHP_EOL . '<ul class="nav nav-pills nav-sidebar flex-column nav-child-indent menu-open" 
        data-widget="treeview" role="menu" data-accordion="false">' . $navItems . '</ul>' . PHP_EOL;
    }

    /**
     * Header Tag
     *
     * @param string $name
     * @return string
     */
    protected function headerTag(string $name)
    {
        return '<li class="nav-header">' . $name . '</li>';
    }

    /**
     * Tree Nav
     *
     * @param array $navItem
     * @return string
     */
    protected function treeNav(array $navItem)
    {
        $treeNav = '';

        $treeNav = PHP_EOL . '<li class="nav-item has-treeview">
                                <a href="' . $navItem['url'] . '" class="nav-link">
                                    <i class="nav-icon ' . $navItem['attributes']['icon'] . '"></i>
                                    <p>
                                        ' . $navItem['title'] . '
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                            <ul class="nav nav-treeview">' . $this->navItems($navItem['children']['nav-items']) . '</ul></li>' . PHP_EOL;

        return $treeNav;
    }

    /**
     * Nav item
     *
     * @param array $navItem
     * @return string
     */
    protected function navItem(array $navItem)
    {
        return  PHP_EOL . '<li class="nav-item">
                                <a href="' . $navItem['url'] . '" class="nav-link">
                                    <i class="nav-icon ' . $navItem['attributes']['icon'] . '"></i>
                                    <p> ' . $navItem['title'] . ' </p>
                                </a>
                           </li>' . PHP_EOL;
    }

    /**
     * Make nav items, it can be called recursively to generate deep level nav items
     *
     * [ex: Parent Nav -> Children Navs -> Children Navs -> Children Navs]
     * 
     * @param array $items
     * @return string
     */
    protected function navItems(array $items)
    {
        $nav = '';

        foreach ($items as $item) {
            $nav .= $this->isTreeNav($item)
                ? $this->treeNav($item)
                : $this->navItem($item);
        }

        return $nav;
    }

    public function navbar(): string
    {
        $nav = '';

        foreach ($this->navItems as $key => $item) {
            if ($key != 'nav-items' && $item['type'] == 'header') {
                $nav .= $this->headerTag($item['title']);

                $nav .= $this->navItems($item['nav-items']);
            }

            if ($key == 'nav-items') {
                $nav .= $this->navItems($item);
            }
        }

        return $this->navTag($this->ulTag($nav));
    }

    protected function isTreeNav(array $nav)
    {
        return $nav['url'] == '#' && !empty($nav['children']['nav-items']);
    }
}
