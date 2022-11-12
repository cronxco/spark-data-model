<?php

return [

    'connection' => env('DATA_MODEL_CONNECTION', config('database.default')),

    'events_table' => env('DATA_MODEL_EVENTS_TABLE', 'events'),
    'objects_table' => env('DATA_MODEL_OBJECTS_TABLE', 'objects'),

    'throw_exceptions' => false,

    /*
     * Here you can set dedicated tables for certain events to be stored in.
     */
    'streams' => [
        // 'custom_table' => [
        //     'custom_event_1',
        //     'custom_event_2',
        // ]
    ],
];
