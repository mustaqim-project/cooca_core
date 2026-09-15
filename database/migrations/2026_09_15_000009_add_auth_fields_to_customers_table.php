<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (! Schema::hasColumn('customers', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
            if (! Schema::hasColumn('customers', 'avatar_url')) {
                $table->string('avatar_url', 500)->nullable()->after('name');
            }
            if (! Schema::hasColumn('customers', 'phone_verified_at')) {
                $table->dateTime('phone_verified_at')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('customers', 'email_verified_at')) {
                $table->dateTime('email_verified_at')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            $columns = [];
            if (Schema::hasColumn('customers', 'password')) {
                $columns[] = 'password';
            }
            if (Schema::hasColumn('customers', 'remember_token')) {
                $columns[] = 'remember_token';
            }
            if (Schema::hasColumn('customers', 'avatar_url')) {
                $columns[] = 'avatar_url';
            }
            if (Schema::hasColumn('customers', 'phone_verified_at')) {
                $columns[] = 'phone_verified_at';
            }
            if (Schema::hasColumn('customers', 'email_verified_at')) {
                $columns[] = 'email_verified_at';
            }

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
