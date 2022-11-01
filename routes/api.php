<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ObjectsController;
use Illuminate\Support\Facades\Route;

Route::post('/registration', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


/*Route::middleware('auth:api')->group(function () {*/
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/parse_file_of_objects', [ObjectsController::class, 'parseFileOfObjects']);
/*});*/
