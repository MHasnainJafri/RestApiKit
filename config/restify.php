<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Configuration
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'model' => 'App\Models\User',

        'controller_namespace' => 'Mhasnainjafri\RestApiKit\Http\Controllers\Auth',

        'otp' => [
            'length' => 6,             // OTP length
            'max_tries' => 3,          // Maximum allowed attempts
            'ttl' => 10,               // Time-to-live for OTP (in minutes)
            'type' => 'integer',       // Type of OTP: 'integer' or 'string'
        ],

        'middleware' => [
            'api',
            'auth:sanctum',
            // DispatchRestApiKitStartingEvent::class,
            // AuthorizeRestApiKit::class,
        ],

        'provider' => 'passport',      // 'passport' or 'sanctum'

        'login_with' => 'email',       // 'email' or 'phone_number'

        'custom_registration_fields' => [
            // 'phone_number' => 'required|string|min:10|max:15|unique:users',
            // 'role' => 'required|string|in:user,admin',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Routing Configuration
    |--------------------------------------------------------------------------
    */
    'api_base' => '/api/RestApiKit',
    'api_prefix' => '/',
    'middleware' => ['api'],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    */
    'search' => [
        'case_sensitive' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'default_ttl' => 60,
        'enabled' => true,
        'policies' => [
            'enabled' => false,
            'ttl' => 5 * 60, // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Forgot Password / OTP Reset Configuration
    |--------------------------------------------------------------------------
    */
    'forget' => [
        'otp_size' => 6,
        'max_otp_tries' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'file_upload_disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | App Debug (optional, can be moved to .env usually)
    |--------------------------------------------------------------------------
    */
    'APP_DEBUG' => true,
];
