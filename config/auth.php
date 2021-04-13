<?php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],
    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'email' => 'auth.emails.reset_password',
            'table' => 'passwords',
            'expire' => 60,
            'view' => 'auth.emails.passwords'
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ]
];
