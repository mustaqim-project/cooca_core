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
        Schema::create('account_recovery_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('ticket_number')->unique();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->string('business_name');
            $table->string('applicant_name');
            $table->string('old_email');
            $table->string('old_phone')->nullable();
            $table->string('new_email');
            $table->string('new_phone');
            $table->string('issue_type')->default('both'); // phone_lost, email_inaccessible, both
            $table->text('reason_description');
            $table->string('identity_card_path');
            $table->string('business_proof_path');
            $table->string('selfie_proof_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('rejected_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('old_email');
            $table->index('new_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_recovery_requests');
    }
};
