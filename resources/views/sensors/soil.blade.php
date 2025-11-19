<x-app-layout>
    @section('page-title', 'Soil Moisture Monitoring')
    
    <div class="min-h-screen bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Soil Moisture Monitoring</h1>
                <p class="mt-1 text-sm text-gray-500">Real-time soil moisture level tracking</p>
            </div>

            <!-- Current Status - Big Gauge -->
            <div class="max-w-2xl mx-auto mb-8">
                <div class="bg-gradient-to-br from-amber-50 via-yellow-50 to-blue-50 rounded-3xl p-10 border-2 border-amber-200 shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-amber-600 to-yellow-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Soil Moisture Level</h2>
                                <p class="text-sm text-gray-600">Current percentage</p>
                            </div>
                        </div>
                        @if($latestMoisture)
                            <span class="text-sm font-medium text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm">
                                {{ $latestMoisture->recorded_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-center py-8">
                        @if($latestMoisture)
                            <div class="text-8xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-blue-600 mb-4">
                                {{ number_format($latestMoisture->value, 1) }}
                                <span class="text-5xl">%</span>
                            </div>
                            
                            <!-- Status Label -->
                            <div class="mt-6">
                                @if($latestMoisture->value < 30)
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-red-100 text-red-800 border border-red-200">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Too Dry - Watering Needed
                                    </span>
                                @elseif($latestMoisture->value > 70)
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Too Wet - Reduce Watering
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Optimal Range
                                    </span>
                                @endif
                            </div>
                        @else
                            <div class="text-6xl font-bold text-gray-400">--</div>
                            <p class="text-lg text-gray-500 mt-4">No data available</p>
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
                            <a href="{{ route('sensors.soil', ['range' => $key]) }}" 
                               class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 {{ $timeRange === $key ? 'bg-brand-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-300 hover:bg-brand-50' }}">
                                {{ $range['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Chart -->
                <div class="mt-6">
                    <canvas id="moistureChart" class="w-full" style="height: 400px;"></canvas>
                </div>
            </div>

            <!-- Detailed Data Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-900">Detailed Readings</h3>
                    <p class="text-sm text-gray-500 mt-1">Showing {{ $moistureLogs->count() }} of {{ $totalLogs }} total readings</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moisture Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($moistureLogs as $log)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $log->sensor->device->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-bold {{ $log->value < 30 ? 'text-red-600' : ($log->value > 70 ? 'text-blue-600' : 'text-green-600') }}">
                                                {{ number_format($log->value, 2) }}%
                                            </div>
                                            <div class="ml-3 w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full {{ $log->value < 30 ? 'bg-red-500' : ($log->value > 70 ? 'bg-blue-500' : 'bg-green-500') }}" 
                                                     style="width: {{ min($log->value, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($log->value < 30)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Too Dry
                                            </span>
                                        @elseif($log->value > 70)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Too Wet
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Optimal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log->recorded_at->format('M d, Y H:i:s') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
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
                                    <a href="{{ route('sensors.soil', ['range' => $timeRange, 'page' => $currentPage - 1]) }}" 
                                       class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </a>
                                @endif
                                @if($currentPage * $perPage < $totalLogs)
                                    <a href="{{ route('sensors.soil', ['range' => $timeRange, 'page' => $currentPage + 1]) }}" 
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
        const ctx = document.getElementById('moistureChart').getContext('2d');
        
        const moistureData = @json($moistureChartData);

        new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Soil Moisture (%)',
                    data: moistureData.map(d => ({
                        x: new Date(d.time),
                        y: d.value
                    })),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
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
                            },
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toFixed(2) + '%';
                                
                                // Add status
                                const value = context.parsed.y;
                                if (value < 30) {
                                    label += ' (Too Dry)';
                                } else if (value > 70) {
                                    label += ' (Too Wet)';
                                } else {
                                    label += ' (Optimal)';
                                }
                                
                                return label;
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
                        title: {
                            display: true,
                            text: 'Moisture (%)',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            color: 'rgb(16, 185, 129)'
                        },
                        ticks: {
                            color: 'rgb(16, 185, 129)',
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        min: 0,
                        max: 100
                    },
                }
            }
        });
    </script>
</x-app-layout>
