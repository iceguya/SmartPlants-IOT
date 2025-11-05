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
    Schema::create('commands', function (Blueprint $t) {
        $t->id();
        $t->string('device_id');
        $t->enum('command', ['water_on','water_off','relay_on','relay_off']);
        $t->json('params')->nullable();        // {"duration_sec":5}
        $t->enum('status', ['pending','sent','ack','failed'])->default('pending');
        $t->timestamp('created_at')->useCurrent();
        $t->timestamp('sent_at')->nullable();
        $t->timestamp('ack_at')->nullable();

        $t->foreign('device_id')->references('id')->on('devices')->cascadeOnDelete();
        $t->index(['device_id','status']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commands');
    }
};
