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
        Schema::table('businesses', function (Blueprint $table): void {
            if (! Schema::hasColumn('businesses', 'pos_supervisor_pin')) {
                $table->string('pos_supervisor_pin', 60)->default('1234')->after('currency_precision');
            }
            if (! Schema::hasColumn('businesses', 'pos_max_cashier_discount_percent')) {
                $table->decimal('pos_max_cashier_discount_percent', 5, 2)->default(10.00)->after('pos_supervisor_pin');
            }
            if (! Schema::hasColumn('businesses', 'pos_require_pin_for_void')) {
                $table->boolean('pos_require_pin_for_void')->default(true)->after('pos_max_cashier_discount_percent');
            }
            if (! Schema::hasColumn('businesses', 'pos_require_pin_for_refund')) {
                $table->boolean('pos_require_pin_for_refund')->default(true)->after('pos_require_pin_for_void');
            }
            if (! Schema::hasColumn('businesses', 'pos_receipt_footer_note')) {
                $table->text('pos_receipt_footer_note')->nullable()->after('pos_require_pin_for_refund');
            }
            if (! Schema::hasColumn('businesses', 'pos_enable_tax')) {
                $table->boolean('pos_enable_tax')->default(false)->after('pos_receipt_footer_note');
            }
            if (! Schema::hasColumn('businesses', 'pos_tax_percent')) {
                $table->decimal('pos_tax_percent', 5, 2)->default(11.00)->after('pos_enable_tax');
            }
            if (! Schema::hasColumn('businesses', 'pos_enable_service_charge')) {
                $table->boolean('pos_enable_service_charge')->default(false)->after('pos_tax_percent');
            }
            if (! Schema::hasColumn('businesses', 'pos_service_charge_percent')) {
                $table->decimal('pos_service_charge_percent', 5, 2)->default(5.00)->after('pos_enable_service_charge');
            }
        });

        Schema::create('pos_approval_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('action_type', 40); // void, refund, high_discount, open_drawer
            $table->string('reference_id', 64)->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_approval_requests');

        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn([
                'pos_supervisor_pin',
                'pos_max_cashier_discount_percent',
                'pos_require_pin_for_void',
                'pos_require_pin_for_refund',
                'pos_receipt_footer_note',
                'pos_enable_tax',
                'pos_tax_percent',
                'pos_enable_service_charge',
                'pos_service_charge_percent',
            ]);
        });
    }
};
