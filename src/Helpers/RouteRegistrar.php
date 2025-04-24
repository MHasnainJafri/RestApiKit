<?php

namespace Mhasnainjafri\RestApiKit\Helpers;

use Illuminate\Support\Facades\Route;

class RouteRegistrar
{
    public static function registerAuthRoutesfunction(array $actions = ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail', 'sendOtp', 'changePassword', 'verifyOtp'])
    {
        $routes = [
            'login' => ['POST', 'RestApiKit/login', 'login'],
            'register' => ['POST', 'RestApiKit/register', 'register'],
            'forgotPassword' => ['POST', 'RestApiKit/forgotPassword', 'forgotPassword'],
            'resetPassword' => ['POST', 'RestApiKit/resetPassword', 'resetPassword'],
            'verifyEmail' => ['POST', 'RestApiKit/verify/{id}/{emailHash}', 'verifyEmail'],
            'sendOtp' => ['POST', 'RestApiKit/sendOtp', 'sendOtp'],
            'verifyOtp' => ['POST', 'RestApiKit/verifyOtp', 'verifyOtp'],
            'changePassword' => ['POST', 'RestApiKit/changePassword', 'changePassword'],
        ];
        foreach ($actions as $action) {
            if (isset($routes[$action])) {
                // dd('\\' . config('restify.auth.controller_namespace') . '\\AuthController::class'  );
                $controller = config('restify.auth.controller_namespace') . '\\AuthController';

                if (!class_exists($controller)) {
                    throw new \Exception("Class {$controller} does not exist.");
                }

                Route::match(
                    [$routes[$action][0]],
                    config('restify.api_prefix').'/'.$routes[$action][1],
                    [$controller, $routes[$action][2]] // Pass class name string, not app($controller)
                );
            }
        }
    }
}
