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
use App\Http\Controllers\AsnItemPhotoController;
use App\Http\Controllers\UserLogController;
use App\Http\Controllers\TruckingController;
use App\Http\Controllers\TruckingTarifController;
use App\Http\Controllers\TruckingInvoiceController;

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
Route::delete('asn-item-photos/{id}', [AsnItemPhotoController::class, 'destroy']);
Route::get('asn-items/{id}/histories', [App\Http\Controllers\AsnItemController::class, 'histories']);
Route::apiResource('forwardings', ForwardingController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('locations', LocationController::class);
Route::apiResource('consignees', ConsigneeController::class);
Route::apiResource('tarifs', TarifController::class);
Route::get('asn-items/qr/{qr_id}', [App\Http\Controllers\AsnItemController::class, 'findByQr']);

Route::middleware(['auth:sanctum', 'log.activity'])->group(function () {
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
    
    // Dispatch Routes
    Route::get('outbound/ready-to-dispatch', [DispatchController::class, 'readyToDispatch']);
    Route::post('outbound/dispatch/generate', [DispatchController::class, 'generateSuratJalan']);
    Route::apiResource('dispatches', DispatchController::class);
    Route::post('outbound/qc/{asn_item_id}', [DispatchController::class, 'outboundQcSubmit']);
    Route::get('outbound/packing', [DispatchController::class, 'packingList']);
    Route::post('outbound/packing/{asn_item_id}', [DispatchController::class, 'packingSubmit']);

    // Trucking Supplier
    Route::apiResource('truckings', TruckingController::class);
    Route::apiResource('trucking-tarifs', TruckingTarifController::class);
    Route::get('trucking-invoices/calculate/{asn}', [TruckingInvoiceController::class, 'calculate']);
    Route::post('trucking-invoices/generate/{asn}', [TruckingInvoiceController::class, 'store']);
    Route::post('trucking-invoices/standalone', [TruckingInvoiceController::class, 'storeStandalone']);
    Route::apiResource('trucking-invoices', TruckingInvoiceController::class);
    
    Route::apiResource('system-logs', SystemLogController::class);
    Route::apiResource('configurations', ConfigurationController::class);
    
    // User Log (report aktivitas user)
    Route::get('user-logs/stats', [UserLogController::class, 'stats']);
    Route::get('user-logs', [UserLogController::class, 'index']);
    Route::delete('user-logs/{id}', [UserLogController::class, 'destroy']);
    
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

    // Report / Laporan module
    Route::get('reports/dashboard', [\App\Http\Controllers\ReportController::class, 'dashboard']);
    Route::get('reports/invoices', [\App\Http\Controllers\ReportController::class, 'invoices']);
    Route::get('reports/revenue', [\App\Http\Controllers\ReportController::class, 'revenue']);
    Route::get('reports/operational', [\App\Http\Controllers\ReportController::class, 'operational']);

    // Client Dashboard (role forwarding / EMKL)
    Route::get('client/dashboard', [\App\Http\Controllers\ClientController::class, 'dashboard']);
    Route::get('client/items', [\App\Http\Controllers\ClientController::class, 'items']);
    Route::get('client/items/{id}', [\App\Http\Controllers\ClientController::class, 'itemDetail']);
    Route::get('client/invoices', [\App\Http\Controllers\ClientController::class, 'invoices']);
    Route::get('client/manifests', [\App\Http\Controllers\ClientController::class, 'manifests']);
    Route::get('client/track/{identifier}', [\App\Http\Controllers\ClientController::class, 'track']);

});

Route::get('invoices/calculate/{asn}', [App\Http\Controllers\InvoiceController::class, 'calculate']);
Route::post('invoices/generate/{asn}', [App\Http\Controllers\InvoiceController::class, 'store']);

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
