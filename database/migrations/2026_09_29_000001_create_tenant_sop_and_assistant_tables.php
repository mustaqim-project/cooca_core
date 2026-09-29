<?php

declare(strict_types=1);

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
        Schema::create('tenant_sop_documents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->integer('total_pages')->default(0);
            $table->integer('total_chunks')->default(0);
            $table->string('status', 30)->default('ready'); // processing, ready, failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'created_at']);
        });

        Schema::create('tenant_sop_chunks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('document_id')->constrained('tenant_sop_documents')->cascadeOnDelete();
            $table->integer('page_number')->default(1);
            $table->string('section_title')->nullable();
            $table->longText('content_text');
            $table->text('keywords')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'document_id']);
            $table->index(['business_id', 'page_number']);
        });

        Schema::create('ai_conversations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('scope', 30)->default('all'); // all, sop, system
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'user_id', 'created_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 20); // user, assistant, system
            $table->longText('content');
            $table->json('sources')->nullable();
            $table->json('action_buttons')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['business_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('tenant_sop_chunks');
        Schema::dropIfExists('tenant_sop_documents');
    }
};
