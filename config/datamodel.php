<?php

return [

    'connection' => env('DATA_MODEL_CONNECTION', config('database.default')),

    'table' => env('DATA_MODEL_TABLE', 'data_model'),

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
