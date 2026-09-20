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
        Schema::create('approval_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('document_type', 50); // purchase_order, expense, supplier_invoice
            $table->string('name', 100)->nullable();
            $table->decimal('min_amount', 15, 2)->default(0.00);
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->unsignedTinyInteger('required_levels')->default(1);
            $table->string('approver_role_level_1', 50)->default('supervisor');
            $table->string('approver_role_level_2', 50)->nullable();
            $table->string('approver_role_level_3', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'document_type', 'is_active']);
        });

        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->uuid('document_id');
            $table->foreignUuid('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('rule_id')->nullable()->constrained('approval_rules')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->unsignedTinyInteger('current_level')->default(1);
            $table->unsignedTinyInteger('total_levels')->default(1);
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'document_type', 'status']);
            $table->index(['business_id', 'document_id']);
            $table->index(['business_id', 'requester_id']);
        });

        Schema::create('approval_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->foreignUuid('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 20); // approved, rejected
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['approval_request_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_rules');
    }
};
