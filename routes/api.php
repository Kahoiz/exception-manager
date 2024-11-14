<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
dd("din mor");
});
Route::post('/log', function () {
    return response()->json(['message' => 'Logged']);
});
