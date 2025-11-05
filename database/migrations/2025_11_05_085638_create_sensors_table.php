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
    Schema::create('sensors', function (Blueprint $t) {
        $t->id();
        $t->string('device_id');
        $t->enum('type', ['soil','temp','hum','color_r','color_g','color_b']);
        $t->string('unit',16)->nullable();
        $t->string('label',100)->nullable();
        $t->timestamps();

        $t->foreign('device_id')->references('id')->on('devices')->cascadeOnDelete();
        $t->unique(['device_id','type']); // unik per device
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};
