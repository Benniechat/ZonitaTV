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
        Schema::table('channels', function (Blueprint $table) {
            if (! Schema::hasColumn('channels', 'channel_number')) {
                $table->string('channel_number', 10)->nullable()->index()->after('slug');
            }
            if (! Schema::hasColumn('channels', 'brand_color')) {
                $table->string('brand_color', 20)->default('#E50914')->after('banner_path');
            }
            if (! Schema::hasColumn('channels', 'watermark_logo_path')) {
                $table->string('watermark_logo_path', 500)->nullable()->after('brand_color');
            }
            if (! Schema::hasColumn('channels', 'station_ident_video_id')) {
                $table->string('station_ident_video_id', 32)->nullable()->after('fallback_video_id');
            }
            if (! Schema::hasColumn('channels', 'is_virtual')) {
                $table->boolean('is_virtual')->default(false)->index()->after('playback_mode');
            }
        });

        Schema::table('channel_source_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('channel_source_rules', 'priority_level')) {
                $table->string('priority_level', 30)->default('balanced')->after('scheduling_mode');
            }
            if (! Schema::hasColumn('channel_source_rules', 'interstitial_ident_interval')) {
                $table->integer('interstitial_ident_interval')->default(4)->after('priority_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn([
                'channel_number',
                'brand_color',
                'watermark_logo_path',
                'station_ident_video_id',
                'is_virtual',
            ]);
        });

        Schema::table('channel_source_rules', function (Blueprint $table) {
            $table->dropColumn([
                'priority_level',
                'interstitial_ident_interval',
            ]);
        });
    }
};
