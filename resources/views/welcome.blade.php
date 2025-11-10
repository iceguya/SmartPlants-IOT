{{--
|--------------------------------------------------------------------------
| File: resources/views/welcome.blade.php
|--------------------------------------------------------------------------
|
| Ini adalah LANDING PAGE (Pengantar) untuk tamu.
| PERBAIKAN UTAMA: Penambahan @vite di <head> untuk memuat Tailwind CSS.
|
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Smart Plants - IoT Monitoring</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        {{-- 
        |--------------------------------------------------------------------------
        | INI PERBAIKAN PENTING!
        |--------------------------------------------------------------------------
        |
        | @vite memanggil CSS (Tailwind) dan JS. Tanpa ini, halaman akan putih.
        |
        --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
        
        <div class="min-h-screen flex flex-col">
            
            <nav class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        
                        <div class="flex items-center">
                            <span class="font-bold text-xl text-green-600">🌱 Smart Plants</span>
                        </div>
                        
                        <div class="flex items-center">
                            @if (Route::has('login'))
                                <div class="space-x-4">
                                    {{-- 
                                     | Logika Cerdas:
                                     | Jika sudah login (@auth), tombol mengarah ke '/dashboard'.
                                     | Jika masih tamu (@else), tombol mengarah ke '/login'.
                                    --}}
                                    @auth
                                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-green-600">Dashboard</a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-green-600">Log in</a>

                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-green-600">Register</a>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <main class="flex-grow">
                <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center">
                    
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white">
                        Monitor Tanamanmu, di Mana Saja.
                    </h1>
                    
                    <p class="mt-4 text-lg md:text-xl text-gray-600 dark:text-gray-400">
                        Sistem IoT kami membantumu memantau suhu, kelembapan, dan kesehatan tanaman secara real-time.
                    </p>
                    
                    <div class="mt-8">
                        {{-- Tombol ini akan mengarahkan ke login jika belum login,
                             atau langsung ke dashboard jika sudah login --}}
                        <a href="{{ route('dashboard') }}" 
                           class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-lg text-lg shadow-lg transition duration-300">
                            Mulai Monitoring
                        </a>
                    </div>
                </div>
            </main>

            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                    &copy; {{ date('Y') }} Tim Smart Plants.
                </div>
            </footer>
            
        </div>
    </body>
</html>