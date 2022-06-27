<?php

namespace RadiateCode\LaravelNavbar\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use RadiateCode\LaravelNavbar\Enums\Constant;

class MenuCacheClearCommand extends Command
{
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
        Cache::forget(Constant::CACHE_NAVS);

        Cache::forget(Constant::CACHE_HTML_RENDERED_NAVS);

        $this->info('laravel-navbar caches are cleared successfully');
    }
}