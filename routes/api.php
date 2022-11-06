<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CalculationController;
use Illuminate\Support\Facades\Route;

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Entry calculation
    Route::get('/calculation_status', [CalculationController::class, 'getCalculationStatus']);
    Route::get('/reference_books', [CalculationController::class, 'getReferenceBooks']);
    // Calculation. Step 1
    Route::post('/parse_file_of_objects', [CalculationController::class, 'parseFileOfObjects']);
    Route::post('/update_object_coords', [CalculationController::class, 'updateObjectCoords']);
    // Calculation. Step 2
    Route::get('/pools', [CalculationController::class, 'getPools']);
    Route::get('/pools/{id}', [CalculationController::class, 'getObjects']);
    Route::get('/pools/{pool_id}/{object_id}', [CalculationController::class, 'getObjectAndAnalogs']);
    Route::get('/all_calculation_objects', [CalculationController::class, 'getAllCalculationObjects']);
    Route::post('/break_calculation', [CalculationController::class, 'breakCalculation']);
});
