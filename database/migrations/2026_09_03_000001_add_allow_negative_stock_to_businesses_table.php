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
            if (! Schema::hasColumn('businesses', 'allow_negative_stock')) {
                $table->boolean('allow_negative_stock')->default(false)->after('rounding_strategy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            if (Schema::hasColumn('businesses', 'allow_negative_stock')) {
                $table->dropColumn('allow_negative_stock');
            }
        });
    }
};
