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
        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'thumbnail_url')) {
                $table->string('thumbnail_url', 500)->nullable()->after('description');
            }
            if (! Schema::hasColumn('videos', 'media_url')) {
                $table->string('media_url', 500)->nullable()->after('thumbnail_url');
            }
            if (! Schema::hasColumn('videos', 'cloudinary_public_id')) {
                $table->string('cloudinary_public_id', 255)->nullable()->after('media_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_url', 'media_url', 'cloudinary_public_id']);
        });
    }
};
