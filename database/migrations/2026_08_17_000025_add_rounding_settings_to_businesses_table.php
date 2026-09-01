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
            $table->string('currency', 10)->default('IDR')->after('description');
            $table->string('rounding_strategy')->default('ROUND')->after('currency');
            $table->unsignedTinyInteger('currency_precision')->default(2)->after('rounding_strategy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn(['currency', 'rounding_strategy', 'currency_precision']);
        });
    }
};
