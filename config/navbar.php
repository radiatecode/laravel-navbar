<?php

use RadiateCode\LaravelNavbar\Presenter\NavbarPresenter;

return [
    /**
     * Presenter for navbar style
     * 
     * [HTML presenter]
     */
    'nav-presenter' => NavbarPresenter::class,

    /**
     * It will set active to requested/current nav
     * 
     * [Note: if you want to set nav active by front-end (Js/Jquery) 
     * Or, if you cached your rendered nav, then you should disable it]
     */
    'enable-nav-active' => false
];