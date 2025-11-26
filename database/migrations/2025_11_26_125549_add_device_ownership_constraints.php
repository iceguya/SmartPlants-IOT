<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // Add index on user_id for faster queries
            $table->index('user_id', 'devices_user_id_index');
            
            // Add composite index for common queries
            $table->index(['user_id', 'status'], 'devices_user_status_index');
            
            // Add index on last_seen for online/offline checks
            $table->index('last_seen', 'devices_last_seen_index');
        });

        Schema::table('provisioning_tokens', function (Blueprint $table) {
            // Add index on user_id
            $table->index('user_id', 'tokens_user_id_index');
            
            // Add index on claimed_device_id for ownership tracking
            $table->index('claimed_device_id', 'tokens_claimed_device_index');
            
            // Add composite index for token validation queries
            $table->index(['token', 'claimed', 'expires_at'], 'tokens_validation_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropIndex('devices_user_id_index');
            $table->dropIndex('devices_user_status_index');
            $table->dropIndex('devices_last_seen_index');
        });

        Schema::table('provisioning_tokens', function (Blueprint $table) {
            $table->dropIndex('tokens_user_id_index');
            $table->dropIndex('tokens_claimed_device_index');
            $table->dropIndex('tokens_validation_index');
        });
    }
};
