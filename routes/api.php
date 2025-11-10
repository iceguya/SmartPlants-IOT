<?php
use App\Http\Controllers\Api\ProvisioningController;
use App\Http\Controllers\Api\DeviceIngestController;
use App\Http\Controllers\Api\CommandController;
use App\Http\Controllers\Api\SensorController;
use Illuminate\Support\Facades\Route;

Route::post('/provision/claim', [ProvisioningController::class, 'claim']);
Route::post('/provisioning/generate', [ProvisioningController::class, 'generate']);

Route::middleware('device.key')->group(function () {
    Route::post('/ingest', [DeviceIngestController::class, 'store']);
    Route::get('/commands/next', [CommandController::class, 'next']);
    Route::post('/commands/{id}/ack', [CommandController::class, 'ack']);
});

Route::get('/sensor-latest', [SensorController::class, 'latest']);