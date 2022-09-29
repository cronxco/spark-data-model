<?php

namespace CronxCo\DataModel;

use Illuminate\Support\Facades\Facade;

class DataModelFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'DataModel';
    }
}
