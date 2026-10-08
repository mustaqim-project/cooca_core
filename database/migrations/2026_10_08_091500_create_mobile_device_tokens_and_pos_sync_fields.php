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
        if (! Schema::hasTable('mobile_device_tokens')) {
            Schema::create('mobile_device_tokens', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->foreignUuid('global_customer_id')->nullable()->constrained('global_customers')->cascadeOnDelete();
                $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
                $table->text('token');
                $table->string('platform', 20)->default('android'); // android, ios, web
                $table->string('device_model', 100)->nullable();
                $table->string('app_type', 30)->default('cooca_my_own'); // cooca_my_own, cooca_customer
                $table->string('app_version', 20)->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['platform', 'app_type']);
                $table->index(['is_active', 'app_type']);
            });
        }

        if (Schema::hasTable('pos_orders') && ! Schema::hasColumn('pos_orders', 'client_uuid')) {
            Schema::table('pos_orders', function (Blueprint $table): void {
                $table->string('client_uuid', 64)->nullable()->after('order_number')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pos_orders') && Schema::hasColumn('pos_orders', 'client_uuid')) {
            Schema::table('pos_orders', function (Blueprint $table): void {
                $table->dropColumn('client_uuid');
            });
        }

        Schema::dropIfExists('mobile_device_tokens');
    }
};
