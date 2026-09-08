<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessLandingPage extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $table = 'business_landing_pages';

    protected $fillable = [
        'business_id',
        'is_published',
        'industry_preset',
        'theme_color',
        'font_family',
        'dark_mode',
        'headline',
        'subheadline',
        'announcement_badge',
        'hero_image_url',
        'logo_url',
        'cta_primary_text',
        'cta_primary_url',
        'cta_secondary_text',
        'cta_secondary_url',
        'about_title',
        'about_story',
        'about_image_url',
        'values',
        'operational_hours',
        'show_pos_products',
        'services_title',
        'services_subtitle',
        'custom_services',
        'gallery_images',
        'gallery_title',
        'gallery_subtitle',
        'section_visibility',
        'testimonials',
        'faqs',
        'stats',
        'social_links',
        'google_maps_embed_url',
        'custom_phone',
        'custom_email',
        'whatsapp_number',
        'whatsapp_welcome_message',
        'instagram_handle',
        'tiktok_handle',
        'facebook_url',
        'custom_address',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_url',
        'footer_description',
        'footer_navigation_title',
        'footer_services_title',
        'footer_contact_title',
        'footer_cta_text',
        'footer_copyright',
    ];

    protected $casts = [
        'is_published'       => 'boolean',
        'dark_mode'          => 'boolean',
        'show_pos_products'  => 'boolean',
        'values'             => 'array',
        'operational_hours'  => 'array',
        'custom_services'    => 'array',
        'gallery_images'     => 'array',
        'testimonials'       => 'array',
        'faqs'               => 'array',
        'stats'              => 'array',
        'social_links'       => 'array',
        'section_visibility' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Generate the direct WhatsApp ordering URL with pre-filled greeting.
     */
    public function getWhatsAppUrl(?string $serviceName = null): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->whatsapp_number ?: $this->custom_phone ?: $this->business->phone ?: '');

        if (! $phone) {
            return '#';
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $msg = $this->whatsapp_welcome_message;

        if ($serviceName) {
            $msg = "Halo {$this->business->name}, saya ingin memesan / menanyakan mengenai: *{$serviceName}*. Apakah masih tersedia?";
        }

        return $msg ? "https://wa.me/{$phone}?text=" . rawurlencode($msg) : "https://wa.me/{$phone}";
    }
}
