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
        Schema::table('locations', function (Blueprint $table): void {
            if (! Schema::hasColumn('locations', 'parent_id')) {
                $table->foreignUuid('parent_id')
                    ->nullable()
                    ->after('business_id')
                    ->constrained('locations')
                    ->nullOnDelete();

                $table->index(['business_id', 'parent_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table): void {
            if (Schema::hasColumn('locations', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropIndex(['business_id', 'parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
