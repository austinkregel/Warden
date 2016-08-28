<?php

return [
    'route' => 'warden',
    'auth'  => [
        /*
         * Set this to your own custom middleware, just please know that it
         * should ensure that the user is logged in.
         */
        'middleware' => ['web', 'auth'],

        'middleware_api' => ['api'],

    ],
    'using' => [
        'csrf'          => true,
        'framework'     => 'bootstrap',
        'custom-routes' => null,
    ],
    'views' => [
        'base-layout' => 'spark::layouts.app',
    ],
    /*
     * Just to make sure that there are fewer things to edit,
     * by default we use the auth.model configuration from
     * the default location to ensure this will work oob
     */
    'models' => [
        'user' => [
            // For model events themselves, please reference the
            // Eloquent events from the laravel docs website.
            // Can be seen here: https://laravel.com/docs/master/eloquent#events
            'model'     => App\User::class,
            'relations' => [
                'roles' => [
                    'update' => function ($user) {
                        \Log::info('A users roles has been updated');
                    },
                    'new' => function ($user) {
                        \Log::info('A users role has been created');
                    },
                    'delete' => function ($user) {
                        \Log::info('A users role has been deleted/removed');
                    },
                ],
            ],
        ],

    ],
];
