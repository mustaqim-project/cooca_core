<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_landing_pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('business_id')->unique()->constrained('businesses')->cascadeOnDelete();
            $table->boolean('is_published')->default(true);
            $table->string('industry_preset')->default('retail');
            $table->string('theme_color')->default('#10B981'); // Emerald green default
            $table->string('font_family')->default('Plus Jakarta Sans');
            $table->boolean('dark_mode')->default(false);

            // Hero Section
            $table->string('headline')->nullable();
            $table->text('subheadline')->nullable();
            $table->string('announcement_badge')->nullable();
            $table->string('hero_image_url', 500)->nullable();
            $table->string('cta_primary_text')->default('Hubungi Kami via WhatsApp');
            $table->string('cta_primary_url', 500)->nullable();
            $table->string('cta_secondary_text')->default('Lihat Katalog & Layanan');
            $table->string('cta_secondary_url', 500)->nullable();

            // About & Story
            $table->string('about_title')->nullable();
            $table->text('about_story')->nullable();
            $table->string('about_image_url', 500)->nullable();
            $table->json('values')->nullable(); // [{"icon": "shield-check", "title": "...", "desc": "..."}]

            // Operational Hours
            $table->json('operational_hours')->nullable(); // [{"day": "Senin", "hours": "08:00 - 17:00", "is_open": true}]

            // Services & Products Showcase
            $table->boolean('show_pos_products')->default(true);
            $table->string('services_title')->nullable();
            $table->text('services_subtitle')->nullable();
            $table->json('custom_services')->nullable(); // [{"title": "...", "desc": "...", "price": "...", "badge": "...", "image_url": "..."}]

            // Gallery & Portfolio
            $table->json('gallery_images')->nullable(); // ["url1", "url2", ...]

            // Testimonials
            $table->json('testimonials')->nullable(); // [{"name": "...", "role": "...", "quote": "...", "rating": 5, "avatar": "..."}]

            // FAQ Accordion
            $table->json('faqs')->nullable(); // [{"question": "...", "answer": "..."}]

            // Contact & Social Media
            $table->text('google_maps_embed_url')->nullable();
            $table->string('custom_phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->text('whatsapp_welcome_message')->nullable();
            $table->string('instagram_handle')->nullable();
            $table->string('tiktok_handle')->nullable();
            $table->string('facebook_url', 500)->nullable();
            $table->string('custom_address')->nullable();

            // SEO Metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->string('og_image_url', 500)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_landing_pages');
    }
};
