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
        Schema::create('whatsapp_admin_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->unique();
            $table->string('name', 100)->default('Nomor Admin Utama');
            $table->string('phone_number', 30)->nullable();
            $table->string('device_name', 100)->nullable();
            $table->string('status', 30)->default('disconnected'); // disconnected, scan_qr, connected
            $table->text('qr_data_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_admin_sessions');
    }
};
