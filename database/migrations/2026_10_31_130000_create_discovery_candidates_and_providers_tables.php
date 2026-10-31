<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Content Providers
        if (! Schema::hasTable('content_providers')) {
            Schema::create('content_providers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('provider_type', 50)->default('youtube_channel')->index();
                $table->string('website_url', 1000)->nullable();
                $table->string('source_feed_url', 1000)->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('default_rights_basis', 50)->default('official_embed_permitted');
                $table->boolean('is_trusted')->default(false)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->text('notes')->nullable();
                $table->dateTime('last_scanned_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Discovery Candidates (Staging Area)
        if (! Schema::hasTable('discovery_candidates')) {
            Schema::create('discovery_candidates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_provider_id')->nullable()->constrained('content_providers')->nullOnDelete();
                $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('source_type', 50)->default('youtube_video')->index();
                $table->string('source_url', 1000);
                $table->string('source_identifier', 255)->index();
                $table->string('content_hash', 64)->index();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('creator_name')->nullable();
                $table->string('thumbnail_url', 1000)->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->string('aspect_ratio', 20)->default('16:9');
                $table->string('resolution', 20)->default('1080p');
                $table->string('content_rating', 20)->default('PG');
                $table->json('tags')->nullable();
                $table->dateTime('published_at')->nullable();
                $table->string('rights_basis', 50)->default('official_embed_permitted');
                $table->string('intake_status', 50)->default('staged')->index(); // staged, approved, rejected, duplicate
                $table->string('rejection_reason', 255)->nullable();
                $table->foreignId('approved_video_id')->nullable()->constrained('videos')->nullOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discovery_candidates');
        Schema::dropIfExists('content_providers');
    }
};
