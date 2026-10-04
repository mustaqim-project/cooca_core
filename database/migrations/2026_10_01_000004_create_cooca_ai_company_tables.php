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
        // 1. AI Provider Configurations (BYOAI - Multi-Provider)
        Schema::create('ai_provider_configs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('provider', 50); // openai, gemini, anthropic, openrouter
            $table->text('api_key'); // Encrypted at rest
            $table->string('model', 100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();
            $table->timestamp('tested_at')->nullable();
            $table->string('status', 30)->default('untested'); // connected, error, untested
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'provider'], 'idx_ai_biz_provider');
            $table->index(['business_id', 'is_active', 'is_default'], 'idx_ai_biz_provider_active');
        });

        // 2. AI Tasks (Execution & Multi-Agent Session Tracking)
        Schema::create('ai_tasks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('executive_role', 50)->default('ceo'); // ceo, coo, cfo, cmo, hr_lead
            $table->string('department', 50)->default('executive'); // executive, sales, marketing, finance, operations, people
            $table->string('agent', 50)->default('business'); // business, sales, inventory, finance, etc.
            $table->string('type', 50)->default('chat'); // chat, diagnosis, scheduled_monitoring, workflow, action_execution
            $table->string('status', 30)->default('pending'); // pending, running, waiting_approval, completed, failed, cancelled
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->text('input');
            $table->json('context')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->unsignedInteger('steps_count')->default(0);
            $table->unsignedInteger('tool_calls_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status'], 'idx_ai_tasks_status');
            $table->index(['business_id', 'created_at'], 'idx_ai_tasks_created');
            $table->index(['business_id', 'agent'], 'idx_ai_tasks_agent');
        });

        // 3. AI Action Proposals (Human-in-the-Loop Maker-Checker)
        Schema::create('ai_action_proposals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('ai_task_id')->nullable()->constrained('ai_tasks')->nullOnDelete();
            $table->string('executive_role', 50);
            $table->string('department', 50);
            $table->string('agent', 50);
            $table->string('tool', 100);
            $table->string('action_type', 100);
            $table->string('risk_level', 20)->default('MEDIUM'); // LOW, MEDIUM, HIGH, CRITICAL
            $table->string('title');
            $table->text('description');
            $table->text('reason');
            $table->json('payload');
            $table->decimal('estimated_cost', 15, 2)->default(0.00);
            $table->string('status', 30)->default('pending'); // pending, approved, rejected, executing, completed, failed
            $table->string('idempotency_key', 100);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'idempotency_key'], 'idx_ai_action_idempotency');
            $table->index(['business_id', 'status'], 'idx_ai_actions_status');
            $table->index(['business_id', 'risk_level'], 'idx_ai_actions_risk');
            $table->index(['business_id', 'created_at'], 'idx_ai_actions_created');
        });

        // 4. AI Work Histories (Immutable Executive Logging & Work Sessions)
        Schema::create('ai_work_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('ai_task_id')->nullable()->constrained('ai_tasks')->nullOnDelete();
            $table->string('session_title');
            $table->text('executive_summary');
            $table->json('participating_agents'); // array of agents
            $table->unsignedInteger('insights_count')->default(0);
            $table->unsignedInteger('actions_count')->default(0);
            $table->unsignedInteger('approved_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['business_id', 'recorded_at'], 'idx_ai_history_recorded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_work_histories');
        Schema::dropIfExists('ai_action_proposals');
        Schema::dropIfExists('ai_tasks');
        Schema::dropIfExists('ai_provider_configs');
    }
};
