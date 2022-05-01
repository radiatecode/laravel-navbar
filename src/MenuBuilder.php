<?php


namespace RadiateCode\LaravelNavbar;


use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use RadiateCode\LaravelNavbar\Presenter\MenuBarPresenter;

class MenuBuilder
{
    private $menus = [];

    private $presenter;

    private const MENU_RENDERED_CACHE_KEY = 'laravel-navbar-rendered';

    private const MENU_RENDERED_COUNT_CACHE_KEY = 'laravel-navbar-rendered-count';

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
        $menuCount = count($this->menus);

        $cacheMenus = $this->getCacheMenus($menuCount);

        if (config('navbar.cache-enable') && $cacheMenus !== null) {
            return $cacheMenus;
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

        $this->cacheMenus($menuCount,$html);

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

    protected function getCacheMenus(int $menuCount)
    {
        if ($menuCount !== Cache::get(self::MENU_RENDERED_COUNT_CACHE_KEY)) {
            Cache::forget(self::MENU_RENDERED_COUNT_CACHE_KEY);

            Cache::forget(self::MENU_RENDERED_CACHE_KEY);

            return null;
        }

        return Cache::get(self::MENU_RENDERED_CACHE_KEY);
    }

    protected function cacheMenus(int $menuCount, $menus)
    {
        $ttl = config('navbar.cache-time');

        $enable = config('navbar.cache-enable');

        if ($enable && ! Cache::has(self::MENU_RENDERED_COUNT_CACHE_KEY)) {
            Cache::put(self::MENU_RENDERED_COUNT_CACHE_KEY, $menuCount, $ttl);

            Cache::put(self::MENU_RENDERED_CACHE_KEY, $menus, $ttl);
        }
    }

    private function getPresenter(): MenuBarPresenter
    {
        return new $this->presenter();
    }
}
