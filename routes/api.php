<?php


use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.jwt')->group(function () {
    Route::apiResource('blogs', BlogController::class);
});
