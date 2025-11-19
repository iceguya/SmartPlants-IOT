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
    Schema::create('provisioning_tokens', function (Blueprint $t) {
        $t->id();
        $t->foreignId('user_id')->constrained()->onDelete('cascade');
        $t->string('token', 64)->unique();   // kode klaim, misal 32-40 char
        $t->string('planned_device_id')->nullable(); // boleh diisi dulu, opsional
        $t->string('name_hint')->nullable(); // hint nama device
        $t->string('location_hint')->nullable();
        $t->timestamp('expires_at');         // kadaluarsa
        $t->boolean('claimed')->default(false);
        $t->string('claimed_device_id')->nullable();
        $t->timestamp('claimed_at')->nullable();
        $t->timestamps();

        $t->index(['claimed','expires_at']);
        $t->index('user_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provisioning_tokens');
    }
};
