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
        // 1. Content Sources (Approved Creators, YouTube Channels & Publishers)
        Schema::create('content_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('source_type', 50)->default('youtube_channel');
            $table->string('youtube_channel_id', 50)->nullable();
            $table->string('website_url')->nullable();
            $table->string('category_slug', 50)->nullable();
            $table->integer('trust_score')->default(80);
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Channel-Video Association & Rotation Tracking (Pivot)
        Schema::create('channel_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->integer('relevance_score')->default(80);
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_reserve')->default(false);
            $table->integer('broadcast_count')->default(0);
            $table->dateTime('last_broadcast_at')->nullable();
            $table->timestamps();

            $table->unique(['channel_id', 'video_id']);
            $table->index(['channel_id', 'is_approved']);
            $table->index(['channel_id', 'last_broadcast_at']);
        });

        // 3. Broadcast History (Telemetry & Playback Auditing)
        Schema::create('broadcast_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_start_at');
            $table->dateTime('scheduled_end_at');
            $table->dateTime('actual_start_at')->nullable();
            $table->dateTime('actual_end_at')->nullable();
            $table->string('status', 30)->default('completed'); // completed, interrupted, failed, skipped
            $table->integer('session_count')->default(0);
            $table->dateTime('created_at')->useCurrent();

            $table->index(['channel_id', 'scheduled_start_at']);
        });

        // 4. Schedule Blocks (Themed TV Programming Blocks)
        Schema::create('schedule_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('start_hour'); // 0 - 23
            $table->integer('end_hour');   // 0 - 23
            $table->string('preferred_format', 50)->default('general');
            $table->json('days_of_week')->nullable(); // [1,2,3,4,5,6,7]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['channel_id', 'start_hour', 'end_hour']);
        });

        // 5. System Alerts (Content Shortages, Playback Errors & Infrastructure Health)
        Schema::create('system_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('level', 20)->default('info'); // info, warning, critical
            $table->string('alert_type', 50);             // queue_shortage, circuit_breaker_trip, rights_expiry
            $table->text('message');
            $table->boolean('is_resolved')->default(false);
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['channel_id', 'is_resolved']);
            $table->index('alert_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_alerts');
        Schema::dropIfExists('schedule_blocks');
        Schema::dropIfExists('broadcast_history');
        Schema::dropIfExists('channel_videos');
        Schema::dropIfExists('content_sources');
    }
};
