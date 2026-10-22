<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_rights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->string('rights_holder_name', 200);
            $table->string('contract_reference', 100)->nullable();

            // Status workflow: pending_review, directory_approved, embedding_approved, redistribution_approved, restricted, expired, rejected
            $table->enum('status', [
                'pending_review',
                'directory_approved',
                'embedding_approved',
                'redistribution_approved',
                'restricted',
                'expired',
                'rejected',
            ])->default('pending_review')->index();

            // Specific permissions
            $table->boolean('allow_directory_listing')->default(false);
            $table->boolean('allow_web_embedding')->default(false);
            $table->boolean('allow_direct_hls')->default(false);
            $table->boolean('allow_mobile_apps')->default(false);
            $table->boolean('allow_smart_tv')->default(false);
            $table->boolean('allow_monetisation')->default(false);

            // Geographic rules
            $table->json('permitted_territories')->nullable(); // e.g. ["NGA", "GHA", "GLOBAL"]
            $table->json('blocked_territories')->nullable();

            $table->date('effective_from');
            $table->date('expires_at')->nullable()->index();

            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();

            $table->timestamps();
        });

        Schema::create('rights_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_right_id')->constrained('content_rights')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('document_path', 255);
            $table->string('file_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rights_documents');
        Schema::dropIfExists('content_rights');
    }
};
