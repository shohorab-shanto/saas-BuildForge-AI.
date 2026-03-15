<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load('organizations');
    });

    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/analyze', [ProjectController::class, 'analyze']);
    Route::post('projects/{project}/generate-architecture', [ProjectController::class, 'generateArchitecture']);
    Route::post('projects/{project}/generate-schema', [ProjectController::class, 'generateSchema']);
    Route::post('projects/{project}/generate-api', [ProjectController::class, 'generateApi']);
    Route::post('projects/{project}/generate-frontend', [ProjectController::class, 'generateFrontend']);
    Route::post('projects/{project}/retry', [ProjectController::class, 'retry']);
});
