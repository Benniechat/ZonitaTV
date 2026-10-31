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
        // 1. Content Partners CRM
        if (! Schema::hasTable('content_partners')) {
            Schema::create('content_partners', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('partner_type', 50)->default('studio')->index(); // studio, tv_network, religious_broadcaster, sports_league, independent_filmmaker, distributor
                $table->string('pipeline_stage', 50)->default('prospect')->index(); // prospect, contact_made, negotiation, agreement_signed, content_received, active, churned
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone', 50)->nullable();
                $table->string('website_url', 1000)->nullable();
                $table->string('country_code', 3)->default('NGA')->index();
                $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('lead_source', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Distribution Agreements
        if (! Schema::hasTable('distribution_agreements')) {
            Schema::create('distribution_agreements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('content_partner_id')->constrained('content_partners')->cascadeOnDelete();
                $table->string('agreement_number', 100)->unique();
                $table->string('title');
                $table->string('agreement_type', 50)->default('revenue_share')->index(); // revenue_share, flat_fee, promotional_exchange, royalty_free, hybrid
                $table->string('exclusivity_type', 50)->default('non_exclusive'); // non_exclusive, exclusive_window, fully_exclusive
                $table->integer('exclusive_window_days')->default(0);
                $table->json('financial_terms')->nullable();
                $table->string('contract_document_url', 1000)->nullable();
                $table->date('start_date');
                $table->date('end_date')->nullable()->index();
                $table->string('status', 50)->default('draft')->index(); // draft, pending_signature, active, suspended, expired, terminated
                $table->dateTime('signed_at')->nullable();
                $table->foreignId('content_right_id')->nullable()->constrained('content_rights')->nullOnDelete();
                $table->text('terms_summary')->nullable();
                $table->timestamps();
            });
        }

        // 3. Link Channels & Videos to Content Partners
        Schema::table('channels', function (Blueprint $table) {
            if (! Schema::hasColumn('channels', 'content_partner_id')) {
                $table->foreignId('content_partner_id')->nullable()->after('broadcaster_website')->constrained('content_partners')->nullOnDelete();
            }
        });

        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'content_partner_id')) {
                $table->foreignId('content_partner_id')->nullable()->after('channel_title')->constrained('content_partners')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (Schema::hasColumn('videos', 'content_partner_id')) {
                $table->dropForeign(['content_partner_id']);
                $table->dropColumn('content_partner_id');
            }
        });

        Schema::table('channels', function (Blueprint $table) {
            if (Schema::hasColumn('channels', 'content_partner_id')) {
                $table->dropForeign(['content_partner_id']);
                $table->dropColumn('content_partner_id');
            }
        });

        Schema::dropIfExists('distribution_agreements');
        Schema::dropIfExists('content_partners');
    }
};
