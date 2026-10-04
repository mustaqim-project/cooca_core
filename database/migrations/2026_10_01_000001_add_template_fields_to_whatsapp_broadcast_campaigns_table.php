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
        Schema::table('whatsapp_broadcast_campaigns', function (Blueprint $table): void {
            if (! Schema::hasColumn('whatsapp_broadcast_campaigns', 'template_name')) {
                $table->string('template_name', 128)->nullable()->after('message')->comment('Meta Approved Template Name for Anti-Block Delivery');
            }
            if (! Schema::hasColumn('whatsapp_broadcast_campaigns', 'template_language')) {
                $table->string('template_language', 16)->default('id')->after('template_name');
            }
            if (! Schema::hasColumn('whatsapp_broadcast_campaigns', 'template_params')) {
                $table->json('template_params')->nullable()->after('template_language')->comment('Dynamic parameter map for Meta Cloud API');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_broadcast_campaigns', function (Blueprint $table): void {
            if (Schema::hasColumn('whatsapp_broadcast_campaigns', 'template_params')) {
                $table->dropColumn('template_params');
            }
            if (Schema::hasColumn('whatsapp_broadcast_campaigns', 'template_language')) {
                $table->dropColumn('template_language');
            }
            if (Schema::hasColumn('whatsapp_broadcast_campaigns', 'template_name')) {
                $table->dropColumn('template_name');
            }
        });
    }
};
