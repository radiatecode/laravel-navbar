<?php

use RadiateCode\LaravelNavbar\Presenter\NavBarPresenter;

return [
    /**
     * Presenter for navbar style
     * 
     * [HTML presenter]
     */
    'menu-presenter' => NavBarPresenter::class,

    /**
     * Cache the render navbar
     */
    'cache-enable' => true,

    /**
     * Cache living duration
     */
    'cache-time' => now()->addDay(),
];