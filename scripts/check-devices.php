<?php
/**
 * Check Devices and Their Users
 * Quick diagnostic script
 * 
 * Usage: php scripts/check-devices.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Device;
use App\Models\User;

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║          📊 DEVICE & USER REPORT                      ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Users
$users = User::all();
echo "👥 USERS ({$users->count()}):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
foreach ($users as $user) {
    $deviceCount = $user->devices()->count();
    echo "ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Name: {$user->name}\n";
    echo "Devices: {$deviceCount}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
}
echo "\n";

// Devices
$devices = Device::all();
echo "🖥️  DEVICES ({$devices->count()}):\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
foreach ($devices as $device) {
    echo "ID: {$device->id}\n";
    echo "Name: {$device->name}\n";
    echo "Location: {$device->location}\n";
    echo "Status: {$device->status}\n";
    echo "User ID: " . ($device->user_id ?? '❌ NULL') . "\n";
    
    if ($device->user) {
        echo "User Email: {$device->user->email}\n";
    } else {
        echo "User Email: ❌ NO USER ASSIGNED\n";
    }
    
    echo "Last Seen: " . ($device->last_seen ?? 'Never') . "\n";
    echo "API Key: {$device->api_key}\n";
    echo "Sensors: " . $device->sensors()->count() . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
}
echo "\n";

// Orphaned devices
$orphaned = Device::whereNull('user_id')->get();
if ($orphaned->count() > 0) {
    echo "⚠️  ORPHANED DEVICES (no user_id):\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($orphaned as $device) {
        echo "  - {$device->id} ({$device->name})\n";
    }
    echo "\n❗ These devices WILL NOT appear in dashboard!\n";
    echo "Fix: php scripts/fix-device-user-id.php\n\n";
} else {
    echo "✅ No orphaned devices found!\n\n";
}

// Dashboard query simulation
echo "🔍 DASHBOARD QUERY SIMULATION:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
foreach ($users as $user) {
    echo "User: {$user->email}\n";
    $userDevices = Device::where('user_id', $user->id)->get();
    echo "Devices visible in dashboard: {$userDevices->count()}\n";
    
    if ($userDevices->isEmpty()) {
        echo "  ❌ No devices will appear!\n";
    } else {
        foreach ($userDevices as $device) {
            $online = $device->status === 'online' ? '🟢 ONLINE' : '🔴 OFFLINE';
            echo "  - {$device->name} ({$online})\n";
        }
    }
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
}
echo "\n";

echo "💡 RECOMMENDATIONS:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
if ($orphaned->count() > 0) {
    echo "❗ Run: php scripts/fix-device-user-id.php\n";
}
if ($devices->where('last_seen', null)->count() > 0) {
    echo "❗ Some devices never sent data - upload firmware to ESP8266\n";
}
if ($devices->where('status', 'offline')->count() > 0) {
    echo "❗ Some devices offline - check ESP8266 connection\n";
}
if ($orphaned->count() === 0 && $devices->where('last_seen', '!=', null)->count() > 0) {
    echo "✅ Everything looks good! Devices should appear in dashboard.\n";
}
echo "\n";
