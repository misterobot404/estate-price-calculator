<?php

use App\Http\Controllers\API\CalculationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/history/excel/{id}', [CalculationController::class, 'getExcel']);
Route::get('/history/excel', [CalculationController::class, 'getGrowExcel']);


Route::get('/ml/{user_id}', [CalculationController::class, 'getExcelWithML']);

Route::get('{path}', function () {
    return view('index');
})->where('path', '(.*)');
