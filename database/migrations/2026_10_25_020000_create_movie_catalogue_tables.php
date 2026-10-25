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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->nullable()->constrained('videos')->nullOnDelete();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->string('slug')->unique();
            $table->integer('release_year')->nullable()->index();
            $table->string('genre')->nullable()->index();
            $table->string('language', 10)->default('en')->index();
            $table->integer('runtime_minutes')->default(90);
            $table->integer('duration_seconds')->default(5400);
            $table->text('synopsis')->nullable();
            $table->string('poster_url', 1000)->nullable();
            $table->string('backdrop_url', 1000)->nullable();

            // Source Metadata
            $table->string('source_provider', 50)->index(); // internet_archive, library_of_congress, wikimedia_commons, youtube_official, nollywood_distributor, independent_filmmaker
            $table->string('source_url', 1000);
            $table->string('source_identifier', 255)->nullable()->index();
            $table->string('stream_url', 1000)->nullable();
            $table->string('media_format', 50)->default('youtube_embed');

            // Rights & Legal Verification
            $table->string('rights_classification', 50)->default('under_review')->index(); // public_domain, creative_commons, authorized_distributor, direct_licence, under_review, restricted
            $table->string('licence_name', 255)->default('Public Domain Mark 1.0');
            $table->string('licence_reference', 500)->nullable();
            $table->boolean('commercial_use_permitted')->default(true);
            $table->boolean('redistribution_permitted')->default(true);
            $table->boolean('public_performance_permitted')->default(true);
            $table->json('permitted_territories')->nullable();
            $table->json('blocked_territories')->nullable();
            $table->text('attribution_requirements')->nullable();
            $table->dateTime('licence_expires_at')->nullable();

            // Playback & Verification
            $table->string('playback_method', 50)->default('embed_player'); // embed_player, direct_stream
            $table->string('verification_status', 50)->default('pending_verification')->index(); // verified, pending_verification, failed_verification, rights_rejected, expired
            $table->dateTime('last_verified_at')->nullable();
            $table->boolean('is_schedulable')->default(false)->index();
            $table->string('target_channel_slug', 100)->nullable()->index();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
