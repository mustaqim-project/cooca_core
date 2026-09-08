<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RestoranNusantaraLandingSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::where('slug', 'restoran-nusantara-rasa')->first();

        if (!$business) {
            $business = Business::create([
                'id' => (string) Str::uuid(),
                'name' => 'Restoran Nusantara Rasa',
                'slug' => 'restoran-nusantara-rasa',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'phone' => '0812-3456-7890',
                'email' => 'reservasi@nusantararasa.id',
                'address' => 'Jl. Sabang No. 18, Menteng, Jakarta Pusat 10350',
                'pos_show_product_images' => true,
            ]);
        } else {
            $business->update([
                'phone' => '0812-3456-7890',
                'email' => 'reservasi@nusantararasa.id',
                'address' => 'Jl. Sabang No. 18, Menteng, Jakarta Pusat 10350',
                'pos_show_product_images' => true,
            ]);
        }

        // Units
        $portion = Unit::where('code', 'porsi')->first() ?? Unit::where('code', 'pcs')->first() ?? Unit::first();
        $glass = Unit::where('code', 'gelas')->first() ?? $portion;
        $box = Unit::where('code', 'box')->first() ?? $portion;

        // 1. Kategori Produk Kuliner
        $catMakanan = ProductCategory::updateOrCreate(
            ['business_id' => $business->id, 'slug' => 'makanan-utama'],
            ['name' => 'Makanan Utama', 'description' => 'Hidangan nasi, daging, ayam, dan olahan rempah khas daerah.']
        );

        $catMinuman = ProductCategory::updateOrCreate(
            ['business_id' => $business->id, 'slug' => 'minuman-tradisional'],
            ['name' => 'Minuman Nusantara', 'description' => 'Kopi seduh, es tradisional, dan ramuan rempah segar.']
        );

        $catCamilan = ProductCategory::updateOrCreate(
            ['business_id' => $business->id, 'slug' => 'kudapan-camilan'],
            ['name' => 'Kudapan & Camilan', 'description' => 'Gorengan renyah dan jajanan pasar pembuka selera.']
        );

        $catKatering = ProductCategory::updateOrCreate(
            ['business_id' => $business->id, 'slug' => 'paket-katering'],
            ['name' => 'Paket Katering & Acara', 'description' => 'Nasi box, bento meeting, dan tumpeng mini untuk acara.']
        );

        // 2. Daftar Produk Nyata dengan Foto Lokal
        $products = [
            [
                'name' => 'Rendang Daging Sapi Payakumbuh',
                'slug' => 'rendang-daging-sapi-payakumbuh',
                'sku' => 'FNB-RND-001',
                'category_id' => $catMakanan->id,
                'output_unit_id' => $portion->id,
                'base_cost' => 28000,
                'selling_price' => 45000,
                'min_stock' => 10,
                'description' => 'Daging sapi pilihan dimasak perlahan selama 6 jam dengan santan murni dan racikan rempah Minang pekat hingga empuk dan meresap sempurna.',
                'image_path' => 'products/rendang.jpg',
            ],
            [
                'name' => 'Sate Maranggi Purwakarta',
                'slug' => 'sate-maranggi-purwakarta',
                'sku' => 'FNB-SAT-002',
                'category_id' => $catMakanan->id,
                'output_unit_id' => $portion->id,
                'base_cost' => 22000,
                'selling_price' => 38000,
                'min_stock' => 15,
                'description' => '10 tusuk sate daging sapi marinasi ketumbar dan gula aren, dibakar arang kelapa, disajikan dengan sambal kecap tomat rawit segar.',
                'image_path' => 'products/satay.jpg',
            ],
            [
                'name' => 'Nasi Goreng Ayam Spesial Nusantara',
                'slug' => 'nasi-goreng-ayam-spesial-nusantara',
                'sku' => 'FNB-NAS-003',
                'category_id' => $catMakanan->id,
                'output_unit_id' => $portion->id,
                'base_cost' => 14000,
                'selling_price' => 28000,
                'min_stock' => 20,
                'description' => 'Nasi goreng beraroma bumbu rempah bawang khas Jawa Tengah, suwiran ayam gurih, telur mata sapi, acar segar, dan kerupuk udang.',
                'image_path' => 'products/rendang.jpg',
            ],
            [
                'name' => 'Ayam Bakar Madu Pedas Manis',
                'slug' => 'ayam-bakar-madu-pedas-manis',
                'sku' => 'FNB-AYM-004',
                'category_id' => $catMakanan->id,
                'output_unit_id' => $portion->id,
                'base_cost' => 17000,
                'selling_price' => 32000,
                'min_stock' => 15,
                'description' => 'Paha/dada ayam pejantan ungkep rempah kuning, diolesi madu hutan dan cabai bakar, disajikan dengan lalapan kemangi dan sambal terasi.',
                'image_path' => 'products/satay.jpg',
            ],
            [
                'name' => 'Es Cendol Dawet Durian Montong',
                'slug' => 'es-cendol-dawet-durian-montong',
                'sku' => 'BEV-CND-005',
                'category_id' => $catMinuman->id,
                'output_unit_id' => $glass->id,
                'base_cost' => 11000,
                'selling_price' => 24000,
                'min_stock' => 25,
                'description' => 'Bulir cendol pandan wangi kenyal, santan kelapa perah segar, sirup gula aren kental, nangka matang, dan topping durian montong manis harum.',
                'image_path' => 'products/cendol.jpg',
            ],
            [
                'name' => 'Es Kopi Susu Aren Nusantara',
                'slug' => 'es-kopi-susu-aren-nusantara',
                'sku' => 'BEV-KPI-006',
                'category_id' => $catMinuman->id,
                'output_unit_id' => $glass->id,
                'base_cost' => 7500,
                'selling_price' => 18000,
                'min_stock' => 30,
                'description' => 'Double shot espresso blend Arabika Gayo & Robusta Temanggung, susu murni pasteurisasi, dan gula aren organik cair yang creamy pas.',
                'image_path' => 'products/cendol.jpg',
            ],
            [
                'name' => 'Tumpeng Mini Nusantara Rasa Komplit',
                'slug' => 'tumpeng-mini-nusantara-rasa-komplit',
                'sku' => 'CAT-TPG-007',
                'category_id' => $catKatering->id,
                'output_unit_id' => $box->id,
                'base_cost' => 26000,
                'selling_price' => 45000,
                'min_stock' => 5,
                'description' => 'Nasi kuning kerucut mini gurih santan, ayam goreng lengkuas, sambal goreng kentang ati ampela, orek tempe manis, telur dadar iris, perkedel, dan sambal bajak.',
                'image_path' => 'products/tumpeng.jpg',
            ],
            [
                'name' => 'Tempe Mendoan Sambal Kecap Pedas',
                'slug' => 'tempe-mendoan-sambal-kecap-pedas',
                'sku' => 'SNK-MDN-008',
                'category_id' => $catCamilan->id,
                'output_unit_id' => $portion->id,
                'base_cost' => 6000,
                'selling_price' => 16000,
                'min_stock' => 20,
                'description' => '4 lembar tempe tipis berbalut tepung bumbu daun bawang, digoreng setengah matang lembut, dicocol sambal kecap rawit bawang merah.',
                'image_path' => 'products/satay.jpg',
            ],
        ];

        foreach ($products as $pData) {
            Product::updateOrCreate(
                ['business_id' => $business->id, 'slug' => $pData['slug']],
                [
                    'name' => $pData['name'],
                    'code' => $pData['sku'],
                    'category_id' => $pData['category_id'],
                    'output_unit_id' => $pData['output_unit_id'],
                    'base_cost' => $pData['base_cost'],
                    'selling_price' => $pData['selling_price'],
                    'min_stock' => $pData['min_stock'],
                    'is_active' => true,
                    'description' => $pData['description'],
                    'image_path' => $pData['image_path'],
                ]
            );
        }

        // 3. Landing Page CMS Terlengkap & Terintegrasi
        BusinessLandingPage::updateOrCreate(
            ['business_id' => $business->id],
            [
                'is_published' => true,
                'industry_preset' => 'resto',
                'theme_color' => '#E11D48',
                'font_family' => 'Plus Jakarta Sans',
                'dark_mode' => false,

                // Hero Content
                'headline' => 'Cita Rasa Rempah Nusantara, Kehangatan Tradisi di Setiap Meja',
                'subheadline' => 'Menyajikan hidangan Indonesia autentik pilihan dari berbagai penjuru Nusantara. Dimasak dari bahan segar setiap hari, diracik dengan bumbu rempah asli, dan disajikan dalam suasana hangat nan nyaman di Menteng, Jakarta Pusat.',
                'announcement_badge' => '✦ DISKON 15% Makan Siang & Menu Baru Sop Buntut Bakar Rempah ✦',
                'hero_image_url' => '/storage/landing_pages/hero_nusantara_rasa.jpg',
                'logo_url' => null,
                'cta_primary_text' => 'Reservasi Meja / Pesan WhatsApp',
                'cta_primary_url' => 'https://wa.me/6281234567890?text=Halo%20Restoran%20Nusantara%20Rasa%2C%20saya%20ingin%20reservasi%20meja%20atau%20memesan%20makanan.',
                'cta_secondary_text' => 'Lihat Menu & Layanan Kami',
                'cta_secondary_url' => '#layanan',

                // About Content
                'about_title' => 'Dapur Nusantara dengan Rasa yang Selalu Dekat di Hati',
                'about_story' => "Berawal dari kecintaan mendalam pada kekayaan rempah Indonesia, Restoran Nusantara Rasa hadir sebagai rumah bagi Anda yang merindukan cita rasa masakan tradisional yang autentik. Kami bermitra langsung dengan petani dan pemasok pasar lokal untuk memastikan setiap helai daun jeruk, rimpang jahe, dan daging segar yang masuk ke dapur kami memiliki kualitas terbaik.\n\nSetiap hidangan disiapkan dengan teknik masak turun-temurun tanpa kompromi rasa. Mulai dari kelezatan Rendang Daging Sapi yang dimasak perlahan hingga gurihnya Ayam Bakar beraroma arang kelapa, kami menyajikannya dengan keramahan khas Indonesia untuk keluarga, sahabat, dan rekan kerja.",
                'about_image_url' => '/storage/landing_pages/about_restaurant.jpg',

                // 4 Pillars of Excellence
                'values' => [
                    [
                        'icon' => 'shield-check',
                        'title' => '100% Halal & Bahan Segar',
                        'description' => 'Daging sapi, ayam, dan bumbu dapur dipasok segar setiap pagi dengan standar kebersihan tinggi dan bersertifikat halal.',
                    ],
                    [
                        'icon' => 'flame',
                        'title' => 'Rempah Asli Warisan Tradisi',
                        'description' => 'Racikan bumbu rempah asli Indonesia tanpa pengawet atau perisa buatan, menghadirkan aroma dan rasa medok yang kaya.',
                    ],
                    [
                        'icon' => 'utensils',
                        'title' => 'Dimasak Fresh Saat Dipesan',
                        'description' => 'Semua sajian dibuat langsung dari wajan koki kami agar kehangatan, aroma wangi, dan kerenyahannya sampai maksimal ke meja Anda.',
                    ],
                    [
                        'icon' => 'users',
                        'title' => 'Ruang Nyaman untuk Rombongan',
                        'description' => 'Tersedia ruang makan keluarga ber-AC, area VIP meeting berkapasitas 25 orang, serta paket prasmanan hingga 150 tamu.',
                    ],
                ],

                // Operational Hours
                'operational_hours' => [
                    ['day' => 'Senin', 'hours' => '10:00 - 22:00 WIB', 'is_open' => true],
                    ['day' => 'Selasa', 'hours' => '10:00 - 22:00 WIB', 'is_open' => true],
                    ['day' => 'Rabu', 'hours' => '10:00 - 22:00 WIB', 'is_open' => true],
                    ['day' => 'Kamis', 'hours' => '10:00 - 22:00 WIB', 'is_open' => true],
                    ['day' => 'Jumat', 'hours' => '10:00 - 23:00 WIB', 'is_open' => true],
                    ['day' => 'Sabtu', 'hours' => '09:00 - 23:00 WIB', 'is_open' => true],
                    ['day' => 'Minggu', 'hours' => '09:00 - 22:00 WIB', 'is_open' => true],
                ],

                // POS Products Display on Landing
                'show_pos_products' => true,
                'services_title' => 'Layanan & Menu Pilihan Kami',
                'services_subtitle' => 'Pilihan tepat untuk makan siang santai, jamuan keluarga, meeting kantor, hingga katering hajatan besar.',

                // Custom Services with local images
                'custom_services' => [
                    [
                        'title' => 'Dine-In & Jamuan Keluarga',
                        'description' => 'Suasana makan nyaman dengan interior kayu bernuansa hangat, alunan musik tradisional lembut, dan pelayanan ramah kekeluargaan.',
                        'price' => 'Mulai Rp 28.000',
                        'badge' => 'Favorit',
                        'image_url' => '/storage/landing_pages/about_restaurant.jpg',
                    ],
                    [
                        'title' => 'Nasi Box & Bento Meeting',
                        'description' => 'Paket makanan higienis dalam lunch box ramah lingkungan, cocok untuk seminar, pelatihan, rapat direksi, dan event kantor.',
                        'price' => 'Rp 35.000 / box',
                        'badge' => 'Corporate',
                        'image_url' => '/storage/products/rendang.jpg',
                    ],
                    [
                        'title' => 'Tumpeng Mini Syukuran',
                        'description' => 'Nasi kuning wangi dengan 7 macam lauk pendamping komplit dan garnish estetik untuk syukuran ulang tahun dan promosi jabatan.',
                        'price' => 'Mulai Rp 45.000 / pax',
                        'badge' => 'Spesial',
                        'image_url' => '/storage/products/tumpeng.jpg',
                    ],
                    [
                        'title' => 'Katering Prasmanan Gathering',
                        'description' => 'Layanan buffet lengkap dengan meja pemanas, peralatan makan stainless, dan staf penyaji profesional untuk pesta dan arisan.',
                        'price' => 'Mulai Rp 75.000 / pax',
                        'badge' => 'Paket Acara',
                        'image_url' => '/storage/gallery/tumpeng.jpg',
                    ],
                    [
                        'title' => 'Coffee & Traditional Bar',
                        'description' => 'Seduhan kopi Arabika Nusantara (Gayo, Toraja, Preanger) berpadu dengan Es Cendol Dawet Durian dan kudapan mendoan hangat.',
                        'price' => 'Mulai Rp 16.000',
                        'badge' => 'Minuman',
                        'image_url' => '/storage/products/cendol.jpg',
                    ],
                    [
                        'title' => 'Ruang VIP Private Meeting',
                        'description' => 'Ruang tertutup ber-AC kapasitas hingga 25 orang dilengkapi proyektor, audio sound, dan koneksi internet WiFi berkecepatan tinggi.',
                        'price' => 'Min. Order Rp 1.5 Jt',
                        'badge' => 'VIP Room',
                        'image_url' => '/storage/landing_pages/hero_nusantara_rasa.jpg',
                    ],
                ],

                // Real Local Gallery Images
                'gallery_title' => 'Suasana Restoran & Dokumentasi Sajian',
                'gallery_subtitle' => 'Momen kebersamaan para tamu dan ragam hidangan lezat yang kami sajikan dengan sepenuh hati.',
                'gallery_images' => [
                    [
                        'url' => '/storage/gallery/rendang.jpg',
                        'caption' => 'Rendang Daging Sapi Payakumbuh dimasak perlahan dengan santan murni dan bumbu rempah pekat.',
                    ],
                    [
                        'url' => '/storage/gallery/satay.jpg',
                        'caption' => 'Sate Maranggi Sapi dibakar arang kelapa dengan aroma harum dan cocolan sambal kecap tomat.',
                    ],
                    [
                        'url' => '/storage/gallery/cendol.jpg',
                        'caption' => 'Es Cendol Dawet Durian segar dengan santan kelapa gurih, nangka manis, dan sirup gula aren murni.',
                    ],
                    [
                        'url' => '/storage/gallery/tumpeng.jpg',
                        'caption' => 'Tumpeng Mini Nusantara dengan nasi kuning gurih dan 7 lauk pendamping tradisional komplit.',
                    ],
                ],

                'section_visibility' => [
                    'hero' => true,
                    'about' => true,
                    'products' => true,
                    'services' => true,
                    'gallery' => true,
                    'testimonials' => true,
                    'faq' => true,
                    'contact' => true,
                    'footer' => true,
                    'footer_brand' => true,
                    'footer_navigation' => true,
                    'footer_services' => true,
                    'footer_contact' => true,
                ],

                // Customer Testimonials
                'testimonials' => [
                    [
                        'name' => 'Dimas Anggara & Keluarga',
                        'role' => 'Pelanggan Setia Family Gathering',
                        'quote' => 'Bumbu rendang dan sate marangginya benar-benar luar biasa! Dagingnya sangat empuk, bumbunya medok meresap. Suasana restorannya sangat ramah untuk anak-anak dan orang tua.',
                        'rating' => 5,
                    ],
                    [
                        'name' => 'Veronica Cindy',
                        'role' => 'General Affairs PT Telkom Indonesia',
                        'quote' => 'Kami sudah langganan nasi box dan katering VIP untuk rapat direksi di Telkom Landmark Tower. Pengiriman selalu tepat waktu, kemasan rapi higienis, dan rasanya konsisten enak.',
                        'rating' => 5,
                    ],
                    [
                        'name' => 'Bagus Pratama',
                        'role' => 'Event Organizer Wedding & Gathering',
                        'quote' => 'Katering prasmanan Nusantara Rasa selalu jadi andalan kami untuk acara perayaan. Tamu-tamu selalu memuji kelezatan ayam bakar madu dan es cendol duriannya!',
                        'rating' => 5,
                    ],
                    [
                        'name' => 'Maya Kartika',
                        'role' => 'Food Enthusiast & Reviewer Kuliner',
                        'quote' => 'Salah satu restoran Nusantara paling nyaman di Menteng. Ruang VIP meeting-nya lengkap dengan fasilitas proyektor, makanannya enak, kopinya juga mantap.',
                        'rating' => 5,
                    ],
                ],

                // Comprehensive FAQs
                'faqs' => [
                    [
                        'question' => 'Apakah seluruh makanan dan minuman di Restoran Nusantara Rasa halal?',
                        'answer' => 'Ya, 100% halal. Seluruh bahan baku daging sapi, ayam, serta bumbu dapur kami bersertifikasi halal dan diolah dengan standar higienis ketat tanpa bahan non-halal.',
                    ],
                    [
                        'question' => 'Bagaimana cara reservasi meja atau ruang VIP untuk acara rombongan?',
                        'answer' => 'Anda dapat langsung klik tombol "Reservasi Meja" di website ini untuk menghubungi tim kami via WhatsApp. Kami sarankan melakukan pemesanan H-2 untuk rombongan di atas 10 orang.',
                    ],
                    [
                        'question' => 'Berapa minimal pemesanan untuk Nasi Box dan Katering Prasmanan?',
                        'answer' => 'Untuk Nasi Box minimal pemesanan 15 box, sedangkan paket katering prasmanan mulai dari 20 pax. Kami melayani pengantaran ke seluruh area Jabodetabek.',
                    ],
                    [
                        'question' => 'Apakah tersedia area parkir dan fasilitas mushola?',
                        'answer' => 'Tersedia area parkir kendaraan mobil & motor dengan layanan valet gratis, serta mushola bersih dan ber-AC di lantai 2 untuk kenyamanan ibadah Anda.',
                    ],
                    [
                        'question' => 'Apakah melayani pemesanan take-away dan pesanan online?',
                        'answer' => 'Tentu. Anda dapat memesan untuk take-away langsung di restoran kami atau memesan pesan-antar melalui WhatsApp Customer Service kami.',
                    ],
                    [
                        'question' => 'Metode pembayaran apa saja yang diterima di kasir POS restoran?',
                        'answer' => 'Kami menerima pembayaran tunai (Cash), QRIS (GoPay, OVO, Dana, BCA, Livin), Kartu Debit/Kredit, dan Transfer Bank untuk katering korporat.',
                    ],
                ],

                // Key Business Statistics
                'stats' => [
                    'clients' => '25.000+',
                    'clients_label' => 'Tamu & Porsi Terlayani',
                    'experience' => '8+ Tahun',
                    'experience_label' => 'Melestarikan Resep Nusantara',
                    'rating' => '4.9 ★',
                    'rating_label' => 'Rating Google (1.200+ Ulasan)',
                ],

                // Social Media & Marketplace Channels
                'social_links' => [
                    'instagram' => 'https://instagram.com/nusantararasa.id',
                    'tiktok' => 'https://tiktok.com/@nusantararasa.id',
                    'facebook' => 'https://facebook.com/nusantararasa.id',
                    'youtube' => 'https://youtube.com/@nusantararasa.id',
                    'shopee' => 'https://shopee.co.id/nusantararasa.id',
                    'tokopedia' => 'https://tokopedia.com/nusantararasa',
                    'gofood' => 'https://gofood.link/nusantararasa',
                    'grabfood' => 'https://grab.onelink.me/nusantararasa',
                ],

                // Contact & Location Details
                'whatsapp_number' => '0812-3456-7890',
                'whatsapp_welcome_message' => 'Halo Restoran Nusantara Rasa, saya ingin reservasi meja / bertanya tentang menu dan katering.',
                'custom_phone' => '0812-3456-7890',
                'custom_email' => 'reservasi@nusantararasa.id',
                'custom_address' => 'Jl. Sabang No. 18, Menteng, Jakarta Pusat 10350',
                'google_maps_embed_url' => 'https://www.google.com/maps?q=Jl.+Sabang+No.+18,+Menteng,+Jakarta+Pusat&output=embed',

                // Footer Content
                'footer_description' => 'Restoran Nusantara Rasa menyajikan hidangan autentik khas Indonesia dengan bumbu rempah asli dan bahan segar. Pilihan utama untuk santap keluarga, katering kantor, dan jamuan spesial.',
                'footer_navigation_title' => 'Navigasi',
                'footer_services_title' => 'Menu & Layanan',
                'footer_contact_title' => 'Kontak & Reservasi',
                'footer_cta_text' => 'Reservasi Meja via WhatsApp',
                'footer_copyright' => '© ' . date('Y') . ' Restoran Nusantara Rasa. All rights reserved. Powered by COOCA.',

                // SEO Metadata
                'meta_title' => 'Restoran Nusantara Rasa | Kuliner Indonesia Autentik Menteng Jakarta',
                'meta_description' => 'Restoran masakan Indonesia autentik di Menteng, Jakarta Pusat. Nikmati hidangan khas Nusantara, tumpeng mini, paket katering prasmanan, dan kopi tradisional.',
                'meta_keywords' => 'restoran nusantara rasa, kuliner menteng, makanan indonesia jakarta pusat, nasi tumpeng mini, katering kantor jakarta, sate maranggi, rendang sapi',
                'og_image_url' => '/storage/landing_pages/hero_nusantara_rasa.jpg',
            ]
        );

        $this->command?->info('Data seeder lengkap untuk Restoran Nusantara Rasa berhasil di-seed.');
    }
}