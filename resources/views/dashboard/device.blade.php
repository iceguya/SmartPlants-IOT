<x-app-layout>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      @if (session('status'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-start">
          <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm">{{ session('status') }}</span>
        </div>
      @endif

      <!-- Header -->
      <div class="mb-8" id="device-header">
        <div class="flex items-center justify-between">
          <div>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Kembali ke Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ $device->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $device->location }} • ID: {{ $device->id }}</p>
          </div>
          <div class="flex items-center space-x-3">
            <a href="{{ route('device.automation', $device) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
              <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Automation
            </a>
            <span id="status-badge" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium {{ $device->is_online ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
              <span class="w-2 h-2 rounded-full mr-2 {{ $device->is_online ? 'bg-green-500' : 'bg-gray-400' }}"></span>
              {{ $device->is_online ? 'Online' : 'Offline' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8" id="quick-stats">
        <div class="bg-white rounded-2xl p-6 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-500">Green Index</h3>
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
            </div>
          </div>
          <p id="green-index" class="text-3xl font-bold text-gray-900">{{ $analytics['green_index'] !== null ? $analytics['green_index'] : '-' }}</p>
          <p class="text-xs text-gray-500 mt-1">G / (R+G+B)</p>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-500">Alerts</h3>
            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
          </div>
          <div id="alerts-container">
            @if (count($analytics['alerts']))
              <div class="space-y-1 mt-3">
                @foreach ($analytics['alerts'] as $a)
                  <div class="text-sm text-orange-700 bg-orange-50 px-3 py-1.5 rounded-lg">{{ $a }}</div>
                @endforeach
              </div>
            @else
              <p class="text-sm text-gray-500 mt-3">Tidak ada peringatan</p>
            @endif
          </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-500">Auto Water</h3>
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
              </svg>
            </div>
          </div>
          <form id="water-form" method="POST" action="{{ route('device.water', $device->id) }}" class="mt-3 flex items-center gap-2">
            @csrf
            <input type="number" name="duration_sec" value="5" min="1" max="60"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-transparent text-sm" 
                   placeholder="Detik">
            <button type="submit" id="water-btn" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
              Aktifkan
            </button>
          </form>
        </div>
      </div>

      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

      <!-- Sensor Charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($device->sensors as $s)
        <div class="bg-white rounded-2xl p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $s->label ?? strtoupper($s->type) }}</h3>
          
          <div class="mb-4">
            <canvas id="chart-{{ $s->id }}" height="200"></canvas>
          </div>

          @php
            $points = $s->readings->reverse();
            $labels = $points->pluck('recorded_at')->map(fn($d)=>$d->format('H:i'))->values();
            $values = $points->pluck('value')->values();
            $m = $analytics['metrics'][$s->type] ?? null;
          @endphp
          
          <script>
            const ctx{{ $s->id }} = document.getElementById('chart-{{ $s->id }}');
            const data{{ $s->id }} = {
              labels: {!! $labels->toJson() !!},
              datasets: [{
                label: '{{ $s->label }}',
                data: {!! $values->toJson() !!},
                borderColor: 'rgb(17, 24, 39)',
                backgroundColor: 'rgba(17, 24, 39, 0.1)',
                tension: 0.4,
                fill: true
              }]
            };
            const chart{{ $s->id }} = new Chart(ctx{{ $s->id }}, {
              type: 'line',
              data: data{{ $s->id }},
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: { display: false }
                },
                scales: {
                  y: { beginAtZero: false }
                }
              }
            });

            // Chart will be updated by global polling mechanism below
          </script>

          <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div>
              <p class="text-xs text-gray-500">Terakhir</p>
              <p class="text-lg font-semibold text-gray-900" id="last-{{ $s->id }}">{{ $m['last'] ?? '-' }} <span class="text-sm font-normal text-gray-500">{{ $s->unit }}</span></p>
              <p class="text-xs text-gray-500" id="last-time-{{ $s->id }}">{{ isset($m['last_at']) ? \Carbon\Carbon::parse($m['last_at'])->format('H:i') : '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Rata-rata 24j</p>
              <p class="text-lg font-semibold text-gray-900" id="avg-{{ $s->id }}">{{ $m['avg'] ?? '-' }} <span class="text-sm font-normal text-gray-500">{{ $s->unit }}</span></p>
              <p class="text-xs text-gray-500" id="minmax-{{ $s->id }}">Min: {{ $m['min'] ?? '-' }} • Max: {{ $m['max'] ?? '-' }}</p>
            </div>
          </div>
        </div>
        @endforeach
      </div>

      <script>
        // Smart auto-refresh: only poll if device is online
        const deviceId = '{{ $device->id }}';
        const deviceStatus = '{{ $device->status }}';
        let pollInterval = null;
        
        // Start polling only if device is online
        if (deviceStatus === 'online') {
          startPolling();
        }
        
        function startPolling() {
          // Single polling mechanism untuk update semua sensor sekaligus
          pollInterval = setInterval(async () => {
            try {
              // Check each sensor for updates
              @foreach($device->sensors as $s)
              await updateSensor{{ $s->id }}();
              @endforeach
            } catch (error) {
              console.error('Polling error:', error);
            }
          }, 30000); // Poll setiap 30 detik (bukan 5-10 detik)
        }
        
        function stopPolling() {
          if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
          }
        }
        
        // Update functions untuk tiap sensor
        @foreach($device->sensors as $s)
        async function updateSensor{{ $s->id }}() {
          try {
            const res = await fetch('{{ url('/api/sensor-latest?sensor_id='.$s->id) }}');
            if (!res.ok) return;
            const data = await res.json();
            
            if (data && data.value !== undefined) {
              // Update chart
              data{{ $s->id }}.labels.push(data.time);
              data{{ $s->id }}.datasets[0].data.push(data.value);
              if (data{{ $s->id }}.labels.length > 50) {
                data{{ $s->id }}.labels.shift();
                data{{ $s->id }}.datasets[0].data.shift();
              }
              chart{{ $s->id }}.update();
              
              // Update display values
              const lastEl = document.getElementById('last-{{ $s->id }}');
              const lastTimeEl = document.getElementById('last-time-{{ $s->id }}');
              if (lastEl) lastEl.innerHTML = data.value + ' <span class="text-sm font-normal text-gray-500">{{ $s->unit }}</span>';
              if (lastTimeEl) lastTimeEl.textContent = data.time;
            }
          } catch (error) {
            console.error('Error updating sensor {{ $s->id }}:', error);
          }
        }
        @endforeach
        
        // Handle water form submission
        document.getElementById('water-form').addEventListener('submit', async function(e) {
          e.preventDefault();
          const btn = document.getElementById('water-btn');
          const form = e.target;
          const formData = new FormData(form);
          
          btn.disabled = true;
          btn.textContent = 'Mengirim...';
          
          try {
            const response = await fetch(form.action, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
              },
              body: formData
            });
            
            if (response.ok) {
              // Show success message
              const duration = formData.get('duration_sec');
              showNotification('Perintah pompa air ' + duration + ' detik berhasil dikirim!', 'success');
              
              // Reset button after 2 seconds
              setTimeout(() => {
                btn.disabled = false;
                btn.textContent = 'Aktifkan';
              }, 2000);
            } else {
              throw new Error('Network response was not ok');
            }
          } catch (error) {
            showNotification('Gagal mengirim perintah. Silakan coba lagi.', 'error');
            btn.disabled = false;
            btn.textContent = 'Aktifkan';
          }
        });
        
        // Function to show notification
        function showNotification(message, type) {
          const bgColor = type === 'success' ? 'bg-green-50' : 'bg-red-50';
          const borderColor = type === 'success' ? 'border-green-200' : 'border-red-200';
          const textColor = type === 'success' ? 'text-green-800' : 'text-red-800';
          
          const div = document.createElement('div');
          div.className = `fixed top-4 right-4 ${bgColor} border ${borderColor} ${textColor} px-6 py-4 rounded-xl shadow-lg z-50 flex items-center`;
          div.innerHTML = `
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>${message}</span>
          `;
          document.body.appendChild(div);
          setTimeout(() => div.remove(), 5000);
        }
        
        // Full page reload setiap 5 menit untuk update analytics dan device status
        setTimeout(() => location.reload(), 300000); // 5 menit
        
        // Indikator countdown
        const indicator = document.createElement('div');
        indicator.className = 'fixed bottom-4 left-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-xs z-50';
        indicator.innerHTML = '<span>🔄 Reload <span id="countdown">300</span>s | Poll: ' + (deviceStatus === 'online' ? '✅ 30s' : '❌ OFF') + '</span>';
        document.body.appendChild(indicator);
        
        let startTime = Date.now();
        setInterval(() => {
          const remaining = 300 - Math.floor((Date.now() - startTime) / 1000);
          const countdownEl = document.getElementById('countdown');
          if (countdownEl) countdownEl.textContent = remaining > 0 ? remaining : 300;
        }, 1000);
      </script>

    </div>
  </div>
</x-app-layout>
