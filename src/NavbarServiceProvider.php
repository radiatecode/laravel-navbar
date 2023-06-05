<?php

namespace RadiateCode\LaravelNavbar;

use Illuminate\Support\ServiceProvider;
use RadiateCode\LaravelNavbar\Html\NavbarBuilder;

class NavbarServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/navbar.php', 'navbar');

        $this->app->bind('nav.html.builder', function () {
            return NavbarBuilder::make();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/navbar.php' => config_path('navbar.php'),
        ], 'navbar-config');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'navbar');
    }
}
