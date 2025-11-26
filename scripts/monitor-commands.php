<?php
/**
 * Monitor Commands Script
 * Watch command status changes in real-time
 * 
 * Usage: php scripts/monitor-commands.php [device_id]
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Device;
use App\Models\Command;

$deviceId = $argv[1] ?? null;

if (!$deviceId) {
    echo "Usage: php scripts/monitor-commands.php [device_id]\n\n";
    echo "Available devices:\n";
    $devices = Device::all();
    foreach ($devices as $device) {
        echo "  - {$device->id} ({$device->name})\n";
    }
    exit(1);
}

$device = Device::find($deviceId);
if (!$device) {
    echo "❌ Device not found: {$deviceId}\n";
    exit(1);
}

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║          📊 COMMAND MONITOR                           ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";
echo "Device: {$device->name} ({$device->id})\n";
echo "Press Ctrl+C to stop monitoring...\n\n";

$lastCheck = null;

while (true) {
    system('clear'); // Linux/Mac - use 'cls' for Windows
    
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║          📊 COMMAND MONITOR                           ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n\n";
    echo "Device: {$device->name} ({$device->id})\n";
    echo "Time: " . now() . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Show pending commands
    echo "🟡 PENDING Commands:\n";
    $pending = Command::where('device_id', $device->id)
        ->where('status', 'pending')
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get();
    
    if ($pending->isEmpty()) {
        echo "  (none)\n";
    } else {
        foreach ($pending as $cmd) {
            echo "  [{$cmd->id}] {$cmd->command} | Created: {$cmd->created_at}\n";
        }
    }
    
    echo "\n🔵 SENT Commands (waiting ACK):\n";
    $sent = Command::where('device_id', $device->id)
        ->where('status', 'sent')
        ->orderBy('id', 'desc')
        ->limit(5)
        ->get();
    
    if ($sent->isEmpty()) {
        echo "  (none)\n";
    } else {
        foreach ($sent as $cmd) {
            echo "  [{$cmd->id}] {$cmd->command} | Sent: {$cmd->sent_at}\n";
        }
    }
    
    echo "\n✅ ACKNOWLEDGED Commands (recent):\n";
    $acked = Command::where('device_id', $device->id)
        ->where('status', 'ack')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();
    
    if ($acked->isEmpty()) {
        echo "  (none)\n";
    } else {
        foreach ($acked as $cmd) {
            $latency = $cmd->sent_at && $cmd->ack_at 
                ? $cmd->sent_at->diffInSeconds($cmd->ack_at) 
                : 'N/A';
            echo "  [{$cmd->id}] {$cmd->command} | ACK: {$cmd->ack_at} (Latency: {$latency}s)\n";
        }
    }
    
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Statistics:\n";
    echo "  Total pending:     " . Command::where('device_id', $device->id)->where('status', 'pending')->count() . "\n";
    echo "  Total sent:        " . Command::where('device_id', $device->id)->where('status', 'sent')->count() . "\n";
    echo "  Total acknowledged: " . Command::where('device_id', $device->id)->where('status', 'ack')->count() . "\n";
    echo "  Total commands:    " . Command::where('device_id', $device->id)->count() . "\n";
    
    echo "\nRefreshing in 2 seconds...\n";
    sleep(2);
}
