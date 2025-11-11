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
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->boolean('enabled')->default(true);
            $table->string('condition_type'); // 'soil_low', 'temp_high', 'scheduled'
            $table->decimal('threshold_value', 8, 2)->nullable();
            $table->string('action'); // 'water_on'
            $table->integer('action_duration')->default(5); // seconds
            $table->integer('cooldown_minutes')->default(60); // prevent spam
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
