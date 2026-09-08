<?php

declare(strict_types=1);

namespace App\Domain\LandingPage;

class IndustryPresets
{
    /**
     * Get list of all 20 supported industries with key and label.
     */
    public static function all(): array
    {
        return [
            'bengkel'     => ['label' => 'Bengkel & Servis Otomotif', 'icon' => 'wrench', 'color' => '#0284C7'],
            'klinik'      => ['label' => 'Klinik Kesehatan & Dokter', 'icon' => 'activity', 'color' => '#0D9488'],
            'resto'       => ['label' => 'Restoran, Kafe & Kuliner (F&B)', 'icon' => 'utensils', 'color' => '#E11D48'],
            'salon'       => ['label' => 'Salon Kecantikan & Barbershop', 'icon' => 'scissors', 'color' => '#9333EA'],
            'retail'      => ['label' => 'Toko Retail & Minimarket', 'icon' => 'shopping-bag', 'color' => '#16A34A'],
            'fashion'     => ['label' => 'Butik Fashion & Konveksi', 'icon' => 'shirt', 'color' => '#DB2777'],
            'laundry'     => ['label' => 'Laundry Kiloan & Dry Clean', 'icon' => 'sparkles', 'color' => '#0891B2'],
            'notaris'     => ['label' => 'Kantor Notaris & PPAT / Hukum', 'icon' => 'scale', 'color' => '#4338CA'],
            'bangunan'    => ['label' => 'Bahan Bangunan & Material', 'icon' => 'hard-hat', 'color' => '#D97706'],
            'percetakan'  => ['label' => 'Percetakan Digital & Sablon', 'icon' => 'printer', 'color' => '#EA580C'],
            'petshop'     => ['label' => 'Pet Shop, Vet & Grooming', 'icon' => 'heart', 'color' => '#F59E0B'],
            'elektronik'  => ['label' => 'Servis Gadget & Toko Elektronik', 'icon' => 'cpu', 'color' => '#4F46E5'],
            'bakery'      => ['label' => 'Toko Roti, Bakery & Pastry', 'icon' => 'cake', 'color' => '#CA8A04'],
            'carwash'     => ['label' => 'Cuci Mobil & Auto Detailing', 'icon' => 'car', 'color' => '#2563EB'],
            'studio_foto' => ['label' => 'Studio Foto & Dokumentasi', 'icon' => 'camera', 'color' => '#7C3AED'],
            'gym'         => ['label' => 'Gym, Fitness & Studio Olahraga', 'icon' => 'dumbbell', 'color' => '#DC2626'],
            'bimbel'      => ['label' => 'Bimbel, Kursus & Les Privat', 'icon' => 'graduation-cap', 'color' => '#2563EB'],
            'ekspedisi'   => ['label' => 'Agen Ekspedisi & Jasa Kurir', 'icon' => 'truck', 'color' => '#F97316'],
            'florist'     => ['label' => 'Florist & Toko Bunga Dekorasi', 'icon' => 'flower-2', 'color' => '#EC4899'],
            'pertanian'   => ['label' => 'Toko Pertanian & Hidroponik', 'icon' => 'sprout', 'color' => '#15803D'],
        ];
    }

    /**
     * Get complete default content preset for a specific industry.
     */
    public static function get(string $key, string $businessName = 'Usaha Kami'): array
    {
        $presets = self::definitions($businessName);

        return $presets[$key] ?? $presets['retail'];
    }

    /**
     * Get all industries formatted for the CMS preset modal.
     * Returns array of objects with id, name, icon (emoji), description, tags, theme_color.
     */
    public static function forModal(): array
    {
        $map = [
            'bengkel'     => ['name' => 'Bengkel & Otomotif',          'icon' => '🔧', 'description' => 'Servis kendaraan, tune-up, ganti oli, spare part, cuci motor/mobil.', 'tags' => ['mekanik','otomotif','servis']],
            'klinik'      => ['name' => 'Klinik Kesehatan',            'icon' => '🏥', 'description' => 'Praktik dokter, klinik gigi, bidan, terapi, dan konsultasi medis.', 'tags' => ['dokter','kesehatan','medis']],
            'resto'        => ['name' => 'Restoran & F&B',             'icon' => '🍽️', 'description' => 'Warung makan, kafe, restoran, katering, dan semua bisnis kuliner.', 'tags' => ['kuliner','makanan','kafe']],
            'salon'       => ['name' => 'Salon & Barbershop',          'icon' => '✂️', 'description' => 'Salon rambut, barbershop, nail art, spa, kecantikan.', 'tags' => ['salon','kecantikan','barbershop']],
            'retail'      => ['name' => 'Toko Retail & Minimarket',    'icon' => '🛒', 'description' => 'Toko kelontong, minimarket, waralaba, dan toko serba ada.', 'tags' => ['toko','retail','minimarket']],
            'fashion'     => ['name' => 'Butik Fashion & Konveksi',    'icon' => '👗', 'description' => 'Pakaian, busana muslim, konveksi, kaos polos, dan aksesori mode.', 'tags' => ['fashion','pakaian','butik']],
            'laundry'     => ['name' => 'Laundry & Dry Clean',         'icon' => '👕', 'description' => 'Laundry kiloan, cuci setrika, dry cleaning, dan laundry ekspres.', 'tags' => ['laundry','cuci','pakaian']],
            'notaris'     => ['name' => 'Notaris, PPAT & Hukum',       'icon' => '⚖️', 'description' => 'Kantor notaris, PPAT, pengacara, legalitas dokumen bisnis.', 'tags' => ['notaris','hukum','legal']],
            'bangunan'    => ['name' => 'Bahan Bangunan & Material',   'icon' => '🏗️', 'description' => 'Toko besi, toko bangunan, material, pasir, batu, semen.', 'tags' => ['bangunan','material','konstruksi']],
            'percetakan'  => ['name' => 'Percetakan & Sablon',         'icon' => '🖨️', 'description' => 'Cetak banner, sablon, digital printing, undangan, merchandise.', 'tags' => ['cetak','sablon','printing']],
            'petshop'     => ['name' => 'Pet Shop & Grooming',         'icon' => '🐾', 'description' => 'Toko hewan, grooming, klinik veteriner, pakan dan aksesoris pet.', 'tags' => ['petshop','hewan','grooming']],
            'elektronik'  => ['name' => 'Servis & Toko Elektronik',    'icon' => '📱', 'description' => 'Servis HP, laptop, elektronik rumah tangga, toko gadget dan aksesoris.', 'tags' => ['elektronik','gadget','servis']],
            'bakery'      => ['name' => 'Bakery, Roti & Pastry',       'icon' => '🍞', 'description' => 'Toko roti, kue, pastry, bakery artisan, dan katering kue.', 'tags' => ['bakery','roti','kue']],
            'carwash'     => ['name' => 'Cuci Mobil & Detailing',      'icon' => '🚗', 'description' => 'Cuci mobil, motor, auto detailing, poles body, coating.', 'tags' => ['carwash','detailing','mobil']],
            'studio_foto' => ['name' => 'Studio Foto & Videografi',    'icon' => '📸', 'description' => 'Studio foto, foto produk, videografi pernikahan, dokumentasi event.', 'tags' => ['foto','videografi','studio']],
            'gym'         => ['name' => 'Gym & Fitness Center',        'icon' => '💪', 'description' => 'Gym, fitness center, studio aerobik, yoga, crossfit, personal trainer.', 'tags' => ['gym','fitness','olahraga']],
            'bimbel'      => ['name' => 'Bimbel & Kursus Les',         'icon' => '📚', 'description' => 'Bimbingan belajar, kursus musik, les privat, coding, dan skill training.', 'tags' => ['bimbel','kursus','pendidikan']],
            'ekspedisi'   => ['name' => 'Ekspedisi & Jasa Kurir',      'icon' => '🚚', 'description' => 'Jasa kirim barang, ekspedisi lokal, agen logistik, packing & cargo.', 'tags' => ['ekspedisi','kurir','logistik']],
            'florist'     => ['name' => 'Florist & Toko Bunga',        'icon' => '💐', 'description' => 'Rangkaian bunga, dekorasi wedding, papan bunga, dan taman.', 'tags' => ['florist','bunga','dekorasi']],
            'pertanian'   => ['name' => 'Pertanian & Hidroponik',      'icon' => '🌱', 'description' => 'Toko tani, bibit, pupuk, hidroponik, agribisnis, dan kebun urban.', 'tags' => ['pertanian','hidroponik','tani']],
        ];

        $catalog = self::all();
        $result  = [];

        foreach ($map as $id => $meta) {
            $result[] = [
                'id'          => $id,
                'name'        => $meta['name'],
                'icon'        => $meta['icon'],
                'description' => $meta['description'],
                'tags'        => $meta['tags'],
                'theme_color' => $catalog[$id]['color'] ?? '#10B981',
            ];
        }

        return $result;
    }

    /**
     * Raw definitions for 20 industries with authentic Indonesian copy.
     */
    protected static function definitions(string $biz): array
    {
        return [
            // 1. BENGKEL OTOMOTIF
            'bengkel' => [
                'theme_color'        => '#0284C7',
                'headline'           => "Servis Kendaraan Terpercaya & Bergaransi di {$biz}",
                'subheadline'        => "Perawatan berkala, tune up, ganti oli, dan perbaikan mesin dengan teknisi berpengalaman serta suku cadang 100% original.",
                'announcement_badge' => "⭐ Garansi Servis 14 Hari & Cek Kendaraan Gratis",
                'cta_primary_text'   => "Booking Servis via WhatsApp",
                'cta_secondary_text' => "Daftar Harga & Paket Servis",
                'about_title'        => "Dedikasi Menjaga Performa Kendaraan Anda",
                'about_story'        => "{$biz} didirikan dengan komitmen memberikan rasa aman bagi setiap pengendara. Kami mengedepankan transparansi estimasi biaya tanpa biaya tersembunyi, pengerjaan cepat dengan peralatan modern, dan garansi pengerjaan.",
                'values' => [
                    ['icon' => 'award', 'title' => 'Mekanik Tersertifikasi', 'desc' => 'Dikerjakan oleh teknisi ahli berpengalaman belasan tahun.'],
                    ['icon' => 'shield-check', 'title' => 'Sparepart 100% Asli', 'desc' => 'Jaminan suku cadang original dengan masa garansi pabrik.'],
                    ['icon' => 'clock', 'title' => 'Pengerjaan Cepat', 'desc' => 'Servis terjadwal dengan estimasi waktu yang tepat.'],
                    ['icon' => 'check-circle', 'title' => 'Transparan Tanpa Nego', 'desc' => 'Pengecekan dan estimasi biaya disetujui sebelum pengerjaan.'],
                ],
                'services' => [
                    ['title' => 'Tune Up Injeksi & Karburator', 'desc' => 'Pembersihan ruang bakar, injector, busi, dan kalibrasi mesin.', 'price' => 'Rp 85.000', 'badge' => 'Populer'],
                    ['title' => 'Paket Ganti Oli + Servis Ringan', 'desc' => 'Termasuk cek rem, kelistrikan, angin ban, dan rantai/CVT.', 'price' => 'Rp 110.000', 'badge' => 'Hemat'],
                    ['title' => 'Servis CVT Matic / Rem Lengkap', 'desc' => 'Pembersihan mangkok ganda, roller, v-belt, dan ganti minyak rem.', 'price' => 'Rp 65.000', 'badge' => null],
                    ['title' => 'Overhaul / Turun Mesin', 'desc' => 'Perbaikan total kebocoran oli, suara kasar, dan performa hilang.', 'price' => 'Rp 450.000', 'badge' => 'Bergaransi'],
                ],
                'faqs' => [
                    ['question' => 'Apakah harus booking terlebih dahulu?', 'answer' => 'Anda bisa datang langsung (walk-in), namun kami menyarankan booking via WhatsApp untuk menghindari antrean.'],
                    ['question' => 'Apakah ada garansi setelah servis?', 'answer' => 'Ya, kami memberikan garansi servis selama 14 hari kerja untuk memastikan kepuasan Anda.'],
                    ['question' => 'Bisakah membawa sparepart sendiri?', 'answer' => 'Bisa, Anda hanya akan dikenakan biaya jasa pemasangan teknisi kami.'],
                ],
                'testimonials' => [
                    ['name' => 'Bambang Sudiro', 'role' => 'Pengguna Motor Harian', 'quote' => 'Mekanik ramah, penjelasannya detail, dan motor jadi enteng banget. Sangat recommended!', 'rating' => 5],
                    ['name' => 'Rina Wijaya', 'role' => 'Karyawan Swasta', 'quote' => 'Bisa booking lewat WhatsApp jadi tidak perlu antre berjam-jam saat jam pulang kantor.', 'rating' => 5],
                ],
            ],

            // 2. KLINIK & DOKTER
            'klinik' => [
                'theme_color'        => '#0D9488',
                'headline'           => "Layanan Kesehatan Profesional & Nyaman untuk Keluarga Anda",
                'subheadline'        => "Klinik pratama modern dengan dokter umum, dokter gigi, laboratorium, dan apotek lengkap siap melayani kesehatan Anda.",
                'announcement_badge' => "🏥 Buka Setiap Hari • Menerima Pasien Umum & Rujukan",
                'cta_primary_text'   => "Konsultasi & Reservasi Dokter",
                'cta_secondary_text' => "Jadwal Praktik & Layanan",
                'about_title'        => "Kesehatan Anda Adalah Prioritas Utama Kami",
                'about_story'        => "{$biz} hadir memberikan layanan medis yang ramah, higienis, dan terjangkau. Didukung oleh tim dokter berpengalaman serta rekam medis digital yang rapi dan aman.",
                'values' => [
                    ['icon' => 'heart-pulse', 'title' => 'Dokter Berpengalaman', 'desc' => 'Tim medis profesional dengan izin praktik resmi (SIP).'],
                    ['icon' => 'sparkles', 'title' => 'Fasilitas Bersih & Higienis', 'desc' => 'Standar sterilisasi tinggi untuk kenyamanan dan keselamatan pasien.'],
                    ['icon' => 'calendar', 'title' => 'Antrean Ramah Digital', 'desc' => 'Daftar nomor antrean langsung dari rumah via WhatsApp.'],
                    ['icon' => 'pill', 'title' => 'Farmasi Lengkap', 'desc' => 'Obat-obatan resmi berstandar BPOM langsung dari distributor resmi.'],
                ],
                'services' => [
                    ['title' => 'Konsultasi Dokter Umum', 'desc' => 'Pemeriksaan fisik, diagnosis, resep obat, dan surat keterangan sehat.', 'price' => 'Rp 50.000', 'badge' => 'Umum'],
                    ['title' => 'Pemeriksaan Gigi & Scaling', 'desc' => 'Pembersihan karang gigi, tambal estetik, dan pencabutan gigi tanpa sakit.', 'price' => 'Rp 150.000', 'badge' => 'Gigi'],
                    ['title' => 'Cek Laboratorium Darah / Gula / Kolesterol', 'desc' => 'Hasil cepat dalam 15 menit dengan akurasi tinggi.', 'price' => 'Rp 75.000', 'badge' => 'Cepat'],
                    ['title' => 'Suntik Vitamin C & Imunitas', 'desc' => 'Tingkatkan daya tahan tubuh dan stamina di masa cuaca pancaroba.', 'price' => 'Rp 120.000', 'badge' => 'Populer'],
                ],
                'faqs' => [
                    ['question' => 'Kapan jadwal praktik dokter umum?', 'answer' => 'Praktik buka setiap hari Senin - Sabtu pukul 08.00 - 21.00 WIB dan Minggu pukul 09.00 - 15.00 WIB.'],
                    ['question' => 'Apakah melayani pemeriksaan di rumah (home care)?', 'answer' => 'Ya, kami menyediakan layanan kunjungan dokter & perawat ke rumah untuk kondisi tertentu.'],
                ],
                'testimonials' => [
                    ['name' => 'drg. Hendra Santoso', 'role' => 'Pasien Rutin', 'quote' => 'Dokternya ramah banget menjelaskan kondisi penyakit, tempatnya bersih dan pelayanannya cepat.', 'rating' => 5],
                    ['name' => 'Siti Nurhaliza', 'role' => 'Ibu Rumah Tangga', 'quote' => 'Pelayanan klinik sangat bagus, anak saya tidak takut diperiksa karena dokternya sangat sabar.', 'rating' => 5],
                ],
            ],

            // 3. RESTORAN & KULINER (F&B)
            'resto' => [
                'theme_color'        => '#E11D48',
                'headline'           => "Cita Rasa Kuliner Autentik yang Menggugah Selera di {$biz}",
                'subheadline'        => "Bahan segar pilihan, resep rahasia warisan, dan suasana santai cocok untuk kumpul keluarga, rekan kerja, dan komunitas.",
                'announcement_badge' => "🔥 Promo Makan Hemat & Free WiFi Area Nyaman",
                'cta_primary_text'   => "Pesan / Reservasi Meja via WA",
                'cta_secondary_text' => "Buku Menu & Daftar Harga",
                'about_title'        => "Kelezatan Dari Dapur yang Penuh Cinta",
                'about_story'        => "Berawal dari kegemaran menyajikan hidangan lezat dan berkualitas, {$biz} hadir untuk menyatukan kehangatan keluarga dan teman lewat santapan terbaik.",
                'values' => [
                    ['icon' => 'utensils', 'title' => '100% Halal & Higienis', 'desc' => 'Bahan baku terverifikasi halal dan diolah dengan standar kebersihan ketat.'],
                    ['icon' => 'flame', 'title' => 'Selalu Disajikan Hangat', 'desc' => 'Dimasak fresh saat dipesan (made to order) untuk rasa optimal.'],
                    ['icon' => 'smile', 'title' => 'Pelayanan Ramah & Cepat', 'desc' => 'Staf ramah siap melayani pesanan Anda dengan senyuman.'],
                    ['icon' => 'map-pin', 'title' => 'Parkir Luas & Musholla', 'desc' => 'Kenyamanan fasilitas lengkap untuk kunjungan santai.'],
                ],
                'services' => [
                    ['title' => 'Paket Nasi Ayam Bakar Madu Spesial', 'desc' => 'Lengkap dengan nasi putih, tahu, tempe, lalapan segar & sambal bajak.', 'price' => 'Rp 28.000', 'badge' => 'Best Seller'],
                    ['title' => 'Iga Bakar Saus Karamel Rempah', 'desc' => 'Daging iga empuk lembut dengan bumbu rempah pilihan meresap sempurna.', 'price' => 'Rp 48.000', 'badge' => 'Chef Choice'],
                    ['title' => 'Es Kopi Susu Gula Aren Barista', 'desc' => 'Espresso blend pilihan dengan susu segar creamy dan gula aren organik.', 'price' => 'Rp 18.000', 'badge' => 'Favorit'],
                    ['title' => 'Paket Gathering / Prasmanan (Min 20 Pax)', 'desc' => 'Pilihan menu lengkap untuk acara arisan, rapat kantor, dan ulang tahun.', 'price' => 'Rp 35.000 / pax', 'badge' => 'Acara'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa booking tempat untuk rombongan?', 'answer' => 'Bisa sekali! Kami memiliki area lantai 2 ber-AC yang muat hingga 60 orang.'],
                    ['question' => 'Apakah bisa pesan antar (delivery)?', 'answer' => 'Bisa, silakan hubungi WhatsApp kami untuk pemesanan langsung tanpa biaya komisi aplikasi.'],
                ],
                'testimonials' => [
                    ['name' => 'Dimas Anggara', 'role' => 'Pecinta Kuliner', 'quote' => 'Bumbu iga bakarnya meresap sampai ke tulang! Sambalnya juara pedas gurihnya.', 'rating' => 5],
                    ['name' => 'Maya Kartika', 'role' => 'Food Vlogger Lokal', 'quote' => 'Tempatnya cozy banget buat nongkrong sore, kopi susunya pas tidak kemanisan.', 'rating' => 5],
                ],
            ],

            // 4. SALON & BARBERSHOP
            'salon' => [
                'theme_color'        => '#9333EA',
                'headline'           => "Sentuhan Gaya & Perawatan Terbaik untuk Penampilan Percaya Diri",
                'subheadline'        => "Potong rambut tren terkini, hair styling, creambath, pewarnaan, facial, hingga nail art ditangani oleh stylist profesional.",
                'announcement_badge' => "✂️ Diskon 15% untuk Kunjungan Pertama",
                'cta_primary_text'   => "Booking Slot Stylist via WA",
                'cta_secondary_text' => "Pilihan Treatment & Harga",
                'about_title'        => "Tampil Maksimal Bersama {$biz}",
                'about_story'        => "Kami percaya setiap orang berhak tampil percaya diri dan menawan. Dengan produk perawatan rambut premium dan teknik modern, kami siap mewujudkan gaya impian Anda.",
                'values' => [
                    ['icon' => 'scissors', 'title' => 'Stylist Bersertifikat', 'desc' => 'Menguasai tren haircut terkini dari classic hingga modern.'],
                    ['icon' => 'sparkles', 'title' => 'Produk Premium Aman', 'desc' => 'Hanya menggunakan brand perawatan rambut resmi tanpa zat berbahaya.'],
                    ['icon' => 'coffee', 'title' => 'Suasana Ruangan Nyaman', 'desc' => 'Dilengkapi AC, musik santai, dan free soft drink untuk relaksasi Anda.'],
                    ['icon' => 'clock', 'title' => 'Tepat Waktu Tanpa Antre', 'desc' => 'Jadwal booking terjadwal ketat untuk menghargai waktu Anda.'],
                ],
                'services' => [
                    ['title' => 'Gentleman Haircut + Wash & Styling', 'desc' => 'Potong rambut presisi, cuci rambut, hot towel, dan styling pomade.', 'price' => 'Rp 50.000', 'badge' => 'Barber'],
                    ['title' => 'Ladies Haircut + Blow Dry', 'desc' => 'Konsultasi bentuk wajah, cuci rambut premium, dan blow styling.', 'price' => 'Rp 75.000', 'badge' => 'Salon'],
                    ['title' => 'Creambath Tradisional Relaksasi', 'desc' => 'Perawatan akar rambut dengan pijatan leher dan bahu 60 menit.', 'price' => 'Rp 85.000', 'badge' => 'Favorit'],
                    ['title' => 'Hair Coloring / Balayage / Highlight', 'desc' => 'Pewarnaan rambut tren modern dengan pelindung batang rambut.', 'price' => 'Rp 250.000', 'badge' => 'Tren'],
                ],
                'faqs' => [
                    ['question' => 'Apakah harus booking jadwal terlebih dahulu?', 'answer' => 'Sangat disarankan booking via WhatsApp minimal 2 jam sebelumnya agar tidak menunggu.'],
                    ['question' => 'Apakah melayani anak-anak?', 'answer' => 'Ya, kami memiliki kursi dan stylist yang ramah dan sabar untuk potongan rambut anak.'],
                ],
                'testimonials' => [
                    ['name' => 'Ferry Gunawan', 'role' => 'Pelanggan Setia Barbershop', 'quote' => 'Fade cut-nya sangat rapi dan presisi. Tempat potong rambut terbaik di kota ini.', 'rating' => 5],
                    ['name' => 'Jessica Amelia', 'role' => 'Beauty Enthusiast', 'quote' => 'Warna balayage-nya keluar sempurna dan rambut tetap lembut tidak rusak. Puas banget!', 'rating' => 5],
                ],
            ],

            // 5. TOKO RETAIL & MINIMARKET
            'retail' => [
                'theme_color'        => '#16A34A',
                'headline'           => "Belanja Kebutuhan Harian Lengkap, Hemat & Dekat di {$biz}",
                'subheadline'        => "Sembako murah, kebutuhan rumah tangga, makanan ringan, produk segar, dan pembayaran tagihan lengkap setiap hari.",
                'announcement_badge' => "🛒 Harga Grosir & Eceran • Promo Setiap Akhir Pekan",
                'cta_primary_text'   => "Pesan via WhatsApp (Bisa Diantar)",
                'cta_secondary_text' => "Katalog Promo Minggu Ini",
                'about_title'        => "Sahabat Kebutuhan Keluarga Anda",
                'about_story'        => "{$biz} hadir memberikan kemudahan belanja kebutuhan pokok dengan harga bersahabat, stok selalu baru dan segar, serta layanan ramah tetangga.",
                'values' => [
                    ['icon' => 'tag', 'title' => 'Harga Pasti Hemat', 'desc' => 'Harga bersaing langsung dari distributor terpercaya.'],
                    ['icon' => 'check-circle-2', 'title' => 'Produk Selalu Fresh', 'desc' => 'Stok produk diperbarui secara berkala dengan tanggal kedaluwarsa aman.'],
                    ['icon' => 'truck', 'title' => 'Layanan Antar ke Rumah', 'desc' => 'Belanja via WhatsApp, pesanan diantar langsung sampai depan pintu.'],
                    ['icon' => 'credit-card', 'title' => 'Bayar Tunai & QRIS', 'desc' => 'Kemudahan transaksi non-tunai bebas repot uang kembalian.'],
                ],
                'services' => [
                    ['title' => 'Beras Premium 5kg Pilihan', 'desc' => 'Beras pulen bersih bebas pemutih dan pengawet kimia.', 'price' => 'Rp 72.000', 'badge' => 'Pokok'],
                    ['title' => 'Minyak Goreng Refill 2 Liter', 'desc' => 'Minyak kelapa sawit jernih dua kali penyaringan.', 'price' => 'Rp 34.000', 'badge' => 'Hemat'],
                    ['title' => 'Telur Ayam Ras Segar 1 Kg', 'desc' => 'Telur pilihan peternakan lokal selalu fresh setiap hari.', 'price' => 'Rp 27.000', 'badge' => 'Segar'],
                    ['title' => 'Paket Sembako Berkah Bulanan', 'desc' => 'Isi beras, minyak, gula, kopi, dan mi instan praktis.', 'price' => 'Rp 165.000', 'badge' => 'Paket'],
                ],
                'faqs' => [
                    ['question' => 'Apakah ada minimal belanja untuk layanan antar?', 'answer' => 'Gratis ongkir untuk radius 2 km dengan minimal belanja Rp 50.000.'],
                    ['question' => 'Jam berapa toko buka?', 'answer' => 'Kami buka setiap hari mulai pukul 07.00 sampai 22.00 WIB.'],
                ],
                'testimonials' => [
                    ['name' => 'Ibu Ratna', 'role' => 'Warga Sekitar', 'quote' => 'Belanja tinggal WA langsung dianterin mas-masnya. Sangat menolong ibu-ibu yang repot di rumah.', 'rating' => 5],
                    ['name' => 'Pak Joko', 'role' => 'Pelanggan Rutin', 'quote' => 'Harganya jujur dan murah, pelayanannya ramah banget.', 'rating' => 5],
                ],
            ],

            // 6. BUTIK FASHION & KONVEKSI
            'fashion' => [
                'theme_color'        => '#DB2777',
                'headline'           => "Koleksi Busana Modis & Jasa Jahit Konveksi Berkualitas",
                'subheadline'        => "Pakaian siap pakai kekinian, baju pesta, seragam kantor, kaos komunitas, hingga custom busana dengan bahan adem dan jahitan presisi.",
                'announcement_badge' => "✨ Koleksi Terbaru Edisi 2026 Ready Stock",
                'cta_primary_text'   => "Konsultasi / Order via WhatsApp",
                'cta_secondary_text' => "Katalog Busana & Portofolio",
                'about_title'        => "Karya Jahit dan Gaya dari Hati",
                'about_story'        => "{$biz} menggabungkan seni desain busana dengan standar jahitan butik berkualitas tinggi. Baik untuk pakaian harian maupun seragam berskala besar, kami jamin kerapian dan kenyamanannya.",
                'values' => [
                    ['icon' => 'sparkles', 'title' => 'Bahan Adem & Nyaman', 'desc' => 'Menggunakan material kain pilihan premium grade A.'],
                    ['icon' => 'scissors', 'title' => 'Jahitan Rapi Berstandar Butik', 'desc' => 'Dikerjakan oleh penjahit profesional berpengalaman puluhan tahun.'],
                    ['icon' => 'layers', 'title' => 'Menerima Partai Besar & Satuan', 'desc' => 'Siap melayani pesanan seragam kantor, arisan, maupun custom satuan.'],
                    ['icon' => 'check-circle', 'title' => 'Fitting Sempurna', 'desc' => 'Garansi revisi ukuran jika pakaian belum pas di badan.'],
                ],
                'services' => [
                    ['title' => 'Dress & Gamis Busana Muslimah', 'desc' => 'Model elegan dengan bahan katun toyobo/ceruty jatuh yang adem.', 'price' => 'Rp 145.000', 'badge' => 'Best Seller'],
                    ['title' => 'Kemeja Batik Pria & Wanita Formal', 'desc' => 'Motif kontemporer dengan furing halus yang menyerap keringat.', 'price' => 'Rp 125.000', 'badge' => 'Formal'],
                    ['title' => 'Konveksi Kaos Komunitas / Sablon (Min 12 Pcs)', 'desc' => 'Bahan Cotton Combed 24s/30s adem dengan sablon plastisol awet.', 'price' => 'Rp 60.000 / pcs', 'badge' => 'Grosir'],
                    ['title' => 'Jasa Jahit Baju Pesta & Kebaya Custom', 'desc' => 'Desain sesuai permintaan pelanggan dengan fitting detail.', 'price' => 'Mulai Rp 200.000', 'badge' => 'Custom'],
                ],
                'faqs' => [
                    ['question' => 'Berapa lama proses pengerjaan konveksi seragam?', 'answer' => 'Untuk pesanan konveksi standar 1-50 pcs membutuhkan waktu 7-10 hari kerja.'],
                    ['question' => 'Apakah bisa kirim ke luar kota?', 'answer' => 'Bisa, kami melayani pengiriman ke seluruh wilayah Indonesia via ekspedisi terpercaya.'],
                ],
                'testimonials' => [
                    ['name' => 'Dewi Anggraini', 'role' => 'Pelanggan Butik', 'quote' => 'Jahitannya rapi banget, bahannya jatuh dan adem dipakai seharian. Bakal langganan terus!', 'rating' => 5],
                    ['name' => 'Rizky Pratama', 'role' => 'Ketua Komunitas Motor', 'quote' => 'Bikin seragam kaos 40 biji hasilnya sangat memuaskan, sablonnya awet dicuci berkali-kali.', 'rating' => 5],
                ],
            ],

            // 7. LAUNDRY & DRY CLEAN
            'laundry' => [
                'theme_color'        => '#0891B2',
                'headline'           => "Cucian Bersih Higienis, Rapi & Wangi Tahan Lama di {$biz}",
                'subheadline'        => "Layanan laundry kiloan, satuan, dry cleaning, cuci bed cover, sepatu, dan gorden dengan mesin modern serta parfum eksklusif.",
                'announcement_badge' => "🧺 Layanan Antar Jemput Gratis (Radius 3 KM)",
                'cta_primary_text'   => "Pesan Antar Jemput via WA",
                'cta_secondary_text' => "Daftar Paket Laundry & Harga",
                'about_title'        => "Perawatan Pakaian Keluarga Anda",
                'about_story'        => "{$biz} berkomitmen merawat setiap helai pakaian Anda dengan standar 1 mesin 1 pelanggan (tanpa dicampur). Kami menggunakan deterjen ramah serat kain dan air tersaring bersih.",
                'values' => [
                    ['icon' => 'shield-check', 'title' => '1 Mesin 1 Pelanggan', 'desc' => 'Pakaian Anda tidak pernah dicampur dengan pakaian pelanggan lain.'],
                    ['icon' => 'sparkles', 'title' => 'Parfum Tahan Lama', 'desc' => 'Pilihan aroma segar premium yang awet menempel di serat kain.'],
                    ['icon' => 'clock', 'title' => 'Tepat Waktu & Kilat', 'desc' => 'Tersedia opsi express selesai dalam hitungan 4-6 jam.'],
                    ['icon' => 'truck', 'title' => 'Free Pickup & Delivery', 'desc' => 'Tinggal kirim share location via WA, kurir kami siap menjemput.'],
                ],
                'services' => [
                    ['title' => 'Laundry Kiloan Cuci Kering Setrika (Reguler 2 Hari)', 'desc' => 'Pakaian dicuci bersih, disetrika uap rapi, dipacking rapi kedap udara.', 'price' => 'Rp 7.000 / kg', 'badge' => 'Favorit'],
                    ['title' => 'Laundry Kiloan Express (Selesai 6 Jam)', 'desc' => 'Solusi darurat untuk pakaian kerja/sekolah yang butuh segera dipakai.', 'price' => 'Rp 14.000 / kg', 'badge' => 'Kilat'],
                    ['title' => 'Cuci Bed Cover Jumbo + Tas Bersih', 'desc' => 'Pembersihan debu tungau dan noda dengan putaran mesin khusus.', 'price' => 'Rp 30.000 / pc', 'badge' => 'Populer'],
                    ['title' => 'Deep Clean Sepatu & Tas Kulit', 'desc' => 'Pembersihan material kanvas, suede, dan kulit dengan cleaner khusus.', 'price' => 'Rp 35.000 / psg', 'badge' => 'Treatment'],
                ],
                'faqs' => [
                    ['question' => 'Bagaimana cara menggunakan layanan antar-jemput?', 'answer' => 'Cukup chat WhatsApp kami, kirim alamat dan jumlah cucian, kurir kami akan segera meluncur.'],
                    ['question' => 'Apakah pakaian putih dipisahkan?', 'answer' => 'Tentu, tim kami melakukan pemilahan warna dan jenis kain sebelum masuk ke proses pencucian.'],
                ],
                'testimonials' => [
                    ['name' => 'Ardiansyah', 'role' => 'Anak Kos Mahasiswa', 'quote' => 'Pakaian disetrika rapi wangi tahan seminggu di lemari. Antar jemputnya juga on time.', 'rating' => 5],
                    ['name' => 'Linda Susanti', 'role' => 'Ibu Rumah Tangga', 'quote' => 'Bed cover tebal jadi wangi dan empuk lagi tanpa ribet nyuci sendiri. Sangat membantu!', 'rating' => 5],
                ],
            ],

            // 8. KANTOR NOTARIS & PPAT
            'notaris' => [
                'theme_color'        => '#4338CA',
                'headline'           => "Layanan Hukum, Akta Notaris & PPAT Terpercaya dan Profesional",
                'subheadline'        => "Pembuatan akta pendirian PT/CV, perjanjian bisnis, balik nama sertifikat tanah, akta jual beli (AJB), dan konsultasi legalitas formal.",
                'announcement_badge' => "⚖️ Pejabat Pembuat Akta Tanah (PPAT) & Notaris Resmi",
                'cta_primary_text'   => "Jadwalkan Konsultasi Legal via WA",
                'cta_secondary_text' => "Daftar Layanan Kenotariatan",
                'about_title'        => "Kepastian Hukum untuk Bisnis dan Aset Anda",
                'about_story'        => "Kantor Notaris & PPAT {$biz} berdedikasi memberikan kepastian hukum yang kokoh, transparan, dan sesuai dengan ketentuan perundang-undangan Republik Indonesia.",
                'values' => [
                    ['icon' => 'scale', 'title' => 'Legalitas Sah & Resmi', 'desc' => 'Terdaftar dan berkekuatan hukum penuh di Kementerian Hukum dan HAM serta BPN.'],
                    ['icon' => 'lock', 'title' => 'Kerahasiaan Dokumen Terjamin', 'desc' => 'Privasi dan keamanan berkas klien dijaga dengan kode etik tinggi.'],
                    ['icon' => 'file-text', 'title' => 'Proses Transparan & Jelas', 'desc' => 'Rincian biaya PNBP, pajak BPHTB, dan jasa dijelaskan sejak awal.'],
                    ['icon' => 'help-circle', 'title' => 'Konsultasi Solutif', 'desc' => 'Membantu menemukan solusi legalitas terbaik bagi bisnis dan keluarga.'],
                ],
                'services' => [
                    ['title' => 'Pendirian PT / CV / Yayasan Lengkap', 'desc' => 'Termasuk Akta Notaris, SK Kemenkumham, NPWP Badan, dan NIB OSS.', 'price' => 'Mulai Rp 3.500.000', 'badge' => 'Bisnis'],
                    ['title' => 'Akta Jual Beli (AJB) Tanah & Bangunan', 'desc' => 'Pengecekan sertifikat BPN, validasi pajak, hingga penandatanganan akta sah.', 'price' => 'Sesuai Nilai Transaksi', 'badge' => 'PPAT'],
                    ['title' => 'Balik Nama & Peningkatan Hak Sertifikat', 'desc' => 'Proses pengurusan sertifikat tanah di kantor BPN hingga selesai.', 'price' => 'Konsultasi Terlebih Dahulu', 'badge' => 'Tanah'],
                    ['title' => 'Legalisasi & Waarmerking Dokumen', 'desc' => 'Pengesahan tanda tangan surat perjanjian, kuasa, dan dokumen resmi.', 'price' => 'Rp 100.000 / berkas', 'badge' => 'Cepat'],
                ],
                'faqs' => [
                    ['question' => 'Dokumen apa saja yang diperlukan untuk membuat PT baru?', 'answer' => 'KTP & NPWP para pendiri, nama PT yang diajukan (3 kata), alamat domisili usaha, dan persentase saham.'],
                    ['question' => 'Berapa lama proses pembuatan akta PT selesai?', 'answer' => 'Rata-rata 3-5 hari kerja setelah nama PT disetujui dan akta ditandatangani.'],
                ],
                'testimonials' => [
                    ['name' => 'Ir. Hendro Wijoyo', 'role' => 'Direktur PT Cahaya Sukses', 'quote' => 'Pengurusan akta PT cepat dan informatif. Semua perizinan NIB selesai tanpa kendala.', 'rating' => 5],
                    ['name' => 'Suryo Utomo', 'role' => 'Klien Transaksi Tanah', 'quote' => 'Pelayanan ramah dan penjelasan pajaknya sangat transparan. Sangat recommended!', 'rating' => 5],
                ],
            ],

            // 9. BAHAN BANGUNAN & MATERIAL
            'bangunan' => [
                'theme_color'        => '#D97706',
                'headline'           => "Pusat Bahan Bangunan & Material Terlengkap, Berkualitas & Cepat Kirim",
                'subheadline'        => "Semen, pasir, besi beton SNI, cat dinding, baja ringan, keramik, pipa, dan perlengkapan tukang untuk proyek renovasi maupun pembangunan baru.",
                'announcement_badge' => "🏗️ Siap Kirim Armada Pick-Up & Truk Sampai Lokasi Proyek",
                'cta_primary_text'   => "Minta Penawaran Harga / Order WA",
                'cta_secondary_text' => "Katalog Material Bangunan",
                'about_title'        => "Mitra Pembangunan Rumah Impian Anda",
                'about_story'        => "{$biz} menyediakan segala kebutuhan material konstruksi dengan stok melimpah, harga bersaing, dan armada pengiriman mandiri yang siap antar tepat waktu.",
                'values' => [
                    ['icon' => 'check-circle-2', 'title' => 'Besi Beton Standar SNI', 'desc' => 'Besi berukuran presisi penuh (full) demi kekokohan struktur bangunan.'],
                    ['icon' => 'truck', 'title' => 'Armada Kirim Siaga', 'desc' => 'Pengiriman cepat langsung ke lokasi proyek Anda di hari yang sama.'],
                    ['icon' => 'tag', 'title' => 'Harga Kontraktor & Retail', 'desc' => 'Tersedia potongan harga khusus untuk pembelian volume proyek.'],
                    ['icon' => 'message-square', 'title' => 'Konsultasi Volume Gratis', 'desc' => 'Bantu hitung kebutuhan material semen, pasir, dan besi dari denah Anda.'],
                ],
                'services' => [
                    ['title' => 'Semen Portland Komposit 40kg & 50kg', 'desc' => 'Semen kuat tekan tinggi dari produsen terkemuka bersertifikat SNI.', 'price' => 'Mulai Rp 54.000 / sak', 'badge' => 'Stok Banyak'],
                    ['title' => 'Besi Beton Polos & Ulir SNI Full', 'desc' => 'Ukuran 6mm, 8mm, 10mm, 12mm, 16mm panjang 12 meter standar.', 'price' => 'Harga Bersaing', 'badge' => 'SNI'],
                    ['title' => 'Pasir Cor & Pasir Pasang 1 Truk', 'desc' => 'Pasir bersih bebas lumpur berlebih cocok untuk cor dan plester.', 'price' => 'Hubungi untuk Rute', 'badge' => 'Curah'],
                    ['title' => 'Baja Ringan Kanal C & Reng Zincalume', 'desc' => 'Rangka atap anti karat tahan rayap dengan ketebalan standar aman.', 'price' => 'Mulai Rp 68.000 / btg', 'badge' => 'Atap'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa bayar di tempat (COD) saat barang sampai?', 'answer' => 'Ya, kami melayani sistem COD untuk area pengiriman tertentu setelah pesanan dikonfirmasi.'],
                    ['question' => 'Berapa minimal order untuk free ongkir?', 'answer' => 'Free ongkir armada pick-up berlaku untuk pembelian material tertentu dalam radius 5 km.'],
                ],
                'testimonials' => [
                    ['name' => 'Pak Marwan', 'role' => 'Mandor Proyek Perumahan', 'quote' => 'Besi betul-betul full SNI, semen selalu ready stok banyak. Pengiriman selalu tepat janji.', 'rating' => 5],
                    ['name' => 'Agus Salim', 'role' => 'Pemilik Rumah Renovasi', 'quote' => 'Sangat terbantu bisa pesan lewat WA, dikasih saran kebutuhan material oleh mas adminnya.', 'rating' => 5],
                ],
            ],

            // 10. PERCETAKAN & SABLON
            'percetakan' => [
                'theme_color'        => '#EA580C',
                'headline'           => "Jasa Cetak Digital, Offset & Sablon Cepat, Tajam & Presisi",
                'subheadline'        => "Cetak banner spanduk, brosur, kartu nama, stiker label kemasan, undangan, kalender, hingga sablon kaos dan merchandise promosi.",
                'announcement_badge' => "🖨️ Mesin Cetak Format Besar Terbaru • Bisa Ditunggu",
                'cta_primary_text'   => "Kirim File & Order via WhatsApp",
                'cta_secondary_text' => "Daftar Produk & Pricelist",
                'about_title'        => "Mewujudkan Desain Anda Dalam Kualitas Nyata",
                'about_story'        => "{$biz} adalah solusi percetakan serba ada yang mengedepankan ketajaman warna, kecepatan cetak, dan ketepatan waktu deadline untuk promosi usaha Anda.",
                'values' => [
                    ['icon' => 'sparkles', 'title' => 'Warna Tajam & Tahan Air', 'desc' => 'Menggunakan tinta outdoor & indoor original Jepang berkualitas tinggi.'],
                    ['icon' => 'clock', 'title' => 'Layanan Kilat Bisa Ditunggu', 'desc' => 'Cetak banner dan stiker meteran selesai dalam hitungan menit.'],
                    ['icon' => 'scissors', 'title' => 'Bisa Cutting Pola Stiker', 'desc' => 'Potong stiker label bulat, oval, atau custom die-cut rapi otomatis.'],
                    ['icon' => 'file-check', 'title' => 'Bantu Cek File Desain', 'desc' => 'Tim kami memeriksa resolusi dan layout file Anda sebelum dicetak.'],
                ],
                'services' => [
                    ['title' => 'Cetak Banner / Spanduk Flexi 280gr - 440gr', 'desc' => 'Cetak spanduk outdoor tajam tahan panas dan hujan, gratis mata ayam.', 'price' => 'Mulai Rp 18.000 / m²', 'badge' => 'Cepat'],
                    ['title' => 'Stiker Vinyl / Bontax + Cutting Kiss Cut', 'desc' => 'Label kemasan makanan, minuman, dan produk botol tahan air dan minyak.', 'price' => 'Rp 15.000 / lbr A3+', 'badge' => 'Favorit UMKM'],
                    ['title' => 'Brosur & Flyer Full Color Art Paper', 'desc' => 'Cetak promosi 1 sisi / 2 sisi ukuran A5 atau A4 tajam mengkilap.', 'price' => 'Mulai Rp 150.000 / rim', 'badge' => 'Promosi'],
                    ['title' => 'Kartu Nama Eksklusif + Laminasi Doff/Glossy', 'desc' => 'Bahan Art Carton 260gr tebal termasuk box mika transparan rapi.', 'price' => 'Rp 35.000 / box (100 pcs)', 'badge' => 'Eksklusif'],
                ],
                'faqs' => [
                    ['question' => 'Format file apa yang disarankan untuk dicetak?', 'answer' => 'Format file PDF, TIFF, JPG, CDR, atau AI dengan color mode CMYK dan resolusi minimal 150-300 dpi.'],
                    ['question' => 'Apakah bisa dibantu buatkan desain jika belum punya?', 'answer' => 'Tentu, tim desainer grafis kami siap membantu pembuatan layout desain promosi Anda.'],
                ],
                'testimonials' => [
                    ['name' => 'Nadia Sabrina', 'role' => 'Owner Bisnis Minuman', 'quote' => 'Stiker label botolnya anti air banget, dimasukkan freezer tidak luntur sama sekali.', 'rating' => 5],
                    ['name' => 'Fauzi Rahman', 'role' => 'Panitia Acara Event', 'quote' => 'Cetak banner 6 meter sore-sore bisa langsung ditunggu dan dipasang malamnya. Luar biasa!', 'rating' => 5],
                ],
            ],

            // 11. PET SHOP & GROOMING
            'petshop' => [
                'theme_color'        => '#F59E0B',
                'headline'           => "Sahabat Terbaik Hewan Kesayangan Anda di {$biz}",
                'subheadline'        => "Pakan premium kucing & anjing, grooming higienis bebas kutu & jamur, pet hotel nyaman, vitamin, dan aksesoris terlengkap.",
                'announcement_badge' => "🐾 Free Antar-Jemput Grooming (S&K Berlaku)",
                'cta_primary_text'   => "Booking Grooming via WhatsApp",
                'cta_secondary_text' => "Katalog Pakan & Perlengkapan",
                'about_title'        => "Merawat Anabul dengan Penuh Kasih Sayang",
                'about_story'        => "{$biz} didirikan oleh para pecinta hewan. Kami memahami bahwa anabul adalah bagian dari keluarga, sehingga kami memperlakukan mereka dengan sabar, lembut, dan higienis.",
                'values' => [
                    ['icon' => 'heart', 'title' => 'Groomer Sabar & Berpengalaman', 'desc' => 'Paham cara menangani anabul yang sensitif dan penakut tanpa stres.'],
                    ['icon' => 'sparkles', 'title' => 'Shampo Medicated Khusus', 'desc' => 'Menggunakan shampo anti kutu, anti jamur, dan pelembut bulu premium.'],
                    ['icon' => 'home', 'title' => 'Pet Hotel Bersih & Ber-AC', 'desc' => 'Kandang terpisah steril dengan update video harian untuk pemilik.'],
                    ['icon' => 'shield-check', 'title' => 'Pakan Original Resmi', 'desc' => 'Produk pakan ternama jaminan keaslian dan masa kedaluwarsa aman.'],
                ],
                'services' => [
                    ['title' => 'Grooming Lengkap Kucing / Anjing', 'desc' => 'Potong kuku, bersihkan telinga, mandi air hangat, blower, dan parfum wangi.', 'price' => 'Mulai Rp 55.000', 'badge' => 'Populer'],
                    ['title' => 'Grooming Treatment Kutu & Jamur', 'desc' => 'Mandi rendam obat khusus membasmi telur kutu dan spora jamur membandel.', 'price' => 'Mulai Rp 75.000', 'badge' => 'Treatment'],
                    ['title' => 'Pet Hotel / Penitipan Anabul (Per Malam)', 'desc' => 'Termasuk makan, ruangan ber-AC, pembersihan kandang, dan jalan santai.', 'price' => 'Rp 45.000 / malam', 'badge' => 'Liburan'],
                    ['title' => 'Pakan Kucing Kering & Basah Premium', 'desc' => 'Tersedia Royal Canin, Pro Plan, Whiskas, Me-O, bolt, dan steril diet.', 'price' => 'Mulai Rp 20.000', 'badge' => 'Nutrisi'],
                ],
                'faqs' => [
                    ['question' => 'Apakah hewan yang dititipkan harus sudah divaksin?', 'answer' => 'Ya, demi keselamatan dan kesehatan bersama, hewan harus sehat dan bebas kutu/jamur parah.'],
                    ['question' => 'Berapa lama durasi proses grooming?', 'answer' => 'Rata-rata 1 hingga 1,5 jam tergantung jenis bulu dan kepatuhan hewan kesayangan.'],
                ],
                'testimonials' => [
                    ['name' => 'Clarissa Putri', 'role' => 'Pecinta Kucing Persia', 'quote' => 'Kucing saya yang galak bisa tenang banget di-grooming di sini. Bulunya jadi wangi dan ngembang!', 'rating' => 5],
                    ['name' => 'Denny Setiawan', 'role' => 'Pemilik Golden Retriever', 'quote' => 'Pet hotelnya terawat banget, tiap hari dikirimin video anjing lagi main. Hati jadi tenang saat dinas luar kota.', 'rating' => 5],
                ],
            ],

            // 12. SERVIS ELEKTRONIK & GADGET
            'elektronik' => [
                'theme_color'        => '#4F46E5',
                'headline'           => "Pusat Servis HP, Laptop & Gadget Cepat, Bergaransi & Transparan",
                'subheadline'        => "Ganti LCD, ganti baterai, perbaikan mati total, kena air, IC power, upgrade SSD/RAM, instalasi software, dan penjualan aksesoris original.",
                'announcement_badge' => "⚡ Ganti Baterai & LCD Bisa Ditunggu 30 Menit",
                'cta_primary_text'   => "Konsultasi Kerusakan via WA",
                'cta_secondary_text' => "Estimasi Biaya & Garansi",
                'about_title'        => "Solusi Tepat untuk Masalah Perangkat Anda",
                'about_story'        => "{$biz} mengedepankan kejujuran diagnosa dan transparansi proses perbaikan. Setiap komponen dicek di depan pelanggan dan bergaransi resmi.",
                'values' => [
                    ['icon' => 'eye', 'title' => 'Pengecekan di Depan Anda', 'desc' => 'Bongkar dan pasang transparan tanpa risiko penukaran sparepart.'],
                    ['icon' => 'shield-check', 'title' => 'Garansi Servis Hingga 90 Hari', 'desc' => 'Klaim garansi mudah tanpa dipersulit jika kendala berulang.'],
                    ['icon' => 'zap', 'title' => 'Sparepart Kualitas Original', 'desc' => 'Layar sentuh responsif, warna tajam, dan baterai berdaya tahan tinggi.'],
                    ['icon' => 'search', 'title' => 'Cek Kerusakan Gratis', 'desc' => 'Gratis biaya pengecekan jika perangkat tidak jadi diperbaiki.'],
                ],
                'services' => [
                    ['title' => 'Ganti LCD Touchscreen iPhone & Android', 'desc' => 'Layar baru jernih, responsif, dan bebas ghost touch.', 'price' => 'Mulai Rp 180.000', 'badge' => 'Bisa Ditunggu'],
                    ['title' => 'Ganti Baterai Original Bergaransi', 'desc' => 'Kembalikan daya tahan baterai agar tidak cepat drop dan panas.', 'price' => 'Mulai Rp 120.000', 'badge' => 'Original'],
                    ['title' => 'Upgrade SSD & Tambah RAM Laptop', 'desc' => 'Bikin laptop lemot jadi ngebut hingga 10x lipat dalam 45 menit.', 'price' => 'Mulai Rp 250.000', 'badge' => 'Performa'],
                    ['title' => 'Servis Mesin Motherboard / Mati Total', 'desc' => 'Perbaikan IC charger, short circuit, dan konslet akibat terkena air.', 'price' => 'Estimasi Dicek', 'badge' => 'Teknisi Ahli'],
                ],
                'faqs' => [
                    ['question' => 'Apakah data di HP/laptop akan hilang saat diservis?', 'answer' => 'Untuk perbaikan hardware seperti LCD dan baterai, data 100% aman dan tidak tersentuh.'],
                    ['question' => 'Berapa lama masa garansi servis?', 'answer' => 'Kami memberikan garansi tertulis mulai dari 30 hari hingga 90 hari tergantung jenis komponen.'],
                ],
                'testimonials' => [
                    ['name' => 'Kevin Sanjaya', 'role' => 'Mahasiswa IT', 'quote' => 'Laptop lemot saya dipasang SSD di sini langsung boot hitungan detik. Cepat dan harganya masuk akal.', 'rating' => 5],
                    ['name' => 'Dian Sastro', 'role' => 'Pengguna iPhone', 'quote' => 'Ganti layar LCD iPhone bisa ditunggu cuma 25 menit. Warnanya sama bagusnya dengan aslinya.', 'rating' => 5],
                ],
            ],

            // 13. TOKO ROTI & BAKERY
            'bakery' => [
                'theme_color'        => '#CA8A04',
                'headline'           => "Kelezatan Roti & Kue Segar Dipanggang Setiap Pagi di {$biz}",
                'subheadline'        => "Roti manis lembut tanpa pengawet, kue tart ulang tahun custom, pastry renyah, kue basah tradisional, dan snack box untuk segala acara.",
                'announcement_badge' => "🍞 Fresh From The Oven Setiap Hari Pukul 07.00 WIB",
                'cta_primary_text'   => "Pesan Kue / Snack Box via WA",
                'cta_secondary_text' => "Katalog Roti & Tart Ulang Tahun",
                'about_title'        => "Kelembutan yang Menemani Setiap Momen Manis",
                'about_story'        => "Di {$biz}, kami hanya menggunakan mentega berkualitas tinggi, ragi alami, dan bahan premium tanpa tambahan bahan pengawet berbahaya. Setiap gigitan menghadirkan kelembutan sejati.",
                'values' => [
                    ['icon' => 'sparkles', 'title' => 'Tanpa Bahan Pengawet', 'desc' => 'Aman dikonsumsi anak-anak dan keluarga setiap hari.'],
                    ['icon' => 'flame', 'title' => 'Fresh Setiap Pagi', 'desc' => 'Roti baru dipanggang setiap hari demi aroma dan tekstur optimal.'],
                    ['icon' => 'cake', 'title' => 'Custom Cake Ulang Tahun', 'desc' => 'Bebas request tema, karakter, dan tulisan sesuai impian Anda.'],
                    ['icon' => 'package', 'title' => 'Siap Terima Partai Besar', 'desc' => 'Kapasitas produksi ratusan kotak snack box rapat dan pengajian.'],
                ],
                'services' => [
                    ['title' => 'Roti Manis Aneka Rasa (Cokelat, Keju, Srikaya)', 'desc' => 'Tekstur super lembut dengan isian melimpah tidak pelit.', 'price' => 'Mulai Rp 7.000 / pc', 'badge' => 'Favorit'],
                    ['title' => 'Kue Tart Ulang Tahun Custom Design', 'desc' => 'Kue sponge lembut berlapis selai dan butter cream premium tidak seret.', 'price' => 'Mulai Rp 125.000', 'badge' => 'Custom'],
                    ['title' => 'Croissant & Danish Pastry Butter Prancis', 'desc' => 'Lapisan luar renyah buttery dengan bagian dalam yang berongga sempurna.', 'price' => 'Rp 18.000 / pc', 'badge' => 'Pastry'],
                    ['title' => 'Paket Snack Box Acara Kantor & Pengajian', 'desc' => 'Isi 2-3 kue pilihan plus air mineral dalam kotak cantik.', 'price' => 'Mulai Rp 12.000 / box', 'badge' => 'Paket Acara'],
                ],
                'faqs' => [
                    ['question' => 'Berapa hari sebelumnya jika ingin pesan kue tart custom?', 'answer' => 'Sebaiknya pesan minimal H-2 sebelum acara agar tim dekorator kami dapat menyiapkan desain terbaik.'],
                    ['question' => 'Berapa lama daya tahan roti tanpa pengawet?', 'answer' => 'Karena tanpa pengawet, roti kami tahan 3-4 hari di suhu ruang atau hingga 1 minggu di dalam kulkas.'],
                ],
                'testimonials' => [
                    ['name' => 'Anita Rahmawati', 'role' => 'Ibu Penyelenggara Ultah Anak', 'quote' => 'Kue tart karakternya persis seperti foto yang saya kirim, dan rasanya enak banget gak bikin eneg!', 'rating' => 5],
                    ['name' => 'Budi Santosa', 'role' => 'Sekretaris Kantor', 'quote' => 'Pesan 120 snack box untuk rapat kantor datang tepat waktu dan kuenya masih hangat. Mantap!', 'rating' => 5],
                ],
            ],

            // 14. CARWASH & DETAILING
            'carwash' => [
                'theme_color'        => '#2563EB',
                'headline'           => "Kilau Bersih Sempurna Mobil & Motor Anda di {$biz}",
                'subheadline'        => "Cuci salju hidrolik, pembersihan kolong anti karat, vacuum interior bersih tuntas, jamur kaca hilang, dan paket salon mobil nano ceramic coating.",
                'announcement_badge' => "🚗 Cuci 5 Kali Gratis 1 Kali Cuci (Kartu Member)",
                'cta_primary_text'   => "Reservasi Cuci / Detailing via WA",
                'cta_secondary_text' => "Daftar Paket Cuci & Poles",
                'about_title'        => "Kebersihan Kendaraan yang Detail Hingga ke Sudut Tersembunyi",
                'about_story'        => "{$biz} bukan sekadar mencuci bodi luar. Kami membersihkan kendaraan Anda dengan teknik dua ember, sampo busa pH netral yang aman untuk cat, dan pembersihan interior bebas debu.",
                'values' => [
                    ['icon' => 'sparkles', 'title' => 'Shampo pH Netral Bersalju', 'desc' => 'Melindungi lapisan clear coat cat mobil dari kusam dan baret swirl mark.'],
                    ['icon' => 'car', 'title' => 'Lift Hidrolik H-Track Aman', 'desc' => 'Pembersihan kolong mobil menyeluruh dari kotoran dan lumpur jalanan.'],
                    ['icon' => 'eye', 'title' => 'Microfiber Khusus Bersih', 'desc' => 'Kain lap dipisahkan khusus bodi, velg, dan kaca agar tidak menimbulkan goresan.'],
                    ['icon' => 'coffee', 'title' => 'Ruang Tunggu Dingin Ber-AC', 'desc' => 'Tersedia kopi, teh, free Wi-Fi, dan sofa nyaman selagi kendaraan Anda dicuci.'],
                ],
                'services' => [
                    ['title' => 'Cuci Mobil Hidrolik + Vacuum Interior', 'desc' => 'Bodi salju, kolong hidrolik, semir ban, dan vacuum karpet serta jok.', 'price' => 'Rp 45.000', 'badge' => 'Reguler'],
                    ['title' => 'Cuci Motor Salju + Semir Ban Detil', 'desc' => 'Pembersihan sela-sela mesin, rantai, velg, dan bodi mengkilap.', 'price' => 'Mulai Rp 15.000', 'badge' => 'Motor'],
                    ['title' => 'Pembersihan Jamur Kaca & Bodi Waterspot', 'desc' => 'Kaca bening bebas buram saat hujan malam hari dan bodi kembali cerah.', 'price' => 'Rp 150.000', 'badge' => 'Perawatan'],
                    ['title' => 'Paket Full Detailing + Paint Protection', 'desc' => 'Poles bodi 3 step, detailing ruang mesin, interior spa, dan proteksi seal wax.', 'price' => 'Mulai Rp 650.000', 'badge' => 'Salon Mobil'],
                ],
                'faqs' => [
                    ['question' => 'Berapa lama proses cuci mobil reguler?', 'answer' => 'Rata-rata 30-40 menit dikerjakan oleh tim 3 orang per kendaraan secara cepat dan teliti.'],
                    ['question' => 'Apakah aman mencuci mobil saat mesin masih panas?', 'answer' => 'Kami selalu mendiamkan kendaraan sejenak agar suhu rem dan mesin turun sebelum disemprot air dingin.'],
                ],
                'testimonials' => [
                    ['name' => 'Wahyudi Pratama', 'role' => 'Pemilik Honda CR-V', 'quote' => 'Paling suka cuci di sini karena kolongnya benar-benar disemprot bersih dan interiornya bebas debu.', 'rating' => 5],
                    ['name' => 'Eko Prasetyo', 'role' => 'Pengguna NMAX', 'quote' => 'Motor keluar kinclong sampai ke sela knalpot. Ruang tunggunya ber-AC enak buat santai.', 'rating' => 5],
                ],
            ],

            // 15. STUDIO FOTO & VIDEO
            'studio_foto' => [
                'theme_color'        => '#7C3AED',
                'headline'           => "Abadikan Momen Berharga dengan Sentuhan Visual Berkelas",
                'subheadline'        => "Foto wisuda, foto keluarga, maternity, pas foto formal kedutaan/ijazah, foto produk komersial, prewedding, dan self photo studio kekinian.",
                'announcement_badge' => "📸 Studio Ber-AC Luas • Properti Lengkap & Hasil Cetak HD",
                'cta_primary_text'   => "Booking Sesi Foto via WhatsApp",
                'cta_secondary_text' => "Pilihan Paket Foto & Portofolio",
                'about_title'        => "Menyimpan Cerita Terbaik dalam Setiap Jepretan",
                'about_story'        => "Di {$biz}, kami menangkap ekspresi tulus dan kehangatan Anda dengan pencahayaan studio profesional, arahan gaya yang santai, dan proses editing warna yang alami.",
                'values' => [
                    ['icon' => 'camera', 'title' => 'Lighting Studio Standar Pro', 'desc' => 'Pencahayaan lembut dan kontras seimbang untuk hasil foto memukau.'],
                    ['icon' => 'smile', 'title' => 'Pengarah Gaya Ramah', 'desc' => 'Tidak perlu kaku di depan kamera, fotografer kami siap memandu pose terbaik.'],
                    ['icon' => 'printer', 'title' => 'Cetak Kanvas & Bingkai Premium', 'desc' => 'Hasil cetak foto tajam anti pudar hingga puluhan tahun.'],
                    ['icon' => 'zap', 'title' => 'Semua File Master Diberikan', 'desc' => 'Dapatkan seluruh softcopy foto resolusi tinggi via Google Drive.'],
                ],
                'services' => [
                    ['title' => 'Paket Foto Wisuda / Sarjana Keluarga', 'desc' => 'Sesi studio 45 menit, 10 foto edit terpilih, 1 cetak 12R + frame bingkai elegan.', 'price' => 'Rp 275.000', 'badge' => 'Populer'],
                    ['title' => 'Self Photo Studio Praktis (15 Menit)', 'desc' => 'Foto sepuasnya dengan remote shutter mandiri, bebas ganti background dan properti.', 'price' => 'Rp 75.000 / 2 orang', 'badge' => 'Tren Muda'],
                    ['title' => 'Pas Foto Formal Lamaran Kerja / Visa / Ijazah', 'desc' => 'Editing rapi, ganti jas/warna background, cetak 4x6 & 3x4 dalam 15 menit.', 'price' => 'Rp 35.000', 'badge' => 'Kilat'],
                    ['title' => 'Foto Produk Katalog & E-Commerce UMKM', 'desc' => 'Foto produk studio bersih background putih/lifestyle untuk konten jualan online.', 'price' => 'Mulai Rp 25.000 / foto', 'badge' => 'Bisnis'],
                ],
                'faqs' => [
                    ['question' => 'Berapa lama softcopy foto bisa diterima setelah sesi?', 'answer' => 'Semua softcopy mentah dikirim di hari yang sama, sedangkan foto edit terpilih selesai dalam 2-3 hari.'],
                    ['question' => 'Berapa kapasitas orang untuk foto keluarga besar di studio?', 'answer' => 'Studio utama kami mampu menampung hingga 20-25 orang sekaligus dengan nyaman.'],
                ],
                'testimonials' => [
                    ['name' => 'Dr. Rian Hermawan', 'role' => 'Keluarga Wisudawan', 'quote' => 'Fotografernya sangat mengarahkan gaya dengan sabar, orang tua kami yang sudah sepuh jadi tidak capek.', 'rating' => 5],
                    ['name' => 'Tiara Maharani', 'role' => 'Pelanggan Self Studio', 'quote' => 'Seru banget foto sendiri sama sahabat tanpa malu-malu di depan fotografer. Hasil warnanya estetik!', 'rating' => 5],
                ],
            ],

            // 16. GYM & FITNESS
            'gym' => [
                'theme_color'        => '#DC2626',
                'headline'           => "Bentuk Tubuh Ideal & Gaya Hidup Sehat di {$biz}",
                'subheadline'        => "Peralatan angkat beban lengkap, cardio zone, kelas zumba, yoga, muay thai, dan bimbingan Personal Trainer (PT) berpengalaman.",
                'announcement_badge' => "💪 Free Trial 1 Hari untuk Member Baru",
                'cta_primary_text'   => "Daftar Member / Trial via WA",
                'cta_secondary_text' => "Daftar Harga Member & Fasilitas",
                'about_title'        => "Kebugaran Fisik untuk Kualitas Hidup Maksimal",
                'about_story'        => "{$biz} hadir memberikan ruang latihan yang suportif, bersih, dan memotivasi siapa pun untuk hidup lebih kuat, sehat, dan bugar tanpa rasa canggung.",
                'values' => [
                    ['icon' => 'dumbbell', 'title' => 'Alat Beban Lengkap & Terawat', 'desc' => 'Dumbbell hingga 50kg, barbel olimpiade, smith machine, dan isolasi otot.'],
                    ['icon' => 'heart-pulse', 'title' => 'Personal Trainer Bersertifikat', 'desc' => 'Program latihan terukur dan panduan pola makan sehat (nutrisi).'],
                    ['icon' => 'users', 'title' => 'Kelas Komunitas Seru', 'desc' => 'Zumba, aerobic, body pump, dan yoga rutin setiap minggu.'],
                    ['icon' => 'shower-head', 'title' => 'Loker & Kamar Mandi Bersih', 'desc' => 'Fasilitas kamar mandi air hangat dan loker berkunci aman.'],
                ],
                'services' => [
                    ['title' => 'Membership Bulanan All-Access', 'desc' => 'Akses bebas tanpa batas ke seluruh alat gym dan ruang latihan setiap hari.', 'price' => 'Rp 175.000 / bulan', 'badge' => 'Best Value'],
                    ['title' => 'Harian / Per Visit (Datang Langsung)', 'desc' => 'Cocok untuk Anda yang ingin mencoba alat atau sedang singgah di kota ini.', 'price' => 'Rp 25.000 / visit', 'badge' => 'Harian'],
                    ['title' => 'Paket 10 Sesi Personal Trainer (PT)', 'desc' => 'Pendampingan 1-on-1, program penurunan berat badan atau penambahan massa otot.', 'price' => 'Rp 750.000', 'badge' => 'Bimbingan'],
                    ['title' => 'Membership Pelajar & Mahasiswa', 'desc' => 'Tarif khusus untuk pelajar aktif dengan menunjukkan kartu tanda pelajar.', 'price' => 'Rp 135.000 / bulan', 'badge' => 'Diskon'],
                ],
                'faqs' => [
                    ['question' => 'Jam berapa gym beroperasi?', 'answer' => 'Kami buka setiap Senin - Sabtu pukul 06.00 - 22.00 WIB dan Minggu pukul 07.00 - 20.00 WIB.'],
                    ['question' => 'Saya pemula dan belum pernah ke gym, apakah ada yang mengajari?', 'answer' => 'Tentu! Staf gym kami akan memberikan tour pengenalan alat gratis dan tips dasar latihan yang aman.'],
                ],
                'testimonials' => [
                    ['name' => 'Bagus Wicaksono', 'role' => 'Member Turun 14 Kg', 'quote' => 'Berkat bimbingan PT di sini, saya berhasil turun dari 88kg ke 74kg dalam 4 bulan secara sehat.', 'rating' => 5],
                    ['name' => 'Siska Permata', 'role' => 'Member Kelas Zumba', 'quote' => 'Komunitas ceweknya seru dan instrukturnya asik banget. Suasana latihannya tidak bikin minder.', 'rating' => 5],
                ],
            ],

            // 17. BIMBEL & KURSUS
            'bimbel' => [
                'theme_color'        => '#2563EB',
                'headline'           => "Raih Prestasi Akademik & Sukses Masuk Sekolah/PTN Impian",
                'subheadline'        => "Bimbingan belajar SD, SMP, SMA, persiapan UTBK SNBT, kedinasan, kursus bahasa Inggris, dan les privat dengan tutor juara yang asyik.",
                'announcement_badge' => "🎓 92% Siswa Kami Sukses Lolos PTN Favorit",
                'cta_primary_text'   => "Daftar / Konsultasi Belajar via WA",
                'cta_secondary_text' => "Program Kelas & Biaya Belajar",
                'about_title'        => "Membangun Pemahaman Konsep, Bukan Sekadar Menghafal",
                'about_story'        => "{$biz} membimbing siswa belajar dengan metode interaktif, rumus cerdas penyelesaian soal cepat, dan pendampingan personal agar siswa mencintai proses belajar.",
                'values' => [
                    ['icon' => 'graduation-cap', 'title' => 'Tutor Lulusan PTN Terkemuka', 'desc' => 'Pengajar muda, komunikatif, dan sabar dalam menjelaskan materi rumit.'],
                    ['icon' => 'book-open', 'title' => 'Modul & Bank Soal Terupdate', 'desc' => 'Sesuai dengan kurikulum merdeka dan pola soal UTBK terbaru.'],
                    ['icon' => 'bar-chart', 'title' => 'Tryout Berkala & Evaluasi', 'desc' => 'Laporan perkembangan nilai siswa dilaporkan secara berkala kepada orang tua.'],
                    ['icon' => 'users', 'title' => 'Kelas Kecil Maksimal 8 Siswa', 'desc' => 'Memastikan setiap anak mendapatkan perhatian dan bimbingan maksimal.'],
                ],
                'services' => [
                    ['title' => 'Program Intensif Sukses UTBK / SNBT SMA', 'desc' => 'Penalaran umum, kuantitatif, literasi, tryout komputer mingguan, dan bedah soal.', 'price' => 'Mulai Rp 450.000 / bln', 'badge' => 'Unggulan'],
                    ['title' => 'Bimbel Reguler SD & SMP (Semua Mapel)', 'desc' => 'Bantu pekerjaan rumah (PR), pendalaman materi sekolah, dan persiapan ujian akhir.', 'price' => 'Mulai Rp 250.000 / bln', 'badge' => 'Dasar'],
                    ['title' => 'Les Privat Guru Datang ke Rumah (1-on-1)', 'desc' => 'Jadwal fleksibel disesuaikan dengan kebutuhan belajar spesifik anak Anda.', 'price' => 'Rp 75.000 / sesi', 'badge' => 'Privat'],
                    ['title' => 'Kursus Bahasa Inggris Speaking & TOEFL', 'desc' => 'Lancar percakapan sehari-hari dan strategi menaikkan skor tes TOEFL.', 'price' => 'Rp 300.000 / bln', 'badge' => 'Bahasa'],
                ],
                'faqs' => [
                    ['question' => 'Apakah ada kelas uji coba (trial class)?', 'answer' => 'Ya, kami menyediakan 1 sesi trial gratis agar calon siswa bisa merasakan metode belajar kami terlebih dahulu.'],
                    ['question' => 'Apakah jadwal les bisa menyesuaikan kegiatan ekstrakurikuler sekolah?', 'answer' => 'Bisa, kami menyediakan pilihan sesi sore dan malam hari yang fleksibel.'],
                ],
                'testimonials' => [
                    ['name' => 'Fadhil Muhammad', 'role' => 'Lolos Kedokteran Unair', 'quote' => 'Metode penalaran logikanya ngebantu banget pas ngerjain soal UTBK yang susah. Tutornya asik!', 'rating' => 5],
                    ['name' => 'Ibu Endang', 'role' => 'Orang Tua Siswa Kelas 6 SD', 'quote' => 'Nilai rapor anak saya meningkat signifikan dan dia jadi semangat belajar tidak malas lagi.', 'rating' => 5],
                ],
            ],

            // 18. AGEN EKSPEDISI & LOGISTIK
            'ekspedisi' => [
                'theme_color'        => '#F97316',
                'headline'           => "Kirim Paket Cepat, Aman & Ongkir Termurah ke Seluruh Nusantara",
                'subheadline'        => "Drop point resmi multi-ekspedisi (J&T, JNE, SiCepat, Anteraja, Pos Indonesia), kargo murah, pick-up paket gratis untuk toko online.",
                'announcement_badge' => "📦 Drop Point Resmi Multi-Kurir • Diskon Ongkir s/d 20%",
                'cta_primary_text'   => "Request Pick-Up Paket via WA",
                'cta_secondary_text' => "Cek Tarif Ongkir & Layanan",
                'about_title'        => "Solusi Pengiriman Terpadu untuk Seller & Warga",
                'about_story'        => "{$biz} memudahkan pengiriman paket Anda ke seluruh Indonesia tanpa perlu pindah-pindah counter. Cukup bawa paket Anda ke sini, kami carikan kurir tercepat dengan ongkir paling hemat.",
                'values' => [
                    ['icon' => 'truck', 'title' => 'Multi-Ekspedisi Lengkap', 'desc' => 'Bebas pilih kurir favorit pelanggan dalam satu tempat terpadu.'],
                    ['icon' => 'tag', 'title' => 'Diskon Ongkir Khusus Seller', 'desc' => 'Cashback dan potongan ongkir reguler untuk pengiriman rutin UMKM.'],
                    ['icon' => 'package-check', 'title' => 'Bantu Packing Aman & Rapi', 'desc' => 'Tersedia bubble wrap tebal, kardus, dan lakban fragile gratis.'],
                    ['icon' => 'search', 'title' => 'Resi Otomatis & Tracking Cepat', 'desc' => 'Nomor resi langsung aktif dan dapat dilacak real-time via WhatsApp.'],
                ],
                'services' => [
                    ['title' => 'Kirim Paket Reguler / Next Day Antar Kota', 'desc' => 'Pilihan ekspedisi tercepat sampai ke alamat tujuan dengan estimasi pasti.', 'price' => 'Mulai Rp 8.000 / kg', 'badge' => 'Cepat'],
                    ['title' => 'Kargo Barang Besar & Berat (Min 10 Kg)', 'desc' => 'Solusi hemat untuk kirim barang dagangan, mesin, dan perabot antar pulau.', 'price' => 'Mulai Rp 3.500 / kg', 'badge' => 'Hemat'],
                    ['title' => 'Free Pick-Up Paket Toko Online / Seller', 'desc' => 'Kurir kami mengambil paket langsung ke rumah atau toko Anda tanpa minimal jumlah.', 'price' => 'Gratis Jemput', 'badge' => 'Seller'],
                    ['title' => 'Pengiriman Dokumen Penting Kilat', 'desc' => 'Berkas dikirim aman dalam amplop segel khusus bersegel anti air.', 'price' => 'Mulai Rp 10.000', 'badge' => 'Dokumen'],
                ],
                'faqs' => [
                    ['question' => 'Jam berapa batas pengiriman (cut-off) agar paket berangkat hari ini?', 'answer' => 'Paket yang masuk sebelum pukul 18.00 WIB akan langsung diberangkatkan ke sorting center pada malam yang sama.'],
                    ['question' => 'Apakah bisa cetak label resi otomatis dari marketplace?', 'answer' => 'Bisa, Anda cukup mengirimkan nomor booking atau PDF resi, kami cetakkan secara gratis.'],
                ],
                'testimonials' => [
                    ['name' => 'Renaldi', 'role' => 'Seller Shopee & TikTok Shop', 'quote' => 'Sangat terbantu ada layanan pick-up gratis, tidak perlu capek antre di counter kurir lagi tiap sore.', 'rating' => 5],
                    ['name' => 'Wati Handayani', 'role' => 'Pengirim Paket Keluarga', 'quote' => 'Adminnya ramah bantu milihin kurir yang paling murah buat kirim oleh-oleh ke Kalimantan.', 'rating' => 5],
                ],
            ],

            // 19. FLORIST & TOKO BUNGA
            'florist' => [
                'theme_color'        => '#EC4899',
                'headline'           => "Ungkapkan Kasih & Kebahagiaan Lewat Rangkaian Bunga Segar Indah",
                'subheadline'        => "Bunga papan ucapan selamat & duka cita, buket bunga wisuda, standing flower, table flowers, bloom box, dan dekorasi bunga pernikahan.",
                'announcement_badge' => "🌸 Pengiriman Hari yang Sama (Same-Day) Tepat Waktu",
                'cta_primary_text'   => "Pesan Karangan Bunga via WA",
                'cta_secondary_text' => "Katalog Buket & Bunga Papan",
                'about_title'        => "Merangkai Cerita Lewat Keindahan Kelopak Alami",
                'about_story'        => "Di {$biz}, setiap tangkai bunga dipilih segar dari kebun terbaik. Florist kami merangkainya dengan sentuhan seni estetika tinggi untuk menyempurnakan setiap momen bahagia dan haru Anda.",
                'values' => [
                    ['icon' => 'flower-2', 'title' => 'Bunga Segar Tiap Pagi', 'desc' => 'Mawar, lily, krisan, dan baby breath mekar segar tahan lama.'],
                    ['icon' => 'clock', 'title' => 'Pengerjaan Cepat 2-3 Jam', 'desc' => 'Pesanan bunga papan kilat untuk ucapan mendadak siap kirim tepat waktu.'],
                    ['icon' => 'camera', 'title' => 'Foto Konfirmasi Sebelum Kirim', 'desc' => 'Foto hasil karangan bunga dan foto di lokasi penerima dikirimkan ke Anda.'],
                    ['icon' => 'sparkles', 'title' => 'Desain Rangkaian Elegan', 'desc' => 'Kombinasi warna harmonis dan wrapping kertas import berkelas.'],
                ],
                'services' => [
                    ['title' => 'Buket Bunga Mawar Segar Wisuda / Ultah', 'desc' => 'Buket cantik berisi 10-20 tangkai mawar segar plus kartu ucapan eksklusif.', 'price' => 'Mulai Rp 125.000', 'badge' => 'Favorit'],
                    ['title' => 'Bunga Papan Ucapan (Selamat / Duka Cita)', 'desc' => 'Ukuran 2m x 1,25m dengan susunan bunga segar atas dan bawah rapi.', 'price' => 'Mulai Rp 350.000', 'badge' => 'Resmi'],
                    ['title' => 'Standing Flower Akrilik / Besi Modern', 'desc' => 'Karangan bunga mewah untuk pembukaan toko baru (grand opening) dan kantor.', 'price' => 'Mulai Rp 450.000', 'badge' => 'Mewah'],
                    ['title' => 'Buket Uang (Money Bouquet) & Snack', 'desc' => 'Rangkaian lembaran uang kertas asli yang disusun rapi dan aman.', 'price' => 'Jasa Rp 75.000', 'badge' => 'Tren'],
                ],
                'faqs' => [
                    ['question' => 'Apakah bisa kirim di hari yang sama (same day)?', 'answer' => 'Bisa! Untuk buket bunga dan bunga papan standar bisa selesai dan terkirim dalam 2-4 jam.'],
                    ['question' => 'Apakah ada kartu ucapan yang disertakan?', 'answer' => 'Tentu, setiap pesanan bunga sudah termasuk kartu ucapan gratis dengan tulisan pesan kustom dari Anda.'],
                ],
                'testimonials' => [
                    ['name' => 'Gabriella Novita', 'role' => 'Pemesanan Buket Wisuda', 'quote' => 'Bunganya fresh banget, mawarnya mekar sempurna dan bungkusannya rapi estetik.', 'rating' => 5],
                    ['name' => 'Drs. H. Mulyono', 'role' => 'Pemesanan Bunga Papan Kantor', 'quote' => 'Pesanan bunga papan selamat sukses kantor rekanan datang tepat pagi hari sebelum acara dimulai. Sangat profesional!', 'rating' => 5],
                ],
            ],

            // 20. TOKO PERTANIAN & HIDROPONIK
            'pertanian' => [
                'theme_color'        => '#15803D',
                'headline'           => "Solusi Tani Subur & Panen Melimpah Terlengkap di {$biz}",
                'subheadline'        => "Bibit unggul bersertifikat, pupuk organik & NPK, pestisida terdaftar, instalasi hidroponik, media tanam, dan perlengkapan perkebunan.",
                'announcement_badge' => "🌱 Konsultasi Masalah Hama & Pemupukan Gratis",
                'cta_primary_text'   => "Konsultasi & Order via WhatsApp",
                'cta_secondary_text' => "Katalog Pupuk & Benih Tani",
                'about_title'        => "Mitra Petani & Pecinta Tanaman Nusantara",
                'about_story'        => "{$biz} mendampingi para petani lokal dan pecinta tanaman hias/hidroponik perkotaan untuk mendapatkan bibit berdaya tumbuh tinggi serta nutrisi tanaman yang tepat dan hemat biaya.",
                'values' => [
                    ['icon' => 'sprout', 'title' => 'Benih Bersertifikat Resmi', 'desc' => 'Daya berkecambah di atas 85% dengan potensi hasil panen optimal.'],
                    ['icon' => 'shield-check', 'title' => 'Obat & Pupuk 100% Asli', 'desc' => 'Menjual produk resmi berizin Kementan tanpa oplosan kimia berbahaya.'],
                    ['icon' => 'message-circle', 'title' => 'Edukasi Budidaya Tani', 'desc' => 'Bantu diagnosis penyakit tanaman dan rekomendasi dosis obat yang tepat.'],
                    ['icon' => 'truck', 'title' => 'Melayani Partai & Karungan', 'desc' => 'Harga grosir siap kirim ke kebun/lahan pertanian Anda.'],
                ],
                'services' => [
                    ['title' => 'Pupuk NPK Mutiara / Phonska / Urea Asli', 'desc' => 'Pupuk penyubur daun, akar, dan perangsang buah berkualitas tinggi.', 'price' => 'Mulai Rp 18.000 / kg', 'badge' => 'Subur'],
                    ['title' => 'Benih Cabai, Tomat & Sayuran Unggul', 'desc' => 'Kemasan pabrik segel tahan simpan dan tahan terhadap serangan virus.', 'price' => 'Mulai Rp 25.000 / bks', 'badge' => 'Benih'],
                    ['title' => 'Insektisida & Fungisida Hama Tanaman', 'desc' => 'Basmi ulat, kutu kebul, wereng, dan jamur busuk daun dengan tuntas.', 'price' => 'Mulai Rp 35.000', 'badge' => 'Proteksi'],
                    ['title' => 'Paket Starter Kit Hidroponik Pemula', 'desc' => 'Netpot, rockwool, nutrisi AB Mix sayur, dan panduan semai lengkap.', 'price' => 'Rp 85.000 / set', 'badge' => 'Urban Farming'],
                ],
                'faqs' => [
                    ['question' => 'Tanaman cabai saya daunnya keriting, obat apa yang cocok?', 'answer' => 'Daun keriting biasanya disebabkan oleh kutu thrips/aphids. Anda bisa konsultasikan foto tanamannya via WA kami untuk dosis yang tepat.'],
                    ['question' => 'Apakah bisa beli pupuk dalam jumlah sak-sakan (50kg)?', 'answer' => 'Tentu, kami menyediakan harga grosir partai karungan dengan armada pengiriman ke lokasi.'],
                ],
                'testimonials' => [
                    ['name' => 'Pak Sukardi', 'role' => 'Petani Sayur Hidroponik', 'quote' => 'Nutrisi AB Mix-nya bagus banget, selada saya tumbuh segar dan daunnya tebal-tebal.', 'rating' => 5],
                    ['name' => 'Bambang Irawan', 'role' => 'Pekebun Melon', 'quote' => 'Adminnya sangat paham obat pertanian. Rekomendasi obat hamanya manjur banget menyelamatkan kebun saya.', 'rating' => 5],
                ],
            ],
        ];
    }
}
