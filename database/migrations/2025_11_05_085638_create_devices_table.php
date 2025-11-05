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
        Schema::create('devices', function (Blueprint $t) {
            $t->string('id')->primary(); // contoh: 'esp-plant-01' atau UID lain
            $t->string('name');
            $t->string('location')->nullable();
            $t->string('api_key', 64)->unique();
            $t->enum('status', ['online','offline'])->default('offline');
            $t->timestamp('last_seen')->nullable();
            $t->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
