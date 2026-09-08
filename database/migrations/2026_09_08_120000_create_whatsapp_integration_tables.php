<?php

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
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
            $table->string('session_id')->unique();
            $table->string('phone_number')->nullable();
            $table->string('device_name')->nullable();
            $table->string('status')->default('disconnected'); // disconnected, scan_qr, connected
            $table->boolean('auto_send_receipt')->default(true);
            $table->text('receipt_template')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        Schema::create('whatsapp_broadcast_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->string('target_filter')->default('all'); // all, bronze, silver, gold, vip, custom
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->string('status')->default('draft'); // draft, processing, completed, failed
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        Schema::create('whatsapp_broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('whatsapp_broadcast_campaigns')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name');
            $table->string('phone_number');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });

        Schema::create('whatsapp_message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('type')->default('custom'); // receipt, broadcast, test, custom
            $table->string('recipient_phone');
            $table->string('recipient_name')->nullable();
            $table->text('message');
            $table->string('status')->default('sent'); // sent, failed
            $table->foreignUuid('order_id')->nullable()->constrained('pos_orders')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'type', 'status']);
            $table->index('recipient_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_logs');
        Schema::dropIfExists('whatsapp_broadcast_recipients');
        Schema::dropIfExists('whatsapp_broadcast_campaigns');
        Schema::dropIfExists('whatsapp_sessions');
    }
};
