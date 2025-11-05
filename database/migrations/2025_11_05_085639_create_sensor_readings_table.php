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
    Schema::create('sensor_readings', function (Blueprint $t) {
        $t->id();
        $t->unsignedBigInteger('sensor_id');
        $t->decimal('value', 10, 3);
        $t->timestamp('recorded_at')->useCurrent();
        $t->timestamps();

        $t->foreign('sensor_id')->references('id')->on('sensors')->cascadeOnDelete();
        $t->index(['sensor_id','recorded_at']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
