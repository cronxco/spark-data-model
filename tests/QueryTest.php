<?php

namespace Tests;

use Illuminate\Database\Eloquent\Builder;
use CronxCo\DataModel\DataModelFacade as DataModel;

class QueryTest extends DataModelTestCase
{
    /** @test */
    public function it_returns_query_builder_when_using_query_method()
    {
        $events = DataModel()->query();
        $this->assertInstanceOf(Builder::class, $events);
    }

    /** @test */
    public function it_gets_all_events()
    {
        DataModel::withExceptions()->addMany('custom_event_1', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        DataModel::withExceptions()->add('custom_event_2', ['key' => 'value']);

        $events = DataModel()->get();
        $this->assertCount(4, $events);
    }

    /** @test */
    public function it_gets_all_events_for_specific_stream()
    {
        $this->addDedicatedTablesToConfig();

        DataModel::withExceptions()->addMany('regular_event', [
            ['key' => 'foo'],
            ['key' => 'bar'],
        ]);

        DataModel::withExceptions()->addMany('custom_event_1', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        DataModel::withExceptions()->addMany('event_foo', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
            ['key' => 'foobar'],
        ]);

        $events = DataModel()->stream('custom_event_table')->get();
        $this->assertCount(3, $events);
    }

    /** @test */
    public function it_gets_all_events_for_specific_event_action()
    {
        DataModel::withExceptions()->addMany('regular_event', [
            ['key' => 'foo'],
        ]);

        DataModel::withExceptions()->addMany('event_foo', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
            ['key' => 'foobar'],
        ]);

        DataModel::withExceptions()->addMany('event_bar', [
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $events = DataModel()->get('event_foo');
        $this->assertCount(4, $events);
    }

    /** @test */
    public function it_gets_all_events_for_specific_event_action_with_dedicated_stream()
    {
        $this->addDedicatedTablesToConfig();

        DataModel::withExceptions()->addMany('regular_event', [
            ['key' => 'foo'],
        ]);

        DataModel::withExceptions()->addMany('event_foo', [
            ['key' => 'foo'],
            ['key' => 'bar'],
            ['key' => 'baz'],
            ['key' => 'foobar'],
        ]);

        DataModel::withExceptions()->addMany('event_bar', [
            ['key' => 'bar'],
            ['key' => 'baz'],
        ]);

        $events = DataModel()->get('event_foo');
        $this->assertCount(4, $events);
    }
}
