<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Store
{
    /**
     * @var bool
     */
    private $withExceptions;

    /**
     * Event Store class constructor.
     */
    public function __construct()
    {
        $this->withExceptions = config('datamodel.throw_exceptions');
    }

    /**
     * @param      $event_action
     * @param      $event_payload
     * @param null $target_id
     * @param null $before
     * @throws \Exception
     */
    public function add($event_action, $event_payload, $target_id = null, $before = null)
    {
        if ($before instanceof Model) {
            $before = array_only($before->attributesToArray(), array_keys($event_payload));
        }

        try {
            $event = new StoreEvent([
                'event_action' => $event_action,
                'event_payload' => $event_payload,
                'target_id' => $target_id,
            ]);

            if ($before) {
                $event->metadata = array_merge($event->metadata ?: [], ['before' => $before]);
            }

            $event->setStream($event_action);

            if ($event->needsDedicatedStreamTableCreation()) {
                $this->createStreamTable($event->getTable());
            }

            $event->save();
        } catch (\Exception $e) {
            if ($this->withExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Add multiple entries of the same event at once using a single query.
     * Disclaimer: this method does not fire any Eloquent model events.
     *
     * @param       $event_action
     * @param array $events
     * @throws \Exception
     */
    public function addMany($event_action, array $events)
    {
        try {
            $event = new StoreEvent();
            $event->setStream($event_action);

            if ($event->needsDedicatedStreamTableCreation()) {
                $this->createStreamTable($event->getTable());
            }

            $events = array_map(function ($e) use ($event_action) {
                return [
                    'event_action' => $event_action,
                    'event_payload' => json_encode($e),
                ];
            }, $events);

            $event->insert($events);
        } catch (\Exception $e) {
            if ($this->withExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Gets the StoreEvent model query Builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return (new StoreEvent())->newQuery();
    }

    /**
     * Gets all entries for a specific event.
     * Returns entries from default table if $event param is null.
     *
     * @param null $event
     * @return mixed
     */
    public function get($event = null)
    {
        $query = new StoreEvent();

        if ($event) {
            $query->setStream($event);
            $query = $query->where('event_action', $event);
        }

        return $query->get();
    }

    /**
     * Gets all event entries from a specific stream table.
     *
     * @param $stream
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function stream($stream)
    {
        $query = new StoreEvent();
        $query = $query->setTable($stream);

        return $query->newQuery();
    }

    /**
     * @return $this
     */
    public function withExceptions()
    {
        $this->withExceptions = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function withoutExceptions()
    {
        $this->withExceptions = false;

        return $this;
    }

    /**
     * @param $table
     */
    public function createStreamTable($table)
    {
        DB::connection(config('datamodel.connection'))->transaction(function () use ($table) {
            $schema = Schema::connection(config('datamodel.connection'));

            $schema->create($table, function (Blueprint $builder) {
                $builder->bigIncrements('event_id')->index();
                $builder->string('event_action')->index();
                $builder->unsignedInteger('target_id')->nullable()->index();
                $builder->longText('event_payload');
                $builder->longText('metadata')->nullable();
                $builder->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            });
        });
    }
}
