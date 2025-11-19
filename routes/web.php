<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvisioningAdminController;
use App\Http\Controllers\AutomationController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ini adalah Peta Aplikasi yang sudah disinkronkan.
|
*/

// ========================================================================
// RUTE UNTUK TAMU (Guest)
// ========================================================================
//
// Rute '/' akan memanggil view 'welcome.blade.php' untuk landing page.
//
Route::get('/', function () {
    return view('welcome');
})->name('welcome');


// ========================================================================
// RUTE UNTUK PENGGUNA YANG SUDAH LOGIN (Authenticated)
// ========================================================================
//
// Kita gunakan prefix '/dashboard' untuk semua rute aplikasi
// dan lindungi dengan middleware 'auth'.
//
Route::middleware(['auth', 'verified'])->group(function () {

    // Rute '/dashboard' akan memanggil DashboardController
    // Ini adalah halaman dashboard utamamu.
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Rute '/dashboard/device/{device}'
    // Rute kustom milikmu untuk melihat detail device
    Route::get('/dashboard/device/{device}', [DashboardController::class, 'device'])
        ->name('device.show');
    
    // Rute untuk water command
    Route::post('/devices/{device}/commands/water-on', [DashboardController::class, 'waterOn'])
        ->name('device.water');

    // SENSOR MONITORING PAGES
    Route::prefix('sensors')->name('sensors.')->group(function () {
        Route::get('/environment', [SensorController::class, 'environment'])
            ->name('environment');
        Route::get('/soil', [SensorController::class, 'soil'])
            ->name('soil');
        Route::get('/health', [SensorController::class, 'health'])
            ->name('health');
    });

    // NOTIFICATION SYSTEM
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/unread', [NotificationController::class, 'unread'])
            ->name('unread');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])
            ->name('mark-all-read');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])
            ->name('mark-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])
            ->name('destroy');
    });

    // AUTOMATION RULES
    Route::get('/devices/{device}/automation', [AutomationController::class, 'index'])
        ->name('device.automation');
    Route::post('/devices/{device}/automation', [AutomationController::class, 'store'])
        ->name('device.automation.store');
    Route::post('/devices/{device}/automation/{rule}/toggle', [AutomationController::class, 'toggle'])
        ->name('device.automation.toggle');
    Route::delete('/devices/{device}/automation/{rule}', [AutomationController::class, 'destroy'])
        ->name('device.automation.destroy');

    // RUTE PROVISIONING (MILIKMU)
    // Rute ini tetap sama dan TIDAK DIUBAH, hanya dipindah ke dalam grup.
    Route::get('/provisioning', [ProvisioningAdminController::class, 'index'])
        ->name('provisioning.index');
    // Endpoint untuk membuat token provisioning dari form web
    Route::post('/provisioning/generate', [ProvisioningAdminController::class, 'generate'])
        ->name('provisioning.generate');
    // Hapus token provisioning
    Route::delete('/provisioning/{id}', [ProvisioningAdminController::class, 'destroy'])
        ->name('provisioning.destroy');

    // Rute Profile bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Memuat rute untuk otentikasi (login, register, logout, dll)
require __DIR__.'/auth.php';