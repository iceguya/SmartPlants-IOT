<x-app-layout>
    @section('page-title', 'Plant Health Monitoring')
    
    <div class="min-h-screen bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Plant Health Monitoring</h1>
                    <p class="mt-1 text-sm text-gray-500">RGB color sensor analysis for plant condition</p>
                </div>
                @php
                    $statusColors = [
                        'Healthy' => 'bg-green-100 text-green-800',
                        'Alert' => 'bg-red-100 text-red-800',
                        'Soil/Stem Detected' => 'bg-amber-100 text-amber-800',
                        'Unusual' => 'bg-blue-100 text-blue-800',
                        'Mixed' => 'bg-purple-100 text-purple-800',
                        'No Data' => 'bg-gray-100 text-gray-800',
                        'Unknown' => 'bg-gray-100 text-gray-800',
                    ];
                    $badgeClass = $statusColors[$colorInterpretation['status']] ?? 'bg-purple-100 text-purple-800';
                @endphp
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium {{ $badgeClass }}">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        @if($colorInterpretation['icon'] === 'check')
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        @elseif($colorInterpretation['icon'] === 'warning')
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        @elseif($colorInterpretation['icon'] === 'alert')
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        @else
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        @endif
                    </svg>
                    {{ $colorInterpretation['status'] }}
                </span>
            </div>

            <!-- Current Status - RGB Display -->
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-gradient-to-br from-purple-50 via-pink-50 to-orange-50 rounded-3xl p-10 border-2 border-purple-200 shadow-xl">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Color Analysis</h2>
                                <p class="text-sm text-gray-600">TCS3200 RGB Sensor</p>
                            </div>
                        </div>
                        @if($latestRed || $latestGreen || $latestBlue)
                            @php
                                $latestTime = collect([$latestRed, $latestGreen, $latestBlue])
                                    ->filter()
                                    ->sortByDesc('recorded_at')
                                    ->first();
                            @endphp
                            <span class="text-sm font-medium text-gray-500 bg-white px-3 py-1 rounded-full shadow-sm">
                                {{ $latestTime->recorded_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                    
                    @if($latestRed || $latestGreen || $latestBlue)
                        <!-- Color Preview Box -->
                        <div class="mb-8">
                            <div class="h-48 rounded-2xl shadow-2xl border-4 border-white" 
                                 style="background-color: {{ $hexColor }};">
                            </div>
                            <p class="text-center mt-3 text-sm text-gray-600 font-medium">Detected Color</p>
                        </div>

                        <!-- RGB Values Grid -->
                        <div class="grid grid-cols-3 gap-6">
                            <!-- Red Value -->
                            <div class="bg-white rounded-xl p-6 shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-semibold text-gray-700 uppercase">Red</span>
                                    <div class="w-8 h-8 bg-red-500 rounded-lg shadow-sm"></div>
                                </div>
                                <div class="text-4xl font-bold text-red-600 mb-3">{{ $rgbValues['r'] }}</div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-black to-red-600 rounded-full" 
                                         style="width: {{ ($rgbValues['r'] / 255) * 100 }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>0</span>
                                    <span>255</span>
                                </div>
                            </div>

                            <!-- Green Value -->
                            <div class="bg-white rounded-xl p-6 shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-semibold text-gray-700 uppercase">Green</span>
                                    <div class="w-8 h-8 bg-green-500 rounded-lg shadow-sm"></div>
                                </div>
                                <div class="text-4xl font-bold text-green-600 mb-3">{{ $rgbValues['g'] }}</div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-black to-green-600 rounded-full" 
                                         style="width: {{ ($rgbValues['g'] / 255) * 100 }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>0</span>
                                    <span>255</span>
                                </div>
                            </div>

                            <!-- Blue Value -->
                            <div class="bg-white rounded-xl p-6 shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-sm font-semibold text-gray-700 uppercase">Blue</span>
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg shadow-sm"></div>
                                </div>
                                <div class="text-4xl font-bold text-blue-600 mb-3">{{ $rgbValues['b'] }}</div>
                                <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-black to-blue-600 rounded-full" 
                                         style="width: {{ ($rgbValues['b'] / 255) * 100 }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>0</span>
                                    <span>255</span>
                                </div>
                            </div>
                        </div>

                        <!-- Color Interpretation -->
                        @php
                            $interpretColors = [
                                'green' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'icon' => 'text-green-600', 'text' => 'text-green-900', 'subtext' => 'text-green-700'],
                                'red' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => 'text-red-600', 'text' => 'text-red-900', 'subtext' => 'text-red-700'],
                                'amber' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => 'text-amber-600', 'text' => 'text-amber-900', 'subtext' => 'text-amber-700'],
                                'blue' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => 'text-blue-600', 'text' => 'text-blue-900', 'subtext' => 'text-blue-700'],
                                'purple' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'icon' => 'text-purple-600', 'text' => 'text-purple-900', 'subtext' => 'text-purple-700'],
                                'gray' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'icon' => 'text-gray-600', 'text' => 'text-gray-900', 'subtext' => 'text-gray-700'],
                            ];
                            $interpretStyle = $interpretColors[$colorInterpretation['color']] ?? $interpretColors['purple'];
                        @endphp
                        <div class="mt-6 p-4 {{ $interpretStyle['bg'] }} rounded-xl border {{ $interpretStyle['border'] }}">
                            <div class="flex items-start space-x-3">
                                <svg class="w-6 h-6 {{ $interpretStyle['icon'] }} mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <div class="text-base font-semibold {{ $interpretStyle['text'] }}">{{ $colorInterpretation['status'] }}</div>
                                    <div class="text-sm {{ $interpretStyle['subtext'] }} mt-1">
                                        {{ $colorInterpretation['message'] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hex Code Display -->
                        <div class="mt-4 bg-white rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                    <span class="text-sm text-gray-700">Hexadecimal Code:</span>
                                </div>
                                <code class="px-4 py-2 bg-gray-100 rounded-lg text-sm font-mono font-semibold text-gray-800">
                                    {{ strtoupper($hexColor) }}
                                </code>
                            </div>
                        </div>
                        
                    @else
                        <div class="text-center py-12">
                            <div class="w-48 h-48 mx-auto bg-gray-200 rounded-2xl flex items-center justify-center mb-4">
                                <svg class="w-16 h-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <p class="text-lg font-medium text-gray-900 mb-1">No Color Data Available</p>
                            <p class="text-sm text-gray-500">Waiting for TCS3200 sensor readings...</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Time Filter Toolbar & Chart -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">RGB Trend Analysis</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($timeRanges as $key => $range)
                            <a href="{{ route('sensors.health', ['range' => $key]) }}" 
                               class="px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 {{ $timeRange === $key ? 'bg-brand-500 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-brand-300 hover:bg-brand-50' }}">
                                {{ $range['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Chart -->
                <div class="mt-6">
                    <canvas id="rgbChart" class="w-full" style="height: 400px;"></canvas>
                </div>
            </div>

            <!-- Detailed Data Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-xl font-semibold text-gray-900">Color Reading History</h3>
                    <p class="text-sm text-gray-500 mt-1">Showing {{ count($rgbLogs) }} of {{ $totalLogs }} total grouped readings</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color Preview</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RGB Values</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hex Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($rgbLogs as $log)
                                @php
                                    $logHex = sprintf("#%02x%02x%02x", $log['r'], $log['g'], $log['b']);
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $log['device']->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="w-20 h-10 rounded-lg shadow-sm border-2 border-gray-300" 
                                             style="background-color: {{ $logHex }};"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-3 text-xs font-mono">
                                            <span class="text-red-600 font-semibold">R: {{ $log['r'] }}</span>
                                            <span class="text-green-600 font-semibold">G: {{ $log['g'] }}</span>
                                            <span class="text-blue-600 font-semibold">B: {{ $log['b'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="text-xs font-mono bg-gray-100 px-2 py-1 rounded text-gray-800">
                                            {{ strtoupper($logHex) }}
                                        </code>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $log['timestamp']->format('M d, Y H:i:s') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="font-medium text-gray-900">No color readings available</p>
                                        <p class="text-sm text-gray-500 mt-1">Color data will appear here once your TCS3200 sensor starts transmitting</p>
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
                                    <a href="{{ route('sensors.health', ['range' => $timeRange, 'page' => $currentPage - 1]) }}" 
                                       class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </a>
                                @endif
                                @if($currentPage * $perPage < $totalLogs)
                                    <a href="{{ route('sensors.health', ['range' => $timeRange, 'page' => $currentPage + 1]) }}" 
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
        const ctx = document.getElementById('rgbChart').getContext('2d');
        
        const redData = @json($redChartData);
        const greenData = @json($greenChartData);
        const blueData = @json($blueChartData);

        new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'Red Component',
                    data: redData.map(d => ({
                        x: new Date(d.time),
                        y: d.value
                    })),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgb(239, 68, 68)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }, {
                    label: 'Green Component',
                    data: greenData.map(d => ({
                        x: new Date(d.time),
                        y: d.value
                    })),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgb(34, 197, 94)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }, {
                    label: 'Blue Component',
                    data: blueData.map(d => ({
                        x: new Date(d.time),
                        y: d.value
                    })),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
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
                            afterBody: function(context) {
                                // Show combined RGB if all three values are present at same time
                                if (context.length === 3) {
                                    const r = context.find(c => c.dataset.label === 'Red Component')?.parsed.y || 0;
                                    const g = context.find(c => c.dataset.label === 'Green Component')?.parsed.y || 0;
                                    const b = context.find(c => c.dataset.label === 'Blue Component')?.parsed.y || 0;
                                    const hex = '#' + [r, g, b].map(v => Math.round(v).toString(16).padStart(2, '0')).join('');
                                    return '\nCombined: ' + hex.toUpperCase();
                                }
                                return '';
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
                            text: 'RGB Value (0-255)',
                            font: {
                                size: 13,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            stepSize: 50
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        min: 0,
                        max: 255
                    },
                }
            }
        });
    </script>
</x-app-layout>
