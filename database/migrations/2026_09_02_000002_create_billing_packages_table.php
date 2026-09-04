<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_packages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type', 24);
            $table->string('code', 80);
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('duration_days')->nullable();
            $table->unsignedBigInteger('token_quantity')->nullable();
            $table->unsignedBigInteger('storage_bytes')->nullable();
            $table->unsignedInteger('token_expiry_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['type', 'code']);
            $table->index(['type', 'is_active', 'sort_order']);
        });

        $now = now();
        DB::table('billing_packages')->insert([
            ['id' => (string) Str::uuid(), 'type' => 'subscription', 'code' => 'core-monthly', 'name' => 'Patungan Bulanan', 'description' => 'Akses penuh Cooca UMKM selama 30 hari.', 'price' => 25000, 'duration_days' => 30, 'token_quantity' => null, 'storage_bytes' => null, 'token_expiry_days' => null, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => (string) Str::uuid(), 'type' => 'subscription', 'code' => 'core-annual', 'name' => 'Patungan Tahunan', 'description' => 'Akses penuh Cooca UMKM selama 365 hari.', 'price' => 250000, 'duration_days' => 365, 'token_quantity' => null, 'storage_bytes' => null, 'token_expiry_days' => null, 'is_active' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['id' => (string) Str::uuid(), 'type' => 'ai_token', 'code' => 'token-1m', 'name' => '1 Juta Token AI', 'description' => 'Batch token AI dengan masa berlaku 30 hari.', 'price' => 50000, 'duration_days' => null, 'token_quantity' => 1000000, 'storage_bytes' => null, 'token_expiry_days' => 30, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => (string) Str::uuid(), 'type' => 'storage', 'code' => 'storage-1gb', 'name' => 'Storage 1 GB', 'description' => 'Kapasitas storage permanen.', 'price' => 50000, 'duration_days' => null, 'token_quantity' => null, 'storage_bytes' => 1073741824, 'token_expiry_days' => null, 'is_active' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_packages');
    }
};
