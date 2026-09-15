<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_store_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
            $table->boolean('is_storefront_enabled')->default(true);
            $table->boolean('is_discoverable')->default(true);
            $table->boolean('allow_pickup')->default(true);
            $table->boolean('allow_delivery')->default(true);
            $table->decimal('min_order_amount', 15, 2)->default(0.00);
            $table->unsignedInteger('order_auto_cancel_minutes')->default(60);
            $table->unsignedInteger('lead_time_hours')->default(0);
            $table->json('operating_days')->nullable();
            $table->json('available_slots')->nullable();
            $table->time('cut_off_time')->nullable();
            $table->unsignedInteger('max_capacity_per_slot')->nullable();
            $table->string('order_notes_placeholder', 255)->nullable();
            $table->text('announcement_text')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'is_discoverable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_store_settings');
    }
};
