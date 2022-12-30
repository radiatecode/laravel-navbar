<?php

use RadiateCode\LaravelNavbar\Presenter\NavBarPresenter;

return [
    /**
     * Controllers path to generate navs
     */
    'controllers-path' => app_path('Http/Controllers'),
    
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