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
        Schema::table('pos_registers', function (Blueprint $table): void {
            if (! Schema::hasColumn('pos_registers', 'device_identifier')) {
                $table->string('device_identifier', 100)->nullable()->after('code');
            }
            if (! Schema::hasColumn('pos_registers', 'default_receipt_printer_id')) {
                $table->foreignUuid('default_receipt_printer_id')->nullable()->after('device_identifier')->constrained('pos_printers')->nullOnDelete();
            }
            if (! Schema::hasColumn('pos_registers', 'default_kitchen_printer_id')) {
                $table->foreignUuid('default_kitchen_printer_id')->nullable()->after('default_receipt_printer_id')->constrained('pos_printers')->nullOnDelete();
            }
            if (! Schema::hasColumn('pos_registers', 'default_cash_drawer_name')) {
                $table->string('default_cash_drawer_name', 100)->nullable()->after('default_kitchen_printer_id');
            }
            if (! Schema::hasColumn('pos_registers', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('is_active');
            }
        });

        Schema::table('pos_shifts', function (Blueprint $table): void {
            if (! Schema::hasColumn('pos_shifts', 'opening_denominations')) {
                $table->json('opening_denominations')->nullable()->after('opening_cash');
            }
            if (! Schema::hasColumn('pos_shifts', 'closing_denominations')) {
                $table->json('closing_denominations')->nullable()->after('closing_cash_actual');
            }
            if (! Schema::hasColumn('pos_shifts', 'cashier_notes')) {
                $table->text('cashier_notes')->nullable()->after('notes');
            }
        });

        Schema::table('pos_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('pos_orders', 'pos_register_id')) {
                $table->foreignUuid('pos_register_id')->nullable()->after('pos_shift_id')->constrained('pos_registers')->nullOnDelete();
                $table->index(['business_id', 'pos_register_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('pos_orders', 'pos_register_id')) {
                $table->dropForeign(['pos_register_id']);
                $table->dropIndex(['business_id', 'pos_register_id']);
                $table->dropColumn('pos_register_id');
            }
        });

        Schema::table('pos_shifts', function (Blueprint $table): void {
            $cols = array_filter(['opening_denominations', 'closing_denominations', 'cashier_notes'], fn ($c) => Schema::hasColumn('pos_shifts', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('pos_registers', function (Blueprint $table): void {
            if (Schema::hasColumn('pos_registers', 'default_receipt_printer_id')) {
                $table->dropForeign(['default_receipt_printer_id']);
                $table->dropColumn('default_receipt_printer_id');
            }
            if (Schema::hasColumn('pos_registers', 'default_kitchen_printer_id')) {
                $table->dropForeign(['default_kitchen_printer_id']);
                $table->dropColumn('default_kitchen_printer_id');
            }
            $cols = array_filter(['device_identifier', 'default_cash_drawer_name', 'last_seen_at'], fn ($c) => Schema::hasColumn('pos_registers', $c));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
