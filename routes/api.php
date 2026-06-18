<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\CustomsDocumentController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('job-orders', JobOrderController::class);
    Route::apiResource('containers', ContainerController::class);
    Route::get('/containers/{id}/logs', [ContainerController::class, 'logs']);
    Route::post('/customs/documents', [CustomsDocumentController::class, 'store']);
});
