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
        Schema::table('content_rights', function (Blueprint $table) {
            $table->foreignId('channel_id')->nullable()->change();

            if (! Schema::hasColumn('content_rights', 'video_id')) {
                $table->foreignId('video_id')->nullable()->after('channel_id')->constrained('videos')->nullOnDelete();
            }
            if (! Schema::hasColumn('content_rights', 'movie_id')) {
                $table->foreignId('movie_id')->nullable()->after('video_id')->constrained('movies')->nullOnDelete();
            }
            if (! Schema::hasColumn('content_rights', 'series_id')) {
                $table->foreignId('series_id')->nullable()->after('movie_id')->constrained('series')->nullOnDelete();
            }
            if (! Schema::hasColumn('content_rights', 'classification')) {
                $table->string('classification', 50)->default('authorized_embed')->after('contract_reference')->index();
            }
            if (! Schema::hasColumn('content_rights', 'licence_name')) {
                $table->string('licence_name', 255)->nullable()->after('classification');
            }
            if (! Schema::hasColumn('content_rights', 'licensing_agreement_text')) {
                $table->text('licensing_agreement_text')->nullable()->after('licence_name');
            }
            if (! Schema::hasColumn('content_rights', 'attribution_requirements')) {
                $table->text('attribution_requirements')->nullable()->after('licensing_agreement_text');
            }
            if (! Schema::hasColumn('content_rights', 'commercial_terms')) {
                $table->json('commercial_terms')->nullable()->after('attribution_requirements');
            }
            if (! Schema::hasColumn('content_rights', 'is_exclusive')) {
                $table->boolean('is_exclusive')->default(false)->after('commercial_terms');
            }
            if (! Schema::hasColumn('content_rights', 'auto_delist_on_expiry')) {
                $table->boolean('auto_delist_on_expiry')->default(true)->after('is_exclusive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_rights', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropForeign(['movie_id']);
            $table->dropForeign(['video_id']);
            $table->dropColumn([
                'video_id',
                'movie_id',
                'series_id',
                'classification',
                'licence_name',
                'licensing_agreement_text',
                'attribution_requirements',
                'commercial_terms',
                'is_exclusive',
                'auto_delist_on_expiry',
            ]);
            $table->foreignId('channel_id')->nullable(false)->change();
        });
    }
};
