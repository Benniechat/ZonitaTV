<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100)->default('Primary HD');

            // Stream Types
            $table->enum('stream_type', [
                'hls_m3u8',
                'mp4_dash',
                'youtube_embed',
                'vimeo_embed',
                'iframe_generic',
                'custom_player',
            ])->default('hls_m3u8');

            $table->text('stream_url')->nullable();
            $table->text('embed_code')->nullable();
            $table->boolean('is_backup')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('bitrate_kbps')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_streams');
    }
};
