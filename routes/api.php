<?php

use Illuminate\Support\Facades\Route;

//To test if the application is alive and running
Route::get('/', function () {
    return response()->json(['message' => 'Healthy']);
});

