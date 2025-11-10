<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProvisioningAdminController; // Pastikan ini di-import

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
        ->name('dashboard'); // Kita beri nama 'dashboard'

    // Rute '/dashboard/device/{device}'
    // Rute kustom milikmu untuk melihat detail device
    Route::get('/dashboard/device/{device}', [DashboardController::class, 'device'])
        ->name('device.show');

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