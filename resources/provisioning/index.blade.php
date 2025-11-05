<x-app-layout>
  <div class="p-6">
    @if (session('status'))
      <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
    @endif

    <h1 class="text-2xl font-bold mb-4">Provisioning</h1>

    <form method="POST" action="/provisioning/generate" class="grid md:grid-cols-4 gap-3 p-4 border rounded mb-6">
      @csrf
      <input name="planned_device_id" class="border rounded px-2 py-1" placeholder="Planned device id (opsional)">
      <input name="name_hint" class="border rounded px-2 py-1" placeholder="Name hint">
      <input name="location_hint" class="border rounded px-2 py-1" placeholder="Location hint">
      <div class="flex gap-2">
        <input name="ttl_hours" type="number" class="border rounded px-2 py-1 w-24" value="12">
        <button class="px-3 py-1 border rounded hover:bg-gray-50">Generate</button>
      </div>
    </form>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead><tr class="border-b">
          <th class="text-left p-2">Token</th>
          <th class="text-left p-2">Planned</th>
          <th class="text-left p-2">Expires</th>
          <th class="text-left p-2">Claimed</th>
          <th class="text-left p-2">Claimed Device</th>
        </tr></thead>
        <tbody>
        @foreach ($tokens as $t)
          <tr class="border-b">
            <td class="p-2 font-mono">{{ $t->token }}</td>
            <td class="p-2">{{ $t->planned_device_id }}</td>
            <td class="p-2">{{ $t->expires_at }}</td>
            <td class="p-2">{{ $t->claimed ? 'yes' : 'no' }}</td>
            <td class="p-2">{{ $t->claimed_device_id }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
