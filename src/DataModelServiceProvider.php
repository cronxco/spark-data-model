<?php

namespace CronxCo\DataModel;

use Illuminate\Support\ServiceProvider;

class DataModelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/DataModel.php' => config_path('DataModel.php'),
        ], 'DataModel');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/DataModel.php', 'DataModel');

        $this->registerMigrations();

        $this->app->singleton('DataModel', function () {
            return new Store;
        });
    }

    protected function registerMigrations()
    {
        return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'DataModel-migrations');
    }
}
