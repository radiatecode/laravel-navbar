<?php


namespace RadiateCode\LaravelNavbar\Html;

use Illuminate\Support\Facades\Cache;
use RadiateCode\LaravelNavbar\Contracts\Presenter;

class NavBuilder
{
    private $navItems = [];

    public function __construct(array $navItems)
    {
        $this->navItems = $navItems;
    }

    public static function make(array $navItems): NavBuilder
    {
        return new self($navItems);
    }

    public function navbar(): string
    {
        $enable = config('navbar.cache.enable');

        if ($enable) {
            return $this->cacheNavbar();
        }

        return $this->getPresenter()->navbar();
    }

    protected function cacheNavbar(bool $reset = false)
    {
        $ttl = config('navbar.cache.ttl');

        $key = 'navbar-' . auth()->id();

        if ($reset) {
            Cache::forget($key);
        }

        return Cache::remember($key, $ttl, function () {
            return $this->getPresenter()->navbar();
        });
    }

    protected function getPresenter(): Presenter
    {
        $presenter = config('navbar.menu-presenter');

        return new $presenter($this->navItems);
    }
}
