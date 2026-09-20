<?php

declare(strict_types=1);

namespace App\Domain\Storefront;

use App\Models\BusinessLandingPage;

final class StorefrontThemeService
{
    /**
     * Complete catalogue of 20 Authentic Industry Themes (§PRD-07).
     *
     * @var array<string, array<string, mixed>>
     */
    private const THEMES = [
        'artisan_brew' => [
            'id' => 'artisan_brew',
            'name' => 'Artisan Brew',
            'industry' => 'Kafe & Kopi Spesialis',
            'font_heading' => 'Playfair Display',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Playfair+Display:ital,wght@0,400..800;1,400..800&family=Inter:wght@300;400;500;600;700',
            'primary_color' => '#8B5A2B',
            'accent_color' => '#C88A58',
            'bg_color' => '#FAF7F2',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'cinematic_overlay',
            'badge_bg' => '#8B5A2B1A',
            'badge_text' => '#8B5A2B',
            'border_radius' => '16px',
            'description' => 'Aura hangat kayu & aroma kopi sangrai, tipografi serif elegan untuk kafe & roastery.',
        ],
        'nusantara_feast' => [
            'id' => 'nusantara_feast',
            'name' => 'Nusantara Feast',
            'industry' => 'Restoran Masakan Nusantara',
            'font_heading' => 'DM Serif Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800',
            'primary_color' => '#991B1B',
            'accent_color' => '#D97706',
            'bg_color' => '#FFFBEB',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'warm_editorial',
            'badge_bg' => '#991B1B15',
            'badge_text' => '#991B1B',
            'border_radius' => '14px',
            'description' => 'Nuansa terracotta rempah & tradisi kuliner nusantara, visual piring lebar menggugah selera.',
        ],
        'neon_crunch' => [
            'id' => 'neon_crunch',
            'name' => 'Neon Crunch',
            'industry' => 'Fast Food, Burger & Snack',
            'font_heading' => 'Outfit',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Outfit:wght@600;700;800;900&family=Inter:wght@400;500;600;700',
            'primary_color' => '#DC2626',
            'accent_color' => '#F59E0B',
            'bg_color' => '#FFF1F2',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'high_energy',
            'badge_bg' => '#DC262618',
            'badge_text' => '#DC2626',
            'border_radius' => '20px',
            'description' => 'Warna merah cerah penuh gairah & diskon kontras tinggi, ideal untuk gerai fast food modern.',
        ],
        'velvet_patisserie' => [
            'id' => 'velvet_patisserie',
            'name' => 'Velvet Patisserie',
            'industry' => 'Bakery, Pastry & Toko Kue',
            'font_heading' => 'Cormorant Garamond',
            'font_body' => 'Poppins',
            'google_fonts' => 'family=Cormorant+Garamond:ital,wght@0,500;0,700;1,500&family=Poppins:wght@300;400;500;600;700',
            'primary_color' => '#BE185D',
            'accent_color' => '#D4AF37',
            'bg_color' => '#FDF2F8',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'soft_patisserie',
            'badge_bg' => '#BE185D15',
            'badge_text' => '#BE185D',
            'border_radius' => '18px',
            'description' => 'Sentuhan pastel rose & emas sampanye anggun, sempurna untuk cake artisan & hampers.',
        ],
        'epicurean_box' => [
            'id' => 'epicurean_box',
            'name' => 'Epicurean Box',
            'industry' => 'Katering & Healthy Meal Prep',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#059669',
            'accent_color' => '#10B981',
            'bg_color' => '#F0FDF4',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'healthy_fresh',
            'badge_bg' => '#05966915',
            'badge_text' => '#059669',
            'border_radius' => '16px',
            'description' => 'Kesegaran hijau zamrud higienis & tabel menu mingguan, dirancang untuk diet katering.',
        ],
        'vogue_minimalist' => [
            'id' => 'vogue_minimalist',
            'name' => 'Vogue Minimalist',
            'industry' => 'Fashion, Apparel & Butik',
            'font_heading' => 'Cormorant Garamond',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600',
            'primary_color' => '#18181B',
            'accent_color' => '#71717A',
            'bg_color' => '#FAFAFA',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'editorial_clean',
            'badge_bg' => '#18181B10',
            'badge_text' => '#18181B',
            'border_radius' => '8px',
            'description' => 'Tata letak editorial majalah mode monokrom berkelas tinggi, fokus tajam pada siluet pakaian.',
        ],
        'nexus_cyber' => [
            'id' => 'nexus_cyber',
            'name' => 'Nexus Dark Cyber',
            'industry' => 'Gadget, Komputer & Elektronik',
            'font_heading' => 'Space Grotesk',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600',
            'primary_color' => '#06B6D4',
            'accent_color' => '#8B5CF6',
            'bg_color' => '#0B0F19',
            'card_bg' => '#111827',
            'header_dark' => true,
            'hero_style' => 'dark_tech_grid',
            'badge_bg' => '#06B6D420',
            'badge_text' => '#22D3EE',
            'border_radius' => '12px',
            'description' => 'Dark mode futuristik pekat beraksen neon cyan, spesifikasi teknologi terstruktur jelas.',
        ],
        'fresh_mart' => [
            'id' => 'fresh_mart',
            'name' => 'Fresh Mart Express',
            'industry' => 'Minimarket, Sayur & Sembako',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#16A34A',
            'accent_color' => '#EA580C',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'catalog_quick',
            'badge_bg' => '#16A34A15',
            'badge_text' => '#16A34A',
            'border_radius' => '12px',
            'description' => 'Kepadatan produk efisien dengan tombol belanja kilat + / -, ramah ibu rumah tangga & lansia.',
        ],
        'apex_velocity' => [
            'id' => 'apex_velocity',
            'name' => 'Apex Velocity',
            'industry' => 'Bengkel, Onderdil & Otomotif',
            'font_heading' => 'Chakra Petch',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;500;600',
            'primary_color' => '#EA580C',
            'accent_color' => '#F97316',
            'bg_color' => '#0F172A',
            'card_bg' => '#1E293B',
            'header_dark' => true,
            'hero_style' => 'racing_carbon',
            'badge_bg' => '#EA580C25',
            'badge_text' => '#FB923C',
            'border_radius' => '10px',
            'description' => 'Gaya karbon balap & oranye mekanik tangguh, filter kompatibilitas jenis kendaraan akurat.',
        ],
        'clinical_pure' => [
            'id' => 'clinical_pure',
            'name' => 'Clinical Pure Trust',
            'industry' => 'Apotek, Farmasi & Alkes',
            'font_heading' => 'Inter',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Inter:wght@400;500;600;700;800',
            'primary_color' => '#0D9488',
            'accent_color' => '#0284C7',
            'bg_color' => '#F0FDFA',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'medical_clean',
            'badge_bg' => '#0D948815',
            'badge_text' => '#0D9488',
            'border_radius' => '12px',
            'description' => 'Nuansa toska medis terpercaya, upload resep dokter langsung terhubung ke apoteker.',
        ],
        'aura_glamour' => [
            'id' => 'aura_glamour',
            'name' => 'Aura Glamour',
            'industry' => 'Klinik Kecantikan, Skincare & Salon',
            'font_heading' => 'Playfair Display',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#9D174D',
            'accent_color' => '#B76E79',
            'bg_color' => '#FFF5F7',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'luxury_spa',
            'badge_bg' => '#9D174D15',
            'badge_text' => '#9D174D',
            'border_radius' => '22px',
            'description' => 'Kombinasi rose gold & marmer lembut, galeri transformasi treatment & booking estetik.',
        ],
        'aqua_bubble' => [
            'id' => 'aqua_bubble',
            'name' => 'Aqua Bubble Clean',
            'industry' => 'Laundry Kiloan, Satuan & Sepatu',
            'font_heading' => 'Plus Jakarta Sans',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#0284C7',
            'accent_color' => '#38BDF8',
            'bg_color' => '#F0F9FF',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'clean_water',
            'badge_bg' => '#0284C715',
            'badge_text' => '#0284C7',
            'border_radius' => '16px',
            'description' => 'Biru laut segar dan higienis, kalkulator kiloan cuci & pelacak nota cucian langsung.',
        ],
        'ironclad_builder' => [
            'id' => 'ironclad_builder',
            'name' => 'Ironclad Builder',
            'industry' => 'Toko Bangunan & Perkakas',
            'font_heading' => 'Barlow Semi Condensed',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Barlow+Semi+Condensed:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#475569',
            'accent_color' => '#EAB308',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'industrial_heavy',
            'badge_bg' => '#EAB30825',
            'badge_text' => '#A16207',
            'border_radius' => '8px',
            'description' => 'Abu-abu semen kokoh dengan penanda safety yellow, kalkulator material per meter persegi.',
        ],
        'pixel_print' => [
            'id' => 'pixel_print',
            'name' => 'Pixel & Print Studio',
            'industry' => 'Percetakan, Sablon & Desain',
            'font_heading' => 'Syne',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Syne:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#7C3AED',
            'accent_color' => '#EC4899',
            'bg_color' => '#FAF5FF',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'creative_studio',
            'badge_bg' => '#7C3AED15',
            'badge_text' => '#7C3AED',
            'border_radius' => '14px',
            'description' => 'Tipografi Syne ekspresif bernuansa studio kreatif, form kirim file cetak pelanggan.',
        ],
        'bibliotheca' => [
            'id' => 'bibliotheca',
            'name' => 'Bibliotheca',
            'industry' => 'Toko Buku, Musik & Stationery',
            'font_heading' => 'Merriweather',
            'font_body' => 'DM Sans',
            'google_fonts' => 'family=Merriweather:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@400;500;700',
            'primary_color' => '#1E293B',
            'accent_color' => '#94A3B8',
            'bg_color' => '#FDFBF7',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'warm_reading',
            'badge_bg' => '#1E293B10',
            'badge_text' => '#1E293B',
            'border_radius' => '10px',
            'description' => 'Nuansa perkamen kertas hangat nan bersahaja, nyaman untuk preview sinopsis & sampul buku.',
        ],
        'playful_paws' => [
            'id' => 'playful_paws',
            'name' => 'Playful Paws',
            'industry' => 'Pet Shop, Klinik & Grooming Hewan',
            'font_heading' => 'Quicksand',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Quicksand:wght@600;700&family=Inter:wght@400;500;600',
            'primary_color' => '#D97706',
            'accent_color' => '#10B981',
            'bg_color' => '#FEF3C7',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'cheerful_rounded',
            'badge_bg' => '#D9770615',
            'badge_text' => '#D97706',
            'border_radius' => '24px',
            'description' => 'Lengkungan ramah bertema biskuit hangat & mint, booking antrean salon anabul mudah.',
        ],
        'hydro_shield' => [
            'id' => 'hydro_shield',
            'name' => 'Hydro Shield Gloss',
            'industry' => 'Cuci Mobil, Motor & Auto Detailing',
            'font_heading' => 'Orbitron',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Orbitron:wght@600;700;800&family=Inter:wght@400;500;600',
            'primary_color' => '#2563EB',
            'accent_color' => '#06B6D4',
            'bg_color' => '#0B0F19',
            'card_bg' => '#111827',
            'header_dark' => true,
            'hero_style' => 'glossy_dark',
            'badge_bg' => '#2563EB25',
            'badge_text' => '#60A5FA',
            'border_radius' => '14px',
            'description' => 'Tampilan hitam pekat kilau hidrofobik, komparasi paket poles nano ceramic coating.',
        ],
        'flora_romance' => [
            'id' => 'flora_romance',
            'name' => 'Flora Romance',
            'industry' => 'Florist & Buket Bunga Segar',
            'font_heading' => 'Bodoni Moda',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Bodoni+Moda:ital,wght@0,500;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600',
            'primary_color' => '#4D7C0F',
            'accent_color' => '#F472B6',
            'bg_color' => '#F7FEE7',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'botanical_romance',
            'badge_bg' => '#4D7C0F15',
            'badge_text' => '#4D7C0F',
            'border_radius' => '20px',
            'description' => 'Hijau botani asri dipadu sentuhan merah muda kelopak, form kartu ucapan custom di checkout.',
        ],
        'titan_kinetic' => [
            'id' => 'titan_kinetic',
            'name' => 'Titan Kinetic',
            'industry' => 'Fitness, Gym & Studio Olahraga',
            'font_heading' => 'Bebas Neue',
            'font_body' => 'Inter',
            'google_fonts' => 'family=Bebas+Neue&family=Inter:wght@400;500;600;700',
            'primary_color' => '#84CC16',
            'accent_color' => '#A3E635',
            'bg_color' => '#171717',
            'card_bg' => '#262626',
            'header_dark' => true,
            'hero_style' => 'athletic_power',
            'badge_bg' => '#84CC1620',
            'badge_text' => '#A3E635',
            'border_radius' => '8px',
            'description' => 'Tipografi bold bertenaga dengan aksen acid lime menyala, perbandingan paket langganan gym.',
        ],
        'sovereign_enterprise' => [
            'id' => 'sovereign_enterprise',
            'name' => 'Sovereign Enterprise',
            'industry' => 'Konsultan, Hukum, Agensi & Jasa B2B',
            'font_heading' => 'Cormorant Garamond',
            'font_body' => 'Plus Jakarta Sans',
            'google_fonts' => 'family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700',
            'primary_color' => '#0F172A',
            'accent_color' => '#3B82F6',
            'bg_color' => '#F8FAFC',
            'card_bg' => '#FFFFFF',
            'header_dark' => false,
            'hero_style' => 'corporate_prestige',
            'badge_bg' => '#0F172A10',
            'badge_text' => '#0F172A',
            'border_radius' => '12px',
            'description' => 'Wibawa biru korporat diplomatik berpadu tipografi terpercaya, formulir proposal RFQ resmi.',
        ],
    ];

    /**
     * Get all available theme presets.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllThemes(): array
    {
        return self::THEMES;
    }

    /**
     * Get configuration for a specific theme key.
     *
     * @param string $presetKey
     * @return array<string, mixed>
     */
    public function getTheme(string $presetKey): array
    {
        return self::THEMES[$presetKey] ?? self::THEMES['artisan_brew'];
    }

    /**
     * Resolve effective theme configuration for a landing page.
     *
     * @param BusinessLandingPage $landingPage
     * @return array<string, mixed>
     */
    public function resolveTheme(BusinessLandingPage $landingPage): array
    {
        $presetKey = $landingPage->getThemePreset();
        $theme = $this->getTheme($presetKey);

        // Allow user manual color override if explicitly specified
        if (! empty($landingPage->theme_color) && $landingPage->theme_color !== '#10B981' && $landingPage->theme_color !== '#007AFF') {
            $theme['primary_color'] = $landingPage->theme_color;
        }

        // Allow user font override if explicitly specified
        if (! empty($landingPage->font_family) && $landingPage->font_family !== 'Plus Jakarta Sans') {
            $theme['font_heading'] = $landingPage->font_family;
            $theme['font_body'] = $landingPage->font_family;
        }

        // Allow dark mode override
        if ($landingPage->dark_mode) {
            $theme['header_dark'] = true;
            $theme['bg_color'] = '#0B0F19';
            $theme['card_bg'] = '#181E2A';
        }

        return $theme;
    }
}
