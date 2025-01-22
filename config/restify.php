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
    | This configuration defines the model used for authentication purposes.
    | You can modify the 'model' setting if you want to use a different
    | authentication model.
    |
    */
    'model' => 'App\Models\User',

    /*
    |--------------------------------------------------------------------------
    | Controller Namespace
    |--------------------------------------------------------------------------
    |
    | This setting defines the namespace of the controller responsible for
    | handling authentication routes. Make sure the specified controller
    | is in the correct namespace and directory.
    |
    */
    'controller_namespace' => 'Mhasnainjafri\RestApiKit\Http\Controllers\Auth',

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the OTP (One-Time Password) behavior, including length, max
    | tries, TTL (time to live), and type (e.g., integer or string).
    |
    */
    'otp' => [
        'length' => 6,            // Length of the OTP
        'max_tries' => 3,         // Maximum allowed attempts
        'ttl' => 10,              // Time-to-live for the OTP (in minutes)
        'type' => 'integer',      // Type of OTP, can be 'integer' or 'string'
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Specify which middleware should be applied to the authentication routes.
    | The 'api' middleware ensures API-based routes are secured and 'auth:sanctum'
    | applies Sanctum authentication to protect the routes.
    |
    */
    'middleware' => [
        'api',                      // API middleware
        'auth:sanctum',             // Sanctum authentication middleware
        // 'DispatchRestApiKitStartingEvent::class',  // Optionally enable event dispatching
        // 'AuthorizeRestApiKit::class',             // Optionally authorize API requests
    ],

    /*
    |--------------------------------------------------------------------------
    | Personal Token Provider
    |--------------------------------------------------------------------------
    |
    | This configuration specifies the package to use for generating personal
    | tokens for authentication. Supported options are "sanctum" or "passport".
    |
    */
    'provider' => 'passport',      // Can also be 'sanctum' if you're using Sanctum

    'login_with' => 'email', // Can be 'email' or 'phone_number' 
    //add custom registration fields
    'custom_registration_fields' => [
        // 'phone_number' => 'required|string|min:10|max:15|unique:users',
        // 'role' => 'required|string|in:user,admin', 
    ],
    /*
    |--------------------------------------------------------------------------
    | Frontend Application URLs
    |--------------------------------------------------------------------------
    |
    | Set the frontend app URL for things like password reset links and user
    | verification links. This URL is used to generate links that will direct
    | users to the frontend of the application.
    |
    */
    'frontend_app_url' => env('FRONTEND_APP_URL', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | Password Reset URL
    |--------------------------------------------------------------------------
    |
    | Define the URL to use for password reset links. It includes placeholders
    | for the token and email, which will be replaced when generating the link.
    |
    */
    'password_reset_url' => env('FRONTEND_APP_URL').'/password/reset?token={token}&email={email}',

    /*
    |--------------------------------------------------------------------------
    | User Verification URL
    |--------------------------------------------------------------------------
    |
    | Set the URL for user verification links. The placeholders will be replaced
    | by the user's ID and hashed email address.
    |
    */
    'user_verify_url' => env('FRONTEND_APP_URL').'/verify/{id}/{emailHash}',
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
