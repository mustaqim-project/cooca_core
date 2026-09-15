<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('global_customers', function (Blueprint $table): void {
            $table->string('google_id', 100)->nullable()->unique()->after('id');
            $table->string('shipping_address', 500)->nullable()->after('phone_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('global_customers', function (Blueprint $table): void {
            $table->dropColumn(['google_id', 'shipping_address']);
        });
    }
};