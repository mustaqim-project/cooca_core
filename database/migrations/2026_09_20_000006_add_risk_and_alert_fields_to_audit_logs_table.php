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
        Schema::table('audit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('audit_logs', 'risk_level')) {
                $table->string('risk_level', 20)->default('low')->after('action');
            }
            if (! Schema::hasColumn('audit_logs', 'risk_reason')) {
                $table->string('risk_reason', 255)->nullable()->after('risk_level');
            }
            if (! Schema::hasColumn('audit_logs', 'notes')) {
                $table->text('notes')->nullable()->after('new_values');
            }
            if (! Schema::hasColumn('audit_logs', 'alert_sent_at')) {
                $table->timestamp('alert_sent_at')->nullable()->after('user_agent');
            }
            if (! Schema::hasColumn('audit_logs', 'alert_recipient')) {
                $table->string('alert_recipient', 50)->nullable()->after('alert_sent_at');
            }

            $table->index(['business_id', 'risk_level', 'created_at']);
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            if (! Schema::hasColumn('suppliers', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('address');
            }
            if (! Schema::hasColumn('suppliers', 'bank_account_number')) {
                $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('suppliers', 'bank_account_holder')) {
                $table->string('bank_account_holder', 150)->nullable()->after('bank_account_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex(['business_id', 'risk_level', 'created_at']);
            $table->dropColumn(['risk_level', 'risk_reason', 'notes', 'alert_sent_at', 'alert_recipient']);
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_holder']);
        });
    }
};
