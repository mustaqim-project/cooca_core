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
        Schema::table('payment_settlements', function (Blueprint $table): void {
            $table->string('proof_image_path')->nullable()->after('notes');
            $table->foreignUuid('admin_id')->nullable()->after('proof_image_path')->constrained('admins')->nullOnDelete();
            $table->timestamp('transferred_at')->nullable()->after('admin_id');
            $table->text('admin_notes')->nullable()->after('transferred_at');
            $table->text('rejection_reason')->nullable()->after('admin_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_settlements', function (Blueprint $table): void {
            $table->dropForeign(['admin_id']);
            $table->dropColumn([
                'proof_image_path',
                'admin_id',
                'transferred_at',
                'admin_notes',
                'rejection_reason',
            ]);
        });
    }
};
