{{--
|--------------------------------------------------------------------------
| File: resources/views/dashboard.blade.php
|--------------------------------------------------------------------------
|
| Ini adalah DASHBOARD UTAMA setelah user login.
| Kita gunakan layout <x-app-layout> yang sudah punya menu navigasi.
|
--}}
<x-app-layout>
    {{-- Slot 'header' untuk judul halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- Konten utama halaman --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 
            |--------------------------------------------------------------------------
            | KODE BARU: Bagian Tombol Aksi (Provisioning)
            |--------------------------------------------------------------------------
            |
            | Sesuai permintaanmu, kita tambahkan area untuk tombol aksi.
            | Kita gunakan komponen <x-primary-button> yang sudah ada.
            |
            --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-medium">Manajemen Perangkat</h3>
                    
                    {{-- Tombol ini mengarah ke rute 'provisioning.index' yang kita buat --}}
                    <a href="{{ route('provisioning.index') }}">
                        <x-primary-button>
                            {{ __('+ Kelola Provisioning') }}
                        </x-primary-button>
                    </a>
                </div>
            </div>

            
            {{-- 
            |--------------------------------------------------------------------------
            | Grid Statistik (Kartu-kartu Sensor)
            |--------------------------------------------------------------------------
            |
            | Ini adalah kode kartu statistik dari sebelumnya.
            |
            --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌡️ Suhu Udara
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        28 <span class="text-3xl">&deg;C</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Kondisi ideal
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        💧 Kelembapan Udara
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        75 <span class="text-3xl">%</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Cukup lembap
                    </p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌱 Kelembapan Tanah
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        45 <span class="text-3xl">%</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Tanah mulai kering
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🚿 Penyiraman
                    </h3>
                    <div class="mt-2 flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 mr-2">Status: </span>
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                            Mati
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Terakhir Menyiram: 08:00 Pagi Tadi
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌿 Kesehatan Tanaman
                    </h3>
                    <div class="mt-2 flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 mr-2">Status: </span>
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            Sehat
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Rekomendasi: Tidak ada
                    </p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        📡 Status Perangkat
                    </h3>
                    <div class="mt-2 flex items-center">
                        <span class="text-gray-600 dark:text-gray-400 mr-2">Koneksi: </span>
                        <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            Online
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Terakhir update: 1 menit lalu
                    </p>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>