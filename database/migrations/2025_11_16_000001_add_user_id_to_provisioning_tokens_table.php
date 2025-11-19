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
        Schema::table('provisioning_tokens', function (Blueprint $table) {
            // Cek apakah kolom user_id SUDAH ADA?
            if (!Schema::hasColumn('provisioning_tokens', 'user_id')) {
                // Jika BELUM ada, baru tambahkan
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('provisioning_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('provisioning_tokens', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
