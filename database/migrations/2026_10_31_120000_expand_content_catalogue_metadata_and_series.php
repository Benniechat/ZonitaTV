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
        // 1. Expand Videos metadata
        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'aspect_ratio')) {
                $table->string('aspect_ratio', 20)->default('16:9')->after('format_type');
            }
            if (! Schema::hasColumn('videos', 'resolution')) {
                $table->string('resolution', 20)->default('1080p')->after('aspect_ratio');
            }
            if (! Schema::hasColumn('videos', 'content_rating')) {
                $table->string('content_rating', 20)->default('PG')->after('resolution');
            }
            if (! Schema::hasColumn('videos', 'advisory_tags')) {
                $table->json('advisory_tags')->nullable()->after('content_rating');
            }
            if (! Schema::hasColumn('videos', 'language_code')) {
                $table->string('language_code', 10)->default('en')->after('advisory_tags')->index();
            }
            if (! Schema::hasColumn('videos', 'country_code')) {
                $table->string('country_code', 3)->default('NGA')->after('language_code')->index();
            }
            if (! Schema::hasColumn('videos', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 2. Expand Movies metadata
        Schema::table('movies', function (Blueprint $table) {
            if (! Schema::hasColumn('movies', 'aspect_ratio')) {
                $table->string('aspect_ratio', 20)->default('16:9')->after('media_format');
            }
            if (! Schema::hasColumn('movies', 'resolution')) {
                $table->string('resolution', 20)->default('1080p')->after('aspect_ratio');
            }
            if (! Schema::hasColumn('movies', 'content_rating')) {
                $table->string('content_rating', 20)->default('PG')->after('resolution');
            }
            if (! Schema::hasColumn('movies', 'advisory_tags')) {
                $table->json('advisory_tags')->nullable()->after('content_rating');
            }
            if (! Schema::hasColumn('movies', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 3. Create Series table
        if (! Schema::hasTable('series')) {
            Schema::create('series', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('synopsis')->nullable();
                $table->string('poster_url', 1000)->nullable();
                $table->string('backdrop_url', 1000)->nullable();
                $table->integer('release_year')->nullable()->index();
                $table->string('genre', 100)->nullable()->index();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('language_code', 10)->default('en')->index();
                $table->string('country_code', 3)->default('NGA')->index();
                $table->string('content_rating', 20)->default('PG');
                $table->json('advisory_tags')->nullable();
                $table->string('status', 50)->default('in_production')->index(); // in_production, running, completed, archived
                $table->boolean('is_published')->default(true)->index();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // 4. Create Seasons table
        if (! Schema::hasTable('seasons')) {
            Schema::create('seasons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();
                $table->integer('season_number')->default(1);
                $table->string('title')->nullable();
                $table->text('synopsis')->nullable();
                $table->string('poster_url', 1000)->nullable();
                $table->integer('air_year')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->unique(['series_id', 'season_number']);
            });
        }

        // 5. Create Episodes table
        if (! Schema::hasTable('episodes')) {
            Schema::create('episodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('season_id')->constrained('seasons')->cascadeOnDelete();
                $table->integer('episode_number')->default(1);
                $table->string('title');
                $table->string('slug')->nullable();
                $table->text('synopsis')->nullable();
                $table->foreignId('video_id')->nullable()->constrained('videos')->nullOnDelete();
                $table->string('stream_url', 1000)->nullable();
                $table->string('thumbnail_url', 1000)->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->string('aspect_ratio', 20)->default('16:9');
                $table->string('resolution', 20)->default('1080p');
                $table->date('air_date')->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index(['season_id', 'episode_number']);
            });
        }

        // 6. Connect programmes to video records
        Schema::table('programmes', function (Blueprint $table) {
            if (! Schema::hasColumn('programmes', 'video_record_id')) {
                $table->foreignId('video_record_id')->nullable()->after('video_id')->constrained('videos')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            if (Schema::hasColumn('programmes', 'video_record_id')) {
                $table->dropForeign(['video_record_id']);
                $table->dropColumn('video_record_id');
            }
        });

        Schema::dropIfExists('episodes');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('series');

        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['aspect_ratio', 'resolution', 'content_rating', 'advisory_tags', 'deleted_at']);
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['aspect_ratio', 'resolution', 'content_rating', 'advisory_tags', 'language_code', 'country_code', 'deleted_at']);
        });
    }
};
