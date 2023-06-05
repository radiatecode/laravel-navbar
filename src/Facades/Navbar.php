<?php

namespace RadiateCode\LaravelNavbar\Facades;

use Illuminate\Support\Facades\Facade;
use RadiateCode\LaravelNavbar\Html\NavbarBuilder;

/**
 * @method static NavbarBuilder navs(array $navItems)
 * @method static string  render()
 * @method static string  navActiveScript()
 * 
 * @see NavbarBuilder
 */
class Navbar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'nav.html.builder';
    }
}
