<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Uniqueness master data:
 * - materials.code UNIQUE per business
 * - products.code UNIQUE per business
 *
 * Database menjadi proteksi akhir terhadap race condition untuk duplicate SKU/code.
 * Data existing yang duplikat (hasil seed) diperbaiki dengan suffix deterministik.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $this->dedupeCodes('materials');
            $this->dedupeCodes('products');
        }

        Schema::table('materials', function (Blueprint $table): void {
            if (Schema::hasIndex('materials', ['business_id', 'code'])) {
                $table->dropIndex(['business_id', 'code']);
            }
            $table->unique(['business_id', 'code'], 'materials_business_id_code_unique');
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasIndex('products', ['business_id', 'code'])) {
                $table->dropIndex(['business_id', 'code']);
            }
            $table->unique(['business_id', 'code'], 'products_business_id_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table): void {
            $table->dropUnique('materials_business_id_code_unique');
            $table->index(['business_id', 'code']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique('products_business_id_code_unique');
            $table->index(['business_id', 'code']);
        });
    }

    /**
     * Beri suffix deterministik pada baris duplikat agar unique constraint bisa berdiri.
     */
    private function dedupeCodes(string $table): void
    {
        $groups = DB::table($table)
            ->select('business_id', 'code')
            ->whereNotNull('code')
            ->where('code', '!=', '')
            ->groupBy('business_id', 'code')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $rows = DB::table($table)
                ->where('business_id', $group->business_id)
                ->where('code', $group->code)
                ->orderBy('created_at')
                ->get();

            $suffix = 1;
            foreach ($rows as $idx => $row) {
                if ($idx === 0) {
                    continue; // pertahankan baris pertama apa adanya
                }

                $tentative = $group->code . '-DUP' . $suffix;
                while (
                    DB::table($table)
                        ->where('business_id', $row->business_id)
                        ->where('code', $tentative)
                        ->exists()
                ) {
                    $suffix++;
                    $tentative = $group->code . '-DUP' . $suffix;
                }

                DB::table($table)->where('id', $row->id)->update(['code' => $tentative]);
            }
        }
    }
};