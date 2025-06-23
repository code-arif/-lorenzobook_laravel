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
        Schema::create('channel_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')
                ->constrained('channels')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->text('message');
            $table->timestamp('sent_at')->useCurrent();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->string('attachment_url')->nullable();
            $table->string('attachment_type')->nullable(); // e.g., 'image', 'video', 'file'
            $table->string('attachment_name')->nullable(); // Original name of the attachment
            $table->string('attachment_size')->nullable(); // Size of the attachment in bytes
            $table->string('attachment_mime_type')->nullable(); // MIME type of the attachment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_messages');
    }
};
