<?php

return [

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],

        'rider' => [
            'driver' => 'jwt',
            'provider' => 'riders',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
            'table' => 'users',
        ],

        'riders' => [
            'driver' => 'eloquent',
            'model' => App\Models\Driver::class,
            'table' => 'drivers',
        ],
    ],

    'passwords' => [
        'riders' => [
            'provider' => 'riders',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

];
