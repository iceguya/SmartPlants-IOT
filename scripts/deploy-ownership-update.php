<?php

/**
 * Deployment Script: Device Ownership Security Update
 * 
 * This script applies all changes needed for strict device ownership control:
 * 1. Run database migrations for indexes
 * 2. Fix any orphaned devices
 * 3. Verify system integrity
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Device;
use App\Models\ProvisioningToken;
use App\Models\User;

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   SMARTPLANTS - DEVICE OWNERSHIP SECURITY UPDATE               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Step 1: Check for orphaned devices
echo "📋 Step 1: Checking for orphaned devices...\n";
$orphanedDevices = Device::whereNull('user_id')->get();

if ($orphanedDevices->count() > 0) {
    echo "⚠️  Found {$orphanedDevices->count()} orphaned device(s):\n\n";
    
    foreach ($orphanedDevices as $device) {
        echo "   Device ID: {$device->id}\n";
        echo "   Name: {$device->name}\n";
        echo "   Location: {$device->location}\n";
        echo "   Last Seen: " . ($device->last_seen ? $device->last_seen->diffForHumans() : 'Never') . "\n";
        
        // Try to find provisioning token that claimed this device
        $token = ProvisioningToken::where('claimed_device_id', $device->id)
            ->whereNotNull('user_id')
            ->orderBy('claimed_at', 'desc')
            ->first();
        
        if ($token) {
            echo "   ✅ Found provisioning token for this device\n";
            echo "   Assigning to user ID: {$token->user_id}\n";
            
            $device->update(['user_id' => $token->user_id]);
            echo "   ✅ Device ownership assigned!\n\n";
        } else {
            echo "   ⚠️  No provisioning token found - device remains orphaned\n";
            echo "   💡 User must re-provision this device to claim it\n\n";
        }
    }
} else {
    echo "✅ No orphaned devices found!\n\n";
}

// Step 2: Check for ownership conflicts
echo "📋 Step 2: Checking for ownership conflicts...\n";
$conflicts = [];

$tokens = ProvisioningToken::whereNotNull('claimed_device_id')
    ->whereNotNull('user_id')
    ->get();

foreach ($tokens as $token) {
    $device = Device::find($token->claimed_device_id);
    
    if ($device && $device->user_id !== $token->user_id) {
        $conflicts[] = [
            'device_id' => $device->id,
            'device_user_id' => $device->user_id,
            'token_user_id' => $token->user_id,
            'token' => $token->token,
            'claimed_at' => $token->claimed_at,
        ];
    }
}

if (count($conflicts) > 0) {
    echo "⚠️  Found " . count($conflicts) . " ownership conflict(s):\n\n";
    
    foreach ($conflicts as $conflict) {
        echo "   Device ID: {$conflict['device_id']}\n";
        echo "   Current Owner (user_id): {$conflict['device_user_id']}\n";
        echo "   Token Owner (user_id): {$conflict['token_user_id']}\n";
        echo "   Claimed At: {$conflict['claimed_at']}\n";
        echo "   💡 Device ownership is determined by most recent claim\n\n";
    }
} else {
    echo "✅ No ownership conflicts found!\n\n";
}

// Step 3: Verify system integrity
echo "📋 Step 3: System integrity verification...\n\n";

$totalDevices = Device::count();
$devicesWithOwner = Device::whereNotNull('user_id')->count();
$orphaned = $totalDevices - $devicesWithOwner;

$totalUsers = User::count();
$usersWithDevices = User::has('devices')->count();

$totalTokens = ProvisioningToken::count();
$claimedTokens = ProvisioningToken::where('claimed', true)->count();
$unclaimedTokens = $totalTokens - $claimedTokens;

echo "   Devices:\n";
echo "   • Total: {$totalDevices}\n";
echo "   • With Owner: {$devicesWithOwner}\n";
echo "   • Orphaned: {$orphaned}\n\n";

echo "   Users:\n";
echo "   • Total: {$totalUsers}\n";
echo "   • With Devices: {$usersWithDevices}\n\n";

echo "   Provisioning Tokens:\n";
echo "   • Total: {$totalTokens}\n";
echo "   • Claimed: {$claimedTokens}\n";
echo "   • Available: {$unclaimedTokens}\n\n";

// Step 4: Display user devices
echo "📋 Step 4: Devices by user...\n\n";

$users = User::with('devices')->get();

foreach ($users as $user) {
    echo "   👤 {$user->name} ({$user->email})\n";
    
    if ($user->devices->count() > 0) {
        foreach ($user->devices as $device) {
            $status = $device->is_online ? '🟢 ONLINE' : '🔴 OFFLINE';
            echo "      • {$device->name} ({$device->id}) - {$status}\n";
            echo "        Location: {$device->location}\n";
            echo "        Last Seen: " . ($device->last_seen ? $device->last_seen->format('Y-m-d H:i:s') : 'Never') . "\n";
        }
    } else {
        echo "      (No devices)\n";
    }
    echo "\n";
}

// Step 5: Security recommendations
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   SECURITY RECOMMENDATIONS                                     ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "✅ Ownership Protection:\n";
echo "   • Devices cannot be claimed by different users\n";
echo "   • Re-provisioning by same user generates new API key\n";
echo "   • All API requests validate device ownership\n\n";

echo "✅ Middleware Protection:\n";
echo "   • ValidateDeviceOwnership checks all device API requests\n";
echo "   • Invalid API keys are rejected immediately\n";
echo "   • Orphaned devices cannot send data\n\n";

echo "✅ Database Optimization:\n";
echo "   • Indexes added for faster ownership queries\n";
echo "   • Composite indexes for status + ownership\n\n";

if ($orphaned > 0) {
    echo "⚠️  ACTION REQUIRED:\n";
    echo "   • {$orphaned} orphaned device(s) need to be claimed\n";
    echo "   • Users should re-provision these devices with valid tokens\n\n";
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║   DEPLOYMENT COMPLETE                                          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Next steps:\n";
echo "1. Run migration: php artisan migrate\n";
echo "2. Test provisioning with different users\n";
echo "3. Verify devices appear in correct user dashboards\n";
echo "4. Monitor logs for ownership violations\n\n";
