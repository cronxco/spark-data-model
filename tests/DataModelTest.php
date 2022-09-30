<?php

namespace Tests;

use CronxCo\DataModel\Store;
use CronxCo\DataModel\StoreEvent;
use CronxCo\DataModel\DataModelFacade as DataModel;

class DataModelTest extends DataModelTestCase
{
    /** @test */
    public function it_registers_testing_config()
    {
        $this->assertEquals('DataModel', config('DataModel.connection'));
        $this->assertEquals('data_model', config('DataModel.table'));
    }

    /** @test */
    public function it_migrates_default_tables_to_database()
    {
        $this->assertDatabaseMissing('data_model', []);
    }

    /** @test */
    public function it_registers_helper_function()
    {
        $this->assertInstanceOf(Store::class, DataModel());
    }

    /** @test */
    public function it_adds_event_to_default_events_table()
    {
        DataModel::withExceptions()->add('some_event', ['key' => 'value']);
        $this->assertDatabaseHas('data_model', [
            'event_action' => 'some_event',
            'event_payload' => json_encode(['key' => 'value']),
        ]);
    }

    /** @test */
    public function it_gets_table_name_for_event_in_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        $table = (new StoreEvent())->getStream('custom_event_1');
        $this->assertEquals('custom_event_table', $table);
    }

    /** @test */
    public function it_sets_table_property_on_store_event_model()
    {
        $this->addDedicatedTablesToConfig();

        $event = (new StoreEvent())->setStream('custom_event_1');
        $this->assertEquals('custom_event_table', $event->getTable());
    }

    /** @test */
    public function it_creates_custom_event_table()
    {
        $this->addDedicatedTablesToConfig();

        DataModel::withExceptions()->add('custom_event_1', ['key' => 'value']);
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('custom_event_table'));
    }

    /** @test */
    public function it_does_not_create_custom_table_if_it_already_exists()
    {
        DataModel::createStreamTable('custom_table');

        $event = new StoreEvent();
        $event->setTable('custom_table');

        $this->assertFalse($event->needsDedicatedStreamTableCreation());
    }

    /** @test */
    public function it_adds_events_to_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        DataModel::withExceptions()->add('custom_event_1', ['key' => 'value']);
        $this->assertDatabaseHas('custom_event_table', [
            'event_action' => 'custom_event_1',
            'event_payload' => json_encode(['key' => 'value']),
        ]);
    }

    /** @test */
    public function it_inserts_multiple_events_at_once()
    {
        DataModel::withExceptions()->addMany('some_event', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $this->assertDatabaseHas('data_model', [
            'event_action' => 'some_event',
            'event_payload' => json_encode(['key' => 'baz']),
        ]);
    }

    /** @test */
    public function it_inserts_multiple_events_at_once_to_dedicated_table()
    {
        $this->addDedicatedTablesToConfig();

        DataModel::withExceptions()->addMany('custom_event_1', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $this->assertDatabaseHas('custom_event_table', [
            'event_action' => 'custom_event_1',
            'event_payload' => json_encode(['key' => 'bar']),
        ]);
    }
}
