<?php

use App\Http\Controllers\ActorController;
use App\Http\Controllers\FilmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('films', FilmController::class);
Route::apiResource('actors', ActorController::class);