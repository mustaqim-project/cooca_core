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
        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->unsignedInteger('print_count')->default(0)->after('status');
            $table->unsignedInteger('reprint_count')->default(0)->after('print_count');
            $table->timestamp('first_printed_at')->nullable()->after('reprint_count');
            $table->timestamp('last_printed_at')->nullable()->after('first_printed_at');
            $table->foreignUuid('last_printed_by')->nullable()->after('last_printed_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table): void {
            $table->dropForeign(['last_printed_by']);
            $table->dropColumn([
                'print_count',
                'reprint_count',
                'first_printed_at',
                'last_printed_at',
                'last_printed_by',
            ]);
        });
    }
};
