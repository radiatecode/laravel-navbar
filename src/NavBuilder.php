<?php


namespace RadiateCode\LaravelNavbar;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use RadiateCode\LaravelNavbar\Enums\Constant;
use RadiateCode\LaravelNavbar\Presenter\MenuBarPresenter;

class NavBuilder
{
    private $menus = [];

    private $presenter;

    public function __construct()
    {
        $this->presenter = config('navbar.menu-presenter');
    }

    public static function instance(): NavBuilder
    {
        return new self();
    }

    /**
     * @throws Exception
     */
    public function build(): string
    {
        if (config('navbar.cache-enable') && $this->hasCache()) {
            return $this->getCachedHtmlNavs();
        }

        $presenter = $this->getPresenter();

        $html = $presenter->openNavTag();

        $html .= $presenter->openNavULTag();

        foreach ($this->menus as $key => $menu) {
            if($menu['type'] == 'header'){

                $html .= $presenter->header($menu['title']);

                continue;
            }

            $html .= $presenter->nav($menu);
        }

        $html .= $presenter->closeNavULTag();

        $html .= $presenter->closeNavTag();

        $this->cacheHtmlNavs($html);

        return $html;
    }

    /**
     * @param  array  $menus
     * @param  string|null  $key // key can contains . to indicate nested
     *
     * @return $this
     */
    public function injectMenus(array $menus, string $key = null): NavBuilder
    {
        if ($key) {
            Arr::set($this->menus, $key, $menus);

            return $this;
        }

        $this->menus = array_merge($this->menus, $menus);

        return $this;
    }

    public function menus(array $menus): NavBuilder
    {
        $this->menus = $menus;

        return $this;
    }

    protected function getCachedHtmlNavs()
    {
        return Cache::get(Constant::CACHE_HTML_RENDERED_NAVS);
    }

    protected function hasCache(): bool
    {
        return Cache::has(Constant::CACHE_HTML_RENDERED_NAVS);
    }

    protected function cacheHtmlNavs($menus)
    {
        $ttl = config('navbar.cache-time');

        $enable = config('navbar.cache-enable');

        if ($enable) {
            Cache::put(Constant::CACHE_HTML_RENDERED_NAVS, $menus, $ttl);
        }
    }

    private function getPresenter(): MenuBarPresenter
    {
        return new $this->presenter();
    }
}
