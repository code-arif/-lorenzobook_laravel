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
        Schema::table('rooms', function (Blueprint $table) {
            $table->timestamp('user_one_muted_until')->nullable()->after('user_two_id');
            $table->timestamp('user_two_muted_until')->nullable()->after('user_one_muted_until');
            $table->timestamp('user_one_deleted_at')->nullable()->after('user_two_muted_until');
            $table->timestamp('user_two_deleted_at')->nullable()->after('user_one_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'user_one_muted_until',
                'user_two_muted_until',
                'user_one_deleted_at',
                'user_two_deleted_at',
            ]);
        });
    }
};
