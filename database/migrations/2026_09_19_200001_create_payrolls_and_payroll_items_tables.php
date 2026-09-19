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
        // 1. Tambah kolom tunjangan pada business_users jika belum ada
        Schema::table('business_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('business_users', 'fixed_allowances')) {
                $table->decimal('fixed_allowances', 15, 2)->default(0.00)->after('daily_rate');
            }
            if (! Schema::hasColumn('business_users', 'variable_allowances')) {
                $table->decimal('variable_allowances', 15, 2)->default(0.00)->after('fixed_allowances');
            }
        });

        // 2. Tabel Batch Penggajian Bulanan (Payrolls)
        if (! Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->unsignedTinyInteger('period_month'); // 1 - 12
                $table->unsignedSmallInteger('period_year'); // e.g. 2026
                $table->string('title', 150); // e.g. "Penggajian September 2026"
                $table->string('status', 30)->default('draft'); // draft, approved, paid, cancelled
                $table->decimal('total_gross_pay', 15, 2)->default(0.00);
                $table->decimal('total_deductions', 15, 2)->default(0.00);
                $table->decimal('total_take_home_pay', 15, 2)->default(0.00);
                $table->decimal('total_company_cost', 15, 2)->default(0.00);
                $table->decimal('total_bpjs_company', 15, 2)->default(0.00);
                $table->decimal('total_bpjs_employee', 15, 2)->default(0.00);
                $table->decimal('total_pph21', 15, 2)->default(0.00);
                $table->decimal('total_loan_deductions', 15, 2)->default(0.00);
                $table->unsignedInteger('total_employees_count')->default(0);
                $table->foreignUuid('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method', 50)->default('bank_transfer'); // bank_transfer, cash, multi
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['business_id', 'period_year', 'period_month'], 'payroll_biz_period_unique');
                $table->index(['business_id', 'status']);
            });
        }

        // 3. Tabel Detail Slip Gaji per Karyawan (Payroll Items)
        if (! Schema::hasTable('payroll_items')) {
            Schema::create('payroll_items', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('payroll_id')->constrained('payrolls')->cascadeOnDelete();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('employee_name', 255);
                $table->string('job_title', 100)->nullable();
                $table->string('employment_type', 30)->default('permanent'); // permanent, contract, daily_worker
                $table->date('join_date')->nullable();
                $table->unsignedInteger('tenure_months')->default(0);

                // Pendapatan & Komponen Upah
                $table->decimal('base_salary', 15, 2)->default(0.00);
                $table->decimal('daily_rate', 15, 2)->default(0.00);
                $table->unsignedSmallInteger('days_worked')->default(0);
                $table->decimal('fixed_allowances', 15, 2)->default(0.00);
                $table->decimal('variable_allowances', 15, 2)->default(0.00);
                $table->decimal('overtime_pay', 15, 2)->default(0.00);
                $table->decimal('commissions', 15, 2)->default(0.00);
                $table->decimal('thr_amount', 15, 2)->default(0.00);
                $table->decimal('gross_pay', 15, 2)->default(0.00);

                // Kontribusi BPJS
                $table->decimal('bpjs_tk_company', 15, 2)->default(0.00);
                $table->decimal('bpjs_tk_employee', 15, 2)->default(0.00);
                $table->decimal('bpjs_kes_company', 15, 2)->default(0.00);
                $table->decimal('bpjs_kes_employee', 15, 2)->default(0.00);

                // Pajak PPh 21 TER
                $table->decimal('pph21_amount', 15, 2)->default(0.00);
                $table->string('pph21_ter_category', 10)->nullable(); // TER A, TER B, TER C
                $table->decimal('pph21_ter_rate', 6, 4)->default(0.0000);

                // Potongan Pinjaman & Lain-Lain
                $table->decimal('loan_deduction', 15, 2)->default(0.00);
                $table->decimal('other_deductions', 15, 2)->default(0.00);
                $table->decimal('total_deductions', 15, 2)->default(0.00);

                // Hasil Akhir
                $table->decimal('take_home_pay', 15, 2)->default(0.00);
                $table->decimal('company_total_cost', 15, 2)->default(0.00);

                // Informasi Pembayaran & Komunikasi
                $table->string('bank_name', 50)->nullable();
                $table->string('bank_account_number', 50)->nullable();
                $table->string('bank_account_holder', 100)->nullable();
                $table->string('whatsapp_number', 30)->nullable();
                $table->string('payslip_token', 64)->unique();
                $table->string('status', 30)->default('draft'); // draft, approved, paid
                $table->json('calculation_payload')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['payroll_id', 'status']);
                $table->index(['business_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');

        Schema::table('business_users', function (Blueprint $table): void {
            if (Schema::hasColumn('business_users', 'variable_allowances')) {
                $table->dropColumn('variable_allowances');
            }
            if (Schema::hasColumn('business_users', 'fixed_allowances')) {
                $table->dropColumn('fixed_allowances');
            }
        });
    }
};
