<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stream_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_stream_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('http_status_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->boolean('is_reachable')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at')->useCurrent()->index();
        });

        Schema::create('viewing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->enum('event_type', [
                'page_view',
                'playback_started',
                'destination_click',
                'guide_view',
                'favourite_toggle',
            ])->index();
            $table->string('ip_country', 3)->default('NGA');
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('problem_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('issue_type', 50)->default('broken_stream'); // broken_stream, sound_issue, wrong_channel, inappropriate
            $table->text('description')->nullable();
            $table->enum('status', ['new', 'investigating', 'resolved', 'dismissed'])->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_reports');
        Schema::dropIfExists('viewing_events');
        Schema::dropIfExists('stream_checks');
    }
};
