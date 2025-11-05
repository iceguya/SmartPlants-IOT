<x-app-layout>
  <div class="p-6">
    @if (session('status'))
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
    @endif
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Devices</h1>
      <a href="/provisioning" class="text-sm underline">Provisioning</a>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($devices as $d)
      <a href="{{ url('/devices/'.$d->id) }}" class="block p-4 rounded border hover:bg-gray-50">
        <div class="text-lg font-semibold">{{ $d->name }}</div>
        <div class="text-sm text-gray-500">{{ $d->id }} — {{ $d->location }}</div>
        <div class="mt-2">
          <span class="px-2 py-1 text-xs rounded {{ $d->status==='online'?'bg-green-100 text-green-800':'bg-gray-100 text-gray-800' }}">
            {{ $d->status }}
          </span>
          <span class="ml-2 text-xs text-gray-500">
            last seen: {{ optional($d->last_seen)->diffForHumans() ?? '-' }}
          </span>
        </div>
      </a>
      @endforeach
    </div>
  </div>
</x-app-layout>
