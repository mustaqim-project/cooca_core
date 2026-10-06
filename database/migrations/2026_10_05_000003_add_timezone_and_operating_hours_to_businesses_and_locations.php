<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('businesses')) {
            Schema::table('businesses', function (Blueprint $table) {
                if (! Schema::hasColumn('businesses', 'timezone')) {
                    $table->string('timezone', 50)->default('Asia/Jakarta')->after('currency_precision');
                }
                if (! Schema::hasColumn('businesses', 'operating_hours')) {
                    $table->json('operating_hours')->nullable()->after('disabled_modules');
                }
            });

            // Backfill existing businesses with explicit default IANA timezone
            DB::table('businesses')
                ->whereNull('timezone')
                ->orWhere('timezone', '')
                ->update(['timezone' => 'Asia/Jakarta']);
        }

        if (Schema::hasTable('locations')) {
            Schema::table('locations', function (Blueprint $table) {
                if (! Schema::hasColumn('locations', 'timezone')) {
                    $table->string('timezone', 50)->nullable()->after('postal_code');
                }
                if (! Schema::hasColumn('locations', 'timezone_mode')) {
                    $table->string('timezone_mode', 20)->default('inherit')->after('timezone');
                }
                if (! Schema::hasColumn('locations', 'operating_hours_mode')) {
                    $table->string('operating_hours_mode', 20)->default('inherit')->after('timezone_mode');
                }
                if (! Schema::hasColumn('locations', 'operating_hours')) {
                    $table->json('operating_hours')->nullable()->after('operating_hours_mode');
                }
            });

            // Backfill existing locations
            DB::table('locations')
                ->whereNull('timezone_mode')
                ->update(['timezone_mode' => 'inherit']);

            DB::table('locations')
                ->whereNull('operating_hours_mode')
                ->update(['operating_hours_mode' => 'inherit']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('businesses')) {
            Schema::table('businesses', function (Blueprint $table) {
                if (Schema::hasColumn('businesses', 'operating_hours')) {
                    $table->dropColumn('operating_hours');
                }
                if (Schema::hasColumn('businesses', 'timezone')) {
                    $table->dropColumn('timezone');
                }
            });
        }

        if (Schema::hasTable('locations')) {
            Schema::table('locations', function (Blueprint $table) {
                if (Schema::hasColumn('locations', 'operating_hours')) {
                    $table->dropColumn('operating_hours');
                }
                if (Schema::hasColumn('locations', 'operating_hours_mode')) {
                    $table->dropColumn('operating_hours_mode');
                }
                if (Schema::hasColumn('locations', 'timezone_mode')) {
                    $table->dropColumn('timezone_mode');
                }
                if (Schema::hasColumn('locations', 'timezone')) {
                    $table->dropColumn('timezone');
                }
            });
        }
    }
};
