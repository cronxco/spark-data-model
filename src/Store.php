<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
     * @throws \Exception
     */
    public function add($event_array, $actor_array, $object_array, $source_uid = null)
    {

        try {
            
            // Create the object or, if the UID already exists, retrieve it

            $object = StoreObject::firstOrCreate([
                'object_uid' => isset($object_array->uid)?$object_array->uid:Hash::make($object_array->title),
                ], [
                'object_type' => $object_array->type,
                'object_title' => $object_array->title,
                'object_content' => isset($object_array->content)?$object_array->content:null,
                'object_metadata' => isset($object_array->metadata)?$object_array->metadata:null,
                'object_url' => isset($object_array->url)?$object_array->url:null,
                'object_image_path' => isset($object_array->image_path)?$object_array->image_path:null,
                'object_time' => isset($object_array->time)?$object_array->time:Carbon::now()->toDateTimeString(),
            ]);
            
            // Create or update the event
            
            $data = StoreEvent::updateOrCreate([
                'source_uid' => is_null($source_uid)?Str::uuid():$source_uid,
                'event_action' => $event_array->action,
                'event_service' => $event_array->service,
                ], [
                'event_payload' => isset($event_array->payload)?$event_array->payload:null,
                'event_metadata' => isset($event_array->metadata)?$event_array->metadata:null,
                'event_time' => isset($event_array->time)?$event_array->time:Carbon::now()->toDateTimeString(),
                'actor_id' => $actor_array->id,
                'actor_metadata' => isset($actor_array->metadata)?$actor_array->metadata:null,
            ]);

            // Check which table the event should be stored in

            $data->setStream($event_array->action);

            if ($data->needsDedicatedStreamTableCreation()) {
                $this->createStreamTable($data->getTable());
            }

            // Associate the object to the event

            $data->object()->associate($object);

            $data->save();

            return $data;

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
                $builder->string('source_uid')->index();
                $builder->string('actor_id')->index();
                $builder->longText('actor_metadata')->nullable();
                $builder->string('event_service')->index();
                $builder->string('event_action')->index();
                $builder->longText('event_payload');
                $builder->longText('event_metadata')->nullable();
                $builder->string('target_id')->index();
                $builder->longText('target_metadata')->nullable();
                $builder->timestamp('event_time')->index();
                $builder->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
                $builder->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->index();
                });
        });
    }
}
