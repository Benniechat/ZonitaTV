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
        Schema::create('comedy_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('channel_category', 50)->default('skit_creators')->index(); // standup_comedy, skit_creators, production_studios, animated_comedy
            $table->string('creator_or_brand')->nullable();
            $table->string('country_code', 10)->default('NGA')->index();
            $table->string('primary_language', 60)->default('Pidgin / English')->index();
            $table->json('sub_genres')->nullable();

            // Web & YouTube Presence
            $table->string('youtube_channel_name');
            $table->string('youtube_channel_url', 500);
            $table->string('youtube_handle', 100)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();

            // Catalogue & Content Profiling
            $table->integer('catalogue_size_est')->default(100);
            $table->boolean('has_full_length_episodes')->default(false);
            $table->tinyInteger('lead_priority')->default(2)->index(); // 1 = Viral Headliners / Top Tier, 2 = Active Popular, 3 = Catalog
            $table->json('sample_skit_titles')->nullable();

            // Rights & Syndication Governance
            $table->string('licensing_status', 50)->default('prospect_identified')->index(); // prospect_identified, contact_initiated, syndication_proposed, agreement_signed, active_broadcasting, declined
            $table->integer('approved_items_count')->default(0);
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
        Schema::dropIfExists('comedy_channels');
    }
};
