<?php


namespace RadiateCode\LaravelNavbar\Presenter;


use RadiateCode\LaravelNavbar\Contracts\Presenter;

class NavBarPresenter implements Presenter
{
    public function openNavTag(string $class = null, array $attributes = []): string
    {
        return PHP_EOL . '<nav class="mt-2">' . PHP_EOL;
    }

    public function closeNavTag(): string
    {
        return PHP_EOL . '</nav>' . PHP_EOL;
    }

    public function openNavULTag(string $class = null, array $attributes = []): string
    {
        return PHP_EOL
            . '<ul class="nav nav-pills nav-sidebar flex-column nav-child-indent menu-open" data-widget="treeview" role="menu" data-accordion="false">'
            . PHP_EOL;
    }

    public function closeNavULTag(): string
    {
        return PHP_EOL . '</ul>' . PHP_EOL;
    }

    /**
     * Add header or section
     *
     * @param string      $title
     * @param string|null $class
     * @param array       $attributes
     *
     * @return string
     */
    public function header(string $title, string $class = null, array $attributes = []): string
    {
        return PHP_EOL . '<li class="nav-header">' . $title . '</li>' . PHP_EOL;
    }

    public function nav($menu): string
    {
        return $this->navbarTreeGenerate($menu);
    }

    protected function navbarTreeGenerate($menu): string
    {
        /**
         * Tree navbar with nav links and children navs
         */
        if (array_key_exists('children', $menu) && $menu['children']) {
            return PHP_EOL . '<li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon ' . $menu['icon'] . '"></i>
                        <p>
                            ' . $menu['title'] . '
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                       ' . $this->navLink($menu['nav-links']) . $this->children($menu) . '
                    </ul>
                </li>' . PHP_EOL;
        }

        /**
         * Tree navbar with nav links
         */
        if (count($menu['nav-links']) > 1){
            return '<li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon ' . $menu['icon'] . '"></i>
                        <p>
                            ' . $menu['title'] . '
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                       ' . $this->navLink($menu['nav-links']) . '
                    </ul>
                </li>' . PHP_EOL;
        }

        /**
         * Single nav link [no-tree]
         */
        return '<li class="nav-item">
                    <a href="' . $menu['nav-links'][0]['link-url'] . '" class="nav-link">
                        <i class="nav-icon ' . $menu['icon'] . '"></i> <p> ' . $menu['title'] . ' </p>
                    </a>
                </li>' . PHP_EOL;

    }

    protected function navLink($links): string
    {
        $linkHtml = '';

        if (count($links) == 0){
            return $linkHtml;
        }

        foreach ($links as $link) {
            $navItemCssClasses = implode(' ',array_merge($link['link-css-class'],['nav_items']));

            $linkHtml .= "<li class='".$navItemCssClasses."'>"
                ."<a class='nav-link' href='" . $link['link-url'] . "'>"
                ."<i class='nav-icon " . $link['link-icon'] . "'></i>" . $link['link-title']
                ."</a>"
                ."</li>" . PHP_EOL;
        }

        return $linkHtml;
    }

    protected function children($menu): string
    {
        $children = '';

        if (array_key_exists('children', $menu) && $menu['children']) {
            foreach ($menu['children'] as $key => $item) {
                $children .= $this->navbarTreeGenerate($item);
            }
        }

        return $children;
    }
}
