<?php

return [

    'connections' => [
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'low',
            'retry_after' => 60,
            'after_commit' => true,
        ],
    ],

];
