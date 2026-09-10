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
        Schema::create('pos_tables', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('table_number', 50);
            $table->string('name', 100)->nullable();
            $table->unsignedInteger('capacity')->default(4);
            $table->string('status', 30)->default('available'); // available, ordering, occupied, preparing, serving, waiting_payment, closed, inactive
            $table->string('qr_token', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'is_active']);
            $table->index('qr_token');
        });

        Schema::create('pos_table_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('pos_table_id')->constrained('pos_tables')->cascadeOnDelete();
            $table->string('session_number', 50)->unique();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 50);
            $table->string('status', 30)->default('open'); // open, closed, cancelled
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['pos_table_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_table_sessions');
        Schema::dropIfExists('pos_tables');
    }
};
