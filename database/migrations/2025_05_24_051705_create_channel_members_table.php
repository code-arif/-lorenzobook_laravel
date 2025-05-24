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
        Schema::create('channel_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('channels')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_active')->default(true);


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_members');
    }
};
