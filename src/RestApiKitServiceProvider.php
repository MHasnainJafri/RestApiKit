<?php

namespace Mhasnainjafri\RestApiKit;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mhasnainjafri\RestApiKit\Commands\ClearCacheCommand;
use Mhasnainjafri\RestApiKit\Commands\CreatePolicyCommand;
use Mhasnainjafri\RestApiKit\Commands\SetupAuthCommand;
use Mhasnainjafri\RestApiKit\Helpers\RouteRegistrar;
use Mhasnainjafri\RestApiKit\Http\Controllers\AuthController;

class RestApiKitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->defineGate();

        Route::macro('restifyAuth', function (array $actions = ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail', 'sendOtp', 'changePassword', 'verifyOtp']) {
            $routes = [
                'login' => ['POST', 'login', 'login'],
                'register' => ['POST', 'register', 'register'],
                'forgotPassword' => ['POST', 'restify/forgotPassword', 'forgotPassword'],
                'resetPassword' => ['POST', 'restify/resetPassword', 'resetPassword'],
                'verifyEmail' => ['POST', 'restify/verify/{id}/{emailHash}', 'verifyEmail'],
                'sendOtp' => ['POST', 'restify/verify/{id}/{emailHash}', 'sendOtp'],
                'verifyOtp' => ['POST', 'restify/verify/{id}/{emailHash}', 'verifyOtp'],
                'changePassword' => ['POST', 'restify/verify/{id}/{emailHash}', 'changePassword'],
            ];

            foreach ($actions as $action) {
                if (isset($routes[$action])) {
                    Route::match([$routes[$action][0]], $routes[$action][1], [AuthController::class, $routes[$action][2]]);
                }
            }
        });

        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'restapikit');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'restapikit');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('restapikit.php'),
            ], 'config');
            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/restapikit'),
            ], 'restapikit-stubs');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/restapikit'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/restapikit'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/restapikit'),
            ], 'lang');*/

            // Registering package commands.
            $this->commands([
                SetupAuthCommand::class,
                ClearCacheCommand::class,
                CreatePolicyCommand::class,

            ]);
        }
        RouteRegistrar::registerAuthRoutes();

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->publishes([
            __DIR__.'/../config/restify.php' => config_path('restify.php'),
        ], 'config');
        $this->app->singleton(ActionMacroManager::class, function () {
            return new ActionMacroManager;
        });

        // Register the main class to use with the facade
        $this->app->singleton('restapikit', function () {
            return new RestApiKit;
        });
    }

    /**
     * Define the global gate for RestApiKit.
     */
    protected function defineGate()
    {
        Gate::define('viewRestApiKit', function ($user = null) {
            // Example logic: allow all authenticated users
            return $user ? true : false;

            // Example for unauthenticated access
            // return true; // Uncomment to allow public access
        });
    }
}
