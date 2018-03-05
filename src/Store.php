<?php

namespace Maslauskas\EventStore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Store
{
    /**
     * @var bool
     */
    private $withExceptions = true;

    /**
     * @param      $event_type
     * @param      $payload
     * @param null $target_id
     * @param null $before
     * @throws \Exception
     */
    public function add($event_type, $payload, $target_id = null, $before = null)
    {
        if($before instanceof Model) {
            $before = array_only($before->attributesToArray(), array_keys($payload));
        }

        try {
            $event = new StoreEvent([
                'event_type' => $event_type,
                'payload' => $payload,
                'target_id' => $target_id,
            ]);

            if($before) {
                $event->metadata = array_merge($event->metadata ?: [], ['before' => $before]);
            }

            $event->setStream($event_type);

            if($event->needsDedicatedStreamTableCreation()) {
                $this->createStreamTable($event->getTable());
            }

            $event->save();
        } catch (\Exception $e) {
            if($this->withExceptions) throw $e;
        }
    }

    /**
     * Add multiple entries of the same event at once using a single query.
     * Disclaimer: this method does not fire any Eloquent model events.
     *
     * @param       $event_type
     * @param array $events
     * @throws \Exception
     */
    public function addMany($event_type, array $events)
    {
        try {
            $event = new StoreEvent();
            $event->setStream($event_type);

            if($event->needsDedicatedStreamTableCreation()) {
                $this->createStreamTable($event->getTable());
            }

            $events = array_map(function($e) use ($event_type) {
                return [
                    'event_type' => $event_type,
                    'payload' => json_encode($e),
                ];
            }, $events);

            $event->insert($events);
        } catch (\Exception $e) {
            if($this->withExceptions) throw $e;
        }
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
        DB::transaction(function() use ($table) {
            $schema = Schema::connection(config('eventstore.connection'));

            $schema->create($table, function(Blueprint $builder) {
                $builder->bigIncrements('event_id')->index();
                $builder->string('event_type')->index();
                $builder->unsignedInteger('target_id')->nullable()->index();
                $builder->longText('payload');
                $builder->longText('metadata')->nullable();
                $builder->timestamp('created_at')->default(DB::raw("CURRENT_TIMESTAMP"))->index();
            });
        });
    }
}