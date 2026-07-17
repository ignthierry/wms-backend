<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ForwardingController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AsnController;
use App\Http\Controllers\AsnItemController;
use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\DeviationController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockOpnameItemController;
use App\Http\Controllers\DeliveryRequestController;
use App\Http\Controllers\DrItemController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\SystemLogController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ConsigneeController;
use App\Http\Controllers\TarifController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::apiResource('roles', RoleController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('asns', AsnController::class);
Route::apiResource('asn-items', AsnItemController::class);
Route::apiResource('forwardings', ForwardingController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('locations', LocationController::class);
Route::apiResource('consignees', ConsigneeController::class);
Route::apiResource('tarifs', TarifController::class);
Route::get('asn-items/qr/{qr_id}', [App\Http\Controllers\AsnItemController::class, 'findByQr']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('receivings', ReceivingController::class);
    Route::apiResource('deviations', DeviationController::class);
    Route::apiResource('stocks', StockController::class);
    Route::apiResource('stock-transfers', StockTransferController::class);
    Route::apiResource('stock-opnames', StockOpnameController::class);
    Route::apiResource('stock-opname-items', StockOpnameItemController::class);
    Route::apiResource('delivery-requests', DeliveryRequestController::class);
    Route::apiResource('dr-items', DrItemController::class);
    Route::apiResource('packings', PackingController::class);
    Route::apiResource('dispatches', DispatchController::class);
    Route::apiResource('system-logs', SystemLogController::class);
    Route::apiResource('configurations', ConfigurationController::class);
    
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);


    Route::get('invoices/calculate/{asn}', [App\Http\Controllers\InvoiceController::class, 'calculate']);
    Route::post('invoices/generate/{asn}', [App\Http\Controllers\InvoiceController::class, 'store']);
});

// External Portal Tracking Routes
Route::get('tracking/cargo/{identifier}', [App\Http\Controllers\TrackingController::class, 'trackCargo']);

// Photo Proxy Route for SFTP
Route::get('photos/{filename}', function ($filename) {
    $disk = \Illuminate\Support\Facades\Storage::disk('sftp');
    // Ensure filename matches how it's stored, which might include the directory
    // If it's passed as just the filename, we prepend the directory
    // In our db it's stored as 'photo_proofs/filename.jpg', so let's allow fetching by path
    $path = 'photo_proofs/' . basename($filename);
    
    if (!$disk->exists($path)) {
        abort(404, 'Photo not found on SFTP server');
    }
    
    $file = $disk->get($path);
    $type = $disk->mimeType($path);
    
    return response($file, 200)->header("Content-Type", $type);
});
