<?php
/**
 * Test Water Command Script
 * 
 * Usage:
 * php scripts/test-water-command.php [device_id] [duration]
 * 
 * Example:
 * php scripts/test-water-command.php esp-plant-01 10
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Device;
use App\Models\Command;

// Get arguments
$deviceId = $argv[1] ?? null;
$duration = $argv[2] ?? 5;

if (!$deviceId) {
    echo "❌ Error: Device ID required\n";
    echo "Usage: php scripts/test-water-command.php [device_id] [duration]\n";
    echo "\n📋 Available devices:\n";
    
    $devices = Device::all();
    foreach ($devices as $device) {
        echo "  - {$device->id} ({$device->name})\n";
    }
    exit(1);
}

// Find device
$device = Device::find($deviceId);
if (!$device) {
    echo "❌ Error: Device not found: {$deviceId}\n";
    exit(1);
}

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║          🔧 WATER COMMAND TEST                        ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";
echo "📋 Device Info:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ID:          {$device->id}\n";
echo "Name:        {$device->name}\n";
echo "Location:    {$device->location}\n";
echo "API Key:     {$device->api_key}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Check pending commands
$pendingCount = Command::where('device_id', $device->id)
    ->where('status', 'pending')
    ->count();

if ($pendingCount > 0) {
    echo "⚠️  Warning: {$pendingCount} pending commands already exist\n\n";
}

// Create command
$cmd = Command::create([
    'device_id' => $device->id,
    'command' => 'water_on',
    'params' => ['duration_sec' => (int)$duration],
    'status' => 'pending',
    'created_at' => now(),
]);

echo "✅ Water command created!\n\n";
echo "📤 Command Details:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "ID:          {$cmd->id}\n";
echo "Command:     {$cmd->command}\n";
echo "Duration:    {$duration} seconds\n";
echo "Status:      {$cmd->status}\n";
echo "Created:     {$cmd->created_at}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "🔍 Checking all pending commands:\n";
$allPending = Command::where('device_id', $device->id)
    ->where('status', 'pending')
    ->orderBy('id')
    ->get();

foreach ($allPending as $c) {
    echo "  - [{$c->id}] {$c->command} | Created: {$c->created_at}\n";
}

echo "\n⏳ Next steps:\n";
echo "  1. ESP8266 will poll /api/commands/next every 10 seconds\n";
echo "  2. Command status will change: pending → sent → ack\n";
echo "  3. Monitor ESP8266 Serial output for execution confirmation\n";
echo "  4. Check logs: tail -f storage/logs/laravel.log\n\n";

// Show how to check command status
echo "🔄 To check command status, run:\n";
echo "  php artisan tinker\n";
echo "  > \\App\\Models\\Command::find({$cmd->id})\n\n";
