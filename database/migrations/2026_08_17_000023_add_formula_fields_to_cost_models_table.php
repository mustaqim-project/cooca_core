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
        Schema::table('cost_models', function (Blueprint $table): void {
            $table->json('formula_definition')->nullable()->after('output_basis');
            $table->unsignedInteger('formula_version')->default(1)->after('formula_definition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cost_models', function (Blueprint $table): void {
            $table->dropColumn(['formula_definition', 'formula_version']);
        });
    }
};
