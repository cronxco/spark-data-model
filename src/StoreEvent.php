<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class StoreEvent extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('DataModel.connection'));
        $this->setTable(config('DataModel.table'));

        parent::__construct($attributes);
    }

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'payload' => 'array',
        'metadata' => 'array',
    ];

    /**
     * @var array
     */
    protected $dates = ['created_at'];

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
        $dedicated_tables = config('DataModel.streams');

        if (empty($dedicated_tables)) {
            return config('DataModel.table');
        }

        foreach ($dedicated_tables as $table => $events) {
            if (array_search($event, $events) !== false) {
                return $table;
            }
        }

        return config('DataModel.table');
    }

    /**
     * Checks if event model needs a dedicated table
     * and creates it if it does not exist.
     *
     * @return bool
     */
    public function needsDedicatedStreamTableCreation()
    {
        return $this->getTable() !== config('DataModel.table')
            && ! Schema::connection(config('DataModel.connection'))->hasTable($this->getTable());
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
