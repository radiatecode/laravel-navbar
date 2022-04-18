<?php


namespace RadiateCode\LaravelNavbar;


use Exception;
use Illuminate\Support\Arr;
use RadiateCode\LaravelNavbar\Presenter\MenuBarPresenter;

class MenuBuilder
{
    private $menus = [];

    private $presenter;

    public function __construct()
    {
        $this->presenter = config('navbar.menu-presenter');
    }

    public static function instance(): MenuBuilder
    {
        return new self();
    }

    /**
     * @throws Exception
     */
    public function build(): string
    {
        $presenter = $this->getPresenter();

        $html = $presenter->openNavTag();

        $html .= $presenter->openNavULTag();

        foreach ($this->menus as $key => $menu) {
            $html .= $presenter->nav($menu);
        }

        $html .= $presenter->closeNavULTag();

        $html .= $presenter->closeNavTag();

        return $html;
    }

    /**
     * @param  array  $menus
     * @param  string|null  $key // key can contains . to indicate nested
     *
     * @return $this
     */
    public function injectMenus(array $menus, string $key = null): MenuBuilder
    {
        if ($key) {
            Arr::set($this->menus, $key, $menus);

            return $this;
        }

        $this->menus = array_merge($this->menus, $menus);

        return $this;
    }

    public function menus(array $menus): MenuBuilder
    {
        $this->menus = $menus;

        return $this;
    }

    private function getPresenter(): MenuBarPresenter
    {
        return new $this->presenter();
    }
}
