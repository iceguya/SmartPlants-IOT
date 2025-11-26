<x-app-layout>
    @section('page-title', 'Notification Center')

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Notification Center</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Stay updated with your plant's health and sensor alerts
                    </p>
                </div>
                
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}" id="markAllReadForm">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:bg-brand-700 active:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Mark All as Read
                        </button>
                    </form>
                @endif
            </div>

            <!-- Stats Bar -->
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Notifications</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $notifications->total() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Unread</p>
                            <p class="text-2xl font-bold text-brand-600 mt-1">{{ $unreadCount }}</p>
                        </div>
                        <div class="w-12 h-12 bg-brand-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Read</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $notifications->total() - $unreadCount }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        @if($notifications->count() > 0)
            <div class="space-y-4">
                @foreach($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $severityColors = [
                            'critical' => [
                                'bg' => 'bg-red-50',
                                'border' => 'border-red-200',
                                'icon_bg' => 'bg-red-100',
                                'icon' => 'text-red-600',
                                'title' => 'text-red-900',
                                'badge' => 'bg-red-500',
                                'solution_bg' => 'bg-red-50',
                                'solution_border' => 'border-red-200',
                                'solution_text' => 'text-red-800',
                            ],
                            'warning' => [
                                'bg' => 'bg-amber-50',
                                'border' => 'border-amber-200',
                                'icon_bg' => 'bg-amber-100',
                                'icon' => 'text-amber-600',
                                'title' => 'text-amber-900',
                                'badge' => 'bg-amber-500',
                                'solution_bg' => 'bg-amber-50',
                                'solution_border' => 'border-amber-200',
                                'solution_text' => 'text-amber-800',
                            ],
                            'info' => [
                                'bg' => 'bg-blue-50',
                                'border' => 'border-blue-200',
                                'icon_bg' => 'bg-blue-100',
                                'icon' => 'text-blue-600',
                                'title' => 'text-blue-900',
                                'badge' => 'bg-blue-500',
                                'solution_bg' => 'bg-blue-50',
                                'solution_border' => 'border-blue-200',
                                'solution_text' => 'text-blue-800',
                            ],
                        ];
                        $colors = $severityColors[$data['severity'] ?? 'info'] ?? $severityColors['info'];
                        $isUnread = is_null($notification->read_at);
                    @endphp

                    <div class="bg-white rounded-xl shadow-sm border {{ $isUnread ? 'border-brand-300 ring-2 ring-brand-100' : 'border-gray-200' }} overflow-hidden transition-all duration-200 hover:shadow-md">
                        <div class="p-6">
                            <div class="flex items-start space-x-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-14 h-14 rounded-xl {{ $colors['icon_bg'] }} border {{ $colors['border'] }} flex items-center justify-center">
                                        <svg class="w-7 h-7 {{ $colors['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-3 mb-1">
                                                <h3 class="text-lg font-bold {{ $colors['title'] }}">
                                                    {{ $data['title'] }}
                                                </h3>
                                                @if($isUnread)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800">
                                                        New
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors['bg'] }} {{ $colors['title'] }} capitalize">
                                                    {{ $data['severity'] }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                {{ $data['message'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Smart Solution Box -->
                                    <div class="mt-4 p-4 {{ $colors['solution_bg'] }} border {{ $colors['solution_border'] }} rounded-lg">
                                        <div class="flex items-start space-x-3">
                                            <svg class="w-5 h-5 {{ $colors['icon'] }} flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold {{ $colors['solution_text'] }} mb-1">💡 Smart Suggestion</p>
                                                <p class="text-sm {{ $colors['solution_text'] }}">{{ $data['solution'] }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metadata Footer -->
                                    <div class="mt-4 flex items-center justify-between text-sm">
                                        <div class="flex items-center space-x-4 text-gray-500">
                                            <div class="flex items-center space-x-1.5">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                                </svg>
                                                <span class="font-medium">{{ $data['device_name'] ?? 'System' }}</span>
                                            </div>
                                            
                                            @if(isset($data['value']) && isset($data['threshold']))
                                                <div class="flex items-center space-x-1.5">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                    </svg>
                                                    <span>{{ $data['value'] }} / {{ $data['threshold'] }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center space-x-4">
                                            <div class="flex items-center space-x-1.5 text-gray-500">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span class="text-xs">
                                                    {{ $notification->created_at->format('d M Y, H:i') }} WIB
                                                </span>
                                                <span class="text-xs text-gray-400">
                                                    ({{ $notification->created_at->diffForHumans() }})
                                                </span>
                                            </div>

                                            @if($isUnread)
                                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-brand-100 text-brand-700 text-xs font-medium rounded-lg hover:bg-brand-200 transition-colors">
                                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Mark as Read
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>

        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 bg-gradient-to-br from-brand-100 to-green-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">You're All Caught Up! 🎉</h3>
                    <p class="text-gray-600 max-w-md mx-auto">
                        No notifications yet. When your plants need attention or sensors detect anomalies, you'll see alerts here.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center px-6 py-3 bg-brand-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-all duration-150">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Handle mark all as read with AJAX for better UX
        document.getElementById('markAllReadForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const response = await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    // Reload page to show updated state
                    window.location.reload();
                }
            } catch (error) {
                console.error('Failed to mark all as read:', error);
                // Fallback to normal form submission
                this.submit();
            }
        });

        // Handle individual mark as read with AJAX
        document.querySelectorAll('form[action*="mark-read"]').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        // Reload page to show updated state
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Failed to mark as read:', error);
                    // Fallback to normal form submission
                    this.submit();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
