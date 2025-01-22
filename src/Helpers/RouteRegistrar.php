<?php

namespace Mhasnainjafri\RestApiKit\Helpers;

use Illuminate\Support\Facades\Route;

class RouteRegistrar
{
    public static function registerAuthRoutesfunction(array $actions = ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail', 'sendOtp', 'changePassword', 'verifyOtp'])
    {
        $routes = [
            'login' => ['POST', 'restify/login', 'login'],
            'register' => ['POST', 'restify/register', 'register'],
            'forgotPassword' => ['POST', 'restify/forgotPassword', 'forgotPassword'],
            'resetPassword' => ['POST', 'restify/resetPassword', 'resetPassword'],
            'verifyEmail' => ['POST', 'restify/verify/{id}/{emailHash}', 'verifyEmail'],
            'sendOtp' => ['POST', 'restify/sendOtp', 'sendOtp'],
            'verifyOtp' => ['POST', 'restify/verifyOtp', 'verifyOtp'],
            'changePassword' => ['POST', 'restify/changePassword', 'changePassword'],
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
                    $routes[$action][1],
                    [$controller, $routes[$action][2]] // Pass class name string, not app($controller)
                );
            }
        }
    }
}
