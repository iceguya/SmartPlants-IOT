<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$devices = App\Models\Device::all();

echo "Total devices: " . $devices->count() . PHP_EOL;

foreach ($devices as $device) {
    echo PHP_EOL;
    echo "Device: {$device->name} ({$device->id})" . PHP_EOL;
    echo "Status (DB): {$device->status}" . PHP_EOL;
    echo "is_online (Accessor): " . ($device->is_online ? 'true' : 'false') . PHP_EOL;
    echo "Last seen: " . ($device->last_seen ? $device->last_seen->format('Y-m-d H:i:s') : 'never') . PHP_EOL;
    
    if ($device->last_seen) {
        $minutesAgo = $device->last_seen->diffInMinutes(now());
        echo "Minutes ago: {$minutesAgo}" . PHP_EOL;
        echo "Should be: " . ($minutesAgo < 5 ? 'online' : 'offline') . PHP_EOL;
    }
}
