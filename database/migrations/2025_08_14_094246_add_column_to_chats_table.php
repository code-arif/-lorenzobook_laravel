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
        Schema::table('chats', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('sender_id')->constrained('groups')->cascadeOnDelete(); // Add group_id
            $table->dropForeign(['receiver_id']); // Drop old foreign key
            $table->foreignId('receiver_id')->nullable()->change(); // Change receiver_id to nullable
        });

        // Re-add foreign key
        Schema::table('chats', function (Blueprint $table) {
            $table->foreign('receiver_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropForeign(['receiver_id']);
            $table->dropForeign(['group_id']);
            $table->foreignId('receiver_id')->nullable(false)->change();
            $table->dropColumn('group_id');
            $table->foreign('receiver_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
