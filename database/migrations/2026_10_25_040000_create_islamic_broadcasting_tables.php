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
        // 1. Islamic Content Providers Directory
        Schema::create('islamic_content_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('provider_type', 50)->default('production_company')->index(); // production_company, television_network, independent_filmmaker, educational_institute, individual_scholar, devotional_nasheed, quran_broadcaster
            $table->json('content_categories')->nullable();
            $table->string('country_code', 10)->default('NGA')->index();
            $table->string('headquarters_city')->nullable();
            $table->string('primary_language', 50)->default('Hausa')->index();
            $table->json('supported_subtitles')->nullable();

            // Web & Presence
            $table->string('youtube_channel_name');
            $table->string('youtube_channel_url', 500);
            $table->string('youtube_handle', 100)->nullable();
            $table->string('official_website', 500)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();

            // Catalogue & Content Profiling
            $table->integer('catalogue_size_est')->default(25);
            $table->string('recent_upload_activity', 50)->default('weekly'); // daily, weekly, monthly, archival
            $table->json('sample_titles')->nullable();

            // Rights, Classification & Verification
            $table->string('source_classification', 50)->default('DISCOVERY_ONLY')->index(); // OFFICIAL_CONTENT_OWNER, AUTHORIZED_DISTRIBUTOR, LICENSED_STREAMING_PROVIDER, OPENLY_LICENSED_SOURCE, PUBLIC_DOMAIN_CANDIDATE, OFFICIAL_EMBED_ONLY, DISCOVERY_ONLY, UNVERIFIED_SOURCE, RESTRICTED_SOURCE
            $table->string('verification_status', 50)->default('VERIFIED_OWNER')->index(); // VERIFIED_OWNER, AUTHORIZED_PARTNER, PENDING_REVIEW, UNVERIFIED
            $table->tinyInteger('confidence_score')->default(85)->index(); // 0 to 100
            $table->string('licensing_status', 50)->default('PROSPECT_IDENTIFIED')->index(); // PROSPECT_IDENTIFIED, OUTREACH_INITIATED, NEGOTIATING, AGREEMENT_SIGNED, ACTIVE_BROADCASTING, DECLINED

            // Operational Clearance
            $table->boolean('commercial_use_permitted')->default(false);
            $table->boolean('continuous_24_7_permitted')->default(false);
            $table->json('permitted_territories')->nullable();
            $table->json('target_channels')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('last_verified_at')->nullable();

            $table->timestamps();
        });

        // 2. Licensing Outreach & Request Register
        Schema::create('islamic_licensing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('islamic_content_providers')->cascadeOnDelete();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('subject');
            $table->string('proposal_model', 50)->default('non_exclusive_streaming'); // non_exclusive_streaming, revenue_sharing, promotional_distribution, official_embed
            $table->json('proposed_territories')->nullable();
            $table->integer('licence_term_months')->default(12);
            $table->string('status', 50)->default('draft')->index(); // draft, sent, in_review, approved, rejected
            $table->dateTime('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('islamic_licensing_requests');
        Schema::dropIfExists('islamic_content_providers');
    }
};
