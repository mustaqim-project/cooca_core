<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Log pengingat langganan (H-7, H-3, H-1, Hari H) ke bisnis owner
        Schema::create('whatsapp_subscription_reminders', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('business_subscription_id')->nullable()->constrained('business_subscriptions')->nullOnDelete();
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_name');
            $table->string('business_name');
            $table->string('recipient_phone');
            $table->string('reminder_type'); // 'h-7', 'h-3', 'h-1', 'hari_h', 'manual'
            $table->date('subscription_ends_at')->nullable();
            $table->text('message');
            $table->string('status')->default('sent'); // 'sent', 'failed'
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index(['business_subscription_id', 'reminder_type', 'subscription_ends_at'], 'idx_wa_sub_reminders_check');
        });

        // 2. Kampanye blast WhatsApp dari Admin ke Bisnis Owner
        Schema::create('whatsapp_admin_blasts', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('media_url', 500)->nullable();
            $table->string('target_filter')->default('all_owners'); // 'all_owners', 'active_subscribers', 'expiring_soon', 'free_tier'
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->string('status')->default('pending'); // 'pending', 'processing', 'completed', 'failed'
            $table->timestamps();
        });

        // 3. Penerima individual dari admin blast
        Schema::create('whatsapp_admin_blast_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blast_id')->constrained('whatsapp_admin_blasts')->cascadeOnDelete();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('owner_name');
            $table->string('business_name');
            $table->string('phone_number');
            $table->string('status')->default('pending'); // 'pending', 'sent', 'failed'
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_admin_blast_recipients');
        Schema::dropIfExists('whatsapp_admin_blasts');
        Schema::dropIfExists('whatsapp_subscription_reminders');
    }
};
