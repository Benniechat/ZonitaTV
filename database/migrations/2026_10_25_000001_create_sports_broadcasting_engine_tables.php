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
        // 1. Competitions / Leagues
        Schema::create('sports_competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->string('code', 20)->nullable()->index(); // e.g. PL, CL, PD, AFCON
            $table->string('country', 100)->nullable();
            $table->string('emblem_url', 500)->nullable();
            $table->string('provider', 50)->default('manual');
            $table->string('provider_id', 100)->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // 2. Football Teams / Clubs
        Schema::create('sports_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('short_name', 100)->nullable();
            $table->string('tla', 10)->nullable(); // Three-letter abbreviation (e.g. ARS, MCI)
            $table->string('slug', 160)->unique();
            $table->string('logo_url', 500)->nullable();
            $table->string('venue_name', 150)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('provider', 50)->default('manual');
            $table->string('provider_id', 100)->nullable()->index();
            $table->timestamps();
        });

        // 3. Normalized Matches & Fixtures
        Schema::create('sports_fixtures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('sports_competitions')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('sports_teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('sports_teams')->cascadeOnDelete();
            $table->string('provider', 50)->default('manual');
            $table->string('provider_id', 100)->nullable()->index();
            $table->dateTime('utc_kickoff')->index();
            $table->dateTime('lagos_kickoff')->index();
            $table->string('status', 30)->default('scheduled')->index(); // scheduled, timed, in_play, paused, finished, postponed, cancelled
            $table->integer('matchday')->nullable();
            $table->string('round', 50)->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->string('match_clock', 20)->nullable(); // e.g. '64\'', 'HT', 'FT'
            $table->string('venue', 150)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_live')->default(false)->index();
            $table->string('thumbnail_card_url', 500)->nullable();
            $table->dateTime('last_synced_at')->nullable();
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            $table->index(['competition_id', 'status']);
            $table->index(['utc_kickoff', 'status']);
        });

        // 4. Broadcaster Verification & Source Ledger
        Schema::create('fixture_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->constrained('sports_fixtures')->cascadeOnDelete();
            $table->string('broadcaster_name', 100)->default('SportyTV')->index();
            $table->string('source_url', 500)->nullable();
            $table->string('feed_endpoint', 500)->nullable();
            $table->string('operating_mode', 30)->default('mode_a_link'); // mode_a_link, mode_b_embed, mode_c_rebroadcast
            $table->string('verification_status', 40)->default('unverified')->index(); // unverified, discovered, confirmed, not_confirmed, source_unavailable, embedding_unavailable, redistribution_not_authorised, authorised_for_distribution, expired
            $table->string('territory', 10)->default('NGA');
            $table->boolean('allow_embedding')->default(false);
            $table->boolean('allow_redistribution')->default(false);
            $table->string('rights_reference', 150)->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['fixture_id', 'verification_status']);
        });

        // 5. Broadcast Rights Ledger
        Schema::create('sports_broadcast_rights', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->string('rights_holder', 150);
            $table->string('territory', 10)->default('NGA');
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->boolean('allow_mode_a')->default(true);
            $table->boolean('allow_mode_b')->default(false);
            $table->boolean('allow_mode_c')->default(false);
            $table->string('document_reference', 150)->nullable();
            $table->string('status', 30)->default('approved'); // approved, pending, revoked, expired
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 6. Broadcast Sessions (Control Room MCR State Machine)
        Schema::create('broadcast_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixture_id')->nullable()->constrained('sports_fixtures')->nullOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
            $table->string('title', 200);
            $table->string('operating_mode', 30)->default('mode_a_link');
            $table->string('backend', 30)->default('mock'); // obs, vmix, direct, mock
            $table->string('current_state', 40)->default('idle')->index(); // idle, monitoring, match_discovered, broadcast_unverified, broadcast_verified, rights_check_pending, rights_approved, preparing, scheduled, starting, on_air, recovering, fallback, stopping, completed, failed
            $table->string('input_source', 500)->nullable();
            $table->string('output_target', 500)->nullable();
            $table->dateTime('scheduled_start_at')->nullable();
            $table->dateTime('actual_start_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['current_state', 'scheduled_start_at']);
        });

        // 7. Broadcast State Transitions Audit Log
        Schema::create('broadcast_state_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('broadcast_sessions')->cascadeOnDelete();
            $table->string('from_state', 40);
            $table->string('to_state', 40);
            $table->string('trigger_job', 150)->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 8. Playback Health & Broadcast Failures Log
        Schema::create('broadcast_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->nullable()->constrained('broadcast_sessions')->nullOnDelete();
            $table->foreignId('fixture_id')->nullable()->constrained('sports_fixtures')->nullOnDelete();
            $table->string('failure_type', 50); // source_unavailable, player_error, auth_failure, network_drop, ingest_error, encoder_error
            $table->string('severity', 20)->default('warning'); // info, warning, critical, fatal
            $table->integer('retry_count')->default(0);
            $table->boolean('resolved')->default(false)->index();
            $table->boolean('fallback_activated')->default(false);
            $table->text('error_message');
            $table->json('diagnostic_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_failures');
        Schema::dropIfExists('broadcast_state_transitions');
        Schema::dropIfExists('broadcast_sessions');
        Schema::dropIfExists('sports_broadcast_rights');
        Schema::dropIfExists('fixture_broadcasts');
        Schema::dropIfExists('sports_fixtures');
        Schema::dropIfExists('sports_teams');
        Schema::dropIfExists('sports_competitions');
    }
};
