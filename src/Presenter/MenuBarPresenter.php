<?php


namespace RadiateCode\LaravelNavbar\Presenter;


use RadiateCode\LaravelNavbar\Contracts\Presenter;

class MenuBarPresenter implements Presenter
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
            . '<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">'
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
        if (array_key_exists('children', $menu) && $menu['children']) {
            return $this->navWithChildren($menu);
        }

        return $this->navWithNoChildren($menu);
    }

    protected function navWithNoChildren($menu): string
    {
        $menuLinks = $menu['nav-links'];

        if (count($menuLinks) > 1){
            return '<li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon ' . $menu['icon'] . '"></i>
                        <p>
                            ' . $menu['title'] . '
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                       ' . $this->navLink($menuLinks) . '
                    </ul>
                </li>' . PHP_EOL;
        }

        return '<li class="nav-item">
                    <a href="' . $menuLinks[0]['link-url'] . '" class="nav-link">
                        <i class="nav-icon ' . $menu['icon'] . '"></i> ' . $menu['title'] . '
                    </a>
                </li>' . PHP_EOL;
    }

    protected function navWithChildren($menu): string
    {
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

    protected function navLink($links): string
    {
        $linkHtml = '';

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
                $children .= $this->navWithChildren($item);
            }
        }

        return $children;
    }
}
