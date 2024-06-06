<?php
namespace Controlink\LaravelArpoone;

use Illuminate\Support\ServiceProvider;

class ArpooneServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @throws \Exception
     */
    public function boot()
    {
        // Corrected method name
        $this->checkRequiredEnvironmentVariables();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish migrations if running in console
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }

        // Publish configuration file
        $this->publishes([
            __DIR__.'/../config/arpoone.php' => config_path('arpoone.php'),
        ], 'config');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/arpoone.php', 'arpoone'
        );
    }

    /**
     * Check if the required environment variables are set
     *
     * @return void
     * @throws \Exception
     */
    protected function checkRequiredEnvirontmentVariables()
    {
        $required = [
            'ARPOONE_API_KEY',
            'ARPOONE_ORGANIZATION_ID',
            'ARPOONE_SENDER',
        ];

        foreach($required as $env){
            if(!env($env)){
                throw new \Exception("The environment variable $env is required, please add it to your .env file.");
            }
        }
    }

}