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
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->string('payment_gateway', 32)->default('manual')->after('payment_method');
            $table->string('gateway_reference', 100)->nullable()->after('payment_gateway');
            $table->string('gateway_pay_code', 100)->nullable()->after('gateway_reference');
            $table->text('gateway_pay_url')->nullable()->after('gateway_pay_code');
            $table->text('gateway_qr_url')->nullable()->after('gateway_pay_url');
            $table->text('gateway_qr_string')->nullable()->after('gateway_qr_url');
            $table->decimal('gateway_fee', 15, 2)->default(0)->after('gateway_qr_string');
            $table->timestamp('gateway_expired_at')->nullable()->after('gateway_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_gateway',
                'gateway_reference',
                'gateway_pay_code',
                'gateway_pay_url',
                'gateway_qr_url',
                'gateway_qr_string',
                'gateway_fee',
                'gateway_expired_at',
            ]);
        });
    }
};
