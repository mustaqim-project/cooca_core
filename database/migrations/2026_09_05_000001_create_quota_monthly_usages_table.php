<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations: pencacah kuota bulanan per resource (purchase_order, pos_transaction).
     * Dipakai oleh EntitlementService untuk batas transaksi paket gratis.
     */
    public function up(): void
    {
        Schema::create('quota_monthly_usages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('resource_type', 32);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->unique(['business_id', 'resource_type', 'year', 'month'], 'quota_monthly_biz_type_year_month_unique');
            $table->index(['business_id', 'resource_type'], 'quota_monthly_biz_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quota_monthly_usages');
    }
};