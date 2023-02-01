<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DataModel
{
    /**
     * @var bool
     */
    private $withExceptions;

    /**
     * Events DataModel class constructor.
     */
    public function __construct()
    {
        $this->withExceptions = config('datamodel.throw_exceptions');
    }

    /**
     * 
     * @param      $event_action
     * @param      $event_value
     * @param null $target_id
     * @throws \Exception
     */
    public function add($event_object, $actor_object, $target_object, $source_uid = null, $tags = null)
    {

        try {
            
            // Create the actor or, if the UID already exists, retrieve it

            $actor = Objects::firstOrCreate([
                'object_uid' => isset($actor_object->uid)?$actor_object->uid:md5($actor_object->type."-".$actor_object->title),
                ], [
                'object_concept' => $actor_object->concept,
                'object_type' => $actor_object->type,
                'object_title' => $actor_object->title,
                'object_content' => isset($actor_object->content)?$actor_object->content:null,
                'object_metadata' => isset($actor_object->metadata)?$actor_object->metadata:null,
                'object_url' => isset($actor_object->url)?$actor_object->url:null,
                'object_image_url' => isset($actor_object->image_url)?$actor_object->image_url:null,
                'object_image_cache' => isset($actor_object->image_cache)?$actor_object->image_cache:null,
                'object_time' => isset($actor_object->time)?$actor_object->time:Carbon::now()->toDateTimeString(),
            ]);

            // Create the target or, if the UID already exists, retrieve it

            $target = Objects::firstOrCreate([
                'object_uid' => isset($target_object->uid)?$target_object->uid:md5($target_object->type."-".$target_object->title),
                ], [
                'object_type' => $target_object->type,
                'object_title' => $target_object->title,
                'object_content' => isset($target_object->content)?$target_object->content:null,
                'object_metadata' => isset($target_object->metadata)?$target_object->metadata:null,
                'object_url' => isset($target_object->url)?$target_object->url:null,
                'object_image_url' => isset($target_object->image_url)?$target_object->image_url:null,
                'object_image_cache' => isset($target_object->image_cache)?$target_object->image_cache:null,
                'object_time' => isset($target_object->time)?$target_object->time:Carbon::now()->toDateTimeString(),
            ]);
            
            // Create or return the event
            
            $data = Events::firstOrCreate([
                'source_uid' => is_null($source_uid)?Str::uuid():$source_uid,
                'event_action' => $event_object->action,
                'event_service' => $event_object->service,
                ], [
                'event_domain' => $event_object->domain,
                'event_value' => isset($event_object->value)?$event_object->value:null,
                'event_value_unit' => isset($event_object->value_unit)?$event_object->value_unit:null,
                'event_metadata' => isset($event_object->metadata)?$event_object->metadata:null,
                'event_time' => isset($event_object->time)?$event_object->time:Carbon::now()->toDateTimeString(),
                'actor_metadata' => isset($actor_object->metadata)?$actor_object->metadata:null,
            ])->actor()->associate($actor)->target()->associate($target);

            // Check which table the event should be stored in

            $data->setStream($event_object->action);

            if ($data->needsDedicatedStreamTableCreation()) {
                $this->createStreamTable($data->getTable());
            }

            $data->save();

            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    $target->attachTag($tag['name'], $tag['category']);
                    $data->attachTag($tag['name'], $tag['category']);
                }
            }
            
            return $data;

        } catch (\Exception $e) {
            if ($this->withExceptions) {
                throw $e;
            }
        }
    }

    /**
     * @param      $event_action
     * @param      $event_value
     * @param null $target_id
     * @throws \Exception
     */
    public function update($event_object, $actor_object, $target_object, $source_uid = null, $tags = null)
    {

        try {
            
            // Create the actor or, if the UID already exists, retrieve it

            $actor = Objects::firstOrCreate([
                'object_uid' => isset($actor_object->uid)?$actor_object->uid:md5($actor_object->type."-".$actor_object->title),
                ], [
                'object_concept' => $actor_object->concept,
                'object_type' => $actor_object->type,
                'object_title' => $actor_object->title,
                'object_content' => isset($actor_object->content)?$actor_object->content:null,
                'object_metadata' => isset($actor_object->metadata)?$actor_object->metadata:null,
                'object_url' => isset($actor_object->url)?$actor_object->url:null,
                'object_image_url' => isset($actor_object->image_url)?$actor_object->image_url:null,
                'object_image_cache' => isset($actor_object->image_cache)?$actor_object->image_cache:null,
                'object_time' => isset($actor_object->time)?$actor_object->time:Carbon::now()->toDateTimeString(),
            ]);

            // Create the target or, if the UID already exists, retrieve it

            $target = Objects::firstOrCreate([
                'object_uid' => isset($target_object->uid)?$target_object->uid:md5($target_object->type."-".$target_object->title),
                ], [
                'object_type' => $target_object->type,
                'object_title' => $target_object->title,
                'object_content' => isset($target_object->content)?$target_object->content:null,
                'object_metadata' => isset($target_object->metadata)?$target_object->metadata:null,
                'object_url' => isset($target_object->url)?$target_object->url:null,
                'object_image_url' => isset($target_object->image_url)?$target_object->image_url:null,
                'object_image_cache' => isset($target_object->image_cache)?$target_object->image_cache:null,
                'object_time' => isset($target_object->time)?$target_object->time:Carbon::now()->toDateTimeString(),
            ]);
            
            // Create or update the event
            
            $data = Events::updateOrCreate([
                'source_uid' => is_null($source_uid)?Str::uuid():$source_uid,
                'event_action' => $event_object->action,
                'event_service' => $event_object->service,
                ], [
                'event_domain' => $event_object->domain,
                'event_value' => isset($event_object->value)?$event_object->value:null,
                'event_value_unit' => isset($event_object->value_unit)?$event_object->value_unit:null,
                'event_metadata' => isset($event_object->metadata)?$event_object->metadata:null,
                'event_time' => isset($event_object->time)?$event_object->time:Carbon::now()->toDateTimeString(),
                'actor_metadata' => isset($actor_object->metadata)?$actor_object->metadata:null,
            ])->actor()->associate($actor)->target()->associate($target);

            // Check which table the event should be stored in

            $data->setStream($event_object->action);

            if ($data->needsDedicatedStreamTableCreation()) {
                $this->createStreamTable($data->getTable());
            }

            $data->save();

            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    $target->attachTag($tag['name'], $tag['category']);
                    $data->attachTag($tag['name'], $tag['category']);
                }
            }
            
            return $data;

        } catch (\Exception $e) {
            if ($this->withExceptions) {
                throw $e;
            }
        }
    }

    /**
     * @param mixed $object_object
     * 
     * @return [type]
     */
    public function addObject($object_object)
    {

        try {
            
            // Create the target or, if the UID already exists, retrieve it

            $object = Objects::firstOrCreate([
                'object_uid' => isset($object_object->uid)?$object_object->uid:md5($object_object->type."-".$object_object->title),
                ], [
                'object_concept' => $object_object->concept,
                'object_type' => $object_object->type,
                'object_title' => $object_object->title,
                'object_content' => isset($object_object->content)?$object_object->content:null,
                'object_metadata' => isset($object_object->metadata)?$object_object->metadata:null,
                'object_url' => isset($object_object->url)?$object_object->url:null,
                'object_image_url' => isset($object_object->image_url)?$object_object->image_url:null,
                'object_image_cache' => isset($object_object->image_cache)?$object_object->image_cache:null,
                'object_time' => isset($object_object->time)?$object_object->time:Carbon::now()->toDateTimeString(),
            ]);

            $object->save();

            return $object;

        } catch (\Exception $e) {
            if ($this->withExceptions) {
                throw $e;
            }
        }
    }

    /**
     * @param mixed $object_object
     * 
     * @return [type]
     */
    public function updateObject($object_object)
    {

        try {
            
            // Create the target or, if the UID already exists, update it

            $object = Objects::updateOrCreate([
                'object_uid' => isset($object_object->uid)?$object_object->uid:md5($object_object->type."-".$object_object->title),
                ], [
                'object_concept' => $object_object->concept,
                'object_type' => $object_object->type,
                'object_title' => $object_object->title,
                'object_content' => isset($object_object->content)?$object_object->content:null,
                'object_metadata' => isset($object_object->metadata)?$object_object->metadata:null,
                'object_url' => isset($object_object->url)?$object_object->url:null,
                'object_image_url' => isset($object_object->image_url)?$object_object->image_url:null,
                'object_image_cache' => isset($object_object->image_cache)?$object_object->image_cache:null,
                'object_time' => isset($object_object->time)?$object_object->time:Carbon::now()->toDateTimeString(),
            ]);

            $object->save();

            return $object;

        } catch (\Exception $e) {
            if ($this->withExceptions) {
                throw $e;
            }
        }
    }

    /**
     * Gets the Events model query Builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return (new Events())->newQuery();
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
        $query = new Events();

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
        $query = new Events();
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
                $builder->string('event_domain')->index();
                $builder->string('event_service')->index();
                $builder->string('event_action')->index();
                $builder->longText('event_value');
                $builder->longText('event_value_unit');
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
