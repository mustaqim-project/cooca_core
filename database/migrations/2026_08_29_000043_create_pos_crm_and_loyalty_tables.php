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
        // 1. Expand customers table with CRM & Loyalty fields
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'segment')) {
                $table->string('segment', 30)->default('regular')->after('company_name'); // regular, vip, wholesale, reseller, staff
            }
            if (! Schema::hasColumn('customers', 'membership_tier')) {
                $table->string('membership_tier', 30)->default('bronze')->after('segment'); // bronze, silver, gold, platinum
            }
            if (! Schema::hasColumn('customers', 'points_balance')) {
                $table->integer('points_balance')->default(0)->after('membership_tier');
            }
            if (! Schema::hasColumn('customers', 'total_spent')) {
                $table->decimal('total_spent', 15, 2)->default(0.00)->after('points_balance');
            }
            if (! Schema::hasColumn('customers', 'total_orders_count')) {
                $table->integer('total_orders_count')->default(0)->after('total_spent');
            }
            if (! Schema::hasColumn('customers', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('total_orders_count');
            }
            if (! Schema::hasColumn('customers', 'anniversary_date')) {
                $table->date('anniversary_date')->nullable()->after('birth_date');
            }
            if (! Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0.00)->after('payment_terms_days');
            }
            if (! Schema::hasColumn('customers', 'current_credit_balance')) {
                $table->decimal('current_credit_balance', 15, 2)->default(0.00)->after('credit_limit');
            }
        });

        // 2. Customer Loyalty Points History
        Schema::create('customer_point_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->integer('points_change'); // positive for earn, negative for redeem
            $table->string('type', 30); // pos_earn, pos_redeem, birthday_bonus, manual_adjustment
            $table->string('reference_id', 64)->nullable();
            $table->integer('balance_after')->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['business_id', 'customer_id']);
        });

        // 3. Vouchers & Promos
        Schema::create('vouchers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('discount_type', 20)->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 15, 2);
            $table->decimal('min_order_amount', 15, 2)->default(0.00);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->string('tier_eligibility', 50)->nullable(); // all, bronze, silver, gold, platinum
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'code']);
            $table->index(['business_id', 'is_active']);
        });

        // 4. Customer Store Credit (Piutang CRM) Ledger
        Schema::create('customer_credit_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type', 20); // charge, payment
            $table->decimal('amount', 15, 2);
            $table->string('reference_type', 30)->nullable(); // pos_order, invoice, manual_payment
            $table->string('reference_id', 64)->nullable();
            $table->decimal('balance_after', 15, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_credit_transactions');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('customer_point_histories');

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropColumn([
                'segment',
                'membership_tier',
                'points_balance',
                'total_spent',
                'total_orders_count',
                'birth_date',
                'anniversary_date',
                'credit_limit',
                'current_credit_balance',
            ]);
        });
    }
};
