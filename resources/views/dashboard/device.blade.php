<x-app-layout>
  <div class="p-6">
    @if (session('status'))
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <h1 class="text-2xl font-bold mb-1">{{ $device->name }}</h1>
    <div class="text-sm text-gray-500 mb-6">{{ $device->id }} — {{ $device->location }}</div>

    <div class="grid md:grid-cols-3 gap-4 mb-6">
      <div class="p-4 border rounded">
        <div class="text-sm text-gray-500">Green Index</div>
        <div class="text-2xl font-semibold">
          {{ $analytics['green_index'] !== null ? $analytics['green_index'] : '-' }}
        </div>
        <div class="text-xs text-gray-500">Rumus: G / (R+G+B)</div>
      </div>
      <div class="p-4 border rounded">
        <div class="text-sm text-gray-500">Alerts</div>
        @if (count($analytics['alerts']))
          <ul class="list-disc pl-5 text-sm">
            @foreach ($analytics['alerts'] as $a)
              <li>{{ $a }}</li>
            @endforeach
          </ul>
        @else
          <div class="text-sm text-gray-500">Tidak ada alert.</div>
        @endif
      </div>
      <div class="p-4 border rounded">
        <div class="text-sm text-gray-500">Auto Action</div>
        <form method="POST" action="{{ url('/devices/'.$device->id.'/commands/water-on') }}" class="flex items-center gap-2">
          @csrf
          <input type="number" name="duration_sec" value="5" class="border rounded px-2 py-1 w-20">
          <button class="px-3 py-1 border rounded hover:bg-gray-50">Water On</button>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="grid md:grid-cols-2 gap-6">
      @foreach($device->sensors as $s)
      <div class="p-4 border rounded">
        <div class="font-semibold mb-2">{{ $s->label ?? strtoupper($s->type) }} ({{ $s->unit }})</div>
        <canvas id="chart-{{ $s->id }}" height="120"></canvas>
        @php
          $points = $s->readings->reverse();
          $labels = $points->pluck('recorded_at')->map(fn($d)=>$d->format('H:i'))->values();
          $values = $points->pluck('value')->values();
        @endphp
        <script>
          const ctx{{ $s->id }} = document.getElementById('chart-{{ $s->id }}');
          const data{{ $s->id }} = {
            labels: {!! $labels->toJson() !!},
            datasets: [{ label: '{{ $s->type }}', data: {!! $values->toJson() !!} }]
          };
          const chart{{ $s->id }} = new Chart(ctx{{ $s->id }}, { type: 'line', data: data{{ $s->id }} });

          // polling 10 detik untuk update titik terakhir
          setInterval(async () => {
            const res = await fetch('{{ url('/api/sensor-latest?sensor_id='.$s->id) }}');
            if (!res.ok) return;
            const j = await res.json();
            if (j && j.value !== undefined) {
              data{{ $s->id }}.labels.push(j.time);
              data{{ $s->id }}.datasets[0].data.push(j.value);
              if (data{{ $s->id }}.labels.length > 100) {
                data{{ $s->id }}.labels.shift();
                data{{ $s->id }}.datasets[0].data.shift();
              }
              chart{{ $s->id }}.update();
            }
          }, 10000);
        </script>

        @php
          $m = $analytics['metrics'][$s->type] ?? null;
        @endphp
        <div class="mt-3 text-sm text-gray-700">
          <div>Last: <b>{{ $m['last'] ?? '-' }}</b> @ {{ isset($m['last_at']) ? \Carbon\Carbon::parse($m['last_at'])->format('H:i') : '-' }}</div>
          <div>Min/Avg/Max (24h): {{ $m['min'] ?? '-' }} / {{ $m['avg'] ?? '-' }} / {{ $m['max'] ?? '-' }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</x-app-layout>
