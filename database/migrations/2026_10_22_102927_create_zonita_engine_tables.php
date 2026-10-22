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
        // 1. Channel Source Rules & Scheduling Configuration
        Schema::create('channel_source_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->json('search_keywords')->nullable();
            $table->json('excluded_keywords')->nullable();
            $table->json('approved_publishers')->nullable();
            $table->json('preferred_formats')->nullable();
            $table->integer('min_duration_minutes')->default(5);
            $table->integer('max_duration_minutes')->default(90);
            $table->string('preferred_language', 10)->default('en');
            $table->integer('repeat_cooldown_hours')->default(6);
            $table->integer('min_reserve_count')->default(5);
            $table->boolean('allow_evergreen')->default(true);
            $table->string('scheduling_mode', 20)->default('hybrid'); // dynamic, fixed, hybrid
            $table->string('timezone', 50)->default('Africa/Lagos');
            $table->timestamps();
        });

        // 2. Verified Video Pool & Eligibility Ledger
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_id', 32)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('channel_title')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->string('privacy_status', 20)->default('public');
            $table->boolean('is_embeddable')->default(true);
            $table->string('availability_status', 32)->default('discovered');
            $table->integer('confidence_score')->default(100);
            $table->integer('consecutive_failures')->default(0);
            $table->dateTime('cooldown_until')->nullable();
            $table->string('rights_basis', 50)->default('official_embed_permitted');
            $table->string('format_type', 50)->default('feature');
            $table->string('category_slug', 50)->nullable();
            $table->boolean('is_evergreen')->default(false);
            $table->integer('relevance_score')->default(100);
            $table->dateTime('last_verified_at')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['availability_status', 'confidence_score']);
            $table->index('category_slug');
        });

        // 3. Video Verification Audit Logs
        Schema::create('video_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('check_type', 32); // oembed, api, player_event
            $table->integer('http_code')->nullable();
            $table->string('status_result', 32);
            $table->json('raw_details')->nullable();
            $table->dateTime('created_at')->useCurrent();
        });

        // 4. Playback Failure Circuit Breaker Logs
        Schema::create('playback_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('error_code', 32);
            $table->string('session_id', 100)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('ip_country', 10)->nullable();
            $table->foreignId('recovered_with_video_id')->nullable()->constrained('videos')->nullOnDelete();
            $table->dateTime('created_at')->useCurrent();
        });

        // 5. Dynamic & Rolling Schedule Entries
        Schema::create('schedule_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('format_type', 50)->default('feature');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_seconds');
            $table->integer('play_order')->default(1);
            $table->string('status', 20)->default('scheduled'); // scheduled, playing, completed, skipped, failed, fallback
            $table->boolean('is_fallback')->default(false);
            $table->timestamps();

            $table->index(['channel_id', 'start_time']);
            $table->index(['channel_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_entries');
        Schema::dropIfExists('playback_failures');
        Schema::dropIfExists('video_verification_logs');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('channel_source_rules');
    }
};
