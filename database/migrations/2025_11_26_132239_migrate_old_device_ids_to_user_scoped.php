<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Automatically converts old device IDs (raw chip IDs like "62563")
     * to new user-scoped format ("user_1_chip_62563")
     */
    public function up(): void
    {
        // Get all devices that don't have user-scoped format
        $devices = DB::table('devices')
            ->whereNotNull('user_id')
            ->whereRaw("id NOT LIKE 'user_%'")
            ->get();

        if ($devices->isEmpty()) {
            echo "✅ No old device IDs found. All devices are already in new format.\n";
            return;
        }

        echo "📋 Found {$devices->count()} device(s) to migrate to user-scoped IDs...\n";

        foreach ($devices as $device) {
            $oldId = $device->id;
            $newId = "user_{$device->user_id}_chip_{$oldId}";

            echo "   • {$oldId} → {$newId}\n";

            // Create new device with user-scoped ID
            DB::table('devices')->insert([
                'id' => $newId,
                'name' => $device->name,
                'location' => $device->location,
                'api_key' => Str::random(40), // Generate new API key for security
                'status' => $device->status,
                'last_seen' => $device->last_seen,
                'user_id' => $device->user_id,
                'created_at' => $device->created_at,
                'updated_at' => now(),
            ]);

            // Update related sensors
            DB::table('sensors')
                ->where('device_id', $oldId)
                ->update(['device_id' => $newId]);

            // Update related commands
            DB::table('commands')
                ->where('device_id', $oldId)
                ->update(['device_id' => $newId]);

            // Update provisioning tokens
            DB::table('provisioning_tokens')
                ->where('claimed_device_id', $oldId)
                ->update(['claimed_device_id' => $newId]);

            // Delete old device
            DB::table('devices')->where('id', $oldId)->delete();
        }

        echo "✅ Successfully migrated {$devices->count()} device(s) to user-scoped format.\n";
        echo "⚠️  Note: ESP8266 devices will need to re-provision on next startup.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot automatically reverse this migration
        // Manual intervention required if rollback is needed
        echo "⚠️  WARNING: This migration cannot be automatically reversed.\n";
        echo "   Device IDs have been changed from raw chip IDs to user-scoped format.\n";
        echo "   Manual database restoration required if rollback is needed.\n";
    }
};
