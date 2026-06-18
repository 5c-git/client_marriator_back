<?php

namespace Modules\Questionnaire\Providers;

use Illuminate\Support\ServiceProvider;

class QuestionnaireServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap module services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('questionnaire.php'),
        ], 'config');
    }

    /**
     * Register module services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php',
            'questionnaire'
        );
    }
}
