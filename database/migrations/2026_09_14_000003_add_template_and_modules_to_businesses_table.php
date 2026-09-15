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
        Schema::table('businesses', function (Blueprint $table): void {
            if (! Schema::hasColumn('businesses', 'industry_category')) {
                $table->string('industry_category')->nullable()->after('currency');
            }
            if (! Schema::hasColumn('businesses', 'template_code')) {
                $table->string('template_code')->nullable()->after('industry_category');
            }
            if (! Schema::hasColumn('businesses', 'disabled_modules')) {
                $table->json('disabled_modules')->nullable()->after('template_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            if (Schema::hasColumn('businesses', 'disabled_modules')) {
                $table->dropColumn('disabled_modules');
            }
            if (Schema::hasColumn('businesses', 'template_code')) {
                $table->dropColumn('template_code');
            }
            if (Schema::hasColumn('businesses', 'industry_category')) {
                $table->dropColumn('industry_category');
            }
        });
    }
};
