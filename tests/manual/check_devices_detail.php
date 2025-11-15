<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$devices = App\Models\Device::all();

foreach ($devices as $device) {
    echo PHP_EOL;
    echo "════════════════════════════════════════" . PHP_EOL;
    echo "Device: {$device->name} ({$device->id})" . PHP_EOL;
    echo "Status (DB): {$device->status}" . PHP_EOL;
    echo "is_online: " . ($device->is_online ? '✅ ONLINE' : '❌ OFFLINE') . PHP_EOL;
    echo "Last seen: " . ($device->last_seen ? $device->last_seen->format('Y-m-d H:i:s') : 'never') . PHP_EOL;
    
    if ($device->last_seen) {
        $minutesAgo = $device->last_seen->diffInMinutes(now());
        $secondsAgo = $device->last_seen->diffInSeconds(now());
        echo "Last activity: {$minutesAgo} min {$secondsAgo} sec ago" . PHP_EOL;
    }
    
    // Cek sensor readings terbaru
    echo PHP_EOL . "Recent sensor readings:" . PHP_EOL;
    $readings = App\Models\SensorReading::whereHas('sensor', function($q) use ($device) {
        $q->where('device_id', $device->id);
    })->orderBy('recorded_at', 'desc')->limit(5)->get();
    
    if ($readings->count() > 0) {
        foreach ($readings as $r) {
            echo "  - {$r->sensor->type}: {$r->value} @ {$r->recorded_at->format('H:i:s')}" . PHP_EOL;
        }
    } else {
        echo "  No readings found." . PHP_EOL;
    }
}

echo PHP_EOL . "════════════════════════════════════════" . PHP_EOL;
echo "Total devices: " . $devices->count() . PHP_EOL;
