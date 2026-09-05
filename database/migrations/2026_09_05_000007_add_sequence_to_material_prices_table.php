<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_prices', function (Blueprint $table): void {
            if (! Schema::hasColumn('material_prices', 'sequence')) {
                $table->unsignedBigInteger('sequence')->default(0)->after('material_id');
                $table->index(['material_id', 'effective_date', 'sequence']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('material_prices', function (Blueprint $table): void {
            if (Schema::hasColumn('material_prices', 'sequence')) {
                $table->dropIndex(['material_id', 'effective_date', 'sequence']);
                $table->dropColumn('sequence');
            }
        });
    }
};
