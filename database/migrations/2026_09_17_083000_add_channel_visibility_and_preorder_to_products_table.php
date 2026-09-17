<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'show_in_website')) {
                    $table->boolean('show_in_website')->default(true)->after('is_active');
                }
                if (! Schema::hasColumn('products', 'show_in_pos')) {
                    $table->boolean('show_in_pos')->default(true)->after('show_in_website');
                }
                if (! Schema::hasColumn('products', 'show_in_sales_order')) {
                    $table->boolean('show_in_sales_order')->default(true)->after('show_in_pos');
                }
                if (! Schema::hasColumn('products', 'show_price_on_web')) {
                    $table->boolean('show_price_on_web')->default(true)->after('show_in_sales_order');
                }
                if (! Schema::hasColumn('products', 'is_preorder')) {
                    $table->boolean('is_preorder')->default(false)->after('show_price_on_web');
                }
                if (! Schema::hasColumn('products', 'preorder_mode')) {
                    $table->string('preorder_mode', 30)->default('customer_schedule')->after('is_preorder');
                }
                if (! Schema::hasColumn('products', 'preorder_lead_days')) {
                    $table->unsignedSmallInteger('preorder_lead_days')->default(1)->after('preorder_mode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                $columnsToDrop = [];
                foreach ([
                    'show_in_website',
                    'show_in_pos',
                    'show_in_sales_order',
                    'show_price_on_web',
                    'is_preorder',
                    'preorder_mode',
                    'preorder_lead_days',
                ] as $col) {
                    if (Schema::hasColumn('products', $col)) {
                        $columnsToDrop[] = $col;
                    }
                }
                if (! empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
