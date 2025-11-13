<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Smart Plants - IoT Monitoring</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>
    
    <body class="font-sans antialiased bg-gray-100 text-gray-900">
        
        <div class="min-h-screen flex flex-col">
            
            <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        
                        <div class="flex items-center">
                            <a href="/" class="flex items-center space-x-2">
                                <svg class="w-6 h-6 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                                <span class="font-bold text-xl text-primary-600">Smart Plants</span>
                            </a>
                        </div>
                        
                        <div class="flex items-center">
                            @if (Route::has('login'))
                                <div class="space-x-4">
                                    @auth
                                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-primary-600">Dashboard</a>
                                    @else
                                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-primary-600">Log in</a>

                                        @if (Route::has('register'))
                                            <a href="{{ route('register') }}" class="text-sm font-medium text-gray-700 hover:text-primary-600">Register</a>
                                        @endif
                                    @endauth
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </nav>

            <main class="flex-grow flex items-center justify-center relative" style="background-image: url('{{ asset('images/bg-landing.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
                
                {{-- Overlay gelap --}}
                <div class="absolute inset-0 bg-black/30"></div>
                
                {{-- Content --}}
                <div class="max-w-4xl mx-auto py-16 px-4 sm:px-6 lg:px-8 text-center bg-white/50 backdrop-blur-md shadow-2xl rounded-2xl m-4 border border-white/20 relative z-10">
                    
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 drop-shadow-sm">
                        Monitor Tanamanmu, di Mana Saja.
                    </h1>
                    
                    <p class="mt-4 text-lg md:text-xl text-gray-700 drop-shadow-sm">
                        Sistem IoT kami membantumu memantau suhu, kelembapan, dan kesehatan tanaman secara real-time.
                    </p>
                    
                    <div class="mt-8">
                        <a href="{{ route('dashboard') }}" 
                           class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-10 rounded-xl text-lg shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                            Mulai Monitoring
                        </a>
                    </div>
                </div>
            </main>

            <footer class="bg-white border-t border-gray-100">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
                    <span>&copy; {{ date('Y') }} Tim Smart Plants.</span>
                    <span class="block sm:inline sm:ml-2">
                        Didesain oleh 
                        <a href="https://firmanfarelrichardo.github.io" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="font-medium hover:text-primary-600 underline">
                           Firman Farel Richardo
                        </a>.
                    </span>
                </div>
            </footer>
            
        </div>
    </body>
</html>