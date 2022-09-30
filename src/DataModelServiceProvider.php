<?php

namespace CronxCo\DataModel;

use Illuminate\Support\ServiceProvider;

class DataModelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/datamodel.php' => config_path('datamodel.php'),
        ], 'datamodel');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/datamodel.php', 'datamodel');

        $this->registerMigrations();

        $this->app->singleton('datamodel', function () {
            return new Store;
        });
    }

    protected function registerMigrations()
    {
        return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'datamodel-migrations');
    }
}
