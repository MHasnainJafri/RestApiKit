<?php

namespace Mhasnainjafri\RestApiKit;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mhasnainjafri\RestApiKit\Commands\ClearCacheCommand;
use Mhasnainjafri\RestApiKit\Commands\CreatePolicyCommand;
use Mhasnainjafri\RestApiKit\Commands\GeneratePostmanCollection;
use Mhasnainjafri\RestApiKit\Commands\MakeRepository;
use Mhasnainjafri\RestApiKit\Commands\SetupAuthCommand;
use Mhasnainjafri\RestApiKit\Helpers\RouteRegistrar;
use Mhasnainjafri\RestApiKit\Http\Controllers\Auth\AuthController;
use Mhasnainjafri\RestApiKit\Repositories\BaseRepository;

class RestApiKitServiceProvider extends ServiceProvider
{
    
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        BaseRepository::boot();

        $this->defineGate();
        $this->mergeConfigFrom(
            __DIR__.'/../config/restify.php', // Path to your package's config file
            'restify' // The name under which the config will be accessed
        );
       

        Route::macro('restifyAuth', function ($actions = ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail', 'sendOtp', 'changePassword', 'verifyOtp']) {
            // Assuming that RouteRegistrar::registerAuthRoutes is a method that registers routes
            RouteRegistrar::registerAuthRoutesfunction($actions);
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
                __DIR__.'/../config/restify.php' => config_path('restify.php'),
            ], 'config');
            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/restapikit'),
            ], 'restapikit-stubs');

            // Publishing the controller .
            $this->publishes([
                __DIR__.'/Http/Controllers/Auth' => base_path('app/Http/Controllers/RestApi/Auth'),
            ], 'restify-AuthControllers');
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
                MakeRepository::class,
                GeneratePostmanCollection::class,
            ]);
        }
        

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
