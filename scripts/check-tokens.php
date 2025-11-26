<?php
/**
 * Check Provisioning Tokens
 * 
 * Usage: php scripts/check-tokens.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProvisioningToken;
use App\Models\Device;
use App\Models\User;

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║          🎫 PROVISIONING TOKENS REPORT                ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

$tokens = ProvisioningToken::all();

echo "Total Tokens: {$tokens->count()}\n\n";

foreach ($tokens as $token) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Token:           {$token->token}\n";
    echo "User ID:         " . ($token->user_id ?? '❌ NULL') . "\n";
    
    if ($token->user) {
        echo "User Email:      {$token->user->email}\n";
    } else {
        echo "User Email:      ❌ NO USER\n";
    }
    
    echo "Claimed:         " . ($token->claimed ? '✅ YES' : '⏸️ NO') . "\n";
    
    if ($token->claimed) {
        echo "Claimed Device:  {$token->claimed_device_id}\n";
        echo "Claimed At:      {$token->claimed_at}\n";
        
        // Check if device exists
        $device = Device::find($token->claimed_device_id);
        if ($device) {
            echo "Device Status:   ✅ EXISTS ({$device->status})\n";
            echo "Device Name:     {$device->name}\n";
            echo "Device User ID:  " . ($device->user_id ?? '❌ NULL') . "\n";
            
            if ($device->user_id === null) {
                echo "⚠️  WARNING: Device has NO user_id - will NOT appear in dashboard!\n";
            } else if ($device->user_id !== $token->user_id) {
                echo "⚠️  WARNING: Device user_id ({$device->user_id}) != Token user_id ({$token->user_id})\n";
            } else {
                echo "✅ Device correctly assigned to user\n";
            }
        } else {
            echo "Device Status:   ❌ NOT FOUND\n";
        }
    } else {
        echo "Claimed Device:  (not claimed yet)\n";
    }
    
    echo "Planned Device:  " . ($token->planned_device_id ?? 'None') . "\n";
    echo "Name Hint:       " . ($token->name_hint ?? 'None') . "\n";
    echo "Location Hint:   " . ($token->location_hint ?? 'None') . "\n";
    echo "Expires:         {$token->expires_at}";
    
    if ($token->expires_at->isPast()) {
        echo " ❌ EXPIRED\n";
    } else {
        echo " ✅ Valid\n";
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Summary
$claimed = $tokens->where('claimed', true)->count();
$unclaimed = $tokens->where('claimed', false)->count();
$withUser = $tokens->whereNotNull('user_id')->count();
$withoutUser = $tokens->whereNull('user_id')->count();
$expired = $tokens->filter(fn($t) => $t->expires_at->isPast())->count();

echo "📊 SUMMARY:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Total Tokens:     {$tokens->count()}\n";
echo "Claimed:          {$claimed}\n";
echo "Unclaimed:        {$unclaimed}\n";
echo "With User ID:     {$withUser}\n";
echo "Without User ID:  {$withoutUser}\n";
echo "Expired:          {$expired}\n\n";

// Check for problems
$problems = [];

// Tokens without user_id
if ($withoutUser > 0) {
    $problems[] = "❗ {$withoutUser} token(s) have no user_id - devices claimed with these will NOT appear in dashboard!";
}

// Expired unclaimed tokens
$expiredUnclaimed = $tokens->filter(fn($t) => !$t->claimed && $t->expires_at->isPast())->count();
if ($expiredUnclaimed > 0) {
    $problems[] = "⚠️  {$expiredUnclaimed} unclaimed token(s) have expired";
}

// Claimed devices without user_id
$claimedTokens = $tokens->where('claimed', true);
foreach ($claimedTokens as $token) {
    $device = Device::find($token->claimed_device_id);
    if ($device && $device->user_id === null) {
        $problems[] = "❗ Device {$device->id} (from token {$token->token}) has no user_id!";
    }
}

if (empty($problems)) {
    echo "✅ No problems found!\n\n";
} else {
    echo "⚠️  PROBLEMS FOUND:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($problems as $problem) {
        echo "{$problem}\n";
    }
    echo "\n🔧 Fix: php scripts/fix-device-user-id.php\n\n";
}
