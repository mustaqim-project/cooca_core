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
        Schema::create('customer_term_reminders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('invoice_id')->nullable()->constrained('invoices')->cascadeOnDelete();
            $table->string('reminder_type', 40); // upcoming_h3, due_date, overdue, credit_balance, manual
            $table->string('channel', 20)->default('both'); // whatsapp, email, both
            $table->string('recipient_phone', 50)->nullable();
            $table->string('recipient_email', 150)->nullable();
            $table->string('status', 30)->default('sent'); // sent, failed, skipped
            $table->string('wa_status', 30)->nullable(); // sent, failed, skipped
            $table->string('email_status', 30)->nullable(); // sent, failed, skipped
            $table->text('error_message')->nullable();
            $table->date('sent_date');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['business_id', 'customer_id', 'reminder_type', 'sent_date'], 'idx_cust_reminder_dedup');
            $table->index(['business_id', 'invoice_id', 'reminder_type', 'sent_date'], 'idx_inv_reminder_dedup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_term_reminders');
    }
};
