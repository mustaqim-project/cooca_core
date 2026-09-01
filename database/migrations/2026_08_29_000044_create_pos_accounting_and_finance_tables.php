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
        // 1. Chart of Accounts (COA)
        Schema::create('chart_of_accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('type', 30); // asset, liability, equity, revenue, cogs, expense
            $table->string('normal_balance', 10)->default('debit'); // debit, credit
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'code']);
            $table->index(['business_id', 'type']);
        });

        // 2. Journal Entries (Double-Entry General Ledger)
        Schema::create('journal_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('entry_number', 64);
            $table->date('entry_date');
            $table->string('reference_type', 40)->nullable(); // pos_order, pos_refund, expense, settlement, stock_adjustment, manual
            $table->string('reference_id', 64)->nullable();
            $table->string('description', 255);
            $table->decimal('total_debit', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'entry_number']);
            $table->index(['business_id', 'entry_date']);
            $table->index(['business_id', 'reference_type', 'reference_id'], 'je_ref_idx');
        });

        Schema::create('journal_entry_lines', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignUuid('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->string('type', 10); // debit, credit
            $table->decimal('amount', 15, 2);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id', 'account_id']);
        });

        // 3. Operational Expenses
        Schema::create('expenses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('expense_number', 64);
            $table->date('expense_date');
            $table->string('category', 50); // operational, rent, utilities, salaries, marketing, maintenance, supplies, other
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30)->default('cash'); // cash, bank_transfer, petty_cash
            $table->foreignUuid('account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('receipt_image_path', 255)->nullable();
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'expense_number']);
            $table->index(['business_id', 'expense_date']);
        });

        // 4. Payment Settlements (Gateway / EDC Reconciliation)
        Schema::create('payment_settlements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('settlement_number', 64);
            $table->date('settlement_date');
            $table->string('payment_channel', 50); // qris, edc_debit, edc_credit, ewallet, other
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('fee_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            $table->string('destination_bank', 100)->nullable();
            $table->string('status', 20)->default('completed'); // pending, completed
            $table->text('notes')->nullable();
            $table->foreignUuid('reconciled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_id', 'settlement_number']);
            $table->index(['business_id', 'settlement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settlements');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};
