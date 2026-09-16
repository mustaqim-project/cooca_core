<?php

declare(strict_types=1);

namespace App\Domain\LandingPage;

class IndustryPresets
{
    /**
     * Map between template codes and legacy alias keys.
     */
    public static function aliasMap(): array
    {
        return [
            'resto'       => 'fnb_resto',
            'bengkel'     => 'service_workshop',
            'fashion'     => 'mfg_garment',
            'percetakan'  => 'mfg_printing',
            'bakery'      => 'fnb_bakery',
            'carwash'     => 'service_autodetailing',
            'laundry'     => 'service_laundry',
            'bangunan'    => 'service_contractor',
            'salon'       => 'service_barbershop',
            'retail'      => 'retail_reseller',
            'ekspedisi'   => 'distributor_fmcg',
            'pertanian'   => 'agri_farming',
        ];
    }

    /**
     * Resolve a preset key from either modern template code or legacy alias.
     */
    public static function resolveKey(?string $key): string
    {
        if (empty($key)) {
            return 'retail_reseller';
        }

        $alias = self::aliasMap();
        if (isset($alias[$key])) {
            return $alias[$key];
        }

        $reverse = array_flip($alias);
        if (isset($reverse[$key])) {
            return $key;
        }

        return $key;
    }

    /**
     * Get list of all supported industries with key, label, Lucide icon, category, and theme color.
     */
    public static function all(): array
    {
        return [
            // ─── 1. KULINER & F&B (7) ──────────────────────────────────────────
            'fnb_resto'           => ['label' => 'Restoran & Rumah Makan (Dine-in)', 'icon' => 'utensils', 'category' => 'fnb', 'color' => '#E11D48'],
            'fnb_cafe'            => ['label' => 'Coffee Shop & Cafe', 'icon' => 'coffee', 'category' => 'fnb', 'color' => '#854D0E'],
            'fnb_bakery'          => ['label' => 'Toko Roti, Bakery & Pastry', 'icon' => 'cake', 'category' => 'fnb', 'color' => '#CA8A04'],
            'fnb_cloud_kitchen'   => ['label' => 'Cloud Kitchen & Delivery Only', 'icon' => 'flame', 'category' => 'fnb', 'color' => '#EA580C'],
            'fnb_catering'        => ['label' => 'Catering & Prasmanan Pesta', 'icon' => 'utensils-crossed', 'category' => 'fnb', 'color' => '#D97706'],
            'fnb_frozen_food'     => ['label' => 'Frozen Food & Olahan Beku', 'icon' => 'snowflake', 'category' => 'fnb', 'color' => '#0284C7'],
            'fnb_catering_diet'   => ['label' => 'Diet & Healthy Catering', 'icon' => 'apple', 'category' => 'fnb', 'color' => '#16A34A'],

            // ─── 2. MANUFAKTUR & PRODUKSI (7) ────────────────────────────────
            'mfg_garment'         => ['label' => 'Konveksi, Butik & Garment', 'icon' => 'shirt', 'category' => 'manufacturing', 'color' => '#DB2777'],
            'mfg_precision'       => ['label' => 'Plastik, Bubut & Logam Presisi', 'icon' => 'cog', 'category' => 'manufacturing', 'color' => '#475569'],
            'mfg_furniture'       => ['label' => 'Furniture & Mebel Kayu Interior', 'icon' => 'armchair', 'category' => 'manufacturing', 'color' => '#B45309'],
            'mfg_craft'           => ['label' => 'Kerajinan Tangan & Handmade Craft', 'icon' => 'palette', 'category' => 'manufacturing', 'color' => '#9333EA'],
            'mfg_printing'        => ['label' => 'Percetakan Digital, Offset & Sablon', 'icon' => 'printer', 'category' => 'manufacturing', 'color' => '#EA580C'],
            'mfg_cosmetics'       => ['label' => 'Kosmetik & Skincare Production', 'icon' => 'sparkles', 'category' => 'manufacturing', 'color' => '#EC4899'],
            'mfg_tailor_custom'   => ['label' => 'Penjahit Jas & Kebaya Custom', 'icon' => 'scissors', 'category' => 'manufacturing', 'color' => '#6D28D9'],

            // ─── 3. RETAIL & TOKO (2) ─────────────────────────────────────────
            'retail_reseller'     => ['label' => 'Toko Retail, Kelontong & Minimarket', 'icon' => 'shopping-bag', 'category' => 'retail', 'color' => '#16A34A'],
            'retail_pharmacy'     => ['label' => 'Apotek, Toko Obat & Alkes', 'icon' => 'cross', 'category' => 'retail', 'color' => '#0D9488'],

            // ─── 4. JASA PROFESIONAL & ACARA (3) ──────────────────────────────
            'service_agency'      => ['label' => 'Digital Creative Agency & IT Software', 'icon' => 'code-2', 'category' => 'service', 'color' => '#2563EB'],
            'service_contractor'  => ['label' => 'Kontraktor, Bahan Bangunan & Renovasi', 'icon' => 'hard-hat', 'category' => 'service', 'color' => '#D97706'],
            'service_event'       => ['label' => 'Event Organizer & Wedding Organizer', 'icon' => 'party-popper', 'category' => 'service', 'color' => '#7C3AED'],

            // ─── 5. JASA OPERASIONAL HARIAN (4) ───────────────────────────────
            'service_workshop'    => ['label' => 'Bengkel Mobil & Motor Otomotif', 'icon' => 'wrench', 'category' => 'service', 'color' => '#0284C7'],
            'service_barbershop'  => ['label' => 'Barbershop, Salon & Kecantikan', 'icon' => 'scissors', 'category' => 'service', 'color' => '#9333EA'],
            'service_laundry'     => ['label' => 'Laundry Kiloan & Dry Clean', 'icon' => 'shirt', 'category' => 'service', 'color' => '#0891B2'],
            'service_autodetailing' => ['label' => 'Cuci Mobil & Auto Detailing', 'icon' => 'car', 'category' => 'service', 'color' => '#2563EB'],

            // ─── 6. DISTRIBUSI & PERTANIAN (2) ────────────────────────────────
            'distributor_fmcg'    => ['label' => 'Distributor Grosir & Jasa Logistik', 'icon' => 'truck', 'category' => 'trading', 'color' => '#F97316'],
            'agri_farming'        => ['label' => 'Pertanian, Peternakan & Hidroponik', 'icon' => 'sprout', 'category' => 'trading', 'color' => '#15803D'],

            // ─── 7. SPESIALIS NICHE LAINNYA (Legacy Support) ──────────────────
            'klinik'              => ['label' => 'Klinik Kesehatan & Dokter', 'icon' => 'activity', 'category' => 'service', 'color' => '#0D9488'],
            'notaris'             => ['label' => 'Kantor Notaris & PPAT / Hukum', 'icon' => 'scale', 'category' => 'service', 'color' => '#4338CA'],
            'petshop'             => ['label' => 'Pet Shop, Vet & Grooming', 'icon' => 'heart', 'category' => 'retail', 'color' => '#F59E0B'],
            'elektronik'          => ['label' => 'Servis Gadget & Toko Elektronik', 'icon' => 'cpu', 'category' => 'service', 'color' => '#4F46E5'],
            'studio_foto'         => ['label' => 'Studio Foto & Videografi', 'icon' => 'camera', 'category' => 'service', 'color' => '#7C3AED'],
            'gym'                 => ['label' => 'Gym, Fitness & Studio Olahraga', 'icon' => 'dumbbell', 'category' => 'service', 'color' => '#DC2626'],
            'bimbel'              => ['label' => 'Bimbel, Kursus & Les Privat', 'icon' => 'graduation-cap', 'category' => 'service', 'color' => '#2563EB'],
            'florist'             => ['label' => 'Florist & Toko Bunga Dekorasi', 'icon' => 'flower-2', 'category' => 'retail', 'color' => '#EC4899'],
        ];
    }

    /**
     * Get complete default content preset for a specific industry.
     */
    public static function get(string $key, string $businessName = 'Usaha Kami'): array
    {
        $resolvedKey = self::resolveKey($key);
        $presets     = self::definitions($businessName);

        if (isset($presets[$resolvedKey])) {
            return $presets[$resolvedKey];
        }

        if (isset($presets[$key])) {
            return $presets[$key];
        }

        // Fallback to retail
        return $presets['retail_reseller'] ?? $presets['retail'] ?? reset($presets);
    }

    /**
     * Get all industries formatted for the CMS preset modal (Zero Emoji, Lucide Icons).
     */
    public static function forModal(): array
    {
        $all = self::all();
        $definitions = [
            'fnb_resto'           => ['name' => 'Restoran & Rumah Makan', 'description' => 'Menu makanan, meja dine-in, reservasi, takeaway, dan delivery.', 'tags' => ['kuliner','resto','dinein','makanan']],
            'fnb_cafe'            => ['name' => 'Coffee Shop & Cafe', 'description' => 'Kopi racikan barista, pastry, suasana nongkrong, wifi dan meeting.', 'tags' => ['kopi','cafe','espresso','tongkrongan']],
            'fnb_bakery'          => ['name' => 'Bakery & Toko Roti', 'description' => 'Roti manis, kue tart ulang tahun, pastry oven fresh, hampers.', 'tags' => ['roti','kue','pastry','tart']],
            'fnb_cloud_kitchen'   => ['name' => 'Cloud Kitchen & Delivery', 'description' => 'Menu cepat saji kemasan rapi, siap antar pesanan online.', 'tags' => ['delivery','online','kitchen','makanan']],
            'fnb_catering'        => ['name' => 'Catering & Prasmanan', 'description' => 'Paket nasi kotak, prasmanan pernikahan, arisan dan gathering kantor.', 'tags' => ['catering','prasmanan','kotak','pesta']],
            'fnb_frozen_food'     => ['name' => 'Frozen Food & Olahan Beku', 'description' => 'Nugget, sosis, dimsum, daging marinasi kemasan vacuum kedap udara.', 'tags' => ['frozen','beku','nugget','dimsum']],
            'fnb_catering_diet'   => ['name' => 'Katering Diet & Makanan Sehat', 'description' => 'Menu kalori terukur, rendah garam, program weight loss & gym.', 'tags' => ['diet','sehat','healthy','kalori']],

            'mfg_garment'         => ['name' => 'Konveksi & Butik Fashion', 'description' => 'Seragam kantor, kaos sablon komunitas, busana muslim, gamis & jilbab.', 'tags' => ['konveksi','garment','baju','fashion','sablon']],
            'mfg_precision'       => ['name' => 'Plastik, Bubut & Logam Presisi', 'description' => 'Jasa bubut CNC, cetakan injeksi plastik, suku cadang mesin industri.', 'tags' => ['bubut','cnc','logam','presisi','plastik']],
            'mfg_furniture'       => ['name' => 'Furniture & Mebel Kayu', 'description' => 'Kitchen set, meja kantor, lemari custom kayu jati & plywood HPL.', 'tags' => ['furniture','mebel','kayu','interior','hpl']],
            'mfg_craft'           => ['name' => 'Kerajinan Tangan & Craft', 'description' => 'Suvenir pernikahan, anyaman, kerajinan kulit & pernak-pernik unik.', 'tags' => ['craft','suvenir','handmade','seni']],
            'mfg_printing'        => ['name' => 'Percetakan & Digital Printing', 'description' => 'Spanduk banner outdoor, stiker label kemasan, brosur, map & nota.', 'tags' => ['cetak','banner','stiker','printing']],
            'mfg_cosmetics'       => ['name' => 'Kosmetik & Skincare', 'description' => 'Serum wajah, sabun kecantikan, body lotion bersertifikat BPOM.', 'tags' => ['skincare','kosmetik','beauty','sabun']],
            'mfg_tailor_custom'   => ['name' => 'Penjahit Jas & Kebaya Custom', 'description' => 'Pola bespoke fitting badan, jas formal pria, kebaya akad & wisuda.', 'tags' => ['tailor','penjahit','jas','kebaya']],

            'retail_reseller'     => ['name' => 'Toko Retail & Minimarket', 'description' => 'Sembako lengkap, kebutuhan rumah tangga harian, snack & minuman.', 'tags' => ['retail','toko','minimarket','kelontong']],
            'retail_pharmacy'     => ['name' => 'Apotek & Toko Obat', 'description' => 'Obat resep dokter, suplemen vitamin, obat bebas dan alat kesehatan.', 'tags' => ['apotek','obat','farmasi','alkes']],

            'service_agency'      => ['name' => 'Digital Agency & IT Software', 'description' => 'Pembuatan website, aplikasi mobile, desain grafis & social media ads.', 'tags' => ['agency','it','software','desain','web']],
            'service_contractor'  => ['name' => 'Kontraktor & Toko Bangunan', 'description' => 'Renovasi rumah, semen, pasir, besi beton SNI, cat dan baja ringan.', 'tags' => ['bangunan','kontraktor','renovasi','semen','besi']],
            'service_event'       => ['name' => 'Event & Wedding Organizer', 'description' => 'Dekorasi pesta pelaminan, dokumentasi foto, sound system & MC.', 'tags' => ['event','wedding','wo','dekorasi']],

            'service_workshop'    => ['name' => 'Bengkel Mobil & Motor', 'description' => 'Servis berkala, tune-up injeksi, ganti oli, rem, ban & sparepart asli.', 'tags' => ['bengkel','otomotif','servis','motor','mobil']],
            'service_barbershop'  => ['name' => 'Barbershop & Salon Rambut', 'description' => 'Potong rambut fade, creambath relaksasi, hair coloring & cuci blow.', 'tags' => ['barber','salon','rambut','styling']],
            'service_laundry'     => ['name' => 'Laundry Kiloan & Dry Clean', 'description' => 'Cuci kering setrika uap, cuci bed cover tebal, sepatu & gorden wangi.', 'tags' => ['laundry','cuci','kiloan','setrika']],
            'service_autodetailing' => ['name' => 'Cuci Mobil & Auto Detailing', 'description' => 'Cuci mobil salju hidrolik, poles body, nano ceramic coating & jamur kaca.', 'tags' => ['carwash','cuci','detailing','coating']],

            'distributor_fmcg'    => ['name' => 'Distributor Grosir & Ekspedisi', 'description' => 'Grosir kartonan sembako, armada antar truk & jasa ekspedisi cargo.', 'tags' => ['distributor','grosir','fmcg','ekspedisi','cargo']],
            'agri_farming'        => ['name' => 'Pertanian, Ternak & Hidroponik', 'description' => 'Bibit tanaman unggul, pupuk organik, pakan ternak & instalasi hidroponik.', 'tags' => ['pertanian','tani','pupuk','bibit','hidroponik']],

            // Additional legacy vertical niches
            'klinik'              => ['name' => 'Klinik Kesehatan Pratama', 'description' => 'Pemeriksaan dokter umum, klinik gigi estetik & cek laboratorium.', 'tags' => ['dokter','klinik','medis','kesehatan']],
            'notaris'             => ['name' => 'Kantor Notaris & PPAT', 'description' => 'Akta pendirian PT/CV, perjanjian legal bisnis, AJB tanah & sertifikat.', 'tags' => ['notaris','ppat','hukum','legal']],
            'petshop'             => ['name' => 'Pet Shop & Anabul Grooming', 'description' => 'Pakan kucing & anjing premium, mandi grooming sehat, pet hotel ber-AC.', 'tags' => ['petshop','anabul','kucing','anjing','grooming']],
            'elektronik'          => ['name' => 'Servis HP, Laptop & Gadget', 'description' => 'Ganti LCD sentuh, baterai original, instal ulang & sparepart garansi.', 'tags' => ['elektronik','gadget','servis','laptop','hp']],
            'studio_foto'         => ['name' => 'Studio Foto & Dokumentasi', 'description' => 'Foto wisuda, foto produk katalog, pas foto kilat & sewa studio.', 'tags' => ['foto','studio','fotografi','wisuda']],
            'gym'                 => ['name' => 'Gym & Fitness Center', 'description' => 'Alat fitness lengkap, kelas aerobik/yoga, keanggotaan member & trainer.', 'tags' => ['gym','fitness','olahraga','sehat']],
            'bimbel'              => ['name' => 'Bimbingan Belajar & Les Privat', 'description' => 'Pendampingan belajar SD-SMA, persiapan UTBK & kursus privat.', 'tags' => ['bimbel','les','kursus','sekolah']],
            'florist'             => ['name' => 'Florist & Toko Bunga', 'description' => 'Buket bunga wisuda segar, papan bunga ucapan selamat & standing flower.', 'tags' => ['florist','bunga','buket','dekorasi']],
        ];

        $result = [];
        foreach ($definitions as $id => $meta) {
            $catMeta = $all[$id] ?? ['category' => 'other', 'color' => '#007AFF', 'icon' => 'sparkles'];
            $result[] = [
                'id'          => $id,
                'name'        => $meta['name'],
                'icon'        => $catMeta['icon'] ?? 'sparkles',
                'category'    => $catMeta['category'] ?? 'other',
                'description' => $meta['description'],
                'tags'        => $meta['tags'],
                'theme_color' => $catMeta['color'] ?? '#007AFF',
            ];
        }

        return $result;
    }

    /**
     * Raw definitions for all 25 industries with authentic Indonesian copy.
     */
    protected static function definitions(string $biz): array
    {
        return [
            // ─── 1. RESTORAN & RUMAH MAKAN ───────────────────────────────────
            'fnb_resto' => [
                'theme_color'        => '#E11D48',
                'headline'           => "Cita Rasa Kuliner Nusantara Autentik di {$biz}",
                'subheadline'        => "Bahan segar pilihan petani lokal, resep tradisi warisan keluarga, dan suasana santai yang nyaman untuk makan bersama keluarga dan kerabat tercinta.",
                'announcement_badge' => "Menu Spesial Hari Ini Tersedia - Disajikan Selalu Hangat",
                'cta_primary_text'   => "Pesan Menu / Reservasi Meja",
                'cta_secondary_text' => "Daftar Menu & Harga",
                'about_title'        => "Kelezatan Dari Dapur Penuh Dedikasi",
                'about_story'        => "Berawal dari kegemaran menyajikan hidangan lezat dan berkualitas, {$biz} hadir untuk menyatukan kehangatan keluarga dan sahabat lewat santapan terbaik.",
                'values' => [
                    ['icon' => 'utensils', 'title' => '100% Halal & Higienis', 'desc' => 'Bahan baku terverifikasi halal dan diolah dengan standar kebersihan ketat.'],
                    ['icon' => 'flame', 'title' => 'Selalu Disajikan Hangat', 'desc' => 'Dimasak fresh saat dipesan (made to order) untuk cita rasa optimal.'],
                    ['icon' => 'smile', 'title' => 'Pelayanan Ramah & Cepat', 'desc' => 'Staf ramah siap melayani pesanan Anda dengan senyuman.'],
                    ['icon' => 'map-pin', 'title' => 'Fasilitas Lengkap & Nyaman', 'desc' => 'Area parkir luas, toilet bersih, dan musholla untuk kenyamanan Anda.'],
                ],
                'services' => [
                    ['title' => 'Paket Nasi Ayam Bakar Madu Spesial', 'desc' => 'Lengkap dengan nasi putih pulen, tahu, tempe, lalapan segar & sambal bajak khas.', 'price' => 'Rp 28.000', 'badge' => 'Favorit'],
                    ['title' => 'Iga Bakar Saus Rempah Nusantara', 'desc' => 'Daging iga empuk lembut dengan bumbu rempah pilihan meresap sempurna sampai ke tulang.', 'price' => 'Rp 48.000', 'badge' => 'Chef Choice'],
                    ['title' => 'Es Kopi Susu Gula Aren Barista', 'desc' => 'Espresso blend pilihan dengan susu segar creamy dan gula aren organik murni.', 'price' => 'Rp 18.000', 'badge' => 'Minuman'],
                    ['title' => 'Paket Prasmanan Keluarga / Acara (Min 20 Pax)', 'desc' => 'Pilihan menu lengkap untuk acara arisan, rapat kantor, dan syukuran keluarga.', 'price' => 'Rp 35.000 / pax', 'badge' => 'Acara'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa reservasi meja untuk rombongan keluarga?', 'answer' => 'Bisa, silakan reservasi melalui website ini atau WhatsApp kami minimal 1 hari sebelumnya.'],
                    ['question' => 'Apakah melayani pemesanan nasi kotak / katering?', 'answer' => 'Ya, kami melayani pesanan nasi kotak dan tumpeng mini untuk berbagai acara.'],
                ],
                'testimonials' => [
                    ['name' => 'Dimas Anggara', 'role' => 'Pelanggan Setia', 'quote' => 'Bumbu iga bakarnya meresap sempurna sampai ke tulang! Sambalnya juara pedas gurihnya.', 'rating' => 5],
                    ['name' => 'Maya Kartika', 'role' => 'Warga Sekitar', 'quote' => 'Tempatnya bersih banget buat kumpul keluarga, ayam bakarnya empuk dan anak-anak suka.', 'rating' => 5],
                ],
            ],

            // ─── 2. COFFEE SHOP & CAFE ────────────────────────────────────────
            'fnb_cafe' => [
                'theme_color'        => '#854D0E',
                'headline'           => "Secangkir Kopi Pilihan & Ruang Tenang untuk Berkarya di {$biz}",
                'subheadline'        => "Biji kopi single origin sangrai segar, minuman non-kopi artisanal, camilan pastry renyah, dan WiFi kencang untuk menemani produktivitas Anda.",
                'announcement_badge' => "Free WiFi Kencang & Colokan di Setiap Meja",
                'cta_primary_text'   => "Pesan Kopi / Booking Meja",
                'cta_secondary_text' => "Buku Menu & Minuman",
                'about_title'        => "Kultur Kopi yang Santun dan Menginspirasi",
                'about_story'        => "{$biz} didirikan sebagai titik temu bagi para penikmat rasa, pekerja kreatif, dan sahabat. Kami menyeduh setiap cangkir dengan presisi suhu dan rasio terbaik.",
                'values' => [
                    ['icon' => 'coffee', 'title' => 'Biji Kopi Nusantara Pilihan', 'desc' => 'Direct trade langsung dari petani kopi lokal Indonesia terbaik.'],
                    ['icon' => 'wifi', 'title' => 'Koneksi Cepat & Suasana Tenang', 'desc' => 'Sangat cocok untuk kerja remote (WFC), meeting santai, dan belajar.'],
                    ['icon' => 'cake', 'title' => 'Pastry Fresh Dibuat Setiap Hari', 'desc' => 'Croissant, brownies, dan donat kampung disajikan fresh dari oven.'],
                    ['icon' => 'heart', 'title' => 'Barista Ramah & Berpengalaman', 'desc' => 'Konsultasikan profil rasa kopi yang Anda sukai dengan barista kami.'],
                ],
                'services' => [
                    ['title' => 'Kopi Susu Senja Gula Aren', 'desc' => 'Signature espresso blend dengan susu murni segar dan gula aren alami.', 'price' => 'Rp 18.000', 'badge' => 'Best Seller'],
                    ['title' => 'Manual Brew V60 Single Origin', 'desc' => 'Seduhan pour-over menonjolkan keasaman buah segar dan aroma floral.', 'price' => 'Rp 24.000', 'badge' => 'Artisan'],
                    ['title' => 'Matcha Latte Uji Kyoto', 'desc' => 'Bubuk matcha murni dipadukan dengan susu segar berbusa halus.', 'price' => 'Rp 22.000', 'badge' => 'Non-Coffee'],
                    ['title' => 'Butter Croissant Crispy', 'desc' => 'Lapisan pastry buttery renyah di luar dan lembut di bagian dalam.', 'price' => 'Rp 16.000', 'badge' => 'Snack'],
                ],
                'faqs' => [
                    ['question' => 'Apakah tersedia area smoking dan non-smoking ber-AC?', 'answer' => 'Ya, kami menyediakan ruangan ber-AC bebas asap rokok di dalam dan area semi-outdoor untuk smoking.'],
                    ['question' => 'Apakah bisa booking area untuk mini workshop atau meeting?', 'answer' => 'Bisa, kami memiliki meeting room kapasitas 10-15 orang lengkap dengan layar monitor.'],
                ],
                'testimonials' => [
                    ['name' => 'Reza Pahlevi', 'role' => 'Pekerja Remote', 'quote' => 'Tempat paling nyaman buat WFC, kopinya pas di lambung dan WiFi-nya kencang stabil.', 'rating' => 5],
                    ['name' => 'Nabila Putri', 'role' => 'Mahasiswi', 'quote' => 'Matcha latte dan croissant-nya enak banget! Suasananya tenang gak berisik.', 'rating' => 5],
                ],
            ],

            // ─── 3. BAKERY & TOKO ROTI ─────────────────────────────────────────
            'fnb_bakery' => [
                'theme_color'        => '#CA8A04',
                'headline'           => "Kelembutan Roti & Pastry Fresh Oven Setiap Pagi di {$biz}",
                'subheadline'        => "Dibuat tanpa bahan pengawet kimia, menggunakan mentega asli kualitas premium, ragi alami, dan isian melimpah yang lumer di mulut.",
                'announcement_badge' => "Dipanggang Segar Setiap Pagi - Bebas Bahan Pengawet",
                'cta_primary_text'   => "Pesan Roti & Kue Online",
                'cta_secondary_text' => "Katalog Roti & Hampers",
                'about_title'        => "Kelezatan Tradisi Bakery Sehat",
                'about_story'        => "{$biz} percaya bahwa roti yang baik bermula dari bahan murni dan kesabaran proses fermentasi. Kami hadirkan aroma panggangan hangat untuk keluarga Anda.",
                'values' => [
                    ['icon' => 'cake', 'title' => 'Bebas Pengawet Kimia', 'desc' => 'Adonan alami aman dikonsumsi seluruh keluarga dan anak-anak.'],
                    ['icon' => 'sparkles', 'title' => '100% Mentega Asli', 'desc' => 'Wangi khas butter alami tanpa aroma buatan yang menyengat.'],
                    ['icon' => 'gift', 'title' => 'Kue Tart & Hampers Cantik', 'desc' => 'Kemasan elegan siap kirim untuk ucapan ulang tahun dan hari raya.'],
                    ['icon' => 'truck', 'title' => 'Pengiriman Cepat Aman', 'desc' => 'Dikemas higienis dengan kurir toko agar bentuk kue tetap utuh.'],
                ],
                'services' => [
                    ['title' => 'Roti Sobek Keju Cokelat Lumer', 'desc' => 'Tekstur lembut dengan isian cokelat lumer dan taburan parutan keju gurih.', 'price' => 'Rp 22.000', 'badge' => 'Favorit'],
                    ['title' => 'Roti Sisir Mentega Jadul', 'desc' => 'Roti sisir legendaris dengan olesan butter harum dan gula kristal.', 'price' => 'Rp 14.000', 'badge' => 'Klasik'],
                    ['title' => 'Kue Ulang Tahun Custom (Diameter 18cm)', 'desc' => 'Bolu lembut berlapis selai strawberry segar dan cream tidak enek.', 'price' => 'Rp 185.000', 'badge' => 'Tart'],
                    ['title' => 'Paket Hampers Roti & Cookies Cantik', 'desc' => 'Cocok untuk hantaran kerabat, rekan kerja, dan bingkisan hari besar.', 'price' => 'Mulai Rp 120.000', 'badge' => 'Gift'],
                ],
                'faqs' => [
                    ['question' => 'Berapa hari daya tahan roti tanpa pengawet?', 'answer' => 'Roti kami bertahan 3-4 hari di suhu ruang dan hingga 7 hari jika disimpan rapat di lemari pendingin.'],
                    ['question' => 'Berapa hari sebelumnya harus memesan kue ulang tahun kustom?', 'answer' => 'Kami menyarankan pemesanan kue ulang tahun kustom minimal H-2 sebelum acara.'],
                ],
                'testimonials' => [
                    ['name' => 'Ibu Wulandari', 'role' => 'Ibu Rumah Tangga', 'quote' => 'Rotinya beneran empuk lembut tanpa bikin enek. Anak-anak sarapan selalu minta roti ini.', 'rating' => 5],
                    ['name' => 'Fajar Nugroho', 'role' => 'Karyawan Swasta', 'quote' => 'Pesan kue ulang tahun buat istri hasilnya cantik persis seperti contoh foto, rasanya premium!', 'rating' => 5],
                ],
            ],

            // ─── 4. CLOUD KITCHEN & DELIVERY ──────────────────────────────────
            'fnb_cloud_kitchen' => [
                'theme_color'        => '#EA580C',
                'headline'           => "Sajian Cepat Saji Higienis & Siap Antar ke Meja Anda dari {$biz}",
                'subheadline'        => "Dapur modern tersertifikasi yang fokus pada pengolahan makanan pesan-antar dengan packaging kedap tumpah dan waktu persiapan kilat.",
                'announcement_badge' => "Kemasan Food Grade Ramah Lingkungan & Anti Tumpah",
                'cta_primary_text'   => "Pesan Sekarang via Toko Online",
                'cta_secondary_text' => "Daftar Menu Siap Antar",
                'about_title'        => "Kecepatan, Kebersihan, dan Rasa Terjamin",
                'about_story'        => "{$biz} dirancang khusus untuk memenuhi kebutuhan makan siang dan malam masyarakat modern yang serba praktis tanpa mengorbankan kualitas gizi dan rasa.",
                'values' => [
                    ['icon' => 'clock', 'title' => 'Persiapan Kilat 10-15 Menit', 'desc' => 'Standar dapur cepat memastikan pesanan langsung dikirim hangat.'],
                    ['icon' => 'shield-check', 'title' => 'Kemasan Sealed Higienis', 'desc' => 'Disertai segel keamanan untuk menjaga kebersihan sampai ke tangan Anda.'],
                    ['icon' => 'tag', 'title' => 'Harga Lebih Hemat Tanpa Mark-up', 'desc' => 'Pesan langsung tanpa biaya komisi aplikasi yang membebani.'],
                    ['icon' => 'truck', 'title' => 'Radius Pengiriman Luas', 'desc' => 'Didukung armada kurir toko dan kurir instan terpercaya.'],
                ],
                'services' => [
                    ['title' => 'Rice Bowl Daging Sapi Teriyaki', 'desc' => 'Daging sapi iris empuk berlumur saus manis gurih dengan taburan wijen.', 'price' => 'Rp 32.000', 'badge' => 'Populer'],
                    ['title' => 'Nasi Kulit Ayam Crispy Sambal Korek', 'desc' => 'Kulit ayam garing renyah dipadu sambal ulek pedas nagih dan serundeng.', 'price' => 'Rp 24.000', 'badge' => 'Pedas'],
                    ['title' => 'Paket Hemat Makan Siang Kantor (5 Porsi)', 'desc' => 'Paket komplit praktis untuk makan bersama rekan kerja di kantor.', 'price' => 'Rp 115.000', 'badge' => 'Hemat'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa melayani takeaway langsung ke outlet?', 'answer' => 'Ya, Anda bisa pesan online terlebih dahulu dan ambil langsung di titik pickup kami.'],
                ],
                'testimonials' => [
                    ['name' => 'Budi Santoso', 'role' => 'Karyawan Startup', 'quote' => 'Pesan makan siang kantor selalu tepat waktu. Kemasannya rapi tidak bocor dan makanannya masih hangat.', 'rating' => 5],
                ],
            ],

            // ─── 5. CATERING & PRASMANAN ──────────────────────────────────────
            'fnb_catering' => [
                'theme_color'        => '#D97706',
                'headline'           => "Jasa Katering Terpercaya untuk Pernikahan, Acara Kantor & Syukuran di {$biz}",
                'subheadline'        => "Pilihan menu lezat berselera nusantara & internasional, dekorasi prasmanan anggun, dan staf pelayan profesional siap menyukseskan momen istimewa Anda.",
                'announcement_badge' => "Free Food Tasting & Konsultasi Menu Prasmanan",
                'cta_primary_text'   => "Minta Penawaran Katering",
                'cta_secondary_text' => "Pilihan Paket Prasmanan",
                'about_title'        => "Mitra Kuliner Terpercaya di Setiap Momen Bersejarah",
                'about_story'        => "Dengan pengalaman melayani ribuan event dan acara formal, {$biz} memastikan hidangan yang disajikan bukan hanya memanjakan lidah, tapi juga menghormati para tamu undangan Anda.",
                'values' => [
                    ['icon' => 'award', 'title' => 'Koki Berpengalaman Belasan Tahun', 'desc' => 'Racikan bumbu konsisten dan porsi memuaskan tanpa kekurangan di hari H.'],
                    ['icon' => 'sparkles', 'title' => 'Dekorasi Meja Prasmanan Mewah', 'desc' => 'Pilihan tema dekorasi prasmanan modern sesuai tema warna acara Anda.'],
                    ['icon' => 'check-circle-2', 'title' => 'Jadwal Pengantaran Tepat Janji', 'desc' => 'Makanan tiba di lokasi acara 2 jam sebelum jam makan dimulai.'],
                    ['icon' => 'users', 'title' => 'Pramusaji Berseragam Rapi', 'desc' => 'Staf ramah, sigap membersihkan piring, dan menjaga kebersihan meja.'],
                ],
                'services' => [
                    ['title' => 'Paket Prasmanan Pernikahan (Mulai 300 Pax)', 'desc' => 'Menu lengkap: 2 olahan daging/ayam, ikan, sop hangat, aneka es & dessert buah.', 'price' => 'Mulai Rp 45.000 / pax', 'badge' => 'Wedding'],
                    ['title' => 'Paket Nasi Kotak Acara Kantor / Rapat (Min 15 Box)', 'desc' => 'Kemasan bento bersekat higienis lengkap sendok, tisu, buah dan air mineral.', 'price' => 'Mulai Rp 25.000 / box', 'badge' => 'Box'],
                    ['title' => 'Tumpeng Mini Nusantara Syukuran', 'desc' => 'Nasi kuning wangi pulen dengan 7 macam lauk tradisional kemasan mika eksklusif.', 'price' => 'Rp 35.000 / porsi', 'badge' => 'Syukuran'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa jadwal food testing sebelum menentukan pilihan?', 'answer' => 'Tentu, kami menyediakan sesi food tasting gratis di kantor kami untuk calon mempelai atau panitia acara.'],
                ],
                'testimonials' => [
                    ['name' => 'Rina & Aditya', 'role' => 'Pengantin', 'quote' => 'Semua tamu memuji rasa makanannya yang enak dan porsinya berlimpah, tidak ada yang kehabisan. Terima kasih banyak!', 'rating' => 5],
                ],
            ],

            // ─── 6. FROZEN FOOD & OLAHAN BEKU ─────────────────────────────────
            'fnb_frozen_food' => [
                'theme_color'        => '#0284C7',
                'headline'           => "Stok Makanan Beku Berkualitas, Praktis & Bergizi untuk Keluarga di {$biz}",
                'subheadline'        => "Olahan daging, ayam, seafood, dan dimsum beku higienis tanpa bahan pengawet kimia berbahaya. Siap goreng dan kukus dalam 5 menit.",
                'announcement_badge' => "Dibekukan Cepat (Blast Freezing) Menjaga Kesegaran Nutrisi",
                'cta_primary_text'   => "Belanja Frozen Food Online",
                'cta_secondary_text' => "Katalog Produk Beku",
                'about_title'        => "Solusi Masak Praktis Ibu Rumah Tangga",
                'about_story'        => "{$biz} hadir memberikan solusi praktis bagi keluarga aktif yang ingin menyajikan santapan lezat bergizi dalam hitungan menit tanpa repot memotong dan membumbui bahan mentah.",
                'values' => [
                    ['icon' => 'snowflake', 'title' => 'Proses Pembekuan Cepat', 'desc' => 'Mencegah pertumbuhan bakteri dan mengunci rasa alami daging.'],
                    ['icon' => 'shield-check', 'title' => 'Daging Segar Bukan Olahan Sisa', 'desc' => 'Kandungan daging asli melimpah tanpa tepung berlebihan.'],
                    ['icon' => 'truck', 'title' => 'Pengiriman Menggunakan Ice Gel', 'desc' => 'Produk tetap beku dingin sampai di tangan Anda.'],
                    ['icon' => 'tag', 'title' => 'Melayani Reseller & Eceran', 'desc' => 'Dapatkan harga khusus untuk pembelian paket reseller.'],
                ],
                'services' => [
                    ['title' => 'Dimsum Ayam Udang Premium (Isi 20 Pcs)', 'desc' => 'Daging ayam padat kenyal dengan cincangan udang segar dan saus chili oil gurih.', 'price' => 'Rp 45.000 / pack', 'badge' => 'Best Seller'],
                    ['title' => 'Nugget Ayam Homemade Keju (500gr)', 'desc' => 'Dibuat dari dada ayam segar tanpa MSG berlebih, aman untuk balita.', 'price' => 'Rp 38.000 / pack', 'badge' => 'Anak-anak'],
                    ['title' => 'Daging Sapi Slice Marinasi Bulgogi (500gr)', 'desc' => 'Irisan daging sapi empuk siap panggang di teflon, bumbu sudah meresap.', 'price' => 'Rp 65.000 / pack', 'badge' => 'Grill'],
                ],
                'faqs' => [
                    ['question' => 'Berapa lama masa simpan produk beku di freezer?', 'answer' => 'Produk kami tahan hingga 3-6 bulan di dalam freezer bersuhu minimal -18 derajat Celcius.'],
                ],
                'testimonials' => [
                    ['name' => 'Citra Dewi', 'role' => 'Ibu Bekerja', 'quote' => 'Sangat menyelamatkan saat pagi hari buru-buru bikin bekal anak. Tinggal goreng atau kukus sebentar sudah siap.', 'rating' => 5],
                ],
            ],

            // ─── 7. DIET & HEALTHY CATERING ───────────────────────────────────
            'fnb_catering_diet' => [
                'theme_color'        => '#16A34A',
                'headline'           => "Katering Sehat & Diet Berselera Tanpa Rasa Hambar di {$biz}",
                'subheadline'        => "Program makanan terukur kalori untuk turun berat badan, pemulihan kesehatan, dan kebugaran gym dengan bahan organik segar tanpa minyak jenuh berlebih.",
                'announcement_badge' => "Dihitung Presisi oleh Konsultan Gizi & Koki Berpengalaman",
                'cta_primary_text'   => "Pilih Paket Katering Diet",
                'cta_secondary_text' => "Konsultasi Target Kalori",
                'about_title'        => "Makan Sehat Tetap Enak dan Menyenangkan",
                'about_story'        => "Diet bukan berarti menyiksa diri dengan makanan hambar. Di {$biz}, kami mengolah bumbu rempah alami dan teknik memasak modern agar makanan sehat terasa selezat hidangan restoran.",
                'values' => [
                    ['icon' => 'heart-pulse', 'title' => 'Informasi Kalori & Makronutrisi Jelas', 'desc' => 'Setiap porsi disertai rincian gramasi protein, karbohidrat, dan serat.'],
                    ['icon' => 'leaf', 'title' => 'Sayuran Organik Bebas Pestisida', 'desc' => 'Bahan baku nabati segar dipanen langsung dari perkebunan ramah lingkungan.'],
                    ['icon' => 'calendar', 'title' => 'Menu Berganti Setiap Hari', 'desc' => 'Jadwal variasi menu selama 30 hari tanpa perulangan yang membosankan.'],
                    ['icon' => 'truck', 'title' => 'Pengantaran Tepat Jam Makan', 'desc' => 'Dikirim 2 kali sehari untuk makan siang dan makan malam segar.'],
                ],
                'services' => [
                    ['title' => 'Paket Weight Loss 5 Hari (Makan Siang & Malam)', 'desc' => 'Kisaran 400-500 Kcal per porsi, tinggi protein untuk defisit kalori efektif.', 'price' => 'Rp 275.000 / minggu', 'badge' => 'Diet'],
                    ['title' => 'Paket Muscle Gain High Protein', 'desc' => 'Dada ayam, salmon, telur dan karbohidrat kompleks untuk pembentukan otot.', 'price' => 'Rp 350.000 / minggu', 'badge' => 'Gym'],
                    ['title' => 'Paket Healthy Lunch Kantor (5 Hari Kerja)', 'desc' => 'Makan siang bergizi seimbang bebas santan jenuh dan MSG berlebih.', 'price' => 'Rp 140.000 / minggu', 'badge' => 'Kantor'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa custom alergi makanan seperti seafood atau kacang?', 'answer' => 'Bisa sekali, cantumkan riwayat alergi Anda saat mendaftar paket dan kami akan menyesuaikan menunya.'],
                ],
                'testimonials' => [
                    ['name' => 'dr. Tania Lestari', 'role' => 'Pelanggan 3 Bulan', 'quote' => 'Turun 6 kg tanpa ngerasa lemas sama sekali. Rasanya enak banget beda dari katering diet lainnya.', 'rating' => 5],
                ],
            ],

            // ─── 8. KONVEKSI & FASHION ────────────────────────────────────────
            'mfg_garment' => [
                'theme_color'        => '#DB2777',
                'headline'           => "Jasa Konveksi Busana, Seragam & Kaos Sablon Berkualitas di {$biz}",
                'subheadline'        => "Melayani pembuatan seragam kantor, kaos polo, kemeja batik, jaket bomber, seragam olahraga, dan busana muslim dengan jahitan rapi dan bahan adem.",
                'announcement_badge' => "Kapasitas Produksi Ribuan Pcs per Bulan - Bergaransi Ukuran",
                'cta_primary_text'   => "Minta Penawaran / Pesan Seragam",
                'cta_secondary_text' => "Katalog Bahan & Portofolio",
                'about_title'        => "Standar Jahitan Butik untuk Skala Partai & Satuan",
                'about_story'        => "{$biz} menggabungkan ketelitian penjahit terampil dengan mesin garmen modern. Kami berkomitmen menyelesaikan setiap pesanan tepat deadline tanpa mengorbankan kerapian jahitan.",
                'values' => [
                    ['icon' => 'shirt', 'title' => 'Pilihan Kain Grade A Lengkap', 'desc' => 'Cotton combed 24s/30s, drill premium, nagata, katun toyobo, dan ceruty.'],
                    ['icon' => 'scissors', 'title' => 'Jahitan Rantai Presisi Rapi', 'desc' => 'Dikerjakan oleh penjahit berpengalaman dengan pengawasan quality control ketat.'],
                    ['icon' => 'printer', 'title' => 'Sablon & Bordir Komputer Tajam', 'desc' => 'Hasil bordir padat tidak mudah terurai dan sablon plastisol tahan cuci.'],
                    ['icon' => 'clock', 'title' => 'Jaminan Selesai Tepat Deadline', 'desc' => 'Garansi waktu pengerjaan untuk kenyamanan acara dan operasional Anda.'],
                ],
                'services' => [
                    ['title' => 'Kemeja Seragam Kantor Drill Nagata (Min 12 Pcs)', 'desc' => 'Bahan adem tidak luntur, sudah termasuk bordir logo komputer 2 titik.', 'price' => 'Mulai Rp 95.000 / pcs', 'badge' => 'Seragam'],
                    ['title' => 'Kaos Komunitas Sablon Plastisol (Min 24 Pcs)', 'desc' => 'Bahan 100% Cotton Combed 30s adem lembut dengan sablon anti pecah.', 'price' => 'Mulai Rp 55.000 / pcs', 'badge' => 'Kaos'],
                    ['title' => 'Jaket Bomber / Hoodie Fleece Katun', 'desc' => 'Bahan tebal lembut nyaman dipakai siang maupun malam hari.', 'price' => 'Mulai Rp 135.000 / pcs', 'badge' => 'Jaket'],
                    ['title' => 'Gamis & Busana Muslimah Custom Butik', 'desc' => 'Model anggun kekinian cocok untuk seragam pengajian dan pesta keluarga.', 'price' => 'Mulai Rp 125.000 / pcs', 'badge' => 'Muslimah'],
                ],
                'faqs' => [
                    ['question' => 'Berapa minimal order untuk pembuatan seragam kantor?', 'answer' => 'Minimal pemesanan seragam konveksi adalah 12 pcs. Untuk kaos sablon minimal 24 pcs.'],
                    ['question' => 'Apakah bisa dibuatkan sampel (mockup) sebelum produksi massal?', 'answer' => 'Bisa, untuk pemesanan di atas 50 pcs kami menyediakan approval sample fisik sebelum pengerjaan penuh.'],
                ],
                'testimonials' => [
                    ['name' => 'Bambang Sugiarto', 'role' => 'HRD PT Berkah Mandiri', 'quote' => 'Seragam kantor 80 pcs selesai tepat waktu sebelum acara tahunan. Kerapian jahitannya mantap!', 'rating' => 5],
                ],
            ],

            // ─── 9. LOGAM, BUBUT & PLASTIK PRESISI ────────────────────────────
            'mfg_precision' => [
                'theme_color'        => '#475569',
                'headline'           => "Pabrikasi Suku Cadang Mesin, Bubut Presisi & Cetakan Molding di {$biz}",
                'subheadline'        => "Pengerjaan bubut konvensional & CNC, milling, wire cut, pengelasan argon, dan cetakan injeksi plastik dengan toleransi ukuran mikron yang akurat.",
                'announcement_badge' => "Didukung Mesin CNC Presisi Tinggi & Teknisi Berpengalaman",
                'cta_primary_text'   => "Konsultasi Gambar Teknik / PO",
                'cta_secondary_text' => "Daftar Kemampuan Bengkel",
                'about_title'        => "Presisi Tanpa Kompromi untuk Kelancaran Industri Anda",
                'about_story'        => "{$biz} melayani kebutuhan rekayasa suku cadang pabrik dan otomotif. Kami memastikan setiap material baja, kuningan, dural, dan teflon dikerjakan sesuai spesifikasi gambar teknis.",
                'values' => [
                    ['icon' => 'cog', 'title' => 'Toleransi Ukuran Akurat', 'desc' => 'Pengecekan menggunakan alat ukur mikrometer dan jangka sorong digital.'],
                    ['icon' => 'layers', 'title' => 'Material Logam Berkualitas', 'desc' => 'Baja S45C, SKD11, VCL, Stainless Steel 304, kuningan dan alumunium.'],
                    ['icon' => 'file-text', 'title' => 'Bisa dari Gambar 2D / 3D CAD', 'desc' => 'Menerima file format DWG, DXF, STEP, atau cukup membawa contoh benda kerja rusak.'],
                ],
                'services' => [
                    ['title' => 'Jasa Bubut Poros & Shaft Mesin', 'desc' => 'Pembuatan as roda, bushing perunggu, ulir drat, dan pully transmisi mesin.', 'price' => 'Estimasi Sesuai Gambar', 'badge' => 'Bubut'],
                    ['title' => 'Fabrikasi Gear & Roda Gigi Custom', 'desc' => 'Pembuatan roda gigi lurus, miring (helical), dan rantai sproket baja.', 'price' => 'Estimasi Sesuai Gambar', 'badge' => 'Gear'],
                    ['title' => 'Molding Plastik & Dies Stamping', 'desc' => 'Rancang bangun cetakan injeksi produk plastik dan pisau pon stamping plat.', 'price' => 'Konsultasi Proyek', 'badge' => 'Molding'],
                ],
                'faqs' => [
                    ['question' => 'Apakah melayani pembuatan part satuan atau harus partai besar?', 'answer' => 'Kami melayani pembuatan prototype satuan maupun produksi massal ratusan pcs.'],
                ],
                'testimonials' => [
                    ['name' => 'Ir. Gunawan', 'role' => 'Maintenance Manager Pabrik', 'quote' => 'Sparepart mesin impor yang patah berhasil dibikin replikanya dengan presisi sempurna. Mesin pabrik bisa jalan lagi.', 'rating' => 5],
                ],
            ],

            // ─── 10. FURNITURE & MEBEL KAYU ───────────────────────────────────
            'mfg_furniture' => [
                'theme_color'        => '#B45309',
                'headline'           => "Karya Mebel Kayu Solid & Interior Custom Berkualitas di {$biz}",
                'subheadline'        => "Pembuatan kitchen set modern, meja kerja, tempat tidur, lemari pakaian, dan mebel kayu jati solid bergaransi anti rayap dan finishing halus.",
                'announcement_badge' => "Free Desain 3D & Konsultasi Ukuran Lokasi",
                'cta_primary_text'   => "Minta Penawaran / Survey Lokasi",
                'cta_secondary_text' => "Galeri Proyek Interior",
                'about_title'        => "Kenyamanan Ruang dengan Sentuhan Kehangatan Alami",
                'about_story'        => "Setiap rumah berhak memiliki interior yang rapi dan fungsional. {$biz} memadukan keindahan urat kayu alami dengan aksesoris engsel soft-close modern untuk kenyamanan hidup Anda.",
                'values' => [
                    ['icon' => 'armchair', 'title' => 'Kayu Pilihan Kering Oven', 'desc' => 'Menggunakan kayu jati, mahoni, dan plywood meranti tebal anti melengkung.'],
                    ['icon' => 'sparkles', 'title' => 'Finishing Halus Sempurna', 'desc' => 'Pilihan lapisan HPL motif marmer/kayu, cat duco halus, dan melamin natural.'],
                    ['icon' => 'wrench', 'title' => 'Pemasangan Rapi Bergaransi', 'desc' => 'Dipasang langsung oleh tukang kayu profesional tanpa merusak dinding Anda.'],
                ],
                'services' => [
                    ['title' => 'Kitchen Set Modern Minimalis HPL', 'desc' => 'Termasuk rak piring stainless, laci bumbu, engsel slow-motion dan lampu LED strip.', 'price' => 'Mulai Rp 1.850.000 / m', 'badge' => 'Dapur'],
                    ['title' => 'Lemari Pakaian Wardrobe Pintu Geser (Sliding)', 'desc' => 'Maksimalisasi ruang kamar tidur dengan cermin besar dan laci tersembunyi.', 'price' => 'Mulai Rp 1.750.000 / m', 'badge' => 'Kamar'],
                    ['title' => 'Meja Makan Kayu Jati Solid 6 Kursi', 'desc' => 'Kayu utuh tebal dengan finishing melamin dove yang menonjolkan serat alami.', 'price' => 'Rp 4.500.000 / set', 'badge' => 'Solid'],
                ],
                'faqs' => [
                    ['question' => 'Berapa lama proses pembuatan kitchen set custom?', 'answer' => 'Waktu produksi di workshop berkisar 14-21 hari kerja, pemasangan di lokasi biasanya 1-2 hari.'],
                ],
                'testimonials' => [
                    ['name' => 'dr. Hendra Wijaya', 'role' => 'Pemilik Rumah Baru', 'quote' => 'Kitchen set rapi banget pengerjaannya, laci-lacinya halus ditutup. Sangat puas dengan hasil kerjanya!', 'rating' => 5],
                ],
            ],

            // ─── 11. KERAJINAN TANGAN & CRAFT ─────────────────────────────────
            'mfg_craft' => [
                'theme_color'        => '#9333EA',
                'headline'           => "Karya Seni Handmade, Souvenir Pernikahan & Kerajinan Unik di {$biz}",
                'subheadline'        => "Dibuat dengan sentuhan tangan penuh ketelitian dari bahan ramah lingkungan, rotan, kulit asli, resin, dan kayu estetik bernilai seni tinggi.",
                'announcement_badge' => "Karya Asli Pengrajin Nusantara - Menerima Custom Souvenir",
                'cta_primary_text'   => "Pesan Souvenir / Custom Craft",
                'cta_secondary_text' => "Katalog Produk Kerajinan",
                'about_title'        => "Kecintaan Pada Seni dan Keterampilan Tangan",
                'about_story'        => "{$biz} memberdayakan tangan-tangan terampil pengrajin lokal untuk menghasilkan produk dekorasi dan suvenir yang bermakna mendalam bagi setiap momen bahagia Anda.",
                'values' => [
                    ['icon' => 'palette', 'title' => 'Otentik & Eksklusif', 'desc' => 'Setiap karya memiliki keunikan karakter dan nilai seni tersendiri.'],
                    ['icon' => 'gift', 'title' => 'Kemasan Giftbox Elegan', 'desc' => 'Dilengkapi kartu ucapan custom dan pita hias yang siap dibagikan.'],
                    ['icon' => 'leaf', 'title' => 'Material Ramah Lingkungan', 'desc' => 'Memanfaatkan bahan alami dan serat daur ulang yang berkesinambungan.'],
                ],
                'services' => [
                    ['title' => 'Souvenir Pouch Kulit Sintetis (Min 100 Pcs)', 'desc' => 'Kemasan mika pita dengan emboss nama mempelai atau logo perusahaan.', 'price' => 'Rp 8.500 / pcs', 'badge' => 'Souvenir'],
                    ['title' => 'Tatakan Gelas Kayu Jati Resin Estetik (Set 4 Pcs)', 'desc' => 'Perpaduan kayu alami dan resin transparan untuk mempercantik meja tamu.', 'price' => 'Rp 65.000 / set', 'badge' => 'Home Decor'],
                    ['title' => 'Hiasan Dinding Macrame Tenun Bohemian', 'desc' => 'Tali katun alami dirajut tangan dengan batang kayu apung alami.', 'price' => 'Rp 85.000', 'badge' => 'Dekorasi'],
                ],
                'faqs' => [
                    ['question' => 'Berapa minimal pemesanan suvenir pernikahan kustom?', 'answer' => 'Minimal order suvenir dengan cetak nama adalah 50-100 pcs tergantung jenis barang.'],
                ],
                'testimonials' => [
                    ['name' => 'Sheila & Kevin', 'role' => 'Klien Souvenir Nikah', 'quote' => 'Souvenir pouch kulitnya cantik banget, tamunya pada suka karena bermanfaat dan jahitannya rapi!', 'rating' => 5],
                ],
            ],

            // ─── 12. PERCETAKAN & DIGITAL PRINTING ────────────────────────────
            'mfg_printing' => [
                'theme_color'        => '#EA580C',
                'headline'           => "Jasa Cetak Digital, Spanduk Banner & Stiker Label Cepat di {$biz}",
                'subheadline'        => "Cetak banner outdoor tahan panas hujan, stiker label kemasan botol/makanan cutting rapi, brosur, kalender, nota NCR, dan perlengkapan promosi usaha.",
                'announcement_badge' => "Mesin Format Besar Terbaru - Cetak Kilat Bisa Ditunggu",
                'cta_primary_text'   => "Kirim File Cetak via WhatsApp",
                'cta_secondary_text' => "Daftar Harga & Ukuran Cetak",
                'about_title'        => "Warna Tajam, Presisi dan Tepat Waktu Deadline",
                'about_story'        => "{$biz} adalah solusi percetakan serba ada bagi para pelaku UMKM dan perusahaan yang membutuhkan materi promosi berkualitas tinggi tanpa khawatir warna meleset.",
                'values' => [
                    ['icon' => 'sparkles', 'title' => 'Tinta Original Warna Cerah', 'desc' => 'Menggunakan tinta outdoor dan indoor Jepang tahan air dan tidak luntur.'],
                    ['icon' => 'scissors', 'title' => 'Cutting Pola Presisi (Kiss Cut / Die Cut)', 'desc' => 'Stiker label dipotong otomatis sesuai lekuk desain logo Anda.'],
                    ['icon' => 'clock', 'title' => 'Layanan Kilat Hari Ini Selesai', 'desc' => 'Cetak banner dan stiker siap ambil dalam hitungan jam.'],
                    ['icon' => 'file-check', 'title' => 'Bantu Cek Resolusi File', 'desc' => 'Tim kami memastikan file cetak Anda tidak pecah sebelum masuk mesin.'],
                ],
                'services' => [
                    ['title' => 'Cetak Banner Spanduk Flexi 280gr - 440gr', 'desc' => 'Cetak promosi outdoor tajam sudah termasuk mata ayam keliling di tiap sudut.', 'price' => 'Mulai Rp 18.000 / m²', 'badge' => 'Kilat'],
                    ['title' => 'Stiker Vinyl / Bontax + Cutting A3+', 'desc' => 'Label kemasan makanan & minuman tahan air dingin freezer, siap tempel.', 'price' => 'Rp 15.000 / lbr A3+', 'badge' => 'Favorit UMKM'],
                    ['title' => 'Brosur Full Color Art Paper 150gr (1 Rim)', 'desc' => 'Cetak promosi tajam mengkilap 2 sisi ukuran A5 atau A4 isi 500 lembar.', 'price' => 'Mulai Rp 165.000 / rim', 'badge' => 'Promosi'],
                    ['title' => 'Nota NCR 2 Rangkap Cetak Custom (Min 10 Buku)', 'desc' => 'Kertas tembus tanpa karbon, sudah termasuk jilid blok dan nomor nota urut.', 'price' => 'Rp 12.000 / buku', 'badge' => 'Nota'],
                ],
                'faqs' => [
                    ['question' => 'Format file apa yang paling bagus untuk dicetak?', 'answer' => 'Format file PDF, TIFF, CDR, atau JPG dengan resolusi minimal 150-300 dpi dan format warna CMYK.'],
                ],
                'testimonials' => [
                    ['name' => 'Nadia Sabrina', 'role' => 'Owner Minuman Kopi', 'quote' => 'Stiker vinyl-nya beneran tahan air dingin, dimasukkan freezer gak copot dan warnanya gak luntur.', 'rating' => 5],
                ],
            ],

            // ─── 13. KOSMETIK & SKINCARE ───────────────────────────────────────
            'mfg_cosmetics' => [
                'theme_color'        => '#EC4899',
                'headline'           => "Produk Perawatan Kulit Alami, Sehat & Bersertifikat di {$biz}",
                'subheadline'        => "Serum pencerah wajah, sabun mandi herbal, tabir surya UV filter, dan toner pelembap diformulasikan lembut untuk kulit tropis Indonesia.",
                'announcement_badge' => "Lolos Uji Dermatologi - Bebas Merkuri & Zat Berbahaya",
                'cta_primary_text'   => "Belanja Skincare Online",
                'cta_secondary_text' => "Katalog Perawatan Kulit",
                'about_title'        => "Kecantikan Sehat Alami dari Hati",
                'about_story'        => "{$biz} percaya kecantikan sejati terpancar dari kulit yang sehat terawat. Kami hanya menggunakan ekstrak botani alami dan bahan aktif berstandar farmasi resmi.",
                'values' => [
                    ['icon' => 'sparkles', 'title' => 'Bahan Aktif Aman & Lembut', 'desc' => 'Niacinamide, Hyaluronic Acid, Centella Asiatica, dan vitamin alami.'],
                    ['icon' => 'shield-check', 'title' => 'Standar Mutu CPKB', 'desc' => 'Diproduksi dengan higienitas tinggi sesuai Cara Pembuatan Kosmetika yang Baik.'],
                    ['icon' => 'heart', 'title' => 'Cruelty-Free & No Harsh Chemicals', 'desc' => 'Bebas paraben berlebih, hidrokuinon, dan pengawet berbahaya.'],
                ],
                'services' => [
                    ['title' => 'Brightening Glow Serum 20ml', 'desc' => 'Mencerahkan flek hitam dan meratakan warna kulit tanpa rasa perih.', 'price' => 'Rp 75.000', 'badge' => 'Best Seller'],
                    ['title' => 'Gentle Facial Cleanser Low pH 100ml', 'desc' => 'Pembersih wajah busa lembut yang menjaga kelembapan barrier kulit.', 'price' => 'Rp 48.000', 'badge' => 'Harian'],
                    ['title' => 'Sunscreen Gel SPF 50 PA++++ (50gr)', 'desc' => 'Tabir surya tekstur seringan air tanpa white-cast dan tidak menyumbat pori.', 'price' => 'Rp 65.000', 'badge' => 'Pelindung'],
                ],
                'faqs' => [
                    ['question' => 'Apakah produk ini aman untuk ibu hamil dan menyusui?', 'answer' => 'Sebagian besar produk dasar kami formulasi ramah ibu hamil, silakan cek deskripsi komposisi produk.'],
                ],
                'testimonials' => [
                    ['name' => 'Anisa Rahma', 'role' => 'Beauty Enthusiast', 'quote' => 'Serumnya ringan cepat meresap, dalam 2 minggu bekas jerawat pudar dan kulit lebih cerah alami.', 'rating' => 5],
                ],
            ],

            // ─── 14. PENJAHIT JAS & KEBAYA CUSTOM ─────────────────────────────
            'mfg_tailor_custom' => [
                'theme_color'        => '#6D28D9',
                'headline'           => "Jasa Jahit Jas Pria, Kebaya Wisuda & Gaun Pengantin di {$biz}",
                'subheadline'        => "Pengerjaan adibusana bespoke dengan pengukuran detail anatomi tubuh, fitting berkala, furing sutra halus, dan jahitan tangan master tailor berpengalaman.",
                'announcement_badge' => "Fitting Sempurna Sesuai Lekuk Tubuh - Garansi Revisi Ukuran",
                'cta_primary_text'   => "Jadwalkan Konsultasi & Pengukuran",
                'cta_secondary_text' => "Galeri Jas & Kebaya Pengantin",
                'about_title'        => "Keanggunan Tradisi Jahit Presisi Tinggi",
                'about_story'        => "Busana yang tepat akan meningkatkan kepercayaan diri pemakainya. Di {$biz}, setiap helai pakaian dirancang khusus untuk mewujudkan siluet tubuh yang proporsional dan elegan.",
                'values' => [
                    ['icon' => 'scissors', 'title' => 'Pola Kustom Perorangan', 'desc' => 'Setiap klien dibuatkan pola individual baru sesuai lekuk tubuh masing-masing.'],
                    ['icon' => 'award', 'title' => 'Master Tailor Berpengalaman', 'desc' => 'Menguasai struktur potongan jas modern (Italian cut / British cut) dan kebaya pakem.'],
                    ['icon' => 'check-circle-2', 'title' => 'Garansi Fitting Sampai Pas', 'desc' => 'Revisi penyesuaian ukuran gratis hingga pakaian nyaman dikenakan.'],
                ],
                'services' => [
                    ['title' => 'Jahit Jas Pria Formal 2 Piece (Jas + Celana)', 'desc' => 'Termasuk bahan wool blend, bantalan pundak impor, dan kancing eksklusif.', 'price' => 'Mulai Rp 1.500.000', 'badge' => 'Jas'],
                    ['title' => 'Jahit Kebaya Wisuda & Pesta Payet Halus', 'desc' => 'Bahan brokat/tille halus dengan taburan mutiara dan payet jahit tangan rapi.', 'price' => 'Mulai Rp 450.000', 'badge' => 'Kebaya'],
                    ['title' => 'Gaun Pengantin Custom Akad / Resepsi', 'desc' => 'Desain gaun impian impian Anda lengkap dengan bustier dan veil panjang anggun.', 'price' => 'Konsultasi Desain', 'badge' => 'Bridal'],
                ],
                'faqs' => [
                    ['question' => 'Berapa kali sesi fitting yang dibutuhkan sebelum baju selesai?', 'answer' => 'Rata-rata 1-2 kali sesi fitting untuk memastikan bahu, pinggang, dan panjang pakaian jatuh pas.'],
                ],
                'testimonials' => [
                    ['name' => 'Ferry Gunawan', 'role' => 'Pengantin Pria', 'quote' => 'Jas pernikahannya pas banget di badan, gak ada lipatan aneh di punggung. Sangat berkelas potongannya.', 'rating' => 5],
                ],
            ],

            // ─── 15. TOKO RETAIL & MINIMARKET ─────────────────────────────────
            'retail_reseller' => [
                'theme_color'        => '#16A34A',
                'headline'           => "Belanja Sembako Lengkap, Hemat & Dekat di {$biz}",
                'subheadline'        => "Beras pilihan, minyak goreng jernih, telur segar, kebutuhan rumah tangga, makanan ringan, dan perlengkapan mandi lengkap dengan harga tetangga.",
                'announcement_badge' => "Harga Eceran & Grosir - Siap Antar ke Rumah Anda",
                'cta_primary_text'   => "Pesan Belanjaan via Toko Online",
                'cta_secondary_text' => "Katalog Promo Minggu Ini",
                'about_title'        => "Sahabat Kebutuhan Harian Keluarga Anda",
                'about_story'        => "{$biz} hadir memberikan kemudahan belanja kebutuhan pokok harian dengan harga jujur bersahabat, stok selalu baru dan segar, serta layanan ramah bertetangga.",
                'values' => [
                    ['icon' => 'tag', 'title' => 'Harga Pasti Bersahabat', 'desc' => 'Harga bersaing langsung dari distributor terpercaya tanpa biaya tambahan.'],
                    ['icon' => 'check-circle-2', 'title' => 'Stok Selalu Baru & Higienis', 'desc' => 'Tanggal kedaluwarsa selalu dipantau ketat demi keamanan konsumsi keluarga.'],
                    ['icon' => 'truck', 'title' => 'Layanan Antar Belanjaan', 'desc' => 'Belanja via ponsel, barang pesanan langsung diantar sampai depan pintu rumah.'],
                    ['icon' => 'credit-card', 'title' => 'Bayar Tunai & QRIS', 'desc' => 'Mendukung pembayaran non-tunai bebas repot uang kembalian.'],
                ],
                'services' => [
                    ['title' => 'Beras Premium 5kg Kemasan', 'desc' => 'Beras pulen bersih bebas pemutih dan wangi pandan alami.', 'price' => 'Rp 72.000', 'badge' => 'Pokok'],
                    ['title' => 'Minyak Goreng Refill 2 Liter', 'desc' => 'Minyak kelapa sawit jernih dua kali penyaringan tidak mudah hitam.', 'price' => 'Rp 34.000', 'badge' => 'Hemat'],
                    ['title' => 'Telur Ayam Ras Segar 1 Kg', 'desc' => 'Telur pilihan peternakan lokal selalu fresh datang setiap pagi.', 'price' => 'Rp 27.000', 'badge' => 'Segar'],
                    ['title' => 'Paket Sembako Lengkap Bulanan', 'desc' => 'Paket hemat isi beras, minyak, gula pasir, teh, kopi, dan mie instan.', 'price' => 'Rp 165.000', 'badge' => 'Paket'],
                ],
                'faqs' => [
                    ['question' => 'Apakah ada layanan pesan antar sampai ke rumah?', 'answer' => 'Ya, kami melayani antar belanjaan langsung ke rumah untuk area terdekat.'],
                ],
                'testimonials' => [
                    ['name' => 'Ibu Ratna', 'role' => 'Warga Sekitar', 'quote' => 'Tinggal pesan lewat web langsung diantar sama kurirnya. Ngebantu banget pas lagi repot ngasuh anak.', 'rating' => 5],
                ],
            ],

            // ─── 16. APOTEK & TOKO OBAT ────────────────────────────────────────
            'retail_pharmacy' => [
                'theme_color'        => '#0D9488',
                'headline'           => "Apotek Terlengkap, Obat Asli & Layanan Farmasi Ramah di {$biz}",
                'subheadline'        => "Penyediaan obat resep dokter, obat bebas berstandar BPOM, suplemen vitamin imunitas, susu medis, dan alat cek kesehatan lengkap terpercaya.",
                'announcement_badge' => "100% Produk Farmasi Resmi dari Distributor PBF Berizin",
                'cta_primary_text'   => "Beli Obat / Kirim Resep Dokter",
                'cta_secondary_text' => "Katalog Vitamin & Obat",
                'about_title'        => "Kesehatan Keluarga Anda Adalah Amanah Kami",
                'about_story'        => "{$biz} didukung oleh apoteker dan asisten tenaga farmasi berijazah resmi. Kami siap memberikan informasi dosis, aturan pakai, dan interaksi obat yang aman dan jelas.",
                'values' => [
                    ['icon' => 'shield-check', 'title' => 'Jaminan Obat Asli BPOM', 'desc' => 'Tidak menjual obat ilegal atau palsu, seluruh stok tercatat faktur resmi PBF.'],
                    ['icon' => 'heart-pulse', 'title' => 'Konsultasi Apoteker Ramah', 'desc' => 'Tanyakan aturan minum dan efek samping obat tanpa dipungut biaya.'],
                    ['icon' => 'file-text', 'title' => 'Tebus Resep Dokter Cepat', 'desc' => 'Kirim foto resep dokter via WhatsApp/Web untuk disiapkan segera.'],
                ],
                'services' => [
                    ['title' => 'Paket Vitamin C & Zinc Imunitas Tubuh', 'desc' => 'Suplemen daya tahan tubuh harian untuk menjaga stamina di cuaca tak menentu.', 'price' => 'Rp 35.000', 'badge' => 'Vitamin'],
                    ['title' => 'Cek Gula Darah, Kolesterol & Asam Urat', 'desc' => 'Pemeriksaan cepat di tempat dengan alat digital akurat dan jarum steril sekali pakai.', 'price' => 'Rp 30.000', 'badge' => 'Cek'],
                    ['title' => 'Alat Tensimeter Digital Lengan Otomatis', 'desc' => 'Alat ukur tekanan darah praktis bersertifikasi akurat untuk lansia di rumah.', 'price' => 'Rp 285.000', 'badge' => 'Alkes'],
                ],
                'faqs' => [
                    ['question' => 'Bagaimana cara menebus obat resep dokter?', 'answer' => 'Cukup kirim foto resep dokter yang jelas melalui tombol WhatsApp kami, apoteker kami akan memverifikasi dan menyiapkan obat.'],
                ],
                'testimonials' => [
                    ['name' => 'Bapak Hartono', 'role' => 'Pasien Rutin', 'quote' => 'Apotekernya ramah banget menjelaskan obat tensi saya. Harganya lebih murah daripada apotek waralaba lain.', 'rating' => 5],
                ],
            ],

            // ─── 17. DIGITAL AGENCY & IT SOFTWARE ─────────────────────────────
            'service_agency' => [
                'theme_color'        => '#2563EB',
                'headline'           => "Jasa Pembuatan Website, Aplikasi & Pemasaran Digital Profesional di {$biz}",
                'subheadline'        => "Kami membantu bisnis UMKM dan korporasi bertransformasi digital melalui website elegan, sistem backend handal, UI/UX modern, dan kampanye iklan digital.",
                'announcement_badge' => "Solusi Digital Berbasis Apple HIG & Cloud Modern",
                'cta_primary_text'   => "Konsultasi Proyek & Portofolio",
                'cta_secondary_text' => "Daftar Layanan Digital",
                'about_title'        => "Teknologi yang Mengembangkan Potensi Bisnis Anda",
                'about_story'        => "{$biz} adalah studio rekayasa perangkat lunak dan desain kreatif. Kami memadukan estetika antarmuka kelas dunia dengan kecepatan infrastruktur komputasi awan yang aman.",
                'values' => [
                    ['icon' => 'code-2', 'title' => 'Teknologi Cepat & Teruji', 'desc' => 'Dibangun dengan arsitektur modern, responsive mobile-first, dan loading gesit.'],
                    ['icon' => 'sparkles', 'title' => 'Desain Bersih & Elegan', 'desc' => 'Antarmuka intuitif yang memikat pelanggan dan meningkatkan konversi penjualan.'],
                    ['icon' => 'shield-check', 'title' => 'Keamanan Data & Garansi Bug', 'desc' => 'Dukungan pemeliharaan teknis dan garansi perbaikan bug pasca-peluncuran.'],
                ],
                'services' => [
                    ['title' => 'Pembuatan Website Company Profile & Portofolio', 'desc' => 'Website elegan mobile responsive, SEO Google ready, dan integrasi WhatsApp.', 'price' => 'Mulai Rp 2.500.000', 'badge' => 'Web'],
                    ['title' => 'Pembuatan Toko Online & Aplikasi Web Kustom', 'desc' => 'Katalog produk mandiri, gerbang pembayaran instan, dan kalkulator ongkir.', 'price' => 'Mulai Rp 4.500.000', 'badge' => 'E-Commerce'],
                    ['title' => 'Desain UI/UX & Redesain Aplikasi Mobile', 'desc' => 'Riset alur pengguna, wireframe prototipe interaktif, dan desain Apple HIG.', 'price' => 'Mulai Rp 3.000.000', 'badge' => 'Desain'],
                ],
                'faqs' => [
                    ['question' => 'Berapa lama proses pengerjaan website company profile standar?', 'answer' => 'Rata-rata 7 hingga 14 hari kerja setelah materi konten dan desain disetujui.'],
                ],
                'testimonials' => [
                    ['name' => 'Ferry Darmawan', 'role' => 'CEO CV Mega Karya', 'quote' => 'Website kami sekarang kelihatan mewah dan loading-nya cepet banget. Klien dari luar kota makin percaya.', 'rating' => 5],
                ],
            ],

            // ─── 18. KONTRAKTOR & BANGUNAN ────────────────────────────────────
            'service_contractor' => [
                'theme_color'        => '#D97706',
                'headline'           => "Jasa Renovasi Rumah, Kontraktor Bangunan & Material Berkualitas di {$biz}",
                'subheadline'        => "Pembangunan rumah tinggal, ruko, renovasi atap bocor, pengecatan, partisi gipsum, dan penyediaan semen, pasir, besi beton SNI serta baja ringan.",
                'announcement_badge' => "Free Survey Lokasi, Gambar Denah & Rencana Anggaran Biaya (RAB)",
                'cta_primary_text'   => "Minta Survey & Hitung Biaya RAB",
                'cta_secondary_text' => "Katalog Material & Layanan",
                'about_title'        => "Membangun dengan Kokoh, Transparan dan Amanah",
                'about_story'        => "{$biz} hadir memberikan rasa tenang bagi pemilik rumah dan investor properti. Kami mengutamakan transparansi bahan bangunan, tukang berpengalaman, dan garansi kebocoran.",
                'values' => [
                    ['icon' => 'hard-hat', 'title' => 'Mandor & Tukang Terampil', 'desc' => 'Pengerjaan rapi, lot tegak lurus, dan sambungan keramik presisi.'],
                    ['icon' => 'file-text', 'title' => 'RAB Jelas Tanpa Biaya Siluman', 'desc' => 'Rincian volume material dan upah tukang dijelaskan transparan di awal.'],
                    ['icon' => 'shield-check', 'title' => 'Garansi Pemeliharaan', 'desc' => 'Jaminan perbaikan jika timbul retak rambut atau rembes air setelah pengerjaan.'],
                ],
                'services' => [
                    ['title' => 'Paket Bangun Rumah Baru (Per Meter Persegi)', 'desc' => 'Termasuk pondasi cakar ayam, bata merah/hebel, atap baja ringan & finishing cat.', 'price' => 'Mulai Rp 3.200.000 / m²', 'badge' => 'Bangun'],
                    ['title' => 'Renovasi Atap Bocor & Ganti Rangka Baja Ringan', 'desc' => 'Pembersihan talang seng lama, ganti usuk reng zincalume anti rayap dan genteng.', 'price' => 'Mulai Rp 185.000 / m²', 'badge' => 'Atap'],
                    ['title' => 'Pasang Keramik & Granit Lantai Presisi', 'desc' => 'Pemasangan lantai granit 60x60 rata tanpa kopong dengan semen perekat khusus.', 'price' => 'Mulai Rp 85.000 / m²', 'badge' => 'Lantai'],
                ],
                'faqs' => [
                    ['question' => 'Apakah biaya survey dan hitung RAB dipungut biaya?', 'answer' => 'Tidak, survey lokasi dan pembuatan draft RAB estimasi gratis tanpa ikatan kontrak.'],
                ],
                'testimonials' => [
                    ['name' => 'Ir. Hendra Kusuma', 'role' => 'Pemilik Rumah Renovasi', 'quote' => 'Renovasi dapur dan ruang belakang selesai tepat waktu sebelum lebaran. Tukangnya sopan dan rapi kerjanya.', 'rating' => 5],
                ],
            ],

            // ─── 19. EVENT & WEDDING ORGANIZER ────────────────────────────────
            'service_event' => [
                'theme_color'        => '#7C3AED',
                'headline'           => "Wujudkan Momen Pernikahan & Acara Impian Sempurna Bersama {$biz}",
                'subheadline'        => "Perencanaan komprehensif hari pernikahan, gathering perusahaan, dekorasi anggun, dokumentasi foto sinematik, sound system, dan pendampingan rundown.",
                'announcement_badge' => "Jadwal Acara Tertata Rapi - Nikmati Hari Bahagia Anda",
                'cta_primary_text'   => "Konsultasi Paket Acara & Wedding",
                'cta_secondary_text' => "Galeri Acara & Portofolio",
                'about_title'        => "Mengabadikan Cerita Bahagia Tanpa Rasa Cemas",
                'about_story'        => "Di {$biz}, kami percaya bahwa pengantin dan keluarga harus menikmati setiap detik hari pernikahan mereka. Biarkan tim profesional kami yang mengurus seluruh koordinasi vendor di lapangan.",
                'values' => [
                    ['icon' => 'party-popper', 'title' => 'Koordinasi Vendor Menyeluruh', 'desc' => 'Menghubungkan katering, MUA, dekorasi, panggung, dan gedung tanpa miskomunikasi.'],
                    ['icon' => 'clock', 'title' => 'Rundown Disiplin & Presisi', 'desc' => 'Waktu akad nikah, resepsi, hingga foto bersama berjalan tertib sesuai susunan acara.'],
                    ['icon' => 'users', 'title' => 'Crew Lapangan Sigap & Ramah', 'desc' => 'Staf pendamping pengantin (bride assistant) siap siaga mendampingi kebutuhan Anda.'],
                ],
                'services' => [
                    ['title' => 'Paket Wedding Organizer Day-Of Coordination', 'desc' => 'Pendampingan 6-8 crew profesional, technical meeting vendor, dan gladi resik.', 'price' => 'Mulai Rp 4.500.000', 'badge' => 'WO'],
                    ['title' => 'Paket All-In One Wedding (Gedung & Vendor)', 'desc' => 'Solusi lengkap dekorasi pelaminan, busana pengantin, katering 500 pax, dan dokumentasi.', 'price' => 'Konsultasi Anggaran', 'badge' => 'All-In'],
                    ['title' => 'Paket Gathering & Seminar Perusahaan', 'desc' => 'Penyediaan stage backdrop panggung, audio system, LED screen, dan registrasi tamu.', 'price' => 'Mulai Rp 3.500.000', 'badge' => 'Event'],
                ],
                'faqs' => [
                    ['question' => 'Berapa bulan sebelum hari H sebaiknya mulai memakai jasa WO?', 'answer' => 'Sangat disarankan mulai berkonsultasi 3 hingga 6 bulan sebelum acara untuk kepastian gedung dan vendor.'],
                ],
                'testimonials' => [
                    ['name' => 'Putri & Yoga', 'role' => 'Pengantin Baru', 'quote' => 'Acara pernikahan kami berlangsung lancar banget berkat tim WO yang super sigap. Orang tua kami juga tenang gak capek!', 'rating' => 5],
                ],
            ],

            // ─── 20. BENGKEL MOBIL & MOTOR ────────────────────────────────────
            'service_workshop' => [
                'theme_color'        => '#0284C7',
                'headline'           => "Servis Kendaraan Terpercaya, Bergaransi & Transparan di {$biz}",
                'subheadline'        => "Tune up injeksi, ganti oli mesin original, servis rem, servis CVT matic, kelistrikan, overhoul mesin, dan suku cadang asli bergaransi kerja 14 hari.",
                'announcement_badge' => "Garansi Servis 14 Hari & Pengecekan Kendaraan Gratis",
                'cta_primary_text'   => "Booking Antrean Servis via WA",
                'cta_secondary_text' => "Daftar Harga Paket Servis",
                'about_title'        => "Menjaga Performa dan Keselamatan Kendaraan Anda",
                'about_story'        => "{$biz} didirikan untuk memberikan rasa aman bagi pengendara. Kami mengedepankan estimasi biaya transparan tanpa biaya tersembunyi, pengerjaan cepat dengan alat modern, dan sparepart original.",
                'values' => [
                    ['icon' => 'wrench', 'title' => 'Mekanik Ahli Berpengalaman', 'desc' => 'Dikerjakan oleh teknisi bersertifikasi yang paham seluk-beluk mesin.'],
                    ['icon' => 'shield-check', 'title' => 'Sparepart 100% Asli', 'desc' => 'Jaminan suku cadang original dengan masa garansi pengerjaan.'],
                    ['icon' => 'clock', 'title' => 'Pengerjaan Cepat Tanpa Antre Lama', 'desc' => 'Sistem booking terjadwal agar motor atau mobil Anda lekas selesai.'],
                    ['icon' => 'check-circle-2', 'title' => 'Estimasi Biaya Sebelum Dikerjakan', 'desc' => 'Pemeriksaan kerusakan dan harga disetujui pelanggan sebelum diganti.'],
                ],
                'services' => [
                    ['title' => 'Paket Servis Ringan + Ganti Oli Mesin', 'desc' => 'Termasuk cek rem depan belakang, kelistrikan lampu, angin ban, dan rantai/CVT.', 'price' => 'Mulai Rp 85.000', 'badge' => 'Hemat'],
                    ['title' => 'Tune Up Injeksi & Pembersihan Throttle Body', 'desc' => 'Kalibrasi sensor injeksi, semprot ruang bakar, dan busi untuk tarikan enteng.', 'price' => 'Rp 65.000', 'badge' => 'Populer'],
                    ['title' => 'Servis CVT Matic Lengkap (Motor Matic)', 'desc' => 'Pembersihan mangkok ganda, roller, v-belt, dan pelumasan grease CVT.', 'price' => 'Rp 50.000', 'badge' => 'Matic'],
                    ['title' => 'Kuras Minyak Rem & Ganti Kampas Rem', 'desc' => 'Pembersihan kaliper cakram dan minyak rem DOT 4 baru demi keselamatan berkendara.', 'price' => 'Mulai Rp 45.000', 'badge' => 'Rem'],
                ],
                'faqs' => [
                    ['question' => 'Apakah harus booking jadwal terlebih dahulu?', 'answer' => 'Anda bisa langsung datang (walk-in), namun kami menyarankan booking melalui website ini agar tidak menunggu antrean.'],
                    ['question' => 'Apakah ada garansi setelah servis?', 'answer' => 'Ya, kami memberikan garansi servis selama 14 hari kerja jika keluhan yang sama berulang.'],
                ],
                'testimonials' => [
                    ['name' => 'Bambang Sudiro', 'role' => 'Pengguna Motor Harian', 'quote' => 'Mekaniknya ramah, penjelasannya detail, dan motor jadi enteng banget tarikannya. Sangat direkomendasikan!', 'rating' => 5],
                ],
            ],

            // ─── 21. BARBERSHOP & SALON RAMBUT ────────────────────────────────
            'service_barbershop' => [
                'theme_color'        => '#9333EA',
                'headline'           => "Sentuhan Gaya Rambut Rapi & Perawatan Maksimal di {$biz}",
                'subheadline'        => "Potong rambut tren terkini, fade presisi, creambath relaksasi, hair styling pomade, perataan warna rambut, dan cuci blow ditangani kapster profesional.",
                'announcement_badge' => "Potong Rambut Nyaman Ber-AC & Free Softdrink",
                'cta_primary_text'   => "Booking Jam Potong Rambut",
                'cta_secondary_text' => "Pilihan Layanan & Tarif",
                'about_title'        => "Tampil Percaya Diri dengan Gaya Rambut Idaman",
                'about_story'        => "Kami percaya setiap orang berhak tampil percaya diri dan menawan. Dengan teknik potong presisi dan produk perawatan rambut pilihan, kami siap mewujudkan penampilan terbaik Anda.",
                'values' => [
                    ['icon' => 'scissors', 'title' => 'Kapster & Stylist Bersertifikat', 'desc' => 'Menguasai tren potongan rambut terkini dari classic gentleman hingga modern crop.'],
                    ['icon' => 'sparkles', 'title' => 'Peralatan Steril & Higienis', 'desc' => 'Gunting, clipper dan handuk dibersihkan steril sebelum digunakan ke pelanggan.'],
                    ['icon' => 'coffee', 'title' => 'Ruangan Ber-AC Dingin & Santai', 'desc' => 'Alunan musik santai, kursi empuk, dan aroma ruangan yang menenangkan.'],
                ],
                'services' => [
                    ['title' => 'Gentleman Haircut + Wash & Pomade Styling', 'desc' => 'Potong rambut presisi, pijat kepala ringan, cuci rambut, dan aplikasi pomade.', 'price' => 'Rp 50.000', 'badge' => 'Barber'],
                    ['title' => 'Ladies Haircut + Wash & Blow Dry', 'desc' => 'Konsultasi bentuk wajah, potong rambut rapi, cuci rambut wangi dan blow.', 'price' => 'Rp 75.000', 'badge' => 'Salon'],
                    ['title' => 'Creambath Tradisional Relaksasi 45 Menit', 'desc' => 'Perawatan akar rambut dengan pijatan leher dan bahu untuk melepas lelah.', 'price' => 'Rp 85.000', 'badge' => 'Favorit'],
                    ['title' => 'Hair Coloring / Cat Rambut Hitam Alami', 'desc' => 'Tutup uban atau pewarnaan tren modern dengan pelindung batang rambut.', 'price' => 'Mulai Rp 120.000', 'badge' => 'Color'],
                ],
                'faqs' => [
                    ['question' => 'Apakah harus booking terlebih dahulu?', 'answer' => 'Disarankan booking jam kedatangan via website atau WhatsApp agar Anda tidak perlu menunggu giliran antre.'],
                ],
                'testimonials' => [
                    ['name' => 'Ferry Gunawan', 'role' => 'Pelanggan Setia', 'quote' => 'Fade cut-nya sangat rapi dan detail. Tempatnya adem dan pelayanannya sangat ramah.', 'rating' => 5],
                ],
            ],

            // ─── 22. LAUNDRY KILOAN & DRY CLEAN ───────────────────────────────
            'service_laundry' => [
                'theme_color'        => '#0891B2',
                'headline'           => "Cucian Bersih Higienis, Rapi & Wangi Tahan Lama di {$biz}",
                'subheadline'        => "Layanan cuci setrika uap kiloan, dry clean satuan jas/gaun, cuci bed cover tebal, sepatu, tas, dan gorden dengan mesin modern 1 pelanggan 1 mesin (tidak dicampur).",
                'announcement_badge' => "1 Mesin 1 Pelanggan - Free Antar Jemput Radius Terdekat",
                'cta_primary_text'   => "Pesan Cuci / Antar Jemput",
                'cta_secondary_text' => "Daftar Paket Laundry & Harga",
                'about_title'        => "Perawatan Pakaian Keluarga dengan Standar Tinggi",
                'about_story'        => "{$biz} berkomitmen merawat setiap helai pakaian Anda tanpa pernah mencampurnya dengan pakaian pelanggan lain. Air bersih tersaring dan detergen ramah serat kain menjamin warna pakaian tetap awet.",
                'values' => [
                    ['icon' => 'shield-check', 'title' => '1 Mesin 1 Pelanggan', 'desc' => 'Pakaian Anda tidak pernah dicampur dengan pakaian orang lain, higienis dan suci.'],
                    ['icon' => 'sparkles', 'title' => 'Parfum Tahan Lama', 'desc' => 'Pilihan aroma segar mewah yang awet menempel di serat kain berhari-hari.'],
                    ['icon' => 'clock', 'title' => 'Layanan Kilat Express 6 Jam', 'desc' => 'Solusi darurat untuk pakaian kerja atau sekolah yang butuh segera dipakai.'],
                    ['icon' => 'truck', 'title' => 'Free Pickup & Delivery', 'desc' => 'Kirim lokasi via WhatsApp, kurir kami siap menjemput cucian kotor Anda.'],
                ],
                'services' => [
                    ['title' => 'Laundry Kiloan Cuci Kering Setrika (Reguler 2 Hari)', 'desc' => 'Pakaian dicuci bersih, disetrika uap rapi, dipacking plastik kedap udara.', 'price' => 'Rp 7.000 / kg', 'badge' => 'Favorit'],
                    ['title' => 'Laundry Kiloan Express (Selesai 6 Jam)', 'desc' => 'Pencucian kilat siap pakai di hari yang sama dengan aroma wangi segar.', 'price' => 'Rp 14.000 / kg', 'badge' => 'Kilat'],
                    ['title' => 'Cuci Bed Cover Jumbo + Tas Higienis', 'desc' => 'Pembersihan debu tungau dan noda dengan putaran mesin khusus berkapasitas besar.', 'price' => 'Rp 30.000 / pc', 'badge' => 'Bedcover'],
                    ['title' => 'Deep Clean Sepatu Sneakers & Kulit', 'desc' => 'Pembersihan sol dan material kanvas/suede dengan cairan cleaner khusus sepatu.', 'price' => 'Rp 35.000 / psg', 'badge' => 'Sepatu'],
                ],
                'faqs' => [
                    ['question' => 'Bagaimana cara menggunakan layanan antar jemput?', 'answer' => 'Cukup klik tombol pesan antar jemput di web ini, isi alamat dan jumlah cucian, kurir kami akan segera meluncur.'],
                ],
                'testimonials' => [
                    ['name' => 'Linda Susanti', 'role' => 'Ibu Rumah Tangga', 'quote' => 'Pakaian disetrika rapi wangi tahan seminggu di lemari. Antar jemputnya juga selalu on time!', 'rating' => 5],
                ],
            ],

            // ─── 23. CUCI MOBIL & AUTO DETAILING ──────────────────────────────
            'service_autodetailing' => [
                'theme_color'        => '#2563EB',
                'headline'           => "Kilau Bersih Sempurna & Perlindungan Cat Mobil Anda di {$biz}",
                'subheadline'        => "Cuci mobil salju hidrolik H-beam, pembersihan interior vakum, poles jamur kaca, penghilang baret halus, dan nano ceramic coating tahan cuaca ekstrem.",
                'announcement_badge' => "Shampoo Mobil pH Netral - Tidak Mengikis Pernis Cat",
                'cta_primary_text'   => "Booking Cuci / Auto Detailing",
                'cta_secondary_text' => "Pilihan Paket & Estimasi",
                'about_title'        => "Perawatan Kendaraan Seperti Mobil Baru Kembali",
                'about_story'        => "Bagi pecinta otomotif, kendaraan bersih adalah sumber kebanggaan. {$biz} menggunakan lap microfiber bersih dan obat poles compound terpercaya untuk mengembalikan kilau asli body mobil Anda.",
                'values' => [
                    ['icon' => 'car', 'title' => 'Lift Hidrolik Aman', 'desc' => 'Kolong mobil dibersihkan tuntas dari lumpur dan pasir yang memicu karat.'],
                    ['icon' => 'sparkles', 'title' => 'Compound Poles Impor', 'desc' => 'Menghilangkan baret halus dan swirl mark tanpa membuat cat botak.'],
                    ['icon' => 'shield-check', 'title' => 'Garansi Nano Coating', 'desc' => 'Efek daun talas (hydrophobic) melindungi body mobil dari noda air hujan.'],
                ],
                'services' => [
                    ['title' => 'Cuci Mobil Hidrolik + Vakum Interior Bersih', 'desc' => 'Cuci kolong, body busa salju, semir ban basah, dan vakum karpet debu.', 'price' => 'Rp 45.000', 'badge' => 'Reguler'],
                    ['title' => 'Pembersihan Jamur Kaca Depan & Samping', 'desc' => 'Pandangan kembali jernih dan bebas buram saat hujan lebat di malam hari.', 'price' => 'Mulai Rp 100.000', 'badge' => 'Kaca'],
                    ['title' => 'Paket Salon Mobil Full Interior & Eksterior', 'desc' => 'Pembersihan jok, plafon, ruang mesin, dan poles body 3 step mengkilap.', 'price' => 'Mulai Rp 550.000', 'badge' => 'Salon'],
                ],
                'faqs' => [
                    ['question' => 'Berapa lama durasi pengerjaan cuci mobil hidrolik?', 'answer' => 'Rata-rata 30 hingga 45 menit sudah termasuk pembersihan vakum interior.'],
                ],
                'testimonials' => [
                    ['name' => 'Denny Pratama', 'role' => 'Penggemar Otomotif', 'quote' => 'Kolong mobil bersih kinclong, pengerjaannya teliti sampai ke sela-sela velg. Ruang tunggunya juga nyaman ber-AC.', 'rating' => 5],
                ],
            ],

            // ─── 24. DISTRIBUTOR GROSIR & LOGISTIK ────────────────────────────
            'distributor_fmcg' => [
                'theme_color'        => '#F97316',
                'headline'           => "Pusat Distribusi Grosir Produk Cepat Habis & Jasa Logistik di {$biz}",
                'subheadline'        => "Pasokan sembako kartonan, makanan ringan, minuman kemasan, dan sabun mandi untuk toko kelontong, warung, minimarket dengan armada kirim cepat.",
                'announcement_badge' => "Harga Grosir Langsung Pabrik - Siap Kirim Armada Truk",
                'cta_primary_text'   => "Minta Pricelist Grosir / Order",
                'cta_secondary_text' => "Katalog Produk Kartonan",
                'about_title'        => "Mitra Pasokan Terpercaya Warung dan Toko Anda",
                'about_story'        => "{$biz} menjembatani pabrik prinsipal dengan ribuan peritel tradisional. Kami menjaga kontinuitas ketersediaan stok barang dan harga yang menguntungkan bagi para pedagang.",
                'values' => [
                    ['icon' => 'truck', 'title' => 'Armada Kirim Terjadwal', 'desc' => 'Pengantaran barang sampai ke toko Anda dengan armada pick-up dan truk siaga.'],
                    ['icon' => 'tag', 'title' => 'Harga Grosir Kartonan Terbaik', 'desc' => 'Makin besar volume pembelian, makin kompetitif harga yang Anda peroleh.'],
                    ['icon' => 'check-circle-2', 'title' => 'Stok Melimpah Selalu Tersedia', 'desc' => 'Kapasitas gudang besar memastikan toko Anda tidak pernah kehabisan barang.'],
                ],
                'services' => [
                    ['title' => 'Minyak Goreng Kemasan 1 Dus (Isi 6 x 2L)', 'desc' => 'Minyak goreng bermerek sertifikat SNI harga grosir kartonan.', 'price' => 'Harga Grosir Kompetitif', 'badge' => 'Karton'],
                    ['title' => 'Mi Instan Aneka Rasa 1 Dus (Isi 40 Bungkus)', 'desc' => 'Stok selalu baru dengan tanggal kedaluwarsa panjang aman display.', 'price' => 'Harga Grosir Kompetitif', 'badge' => 'Mi'],
                    ['title' => 'Gula Pasir Kristal Karungan 50 Kg', 'desc' => 'Gula tebu putih bersih kering cocok untuk industri makanan dan minuman.', 'price' => 'Harga Grosir Kompetitif', 'badge' => 'Karung'],
                ],
                'faqs' => [
                    ['question' => 'Berapa minimal pemesanan untuk pengantaran ke toko?', 'answer' => 'Minimal pemesanan pengantaran gratis adalah 5 karton gabungan untuk rute area reguler.'],
                ],
                'testimonials' => [
                    ['name' => 'Pak Joko Santoso', 'role' => 'Pemilik Toko Kelontong', 'quote' => 'Barang selalu ada dan pengiriman cepat. Gak perlu repot tutup toko buat belanja ke pasar induk.', 'rating' => 5],
                ],
            ],

            // ─── 25. PERTANIAN & AGRIBISNIS ───────────────────────────────────
            'agri_farming' => [
                'theme_color'        => '#15803D',
                'headline'           => "Pusat Bibit Tanaman Unggul, Pupuk & Perlengkapan Tani di {$biz}",
                'subheadline'        => "Benih bersertifikat tahan hama, pupuk organik, nutrisi hidroponik AB Mix, media tanam gembur, pakan ternak berprotein, dan obat pengendali hama tanaman.",
                'announcement_badge' => "Benih Unggul Bersertifikasi Resmi - Daya Tumbuh Tinggi",
                'cta_primary_text'   => "Belanja Kebutuhan Tani Online",
                'cta_secondary_text' => "Katalog Benih & Pupuk",
                'about_title'        => "Mendukung Kesuburan Bumi dan Kemakmuran Petani",
                'about_story'        => "{$biz} hadir mendampingi para petani konvensional maupun penggiat urban farming hidroponik. Kami menyediakan saprotan berkualitas untuk hasil panen yang melimpah.",
                'values' => [
                    ['icon' => 'sprout', 'title' => 'Benih Daya Kecambah >85%', 'desc' => 'Kemasan kedap udara menjaga kualitas benih tetap segar dan vigor tinggi.'],
                    ['icon' => 'leaf', 'title' => 'Pupuk Organik & Ramah Hayati', 'desc' => 'Menyuburkan struktur tanah tanpa merusak ekosistem mikroba alami.'],
                    ['icon' => 'help-circle', 'title' => 'Konsultasi Perawatan Tanaman', 'desc' => 'Bantu diagnosa penyakit daun dan takaran pemupukan yang tepat.'],
                ],
                'services' => [
                    ['title' => 'Benih Cabai Rawit & Keriting Unggul (Isi 1.750 Butir)', 'desc' => 'Tahan layu bakteri dan virus kuning dengan potensi panen tinggi.', 'price' => 'Rp 65.000 / bks', 'badge' => 'Benih'],
                    ['title' => 'Nutrisi Hidroponik AB Mix Sayur Daun (1 Liter Pekatan)', 'desc' => 'Formula hara lengkap untuk selada, pakcoy, kangkung dan bayam hijau subur.', 'price' => 'Rp 35.000 / set', 'badge' => 'Hidroponik'],
                    ['title' => 'Pupuk Kandang Fermentasi Halus 10 Kg', 'desc' => 'Bebas bau menyengat dan sudah matang, siap pakai untuk campuran pot.', 'price' => 'Rp 18.000 / sak', 'badge' => 'Pupuk'],
                ],
                'faqs' => [
                    ['question' => 'Apakah melayani pengiriman benih ke luar pulau?', 'answer' => 'Ya, kami melayani pengiriman benih dan nutrisi ke seluruh wilayah Indonesia.'],
                ],
                'testimonials' => [
                    ['name' => 'Wahyudi', 'role' => 'Petani Sayur Hidroponik', 'quote' => 'Nutrisi AB Mix-nya bagus banget, daun selada tebal renyah dan cepat panen. Langganan terus di sini.', 'rating' => 5],
                ],
            ],
        ];
    }
}
