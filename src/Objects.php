<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\Schema;
use CronxCo\DataModel\Events;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Spatie\Tags\HasTags;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Laravel\Scout\Searchable;
use LaracraftTech\LaravelUsefulAdditions\Traits\UsefulScopes;

class Objects extends Model implements HasMedia
{
    use HasTags;
    use InteractsWithMedia;
    use SoftDeletes, CascadeSoftDeletes;
    use Searchable;
    use UsefulScopes;


    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('datamodel.connection'));
        $this->setTable(config('datamodel.objects_table'));

        parent::__construct($attributes);
    }

    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    public $primaryKey = 'object_uid';
    /**
     * @var string
     */
    protected $keyType = 'string';
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'object_metadata' => 'array',
            'object_time' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime'
        ];
    }

    protected $cascadeDeletes = ['events_as_target', 'events_as_actor'];

    /**
     * @return [type]
     */
    public function events_as_target()
    {
        return $this->hasMany(Events::class, 'target_uid', 'object_uid');
    }

    /**
     * @return [type]
     */
    public function events_as_actor()
    {
        return $this->hasMany(Events::class, 'actor_uid', 'object_uid');
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
            "id" => (string) $this->object_uid,
            "object_uid" => (string) $this->object_uid,
            "tag_list" => (array) $tagArray,
            "created_at" => $this->created_at->timestamp,
            "updated_at" => $this->updated_at->timestamp,
            "object_time" => $this->object_time->timestamp,
        ]);
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

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }
}
