<x-app-layout>
    @section('page-title', 'Environment Monitoring')
    
    <div class="min-h-screen bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Environment Monitoring</h1>
                <p class="mt-1 text-sm text-gray-500">Real-time temperature and humidity tracking</p>
            </div>

            <!-- Current Status - Big Gauges -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <!-- Temperature Gauge -->
                <div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-2xl p-8 border-2 border-red-200 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Temperature</h2>
                                <p class="text-xs text-gray-600">Current Reading</p>
                            </div>
                        </div>
                        @if($latestTemp)
                            <span class="text-xs font-medium bg-white px-3 py-1 rounded-full text-gray-600 shadow-sm">
                                {{ $latestTemp->recorded_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-center py-6">
                        @if($latestTemp)
                            <div class="text-7xl font-bold text-red-600 mb-2">
                                {{ number_format($latestTemp->value, 1) }}
                                <span class="text-4xl text-red-400">°C</span>
                            </div>
                        @else
                            <div class="text-5xl font-bold text-gray-400">--</div>
                            <p class="text-sm text-gray-500 mt-2">No data available</p>
                        @endif
                    </div>
                </div>

                <!-- Humidity Gauge -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-8 border-2 border-blue-200 shadow-lg">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Humidity</h2>
                                <p class="text-xs text-gray-600">Current Reading</p>
                            </div>
                        </div>
                        @if($latestHumidity)
                            <span class="text-xs font-medium bg-white px-3 py-1 rounded-full text-gray-600 shadow-sm">
                                {{ $latestHumidity->recorded_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-center py-6">
                        @if($latestHumidity)
                            <div class="text-7xl font-bold text-blue-600 mb-2">
                                {{ number_format($latestHumidity->value, 1) }}
                                <span class="text-4xl text-blue-400">%</span>
                            </div>
                        @else
                            <div class="text-5xl font-bold text-gray-400">--</div>
                            <p class="text-sm text-gray-500 mt-2">No data available</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Time Filter Toolbar & Chart -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Time Range</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($timeRanges as $key => $range)
                            <a href="{{ route('sensors.environment', ['range' => $key]) }}" 
                               class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 {{ $timeRange === $key ? 'bg-brand-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-300 hover:bg-brand-50' }}">
                                {{ $range['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Chart -->
                <div class="mt-6">
                    <canvas id="environmentChart" class="w-full" style="height: 400px;"></canvas>
                </div>
            </div>

            <!-- Detailed Data Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-900">Detailed Readings</h3>
                    <p class="text-sm text-gray-500 mt-1">Showing {{ $tempLogs->count() }} of {{ $totalLogs }} total readings</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($tempLogs as $log)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $log->sensor->device->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->sensor->type === 'temp')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Temperature
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Humidity
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $log->sensor->type === 'temp' ? 'text-red-600' : 'text-blue-600' }}">
                                        {{ number_format($log->value, 2) }}{{ $log->sensor->type === 'temp' ? '°C' : '%' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->sensor->type === 'temp')
                                            @if($log->value < 15)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Cold</span>
                                            @elseif($log->value > 30)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Hot</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Normal</span>
                                            @endif
                                        @else
                                            @if($log->value < 30)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Low</span>
                                            @elseif($log->value > 70)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">High</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Normal</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->recorded_at->format('M d, Y H:i:s') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No readings available for this time range
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($totalLogs > $perPage)
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ ($currentPage - 1) * $perPage + 1 }}</span> to 
                                <span class="font-medium">{{ min($currentPage * $perPage, $totalLogs) }}</span> of 
                                <span class="font-medium">{{ $totalLogs }}</span> results
                            </div>
                            <div class="flex space-x-2">
                                @if($currentPage > 1)
                                    <a href="{{ route('sensors.environment', ['range' => $timeRange, 'page' => $currentPage - 1]) }}" 
                                       class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </a>
                                @endif
                                @if($currentPage * $perPage < $totalLogs)
                                    <a href="{{ route('sensors.environment', ['range' => $timeRange, 'page' => $currentPage + 1]) }}" 
                                       class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700">
                                        Next
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        const ctx = document.getElementById('environmentChart').getContext('2d');
        
        const tempData = @json($tempChartData);
        const humidityData = @json($humidityChartData);

        new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Temperature (°C)',
                    data: tempData.map(d => ({
                        x: new Date(d.time),
                        y: d.value
                    })),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    yAxisID: 'y',
                }, {
                    label: 'Humidity (%)',
                    data: humidityData.map(d => ({
                        x: new Date(d.time),
                        y: d.value
                    })),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 13,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        displayColors: true,
                        callbacks: {
                            title: function(context) {
                                return new Date(context[0].parsed.x).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            displayFormats: {
                                minute: 'HH:mm',
                                hour: 'HH:mm',
                                day: 'MMM DD'
                            }
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Temperature (°C)',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            color: 'rgb(239, 68, 68)'
                        },
                        ticks: {
                            color: 'rgb(239, 68, 68)',
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Humidity (%)',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            color: 'rgb(59, 130, 246)'
                        },
                        ticks: {
                            color: 'rgb(59, 130, 246)',
                            font: {
                                size: 12,
                                weight: '600'
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                }
            }
        });
    </script>
</x-app-layout>
