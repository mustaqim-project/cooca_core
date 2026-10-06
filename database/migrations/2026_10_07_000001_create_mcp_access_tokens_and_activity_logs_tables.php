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
        Schema::create('mcp_access_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100)->comment('Nama client atau integrasi AI');
            $table->string('token_hash', 64)->unique()->comment('SHA-256 hash dari token');
            $table->json('abilities')->comment('Daftar abilities/scopes yang diizinkan');
            $table->string('provider_hint', 50)->default('all')->comment('claude, openai, gemini, cursor, custom, all');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'is_active'], 'idx_mcp_tokens_biz_active');
        });

        Schema::create('mcp_activity_logs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('token_id')->nullable()->constrained('mcp_access_tokens')->nullOnDelete();
            $table->string('tool_name', 100);
            $table->string('client_provider', 50)->default('custom');
            $table->json('arguments_payload')->nullable();
            $table->string('response_status', 20)->default('success'); // success | error
            $table->unsignedInteger('execution_time_ms')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['business_id', 'created_at'], 'idx_mcp_logs_biz_time');
            $table->index(['business_id', 'tool_name'], 'idx_mcp_logs_biz_tool');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_activity_logs');
        Schema::dropIfExists('mcp_access_tokens');
    }
};
