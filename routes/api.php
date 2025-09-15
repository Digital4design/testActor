<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/actors/check-email', [App\Http\Controllers\ActorController::class, 'checkEmail']);
Route::post('/actors/check-description', [App\Http\Controllers\ActorController::class, 'checkDescription']);
Route::get('/actors/prompt-validation', [App\Http\Controllers\ActorController::class, 'store']);
