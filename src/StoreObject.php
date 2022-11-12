<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class StoreObject extends Model
{
        use HasTags;
    
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

    public $primaryKey = 'object_uid';

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'object_metadata' => 'array'
    ];

    /**
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public function events()
    {
        return $this->hasMany(StoreEvent::class);
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