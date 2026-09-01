<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('title', 180);
            $table->string('category', 40);
            $table->string('severity', 20)->default('normal');
            $table->text('description');
            $table->text('steps_to_reproduce')->nullable();
            $table->text('expected_behavior')->nullable();
            $table->text('actual_behavior')->nullable();
            $table->string('environment', 255)->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 30)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('admin_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status']);
            $table->index(['status', 'priority']);
        });

        Schema::create('feature_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('title', 180);
            $table->string('category', 40);
            $table->text('description');
            $table->text('business_value')->nullable();
            $table->text('use_case')->nullable();
            $table->text('proposed_solution')->nullable();
            $table->string('status', 30)->default('submitted');
            $table->string('priority', 20)->default('normal');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('admin_notes')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->index(['business_id', 'status']);
            $table->index(['status', 'priority']);
        });

        Schema::create('feedback_updates', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('trackable_type', 100);
            $table->uuid('trackable_id');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('status', 30)->nullable();
            $table->unsignedTinyInteger('progress_percent')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['trackable_type', 'trackable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_updates');
        Schema::dropIfExists('feature_requests');
        Schema::dropIfExists('bug_reports');
    }
};
