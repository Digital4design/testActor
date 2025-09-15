<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('form');
});
Route::get('/actors',[App\Http\Controllers\ActorController::class,'index']);
