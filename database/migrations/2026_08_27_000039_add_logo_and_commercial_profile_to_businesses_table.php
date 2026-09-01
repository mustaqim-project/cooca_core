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
            $table->string('logo_path')->nullable()->after('name');
            $table->string('phone', 50)->nullable()->after('description');
            $table->string('email', 150)->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            $table->string('tax_identification_number', 50)->nullable()->after('address');
            $table->string('bank_name', 100)->nullable()->after('tax_identification_number');
            $table->string('bank_account_number', 100)->nullable()->after('bank_name');
            $table->string('bank_account_holder', 150)->nullable()->after('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table): void {
            $table->dropColumn([
                'logo_path',
                'phone',
                'email',
                'address',
                'tax_identification_number',
                'bank_name',
                'bank_account_number',
                'bank_account_holder',
            ]);
        });
    }
};
