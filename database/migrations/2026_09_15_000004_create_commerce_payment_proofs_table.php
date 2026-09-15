<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_payment_proofs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('commerce_order_id')->constrained('commerce_orders')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size_kb');
            $table->string('mime_type', 100);
            $table->string('sender_bank', 100)->nullable();
            $table->string('sender_account_name', 150)->nullable();
            $table->string('status', 32)->default('pending'); // pending, verified, rejected
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status'], 'cpp_biz_status_idx');
            $table->index('commerce_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_payment_proofs');
    }
};
