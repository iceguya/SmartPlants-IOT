<?php
/**
 * Fix Devices Without User ID
 * 
 * This script will:
 * 1. Find all devices without user_id
 * 2. Assign them to first user (or specific user)
 * 3. Create new provisioning tokens with user_id
 * 
 * Usage: php scripts/fix-device-user-id.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Device;
use App\Models\User;
use App\Models\ProvisioningToken;
use Illuminate\Support\Str;

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║        🔧 FIX DEVICES WITHOUT USER_ID                 ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// Step 1: Check devices without user_id
echo "📋 Step 1: Checking devices...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$orphanedDevices = Device::whereNull('user_id')->get();

if ($orphanedDevices->isEmpty()) {
    echo "✅ All devices have user_id assigned!\n\n";
} else {
    echo "⚠️  Found {$orphanedDevices->count()} device(s) without user_id:\n\n";
    
    foreach ($orphanedDevices as $device) {
        echo "  - {$device->id} ({$device->name})\n";
    }
    echo "\n";

    // Get first user or create one
    $user = User::first();
    
    if (!$user) {
        echo "❌ No users found in database!\n";
        echo "Please create a user first:\n";
        echo "  php artisan tinker\n";
        echo "  > User::factory()->create(['email' => 'admin@example.com'])\n\n";
        exit(1);
    }

    echo "👤 Assigning devices to user: {$user->email} (ID: {$user->id})\n\n";
    echo "❓ Continue? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    
    if (trim($line) !== 'y') {
        echo "❌ Cancelled.\n";
        exit(0);
    }

    // Update devices
    $updated = 0;
    foreach ($orphanedDevices as $device) {
        $device->update(['user_id' => $user->id]);
        echo "  ✅ Updated: {$device->id}\n";
        $updated++;
    }
    
    echo "\n✅ Updated {$updated} device(s)!\n\n";
}

// Step 2: Check provisioning tokens
echo "📋 Step 2: Checking provisioning tokens...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$orphanedTokens = ProvisioningToken::whereNull('user_id')->get();

if ($orphanedTokens->isEmpty()) {
    echo "✅ All tokens have user_id assigned!\n\n";
} else {
    echo "⚠️  Found {$orphanedTokens->count()} token(s) without user_id:\n\n";
    
    foreach ($orphanedTokens as $token) {
        $status = $token->claimed ? '🔒 CLAIMED' : '🔓 UNCLAIMED';
        echo "  - {$token->token} ({$status})\n";
    }
    echo "\n";

    $user = User::first();
    echo "👤 Assigning tokens to user: {$user->email} (ID: {$user->id})\n\n";
    
    // Update tokens
    $updated = 0;
    foreach ($orphanedTokens as $token) {
        $token->update(['user_id' => $user->id]);
        echo "  ✅ Updated: {$token->token}\n";
        $updated++;
    }
    
    echo "\n✅ Updated {$updated} token(s)!\n\n";
}

// Step 3: Create new provisioning token
echo "📋 Step 3: Create new provisioning token?\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$user = User::first();
echo "Create new token for: {$user->email}? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);

if (trim($line) === 'y') {
    $newToken = ProvisioningToken::create([
        'token' => Str::random(36),
        'user_id' => $user->id,
        'planned_device_id' => 'esp-auto-' . time(),
        'name_hint' => 'ESP8266 SmartPlant (Auto)',
        'location_hint' => 'Home',
        'expires_at' => now()->addDays(30),
        'claimed' => false,
    ]);

    echo "\n✅ New token created!\n\n";
    echo "╔════════════════════════════════════════════════════════╗\n";
    echo "║          📋 NEW PROVISIONING TOKEN                    ║\n";
    echo "╚════════════════════════════════════════════════════════╝\n\n";
    echo "Token:       {$newToken->token}\n";
    echo "User:        {$user->email}\n";
    echo "Device ID:   {$newToken->planned_device_id}\n";
    echo "Name:        {$newToken->name_hint}\n";
    echo "Location:    {$newToken->location_hint}\n";
    echo "Expires:     {$newToken->expires_at}\n";
    echo "Status:      UNCLAIMED\n\n";
    echo "📝 Update ESP8266 code:\n";
    echo "const char* provisionToken = \"{$newToken->token}\";\n\n";
}

// Step 4: Summary
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║          ✅ SUMMARY                                    ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

$totalDevices = Device::count();
$devicesWithUser = Device::whereNotNull('user_id')->count();
$totalTokens = ProvisioningToken::count();
$tokensWithUser = ProvisioningToken::whereNotNull('user_id')->count();

echo "Devices:\n";
echo "  Total:        {$totalDevices}\n";
echo "  With user_id: {$devicesWithUser}\n";
echo "  Missing:      " . ($totalDevices - $devicesWithUser) . "\n\n";

echo "Tokens:\n";
echo "  Total:        {$totalTokens}\n";
echo "  With user_id: {$tokensWithUser}\n";
echo "  Missing:      " . ($totalTokens - $tokensWithUser) . "\n\n";

if ($devicesWithUser === $totalDevices && $tokensWithUser === $totalTokens) {
    echo "✅ All devices and tokens have user_id assigned!\n";
    echo "✅ Devices should now appear in dashboard!\n\n";
} else {
    echo "⚠️  Some items still missing user_id\n";
    echo "Run this script again to fix them.\n\n";
}

echo "🔄 Next steps:\n";
echo "  1. Login to dashboard: https://kurokana.alwaysdata.net\n";
echo "  2. Check 'Connected Devices' section\n";
echo "  3. Upload new token to ESP8266 if created\n";
echo "  4. Device should appear after sending first data\n\n";
