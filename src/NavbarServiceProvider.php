<?php

namespace RadiateCode\LaravelNavbar;

use Illuminate\Support\ServiceProvider;
use RadiateCode\LaravelNavbar\Console\NavCacheClearCommand;

class NavbarServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/navbar.php', 'navbar');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/navbar.php' => config_path('navbar.php'),
        ], 'navbar-config');
    }
}