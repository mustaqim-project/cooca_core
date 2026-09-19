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
        // 1. Expand business_users with HRM fields
        Schema::table('business_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('business_users', 'job_title')) {
                $table->string('job_title', 100)->nullable()->after('role');
            }
            if (! Schema::hasColumn('business_users', 'employment_type')) {
                $table->string('employment_type', 30)->default('permanent')->after('job_title'); // permanent, contract, daily_worker
            }
            if (! Schema::hasColumn('business_users', 'join_date')) {
                $table->date('join_date')->nullable()->after('employment_type');
            }
            if (! Schema::hasColumn('business_users', 'base_salary')) {
                $table->decimal('base_salary', 15, 2)->default(0.00)->after('join_date');
            }
            if (! Schema::hasColumn('business_users', 'daily_rate')) {
                $table->decimal('daily_rate', 15, 2)->default(0.00)->after('base_salary');
            }
            if (! Schema::hasColumn('business_users', 'hourly_rate')) {
                $table->decimal('hourly_rate', 15, 2)->default(0.00)->after('daily_rate');
            }
            if (! Schema::hasColumn('business_users', 'pin_hash')) {
                $table->string('pin_hash', 255)->nullable()->after('hourly_rate');
            }
            if (! Schema::hasColumn('business_users', 'bank_name')) {
                $table->string('bank_name', 50)->nullable()->after('pin_hash');
            }
            if (! Schema::hasColumn('business_users', 'bank_account_number')) {
                $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('business_users', 'bank_account_holder')) {
                $table->string('bank_account_holder', 100)->nullable()->after('bank_account_number');
            }
            if (! Schema::hasColumn('business_users', 'whatsapp_number')) {
                $table->string('whatsapp_number', 30)->nullable()->after('bank_account_holder');
            }
            if (! Schema::hasColumn('business_users', 'tax_ptkp_status')) {
                $table->string('tax_ptkp_status', 10)->default('TK/0')->after('whatsapp_number');
            }
            if (! Schema::hasColumn('business_users', 'bpjs_tk_enabled')) {
                $table->boolean('bpjs_tk_enabled')->default(false)->after('tax_ptkp_status');
            }
            if (! Schema::hasColumn('business_users', 'bpjs_kes_enabled')) {
                $table->boolean('bpjs_kes_enabled')->default(false)->after('bpjs_tk_enabled');
            }
            if (! Schema::hasColumn('business_users', 'primary_location_id')) {
                $table->foreignUuid('primary_location_id')->nullable()->after('bpjs_kes_enabled')->constrained('locations')->nullOnDelete();
            }
        });

        // 2. Employee Branch Assignments (Multi-branch staffing)
        if (! Schema::hasTable('employee_branch_assignments')) {
            Schema::create('employee_branch_assignments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
                $table->string('role_in_branch', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['business_id', 'user_id', 'location_id'], 'emp_branch_unique');
                $table->index(['business_id', 'location_id']);
            });
        }

        // 3. Employee Loans & Kasbon
        if (! Schema::hasTable('employee_loans')) {
            Schema::create('employee_loans', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('loan_number', 64);
                $table->date('loan_date');
                $table->decimal('amount', 15, 2);
                $table->integer('tenor_months')->default(1);
                $table->decimal('monthly_installment', 15, 2);
                $table->decimal('remaining_balance', 15, 2);
                $table->string('status', 20)->default('pending'); // pending, active, completed, cancelled
                $table->string('purpose', 255)->nullable();
                $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['business_id', 'loan_number']);
                $table->index(['business_id', 'user_id', 'status']);
            });
        }

        // 4. Employee Commissions (POS & service attributions)
        if (! Schema::hasTable('employee_commissions')) {
            Schema::create('employee_commissions', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->uuid('pos_order_id')->nullable();
                $table->string('commission_type', 30)->default('flat'); // flat, percentage, target_bonus, split
                $table->decimal('base_amount', 15, 2)->default(0.00);
                $table->decimal('rate', 8, 4)->default(0.0000);
                $table->decimal('earned_amount', 15, 2);
                $table->date('date');
                $table->string('status', 20)->default('earned'); // earned, approved, paid, cancelled
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'user_id', 'status']);
                $table->index(['business_id', 'date']);
            });
        }

        // 5. Branch Product Prices (Multi-Pricing per Location)
        if (! Schema::hasTable('branch_product_prices')) {
            Schema::create('branch_product_prices', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
                $table->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
                $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('price', 15, 2);
                $table->decimal('cost_price', 15, 2)->nullable();
                $table->boolean('is_available')->default(true);
                $table->timestamps();

                $table->unique(['business_id', 'location_id', 'product_id'], 'branch_prod_unique');
                $table->index(['business_id', 'location_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_product_prices');
        Schema::dropIfExists('employee_commissions');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('employee_branch_assignments');

        Schema::table('business_users', function (Blueprint $table): void {
            $table->dropForeign(['primary_location_id']);
            $table->dropColumn([
                'job_title',
                'employment_type',
                'join_date',
                'base_salary',
                'daily_rate',
                'hourly_rate',
                'pin_hash',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
                'whatsapp_number',
                'tax_ptkp_status',
                'bpjs_tk_enabled',
                'bpjs_kes_enabled',
                'primary_location_id',
            ]);
        });
    }
};
