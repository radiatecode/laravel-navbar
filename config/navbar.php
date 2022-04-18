<?php

use RadiateCode\LaravelNavbar\Presenter\MenuBarPresenter;

return [
    /**
     * HTML presenter for menu/navbar
     */
    'menu-presenter' => MenuBarPresenter::class,

    /**
     * Cache living duration
     */
    'cache-time' => now()->addDay(),
];