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
        Schema::create('content_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('complainant_name');
            $table->string('complainant_email');
            $table->string('complainant_organization')->nullable();
            $table->string('relationship_to_owner')->default('owner'); // owner, authorized_agent, legal_counsel, other
            $table->string('complaint_type')->default('copyright'); // copyright, trademark, privacy, unauthorized_broadcast, other
            $table->text('copyrighted_work_description');
            $table->string('infringing_url');
            $table->string('content_reference')->nullable(); // Video ID, title, or channel
            $table->foreignId('video_id')->nullable()->constrained('videos')->nullOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
            $table->text('infringement_details');
            $table->boolean('good_faith_declaration')->default(false);
            $table->boolean('authority_confirmation')->default(false);
            $table->string('status')->default('pending'); // pending, under_investigation, content_disabled, content_removed, rejected, resolved
            $table->string('ip_address', 45)->nullable();
            $table->string('action_taken')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_complaints');
    }
};
