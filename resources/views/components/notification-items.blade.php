@forelse($notifications as $notification)
    @php
        $data = $notification->data;
        $severityColors = [
            'critical' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'icon' => 'text-red-600', 'text' => 'text-red-900'],
            'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'icon' => 'text-amber-600', 'text' => 'text-amber-900'],
            'info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'icon' => 'text-blue-600', 'text' => 'text-blue-900'],
        ];
        $colors = $severityColors[$data['severity'] ?? 'info'] ?? $severityColors['info'];
        $isUnread = is_null($notification->read_at);
    @endphp
    
    <div class="px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 {{ $isUnread ? 'bg-brand-50/30' : '' }}" 
         data-notification-id="{{ $notification->id }}">
        
        <div class="flex items-start space-x-3">
            <!-- Icon -->
            <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $colors['bg'] }} border {{ $colors['border'] }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $colors['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    @if($data['icon'] === 'soil')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    @elseif($data['icon'] === 'temperature')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    @elseif($data['icon'] === 'health')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    @endif
                </svg>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <!-- Title and Time -->
                <div class="flex items-start justify-between mb-1">
                    <h4 class="text-sm font-semibold {{ $colors['text'] }} truncate">
                        {{ $data['title'] }}
                    </h4>
                    @if($isUnread)
                        <span class="ml-2 flex-shrink-0 w-2 h-2 bg-brand-500 rounded-full"></span>
                    @endif
                </div>

                <!-- Message -->
                <p class="text-xs text-gray-600 mb-2">
                    {{ $data['message'] }}
                </p>

                <!-- Smart Advice Box -->
                <div class="bg-gradient-to-r from-brand-50 to-green-50 border border-brand-200 rounded-lg px-3 py-2 mb-2">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-brand-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-xs font-medium text-brand-900">💡 Suggestion:</p>
                            <p class="text-xs text-brand-800 mt-0.5">{{ $data['solution'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Device Name & Timestamp -->
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span class="font-medium">{{ $data['device_name'] ?? 'System' }}</span>
                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="px-4 py-8 text-center text-gray-500 text-sm">
        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p class="font-medium text-gray-900 mb-1">All Clear!</p>
        <p class="text-xs">No sensor alerts at the moment</p>
    </div>
@endforelse
