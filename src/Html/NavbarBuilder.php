<?php


namespace RadiateCode\LaravelNavbar\Html;

use Illuminate\Support\Facades\View;
use RadiateCode\LaravelNavbar\Contracts\Presenter;

class NavbarBuilder
{
    private $navItems = [];

    public static function make(): NavbarBuilder
    {
        return new self();
    }

    public function navs(array $navItems): NavbarBuilder
    {
        $this->navItems = $navItems;

        return $this;
    }

    public function render(): string
    {
        return $this->getPresenter()->navbar();
    }

    /**
     * It is usefull if we need to higlight the active nav item by JS(Jquery)
     *
     * @return string
     */
    public function navActiveScript()
    {
        return View::make('navbar::active-scripts')->render();
    }

    protected function getPresenter(): Presenter
    {
        $presenter = config('navbar.nav-presenter');

        return new $presenter($this->navItems);
    }
}
