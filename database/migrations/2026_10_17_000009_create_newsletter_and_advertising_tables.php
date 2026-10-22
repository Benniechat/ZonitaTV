<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique();
            $table->json('topics')->nullable(); // e.g. ["sports", "news", "christian", "education"]
            $table->boolean('is_active')->default(true);
            $table->string('unsubscribe_token', 64)->unique()->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('advertisers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('email', 150);
            $table->string('phone', 50)->nullable();
            $table->string('website', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('advertising_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->constrained()->cascadeOnDelete();
            $table->string('title', 150);
            $table->enum('placement', [
                'homepage_banner',
                'featured_collection',
                'guide_header',
                'player_sidebar',
            ])->default('homepage_banner');
            $table->string('banner_image_path', 255)->nullable();
            $table->string('destination_url', 500);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget_ngn', 12, 2)->default(0);
            $table->enum('status', ['draft', 'pending_approval', 'active', 'paused', 'completed'])->default('draft');
            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertising_campaigns');
        Schema::dropIfExists('advertisers');
        Schema::dropIfExists('newsletter_subscriptions');
    }
};
