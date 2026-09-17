<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commerce_orders')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('commerce_orders', 'payment_gateway')) {
                    $table->string('payment_gateway', 32)->default('manual')->after('payment_status');
                }
                if (! Schema::hasColumn('commerce_orders', 'payment_channel')) {
                    $table->string('payment_channel', 64)->nullable()->after('payment_gateway');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_reference')) {
                    $table->string('gateway_reference', 100)->nullable()->after('payment_channel')->index('co_gateway_ref_idx');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_pay_code')) {
                    $table->string('gateway_pay_code', 100)->nullable()->after('gateway_reference');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_pay_url')) {
                    $table->text('gateway_pay_url')->nullable()->after('gateway_pay_code');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_qr_url')) {
                    $table->text('gateway_qr_url')->nullable()->after('gateway_pay_url');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_qr_string')) {
                    $table->text('gateway_qr_string')->nullable()->after('gateway_qr_url');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_fee')) {
                    $table->decimal('gateway_fee', 15, 2)->default(0.00)->after('gateway_qr_string');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_expired_at')) {
                    $table->dateTime('gateway_expired_at')->nullable()->after('gateway_fee');
                }
                if (! Schema::hasColumn('commerce_orders', 'gateway_payload')) {
                    $table->json('gateway_payload')->nullable()->after('gateway_expired_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('commerce_orders')) {
            Schema::table('commerce_orders', function (Blueprint $table): void {
                $columns = [
                    'payment_gateway',
                    'payment_channel',
                    'gateway_reference',
                    'gateway_pay_code',
                    'gateway_pay_url',
                    'gateway_qr_url',
                    'gateway_qr_string',
                    'gateway_fee',
                    'gateway_expired_at',
                    'gateway_payload',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('commerce_orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
