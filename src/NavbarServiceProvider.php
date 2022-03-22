<?php


use Illuminate\Support\ServiceProvider;

class NavbarServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/navbar.php', 'navbar');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__
            .'/../config/navbar.php' => config_path('navbar.php'),
        ], 'navbar-config');
    }
}