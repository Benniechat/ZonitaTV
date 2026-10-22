<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->string('tagline', 255)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('banner_path', 255)->nullable();
            $table->string('broadcaster_name', 150)->nullable();
            $table->string('broadcaster_website', 255)->nullable();
            $table->string('official_destination_url', 500);
            $table->string('country_code', 3)->default('NGA')->index();
            $table->string('language_code', 10)->default('en')->index();

            // Playback mode: direct_stream, embed_player, external_destination, zoniva_original
            $table->enum('playback_mode', [
                'direct_stream',
                'embed_player',
                'external_destination',
                'zoniva_original',
            ])->default('external_destination');

            // Editorial flags
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('display_order')->default(0);

            // Technical diagnostics cache
            $table->timestamp('last_verified_at')->nullable();
            $table->boolean('is_technically_healthy')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
