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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image_url')->nullable();
            $table->enum('channel_type', ['private', 'public'])->default('private');
            $table->string('invite_token')->nullable()->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
