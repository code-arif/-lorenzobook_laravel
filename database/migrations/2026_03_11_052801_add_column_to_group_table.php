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
        Schema::table('groups', function (Blueprint $table) {
            if (! Schema::hasColumn('groups', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('created_by');
            }
            if (! Schema::hasColumn('groups', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('last_activity_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['last_activity_at', 'is_active']);
        });
    }
};
