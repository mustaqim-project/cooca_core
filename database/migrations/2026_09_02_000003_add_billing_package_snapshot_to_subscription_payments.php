<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->foreignUuid('billing_package_id')->nullable()->after('payment_type')->constrained('billing_packages')->nullOnDelete();
            $table->string('package_name', 120)->nullable()->after('billing_package_id');
            $table->unsignedInteger('package_duration_days')->nullable()->after('package_name');
            $table->index(['billing_package_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropForeign(['billing_package_id']);
            $table->dropIndex(['billing_package_id', 'status']);
            $table->dropColumn(['billing_package_id', 'package_name', 'package_duration_days']);
        });
    }
};
