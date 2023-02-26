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
    'cache' => [
        'enable' => true,
        'ttl' => now()->addDay() // cache duration
    ],
];