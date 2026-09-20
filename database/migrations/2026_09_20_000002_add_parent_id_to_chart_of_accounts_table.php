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
        Schema::table('chart_of_accounts', function (Blueprint $table): void {
            $table->foreignUuid('parent_id')
                ->nullable()
                ->after('business_id')
                ->constrained('chart_of_accounts')
                ->nullOnDelete();

            $table->index(['business_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chart_of_accounts', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['business_id', 'parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
