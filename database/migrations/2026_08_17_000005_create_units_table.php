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
        Schema::create('units', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->nullable()->constrained('businesses')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name', 100);
            $table->string('category', 50); // weight, volume, length, quantity, time, area, custom
            $table->boolean('is_base')->default(false);
            $table->unsignedTinyInteger('default_precision')->default(2);
            $table->timestamps();

            $table->index(['business_id', 'category']);
            $table->index(['business_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
