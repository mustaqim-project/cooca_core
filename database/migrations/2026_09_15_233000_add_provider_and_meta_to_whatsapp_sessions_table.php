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
        Schema::table('whatsapp_sessions', function (Blueprint $table) {
            $table->string('provider')->default('baileys')->after('status'); // 'baileys' or 'meta_cloud'
            $table->string('meta_phone_number_id')->nullable()->after('provider');
            $table->text('meta_access_token')->nullable()->after('meta_phone_number_id');
            $table->string('meta_waba_id')->nullable()->after('meta_access_token');
            $table->string('meta_template_name')->nullable()->after('meta_waba_id');
            $table->boolean('is_active')->default(true)->after('meta_template_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'provider',
                'meta_phone_number_id',
                'meta_access_token',
                'meta_waba_id',
                'meta_template_name',
                'is_active',
            ]);
        });
    }
};
