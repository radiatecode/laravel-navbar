<?php

use RadiateCode\LaravelNavbar\Presenter\MenuBarPresenter;

return [
    /**
     * Presenter for navbar style
     * 
     * [HTML presenter]
     */
    'menu-presenter' => MenuBarPresenter::class,

    /**
     * Cache the render navbar
     */
    'cache-enable' => true,

    /**
     * Cache living duration
     */
    'cache-time' => now()->addDay(),
];