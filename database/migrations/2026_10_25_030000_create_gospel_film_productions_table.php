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
        Schema::create('gospel_film_productions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('founder_or_minister')->nullable();
            $table->string('ministry_type', 50)->default('drama_ministry')->index();
            $table->string('country_code', 10)->default('NGA')->index();
            $table->string('headquarters_city')->nullable();
            $table->string('primary_language', 50)->default('Yoruba with English Subtitles')->index();
            $table->json('sub_genres')->nullable();

            // Web & YouTube Presence
            $table->string('youtube_channel_name');
            $table->string('youtube_channel_url', 500);
            $table->string('youtube_handle', 100)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();

            // Catalogue & Content Profiling
            $table->integer('catalogue_size_est')->default(20);
            $table->boolean('has_full_length_movies')->default(true);
            $table->boolean('has_english_subtitles')->default(true);
            $table->tinyInteger('lead_priority')->default(3)->index(); // 1 = Top Priority (Full-length active), 2 = High, 3 = Standard
            $table->json('sample_feature_titles')->nullable();

            // Rights & Syndication Governance
            $table->string('licensing_status', 50)->default('prospect_identified')->index(); // prospect_identified, contact_initiated, syndication_proposed, agreement_signed, active_broadcasting, declined
            $table->integer('approved_films_count')->default(0);
            $table->boolean('commercial_use_approved')->default(false);
            $table->boolean('continuous_24_7_approved')->default(false);
            $table->json('permitted_territories')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('last_scanned_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gospel_film_productions');
    }
};
