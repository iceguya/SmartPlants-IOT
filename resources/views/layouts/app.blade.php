<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SmartPlants') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Chart.js for sensor data visualization -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
        
        <style>
            [x-cloak] { display: none !important; }
        </style>

        <!-- Notification Center Script -->
        <script>
            function notificationCenter() {
                return {
                    dropdownOpen: false,
                    unreadCount: 0,
                    notificationsHtml: '<div class="px-4 py-8 text-center text-gray-500 text-sm"><div class="animate-pulse">Loading...</div></div>',
                    pollInterval: null,

                    init() {
                        // Initial fetch
                        this.fetchNotifications();
                        
                        // Poll every 5 seconds
                        this.pollInterval = setInterval(() => {
                            this.fetchNotifications();
                        }, 5000);
                    },

                    toggleDropdown() {
                        this.dropdownOpen = !this.dropdownOpen;
                        if (this.dropdownOpen) {
                            this.fetchNotifications();
                        }
                    },

                    async fetchNotifications() {
                        try {
                            const response = await fetch('/notifications/unread', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                }
                            });

                            if (response.ok) {
                                const data = await response.json();
                                this.unreadCount = data.count;
                                this.notificationsHtml = data.html;
                            }
                        } catch (error) {
                            console.error('Failed to fetch notifications:', error);
                        }
                    },

                    async markAllAsRead() {
                        try {
                            const response = await fetch('/notifications/mark-all-read', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                }
                            });

                            if (response.ok) {
                                this.unreadCount = 0;
                                this.fetchNotifications();
                            }
                        } catch (error) {
                            console.error('Failed to mark notifications as read:', error);
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-slate-50" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen">
            
            <!-- Sidebar for Desktop -->
            <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0"
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
                
                <!-- Logo Area -->
                <div class="flex items-center justify-between h-16 px-6 border-b border-gray-100">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900">SmartPlants</span>
                    </a>
                    
                    <!-- Close button for mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="px-4 py-6 space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}"
                       @click="sidebarOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Sensor Monitoring Section -->
                    <div class="pt-4 pb-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sensor Monitoring</p>
                    </div>

                    <a href="{{ route('sensors.environment') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('sensors.environment') ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}"
                       @click="sidebarOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="font-medium">Environment</span>
                    </a>

                    <a href="{{ route('sensors.soil') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('sensors.soil') ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}"
                       @click="sidebarOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <span class="font-medium">Soil Moisture</span>
                    </a>

                    <a href="{{ route('sensors.health') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('sensors.health') ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}"
                       @click="sidebarOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="font-medium">Plant Health</span>
                    </a>

                    <!-- Alerts & Settings Section -->
                    <div class="pt-4 pb-2">
                        <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Alerts & Settings</p>
                    </div>

                    <a href="{{ route('notifications.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('notifications.index') ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}"
                       @click="sidebarOpen = false"
                       x-data="notificationCenter()">
                        <div class="relative">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span x-show="unreadCount > 0" 
                                  class="absolute -top-1 -left-1 flex items-center justify-center w-4 h-4 text-[9px] font-bold text-white bg-red-500 rounded-full"
                                  x-text="unreadCount > 9 ? '9+' : unreadCount"
                                  x-cloak></span>
                        </div>
                        <span class="font-medium">Notifications</span>
                    </a>

                    <a href="{{ route('provisioning.index') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('provisioning.index') ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}"
                       @click="sidebarOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="font-medium">Provisioning</span>
                    </a>

                    <a href="{{ route('profile.edit') }}" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('profile.edit') ? 'bg-brand-50 text-brand-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}"
                       @click="sidebarOpen = false">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="font-medium">Profile</span>
                    </a>
                </nav>

                <!-- User Section at Bottom -->
                <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-between px-3 py-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-9 h-9 bg-gradient-to-br from-brand-500 to-brand-600 rounded-full flex items-center justify-center shadow-sm flex-shrink-0">
                                <span class="text-sm font-semibold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors flex-shrink-0" title="Logout">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" 
                 @click="sidebarOpen = false"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 lg:hidden"
                 x-cloak></div>

            <!-- Main Content Area -->
            <div class="lg:pl-64">
                
                <!-- Top Navigation Bar -->
                <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                        
                        <!-- Mobile Menu Button -->
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Page Title / Breadcrumb (optional) -->
                        <div class="flex-1 lg:flex-none">
                            <h1 class="text-lg font-semibold text-gray-900 ml-2 lg:ml-0">
                                @yield('page-title', 'Dashboard')
                            </h1>
                        </div>

                        <!-- Right side - Notification Bell -->
                        <div class="flex items-center space-x-2" x-data="notificationCenter()">
                            <!-- Notification Bell with Badge -->
                            <div class="relative">
                                <button @click="toggleDropdown" 
                                        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors relative"
                                        :class="{ 'bg-brand-50 text-brand-600': dropdownOpen }">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <!-- Badge Counter -->
                                    <span x-show="unreadCount > 0" 
                                          x-text="unreadCount > 9 ? '9+' : unreadCount"
                                          class="absolute -top-1 -right-1 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full border-2 border-white"
                                          x-cloak></span>
                                </button>

                                <!-- Notification Dropdown -->
                                <div x-show="dropdownOpen" 
                                     @click.away="dropdownOpen = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden z-50"
                                     x-cloak>
                                    
                                    <!-- Dropdown Header -->
                                    <div class="px-4 py-3 bg-gradient-to-r from-brand-50 to-green-50 border-b border-brand-100">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-900">Sensor Alerts</h3>
                                            <button @click="markAllAsRead" 
                                                    x-show="unreadCount > 0"
                                                    class="text-xs text-brand-600 hover:text-brand-700 font-medium">
                                                Mark all read
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-0.5">
                                            <span x-text="unreadCount"></span> unread alert<span x-show="unreadCount !== 1">s</span>
                                        </p>
                                    </div>

                                    <!-- Notifications List -->
                                    <div class="max-h-96 overflow-y-auto" id="notificationList" x-html="notificationsHtml">
                                        <!-- Notifications will be injected here -->
                                        <div class="px-4 py-8 text-center text-gray-500 text-sm">
                                            <div class="animate-pulse">Loading...</div>
                                        </div>
                                    </div>

                                    <!-- Dropdown Footer -->
                                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                                        <a href="{{ route('notifications.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">
                                            View all notifications →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main Content -->
                <main class="min-h-[calc(100vh-4rem)]">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>