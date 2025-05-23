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
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['admin', 'member'])->default('member'); // admin, member
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->boolean('is_kicked')->default(false);
            $table->boolean('is_left')->default(false);
            $table->boolean('is_joined')->default(false);
            $table->boolean('is_invited')->default(false);
            $table->boolean('is_requested')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_reported')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_silenced')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_online')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
