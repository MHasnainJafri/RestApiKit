<?php

// use Binaryk\LaravelRestApiKit\Http\Middleware\AuthorizeRestApiKit;
// use Binaryk\LaravelRestApiKit\Http\Middleware\DispatchRestApiKitStartingEvent;
// use Binaryk\LaravelRestApiKit\Repositories\ActionLogRepository;

return [
    'auth' => [
        /*
        |--------------------------------------------------------------------------
        | Table containing authenticatable resource
        |--------------------------------------------------------------------------
        |
        | This configuration contain the name of the table used for the authentication.
        |
        */

        'table' => 'users',

        /*
        |--------------------------------------------------------------------------
        |
        |--------------------------------------------------------------------------
        |
        | Next you may configure the package you're using for the personal tokens generation,
        | this will be used for the verification of the authenticatable model and provide the
        | authorizable functionality
        |
        | Supported: "sanctum"
        */

        'provider' => 'sanctum',

        /*
        |--------------------------------------------------------------------------
        | Auth frontend app url
        |--------------------------------------------------------------------------
        |
        |URL used for reset password URL generating.
        |
        |
        */

        'frontend_app_url' => env('FRONTEND_APP_URL', env('APP_URL')),

        'password_reset_url' => env('FRONTEND_APP_URL').'/password/reset?token={token}&email={email}',

        'user_verify_url' => env('FRONTEND_APP_URL').'/verify/{id}/{emailHash}',

        'user_model' => "\App\Models\User",
    ],

    'APP_DEBUG' => true,
    /*
    |--------------------------------------------------------------------------
    | RestApiKitJS
    |--------------------------------------------------------------------------
    |
    | This configuration is used for supporting the RestApiKitJS
    |
    */
    'RestApiKitjs' => [
        /*
        | Token to authorize the setup endpoint.
        */
        'token' => env('RestApiKitJS_TOKEN', 'testing'),

        /*
        | The API base url.
        */
        'api_url' => env('API_URL', env('APP_URL')),
    ],

    /*
    |--------------------------------------------------------------------------
    | RestApiKit Base Route
    |--------------------------------------------------------------------------
    |
    | This configuration is used as a prefix path where RestApiKit will be accessible from.
    | Feel free to change this path to anything you like.
    |
    */

    'base' => '/api/RestApiKit',

    /*
    |--------------------------------------------------------------------------
    | RestApiKit Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every RestApiKit route, giving you the
    | chance to add your own middleware to this stack or override any of
    | the existing middleware. Or, you can just stick with this stack.
    |
    */

    'middleware' => [
        'api',
        'auth:sanctum',
        // DispatchRestApiKitStartingEvent::class,
        // AuthorizeRestApiKit::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | RestApiKit Logs
    |--------------------------------------------------------------------------
    */
    'logs' => [
        /*
        | Repository used to list logs.
        */
        // 'repository' => ActionLogRepository::class,

        /**
         | Inform RestApiKit to log or not action logs.
         */
        'enable' => env('RestApiKit_ENABLE_LOGS', true),

        /**
        | Inform RestApiKit to log model changes from any source, or just RestApiKit. Set to `false` to log just RestApiKit logs.
         */
        'all' => env('RestApiKit_WRITE_ALL_LOGS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | RestApiKit Search
    |--------------------------------------------------------------------------
    */
    'search' => [
        /*
        | Specify either the search should be case-sensitive or not.
        */
        'case_sensitive' => true,
    ],

    'repositories' => [

        /*
        | Specify either to serialize index meta (policy) information or not. For performance reasons we recommend disabling it.
        */
        'serialize_index_meta' => false,

        /*
        | Specify either to serialize show meta (policy) information or not.
        */
        'serialize_show_meta' => true,
    ],

    'cache' => [
        /*
        | Specify the cache configuration for the resources policies.
        | When enabled, methods from the policy will be cached for the active user.
        */
        'default_ttl' => 60,
        'enabled' => true,
        'policies' => [
            'enabled' => false,

            'ttl' => 5 * 60, // seconds
        ],
    ],
    'forget' => [
        'otp_size' => 6,
        'max_otp_tries' => 3,
    ],

    /*
    | Specify if RestApiKit can call OpenAI for solution generation.
    |
    | By default this feature is enabled, but you still have to extend the Exception handler with the RestApiKit one and set the API key.
     */
    'ai_solutions' => true,

    'file_upload_disk' => 'local',
    'logger' => true,
];
