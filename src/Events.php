<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Tags\HasTags;
use CronxCo\DataModel\HasSqid;
use Laravel\Scout\Searchable;
use LaracraftTech\LaravelUsefulAdditions\Traits\UsefulScopes;

class Events extends Model
{
    use HasTags;
    use HasSqid;
    use SoftDeletes;
    use Searchable;
    use UsefulScopes;


    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('datamodel.connection'));
        $this->setTable(config('datamodel.events_table'));

        parent::__construct($attributes);
    }

    /**
     * @var bool
     */
    public $timestamps = false;

    public $primaryKey = 'event_id';

    /**
     * @var array
     */
    protected $guarded = [];

    protected $with = ['target', 'actor'];

    protected function casts(): array
    {
        return [
            'event_value' => 'array',
            'actor_metadata' => 'array',
            'event_metadata' => 'array',
            'target_metadata' => 'array',
            'event_time' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }

    public function target()
    {
        return $this->belongsTo(Objects::class, 'target_uid', 'object_uid')->with('media');
    }

    public function actor()
    {
        return $this->belongsTo(Objects::class, 'actor_uid', 'object_uid')->with('media');
    }

    public function target_children()
    {
        return $this->hasMany(Events::class, 'actor_uid', 'target_uid')->with('target', 'actor');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray()
    {
        $tagArray = [];
        if (!empty($this->tags)) {
            foreach ($this->tags as $tag)
                $tagArray[] = $tag->slug;
        }
        return array_merge($this->toArray(), [
            "id" => (string) $this->event_id,
            "event_id" => (string) $this->event_id,
            "source_uid" => (string) $this->source_uid,
            "actor_uid" => (string) $this->actor_uid,
            "actor_title" => (string) $this->actor->object_title,
            "target_uid" => (string) $this->target_uid,
            "target_title" => (string) $this->target->object_title,
            "tag_list" => (array) $tagArray,
            "created_at" => $this->created_at->timestamp,
            "updated_at" => $this->updated_at->timestamp,
            "event_time" => $this->event_time->timestamp,
        ]);
    }

    /**
     * @param $event
     * @return $this
     */
    public function setStream($event)
    {
        $table = $this->getStream($event);
        $this->setTable($table);

        return $this;
    }

    /**
     * @param $event
     * @return \Illuminate\Config\Repository|int|mixed|string
     */
    public function getStream($event)
    {
        $dedicated_tables = config('datamodel.streams');

        if (empty($dedicated_tables)) {
            return config('datamodel.events_table');
        }

        foreach ($dedicated_tables as $table => $events) {
            if (array_search($event, $events) !== false) {
                return $table;
            }
        }

        return config('datamodel.table');
    }

    /**
     * Checks if event model needs a dedicated table
     * and creates it if it does not exist.
     *
     * @return bool
     */
    public function needsDedicatedStreamTableCreation()
    {
        return $this->getTable() !== config('datamodel.events_table')
            && !Schema::connection(config('datamodel.connection'))->hasTable($this->getTable());
    }

    /**
     * Override default Eloquent Builder newInstance method.
     *
     * @param array $attributes
     * @param bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $model = parent::newInstance($attributes, $exists);
        $model->setTable($this->getTable());

        return $model;
    }
}
