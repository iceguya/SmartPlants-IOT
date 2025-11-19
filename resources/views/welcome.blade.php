<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>SmartPlants - Intelligent IoT Plant Monitoring</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    
    <body class="font-sans antialiased bg-white text-gray-900">
        
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-md border-b border-gray-100 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    
                    <!-- Logo -->
                    <a href="/" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <span class="font-bold text-xl text-gray-900">SmartPlants</span>
                    </a>

                    <!-- Live Status Badge -->
                    <div class="hidden md:flex items-center space-x-2 px-3 py-1.5 bg-brand-50 rounded-full border border-brand-200">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                        </span>
                        <span class="text-xs font-medium text-brand-700">System Online</span>
                    </div>
                    
                    <!-- Auth Links -->
                    @if (Route::has('login'))
                        <div class="flex items-center space-x-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" 
                                   class="text-sm font-medium text-gray-700 hover:text-brand-600 transition-colors">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" 
                                   class="text-sm font-medium text-gray-700 hover:text-brand-600 transition-colors">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md">
                                        Get Started
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative min-h-screen flex items-center justify-center bg-gradient-to-br from-white via-brand-50/30 to-brand-100/50 pt-16">
            
            <!-- Decorative Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute top-20 right-10 w-72 h-72 bg-brand-200 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
                <div class="absolute top-40 left-10 w-72 h-72 bg-green-200 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-brand-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
                <div class="text-center">
                    
                    <!-- Hero Badge -->
                    <div class="inline-flex items-center space-x-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-200 mb-8">
                        <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Powered by IoT Technology</span>
                    </div>

                    <!-- Hero Title -->
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-gray-900 tracking-tight mb-6">
                        Monitor Your Plants
                        <span class="block text-brand-600">Intelligently</span>
                    </h1>

                    <!-- Hero Subtitle -->
                    <p class="max-w-3xl mx-auto text-xl sm:text-2xl text-gray-600 mb-10 leading-relaxed">
                        Real-time IoT monitoring for soil moisture, temperature, and plant health. 
                        Make data-driven decisions to keep your plants thriving.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('register') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-brand-600 hover:bg-brand-700 text-white text-lg font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Get Started Free
                        </a>
                        <a href="#features" 
                           class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-50 text-gray-900 text-lg font-semibold rounded-xl border-2 border-gray-200 hover:border-brand-300 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Documentation
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm">
                            <div class="text-3xl font-bold text-brand-600">24/7</div>
                            <div class="text-sm text-gray-600 mt-1">Monitoring</div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm">
                            <div class="text-3xl font-bold text-brand-600">Real-time</div>
                            <div class="text-sm text-gray-600 mt-1">Data Sync</div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm">
                            <div class="text-3xl font-bold text-brand-600">Smart</div>
                            <div class="text-sm text-gray-600 mt-1">Automation</div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-gray-100 shadow-sm">
                            <div class="text-3xl font-bold text-brand-600">Cloud</div>
                            <div class="text-sm text-gray-600 mt-1">Analytics</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 lg:py-32 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                        Powerful Features
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Everything you need to monitor and optimize your plant health in one intelligent platform.
                    </p>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    
                    <!-- Feature 1: Real-time Monitoring -->
                    <div class="group bg-white rounded-2xl p-8 border border-gray-200 hover:border-brand-300 hover:shadow-xl transition-all duration-300 cursor-pointer">
                        <div class="w-14 h-14 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-brand-600 transition-colors">
                            Real-time Monitoring
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Track soil moisture, temperature, and humidity in real-time with instant updates to your dashboard.
                        </p>
                    </div>

                    <!-- Feature 2: Smart Automation -->
                    <div class="group bg-white rounded-2xl p-8 border border-gray-200 hover:border-brand-300 hover:shadow-xl transition-all duration-300 cursor-pointer">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">
                            Smart Automation
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Set up intelligent rules to automate watering and care based on sensor readings and conditions.
                        </p>
                    </div>

                    <!-- Feature 3: Data Analytics -->
                    <div class="group bg-white rounded-2xl p-8 border border-gray-200 hover:border-brand-300 hover:shadow-xl transition-all duration-300 cursor-pointer">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-purple-600 transition-colors">
                            Data Analytics
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Visualize trends and patterns with beautiful charts. Make informed decisions backed by data.
                        </p>
                    </div>

                    <!-- Feature 4: Cloud Connected -->
                    <div class="group bg-white rounded-2xl p-8 border border-gray-200 hover:border-brand-300 hover:shadow-xl transition-all duration-300 cursor-pointer">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-orange-600 transition-colors">
                            Cloud Connected
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Access your plant data from anywhere. Secure cloud storage with automatic backups.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                
                <!-- Footer Content -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    
                    <!-- Brand Column -->
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                            </div>
                            <span class="font-bold text-xl text-gray-900">SmartPlants</span>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Intelligent IoT monitoring system for your plants. Monitor, analyze, and automate plant care with ease.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="#features" class="text-gray-600 hover:text-brand-600 transition-colors text-sm">Features</a></li>
                            <li><a href="{{ route('login') }}" class="text-gray-600 hover:text-brand-600 transition-colors text-sm">Login</a></li>
                            <li><a href="{{ route('register') }}" class="text-gray-600 hover:text-brand-600 transition-colors text-sm">Get Started</a></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-4">Connect</h3>
                        <p class="text-gray-600 text-sm mb-3">
                            Built with ❤️ for plant enthusiasts everywhere.
                        </p>
                        <div class="inline-flex items-center space-x-2 px-3 py-1.5 bg-brand-50 rounded-full border border-brand-200">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                            </span>
                            <span class="text-xs font-medium text-brand-700">All Systems Operational</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Bottom -->
                <div class="pt-8 border-t border-gray-200 text-center">
                    <p class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} SmartPlants IoT. All rights reserved.
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Designed by 
                        <a href="https://firmanfarelrichardo.github.io" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="font-medium text-brand-600 hover:text-brand-700 transition-colors">
                           Firman Farel Richardo
                        </a>
                    </p>
                </div>
            </div>
        </footer>

        <!-- Custom Animations -->
        <style>
            @keyframes blob {
                0%, 100% { transform: translate(0, 0) scale(1); }
                25% { transform: translate(20px, -50px) scale(1.1); }
                50% { transform: translate(-20px, 20px) scale(0.9); }
                75% { transform: translate(50px, 50px) scale(1.05); }
            }
            
            .animate-blob {
                animation: blob 7s infinite;
            }
            
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            
            .animation-delay-4000 {
                animation-delay: 4s;
            }
        </style>
    </body>
</html>