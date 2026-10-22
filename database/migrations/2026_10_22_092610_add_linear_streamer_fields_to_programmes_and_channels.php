<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->string('curation_type')->default('linear_streamer')->after('playback_mode'); // linear_streamer, external_live, zoniva_original
            $table->json('curation_keywords')->nullable()->after('curation_type');
            $table->string('fallback_video_id', 30)->nullable()->after('curation_keywords');
        });

        Schema::table('programmes', function (Blueprint $table) {
            $table->string('video_source')->default('youtube')->after('poster_image');
            $table->string('video_id', 50)->nullable()->after('video_source');
            $table->text('video_url')->nullable()->after('video_id');
            $table->unsignedInteger('play_order')->default(0)->after('duration_minutes');
            $table->string('presenter_or_artist', 150)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropColumn(['video_source', 'video_id', 'video_url', 'play_order', 'presenter_or_artist']);
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['curation_type', 'curation_keywords', 'fallback_video_id']);
        });
    }
};
