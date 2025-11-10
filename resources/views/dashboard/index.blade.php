{{--
|--------------------------------------------------------------------------
| File: resources/views/dashboard/index.blade.php
|--------------------------------------------------------------------------
|
| Ini adalah DASHBOARD KUSTOM milikmu.
| Kita tambahkan Tombol Provisioning dan Kartu Statistik di atas
| daftar perangkatmu yang sudah ada.
|
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- 
            |--------------------------------------------------------------------------
            | KODE BARU: Tombol Aksi (Provisioning)
            |--------------------------------------------------------------------------
            |
            | Tombol ini mengarah ke rute 'provisioning.index' yang
            | memanggil controller ProvisioningAdminController milikmu.
            |
            --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-medium">Manajemen Perangkat</h3>
                    
                    <a href="{{ route('provisioning.index') }}">
                        <x-primary-button>
                            {{ __('+ Kelola Provisioning') }}
                        </x-primary-button>
                    </a>
                </div>
            </div>

            {{-- 
            |--------------------------------------------------------------------------
            | KODE BARU: Grid Statistik (Kartu-kartu Sensor)
            |--------------------------------------------------------------------------
            |
            | Ini adalah kartu-kartu statis (data pura-pura) untuk
            | menampilkan data sensor secara global.
            |
            --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌡️ Suhu Udara
                    </h3>
                    {{-- 
                        Di Backend (DashboardController), kamu bisa memuat
                        data rata-rata: $avgTemp = $analyticsService->getAverage('temperature');
                        Lalu di sini: $avgTemp ?? '28'
                    --}}
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        {{ $analytics['temperature']['avg'] ?? '28' }} <span class="text-3xl">&deg;C</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Rata-rata 24 Jam
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        💧 Kelembapan Udara
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        {{ $analytics['humidity']['avg'] ?? '75' }} <span class="text-3xl">%</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Rata-rata 24 Jam
                    </p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">
                        🌱 Kelembapan Tanah
                    </h3>
                    <p class="mt-2 text-5xl font-bold text-gray-900 dark:text-white">
                        {{ $analytics['soil_moisture']['avg'] ?? '45' }} <span class="text-3xl">%</span>
                    </p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Rata-rata 24 Jam
                    </p>
                </div>
            </div>

            
            {{-- 
            |--------------------------------------------------------------------------
            | KODE ASLI MILIKMU (Daftar Perangkat)
            |--------------------------------------------------------------------------
            |
            | Kode ini tidak diubah, hanya diposisikan di bawah kartu.
            | Ini akan menampilkan daftar perangkat yang terdaftar.
            |
            --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Perangkat Terdaftar</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($devices as $device)
                            <a href="{{ route('device.show', $device) }}" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <h4 class="font-semibold text-lg">{{ $device->name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">ID: {{ $device->device_id }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    Status: 
                                    <span class="font-medium {{ $device->is_online ? 'text-green-500' : 'text-red-500' }}">
                                        {{ $device->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                </p>
                            </a>
                        @empty
                            <p class_="text-gray-500 dark:text-gray-400">Belum ada perangkat terdaftar.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>