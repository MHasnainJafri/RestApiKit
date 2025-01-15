<?php

namespace Mhasnainjafri\RestApiKit\Helpers;

use Illuminate\Support\Facades\Route;
use Mhasnainjafri\RestApiKit\Http\Controllers\AuthController;

class RouteRegistrar
{
    public static function registerAuthRoutes(array $actions = ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail'])
    {
        Route::macro('RestApiKitAuth', function (array $actions = []) {
            $availableActions = [
                'register' => 'register',
                'login' => 'login',
                'forgotPassword' => 'forgotPassword',
                'resetPassword' => 'resetPassword',
                'verifyEmail' => 'verifyEmail',
            ];

            foreach ($actions ?: array_keys($availableActions) as $action) {
                if (isset($availableActions[$action])) {
                    Route::post("/api/{$action}", [AuthController::class, $availableActions[$action]]);
                }
            }
        });
    }
}
