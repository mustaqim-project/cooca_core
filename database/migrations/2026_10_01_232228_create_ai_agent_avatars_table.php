<?php

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
        Schema::create('ai_agent_avatars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('agent_role', 50); // ceo, cfo, coo, cmo, sales, inventory, etc.
            $table->string('custom_name', 100)->nullable();
            $table->string('preset', 50)->default('executive_male');
            $table->string('suit_color', 20)->default('#0f172a');
            $table->string('skin_tone', 20)->default('#fbcfe8');
            $table->string('hair_color', 20)->default('#18181b');
            $table->string('hair_style', 50)->default('classic');
            $table->string('accessory', 50)->default('none');
            $table->string('avatar_icon', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'agent_role'], 'idx_biz_agent_avatar');
            $table->index(['business_id'], 'idx_agent_avatar_biz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_agent_avatars');
    }
};
