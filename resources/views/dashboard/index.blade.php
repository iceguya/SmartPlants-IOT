<x-app-layout>
    @section('page-title', 'Dashboard - Command Center')
    
    <div class="min-h-screen bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header with Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Command Center</h1>
                    <p class="mt-1 text-sm text-gray-500">Real-time monitoring and control dashboard</p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    <button onclick="location.reload()" 
                            class="inline-flex items-center px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl border border-gray-200 hover:border-brand-300 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh
                    </button>
                    <a href="{{ route('provisioning.index') }}" 
                       class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Device
                    </a>
                </div>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <!-- Total Devices -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-brand-100 to-brand-50 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Devices</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $devices->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Active Sensors -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Sensors</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $devices->where('is_online', true)->count() * 3 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-50 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Alerts</p>
                            <p class="text-2xl font-bold text-gray-900">0</p>
                        </div>
                    </div>
                </div>

                <!-- System Health -->
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-50 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">System Health</p>
                            <p class="text-2xl font-bold text-green-600">Excellent</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Environmental Stats (Previous Stats Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Temperature -->
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Average Temperature</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $analytics['temperature']['avg'] ?? '28' }}<span class="text-xl text-gray-500">°C</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Last 24 hours</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-100 to-orange-50 rounded-2xl flex items-center justify-center shadow-sm">
                            <svg class="w-7 h-7 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Humidity -->
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Average Humidity</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $analytics['humidity']['avg'] ?? '75' }}<span class="text-xl text-gray-500">%</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Last 24 hours</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center shadow-sm">
                            <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Soil Moisture -->
                <div class="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Average Soil Moisture</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">
                                {{ $analytics['soil_moisture']['avg'] ?? '45' }}<span class="text-xl text-gray-500">%</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">Last 24 hours</p>
                        </div>
                        <div class="w-14 h-14 bg-gradient-to-br from-brand-100 to-brand-50 rounded-2xl flex items-center justify-center shadow-sm">
                            <svg class="w-7 h-7 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Grid Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Connected Devices</h2>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $devices->count() }} total devices • 
                                <span class="text-brand-600 font-medium">{{ $devices->where('is_online', true)->count() }} online</span> • 
                                <span class="text-gray-400">{{ $devices->where('is_online', false)->count() }} offline</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    @forelse ($devices as $device)
                        <!-- Device Card -->
                        <div class="group bg-gradient-to-br from-white to-slate-50/50 rounded-2xl p-6 mb-4 last:mb-0 border-2 transition-all duration-300 hover:shadow-lg {{ $device->is_online ? 'border-brand-200 hover:border-brand-400' : 'border-gray-200 hover:border-gray-300' }}">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                
                                <!-- Device Info -->
                                <div class="flex items-start space-x-4 mb-4 lg:mb-0">
                                    <!-- Device Icon -->
                                    <div class="flex-shrink-0">
                                        <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-md border-2 transition-colors {{ $device->is_online ? 'border-brand-200 group-hover:border-brand-400' : 'border-gray-200' }}">
                                            <svg class="w-8 h-8 {{ $device->is_online ? 'text-brand-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Device Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-brand-700 transition-colors">
                                                {{ $device->name }}
                                            </h3>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $device->is_online ? 'bg-brand-100 text-brand-700 border border-brand-300' : 'bg-gray-100 text-gray-600 border border-gray-300' }}">
                                                <span class="w-2 h-2 rounded-full mr-1.5 {{ $device->is_online ? 'bg-brand-500 animate-pulse' : 'bg-gray-400' }}"></span>
                                                {{ $device->is_online ? 'Online' : 'Offline' }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 mb-2">
                                            <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-xs">{{ $device->id }}</span>
                                        </p>
                                        <div class="flex flex-wrap items-center gap-4 text-sm">
                                            <div class="flex items-center text-gray-600">
                                                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-xs">
                                                    Last seen: 
                                                    @if($device->is_online)
                                                        <span class="text-brand-600 font-medium">Just now</span>
                                                    @else
                                                        <span class="text-gray-500">{{ $device->updated_at->diffForHumans() }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex items-center text-gray-600">
                                                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                <span class="text-xs">3 sensors active</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="flex items-center space-x-3 mt-4 lg:mt-0">
                                    <a href="{{ route('device.show', $device) }}" 
                                       class="inline-flex items-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow-md group-hover:scale-105 transform duration-200">
                                        View Details
                                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-slate-100 to-slate-50 rounded-2xl flex items-center justify-center mb-6 border-2 border-slate-200">
                                <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Devices Connected</h3>
                            <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                                Get started by adding your first IoT device to the system. It only takes a few minutes to set up.
                            </p>
                            <a href="{{ route('provisioning.index') }}" 
                               class="inline-flex items-center px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition-all shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Your First Device
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Auto-refresh Script with Indicator -->
    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => location.reload(), 30000);
        
        // Refresh countdown indicator
        let lastRefresh = Date.now();
        const indicator = document.createElement('div');
        indicator.className = 'fixed bottom-4 right-4 bg-gray-900/90 backdrop-blur-sm text-white px-4 py-2.5 rounded-xl shadow-xl text-xs z-50 flex items-center space-x-2';
        indicator.innerHTML = `
            <svg class="w-4 h-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Auto-refresh in <span id="countdown" class="font-mono font-bold text-brand-400">30</span>s</span>
        `;
        document.body.appendChild(indicator);
        
        setInterval(() => {
            const remaining = 30 - Math.floor((Date.now() - lastRefresh) / 1000);
            const countdownEl = document.getElementById('countdown');
            if (countdownEl) {
                countdownEl.textContent = remaining > 0 ? remaining : 30;
            }
        }, 1000);
    </script>
</x-app-layout>
