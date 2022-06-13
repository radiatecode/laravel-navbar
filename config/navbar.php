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

    /**
     * Permission resolver used when any menu is restricted by permissions
     *
     * [note: it will check is the logged in user allowed to see the menu]
     */
    'permissions-resolver' => PermissionsResolver::class
];