<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use File;

class WordPressServiceProvider extends ServiceProvider
{
    protected $bootstrapFilePath = '../wordpress/wp-load.php';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load assets
        // wp_enqueue_style('app', '/app/public/app.css');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Load wordpress bootstrap file
        if(File::exists($this->bootstrapFilePath)) {
            require_once $this->bootstrapFilePath;
        } else {
            throw new \RuntimeException('WordPress Bootstrap file not found!');
        }
    }
}
