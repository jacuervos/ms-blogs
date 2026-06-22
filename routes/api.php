<?php


use App\Models\Rol;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth.jwt', 'role:'.Rol::ADMIN])->group(function () {
    Route::get('blogs_web', '\App\Http\Controllers\BlogIndexController@index')->name('blog.index'); //Mostrar blogs
    Route::post('blogs_web', '\App\Http\Controllers\BlogStoreController@store')->name('blog.store'); //Crear blogs
});
