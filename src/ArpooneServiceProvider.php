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
    public function boot(): void
    {
        // Check required environment variables
        $this->checkRequiredEnvironmentVariables();

        // Load migrations from the package
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if ($this->app->runningInConsole()) {
            // Publish migrations with a custom tag 'arpoone-migrations'
            $this->publishes([
                __DIR__.'/database/migrations' => database_path('migrations'),
            ], 'arpoone-migrations');

            // Publish configuration file with a custom tag 'arpoone-config'
            $this->publishes([
                __DIR__.'/config/arpoone.php' => config_path('arpoone.php'),
            ], 'arpoone-config');
        }

        // Load routes from package
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge the package configuration with the application's published config
        $this->mergeConfigFrom(
            __DIR__.'/config/arpoone.php', 'arpoone'
        );
    }

    /**
     * Check if the required environment variables are set
     *
     * @return void
     * @throws \Exception
     */
    protected function checkRequiredEnvironmentVariables(): void
    {
        if(!config('arpoone.multi_tenant', false)) {
            $required = [
                'arpoone.api_key' => 'ARPOONE_API_KEY',
                'arpoone.organization_id' => 'ARPOONE_ORGANIZATION_ID',
                'arpoone.sms_sender' => 'ARPOONE_SMS_SENDER',
                'arpoone.email_sender'=> 'ARPOONE_EMAIL_SENDER',
                'arpoone.email_sender_name'=> 'ARPOONE_EMAIL_SENDER_NAME',
            ];

            foreach ($required as $configKey => $envVar) {
                if (empty(config($configKey))) {
                    throw new \Exception("The environment variable $envVar is required, please add it to your .env file.");
                }
            }
        }
    }
}
