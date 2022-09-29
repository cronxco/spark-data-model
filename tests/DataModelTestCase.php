<?php

namespace Tests;

use Orchestra\Testbench\TestCase;
use CronxCo\DataModel\DataModelFacade;
use CronxCo\DataModel\DataModelServiceProvider;

abstract class DataModelTestCase extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [DataModelServiceProvider::class];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'DataModel' => DataModelFacade::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'DataModel');
        $app['config']->set('DataModel.connection', 'DataModel');

        $app['config']->set('database.connections.DataModel', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    public function addDedicatedTablesToConfig()
    {
        $this->app['config']->set('DataModel.streams', [
            'custom_event_table' => [
                'custom_event_1',
                'custom_event_2',
            ],
            'other_event_stream' => [
                'event_foo',
                'event_bar',
            ],
        ]);
    }
}
