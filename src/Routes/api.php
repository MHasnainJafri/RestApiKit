<?php

use Illuminate\Support\Facades\Route;
use Mhasnainjafri\RestApiKit\Http\Controllers\AuthController;

Route::prefix('api')
    ->middleware(config('restify.middleware', ['api']))
    ->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('restify/forgotPassword', [AuthController::class, 'forgotPassword']);
        Route::post('restify/resetPassword', [AuthController::class, 'resetPassword']);
        Route::post('restify/verify/{id}/{emailHash}', [AuthController::class, 'verifyEmail']);
    });
