<?php

namespace RadiateCode\LaravelNavbar\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MenuCacheClearCommand extends Command
{
    private const MENU_CACHE_KEY = 'laravel-navbar';

    private const MENU_COUNT_CACHE_KEY = 'laravel-navbar-count';

    private const MENU_RENDERED_CACHE_KEY = 'laravel-navbar-rendered';

    private const MENU_RENDERED_COUNT_CACHE_KEY = 'laravel-navbar-rendered-count';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'navbar:cache-clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear laravel navbar caches';


    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Cache::forget(self::MENU_COUNT_CACHE_KEY);

        Cache::forget(self::MENU_RENDERED_COUNT_CACHE_KEY);

        Cache::forget(self::MENU_CACHE_KEY);

        Cache::forget(self::MENU_RENDERED_CACHE_KEY);

        $this->info('laravel-navbar caches are cleared successfully');
    }
}