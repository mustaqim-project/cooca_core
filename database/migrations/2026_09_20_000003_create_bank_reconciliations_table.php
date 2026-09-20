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
        Schema::create('bank_statements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('cash_account_id')->constrained('cash_accounts')->cascadeOnDelete();
            $table->string('filename', 255);
            $table->date('statement_date');
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('closing_balance', 15, 2)->default(0.00);
            $table->string('status', 30)->default('in_progress'); // in_progress, completed
            $table->unsignedInteger('total_lines')->default(0);
            $table->unsignedInteger('reconciled_lines')->default(0);
            $table->foreignUuid('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'statement_date']);
            $table->index(['business_id', 'cash_account_id']);
        });

        Schema::create('bank_statement_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('bank_statement_id')->constrained('bank_statements')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('description', 255);
            $table->string('reference_number', 100)->nullable();
            $table->string('type', 10); // debit, credit
            $table->decimal('amount', 15, 2);
            $table->decimal('balance', 15, 2)->nullable();
            $table->string('status', 30)->default('unmatched'); // unmatched, matched, reconciled
            $table->string('matched_transaction_type', 50)->nullable(); // cash_transaction, pos_order_payment, invoice_payment
            $table->string('matched_transaction_id', 64)->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignUuid('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['bank_statement_id', 'status']);
            $table->index(['business_id', 'transaction_date']);
            $table->index(['business_id', 'matched_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statements');
    }
};
