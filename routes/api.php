<?php
use App\Http\Controllers\Api\ProvisioningController;
use App\Http\Controllers\Api\DeviceIngestController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

Route::post('/provision/claim', [ProvisioningController::class, 'claim']);
Route::post('/provisioning/generate', [ProvisioningController::class, 'generate']);

// Protected device routes - require both valid API key AND ownership validation
Route::middleware(['device.key', 'device.ownership'])->group(function () {
    Route::post('/ingest', [DeviceIngestController::class, 'store']);
    Route::get('/commands/next', [CommandController::class, 'next']);
    Route::post('/commands/{id}/ack', [CommandController::class, 'ack']);
});

Route::get('/sensor-latest', [SensorController::class, 'latest']);