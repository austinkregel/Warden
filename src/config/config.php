<?php

return [
    'route' => 'warden',
    'auth' => [
        /*
         * Set this to your own custom middleware, just please know that it
         * should ensure that the user is logged in.
         */
        'middleware' => Kregel\Warden\Http\Middleware\Authentication::class,
        'middleware_name' => 'custom-auth',
        /*
         * Name of a login route, it's recommended to have it named, but incase
         * that doesn't work we have the 'fail_over_route' config to choose.
         */
        'route' => 'login',
        /*
         * If the desired route does not exist then use the one below instead
         * If you plan to use this with Spark, edi the fail_over_route to
         * /login  instead of /auth/login
         */
        'fail_over_route' => '/auth/login',
    ],
    /*
     * Actual application configuration
     */
    'using' => [
        'fontawesome' => true,
        'csrf' => true,
    ],
    'views' => [
        'base-layout' => 'spark::layouts.app',
    ],
    /**
     * Just to make sure that there are fewer things to edit,
     * by default we use the auth.model configuration from
     * the default location to ensure this will work oob
     */
    'models' => [
        'user' => [
            // For model events themselves, please reference the
            // Eloquent events from the laravel docs website.
            // Can be seen here: https://laravel.com/docs/5.1/eloquent#events
            'model' => App\Models\User::class,
            'relations' => [
                'roles' => [
                    'update' => function($user){
                        \Log::info('A users roles has been updated');
                    },
                    'new' => function ($user){
                        \Log::info('A users role has been created');
                    },
                    'delete' => function ($user){
                        \Log::info('A users role has been deleted/removed');
                    }
                ]
            ]
        ],

    ],
];
