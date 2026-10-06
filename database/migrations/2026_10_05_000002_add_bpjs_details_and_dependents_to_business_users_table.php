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
        Schema::table('business_users', function (Blueprint $table): void {
            if (! Schema::hasColumn('business_users', 'nik_ktp')) {
                $table->string('nik_ktp', 30)->nullable()->after('whatsapp_number');
            }
            if (! Schema::hasColumn('business_users', 'npwp')) {
                $table->string('npwp', 30)->nullable()->after('nik_ktp');
            }
            if (! Schema::hasColumn('business_users', 'bpjs_tk_number')) {
                $table->string('bpjs_tk_number', 50)->nullable()->after('bpjs_tk_enabled');
            }
            if (! Schema::hasColumn('business_users', 'bpjs_kes_number')) {
                $table->string('bpjs_kes_number', 50)->nullable()->after('bpjs_kes_enabled');
            }
            if (! Schema::hasColumn('business_users', 'bpjs_dependents_count')) {
                $table->unsignedSmallInteger('bpjs_dependents_count')->default(0)->after('bpjs_kes_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_users', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('business_users', 'bpjs_dependents_count')) {
                $columns[] = 'bpjs_dependents_count';
            }
            if (Schema::hasColumn('business_users', 'bpjs_kes_number')) {
                $columns[] = 'bpjs_kes_number';
            }
            if (Schema::hasColumn('business_users', 'bpjs_tk_number')) {
                $columns[] = 'bpjs_tk_number';
            }
            if (Schema::hasColumn('business_users', 'npwp')) {
                $columns[] = 'npwp';
            }
            if (Schema::hasColumn('business_users', 'nik_ktp')) {
                $columns[] = 'nik_ktp';
            }

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
