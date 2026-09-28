<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Models\Product;

class MarketplaceCategoryRegistry
{
    /**
     * Complete Official Marketplace Taxonomy (TikTok Shop & Tokopedia & Shopee).
     *
     * @return array<int, array{id: string, name: string, description: string, icon: string, keywords: array<string>}>
     */
    public static function all(): array
    {
        return [
            [
                'id'          => '601100',
                'name'        => 'Makanan & Minuman',
                'description' => 'Makanan & Minuman, termasuk Minuman, Makanan Ringan, Makanan Segar & Beku, Makanan Instan, Roti, Makanan Pokok, & Keperluan Memasak',
                'icon'        => 'coffee',
                'keywords'    => ['makanan', 'minuman', 'kopi', 'tea', 'teh', 'snack', 'roti', 'cake', 'biskuit', 'sirup', 'jus', 'sambal', 'bumbu', 'mie', 'beras', 'susu', 'gula', 'snack', 'frozen'],
            ],
            [
                'id'          => '600100',
                'name'        => 'Perlengkapan Rumah Tangga',
                'description' => 'Semua Persediaan Rumah, termasuk persediaan Perawatan Rumah & Kamar Mandi, Dekor Rumah & Pesta, Perapi Rumah, dan Alat Rumah Tangga.',
                'icon'        => 'home',
                'keywords'    => ['rumah', 'tangga', 'kebersihan', 'sapu', 'pel', 'dekorasi', 'wadah', 'laundry', 'jemuran', 'tempat sampah'],
            ],
            [
                'id'          => '600101',
                'name'        => 'Perlengkapan Dapur',
                'description' => 'Semua Keperluan Dapur, termasuk Alat Masak, Alat Pembuat Roti, Peralatan Makan, Peralatan Minum, Peralatan Dapur, & Aksesori Barbeku',
                'icon'        => 'utensils',
                'keywords'    => ['dapur', 'wajan', 'panci', 'pisau', 'sendok', 'garpu', 'piring', 'gelas', 'botol', 'tumbler', 'oven', 'mixer', 'talenan', 'spatula'],
            ],
            [
                'id'          => '600102',
                'name'        => 'Tekstil & Soft Furnishing',
                'description' => 'Termasuk Seprai, Kain & Persediaan Menjahit, dan Tekstil Rumah Tangga',
                'icon'        => 'scissors',
                'keywords'    => ['seprai', 'sprei', 'selimut', 'kain', 'bantal', 'guling', 'sarung', 'taplak', 'gorden', 'handuk'],
            ],
            [
                'id'          => '600103',
                'name'        => 'Peralatan Rumah Tangga',
                'description' => 'Termasuk Elektronik Rumah Tangga, Elektronik Rumah Besar, Elektronik Dapur, dan Elektronik Komersial',
                'icon'        => 'tv',
                'keywords'    => ['blender', 'rice cooker', 'dispenser', 'kipas', 'ac', 'kulkas', 'mesin cuci', 'setrika', 'vacuum', 'air fryer'],
            ],
            [
                'id'          => '600200',
                'name'        => 'Pakaian & Dalaman Wanita',
                'description' => 'Semua Busana Wanita, termasuk Atasan, Bawahan, Gaun, Pakaian Dalam, Jas & Overal, Pakaian Tidur, dan Pakaian Spesial',
                'icon'        => 'shirt',
                'keywords'    => ['wanita', 'dress', 'gaun', 'blouse', 'rok', 'tunik', 'daster', 'lingerie', 'bra', 'celana wanita', 'outer'],
            ],
            [
                'id'          => '600201',
                'name'        => 'Muslim Fashion',
                'description' => 'Busana Muslim, termasuk Pakaian Islami Pria, Wanita, dan Anak, Hijab, Pakaian Luar, Pakaian Olahraga Islami, Aksesori Islami, Pakaian & Perlengkapan Doa, serta Perlengkapan Umrah',
                'icon'        => 'sparkles',
                'keywords'    => ['muslim', 'hijab', 'jilbab', 'gamis', 'mukena', 'sarung', 'koko', 'pashmina', 'khimar', 'abaya', 'sajadah', 'peci', 'songkok'],
            ],
            [
                'id'          => '600202',
                'name'        => 'Sepatu',
                'description' => 'Mencakup semua jenis sepatu, termasuk sepatu Pria & Wanita dan Aksesori Sepatu',
                'icon'        => 'footprints',
                'keywords'    => ['sepatu', 'sneakers', 'sandal', 'boots', 'heels', 'flat shoes', 'pantofel', 'kaos kaki', 'tali sepatu', 'insole'],
            ],
            [
                'id'          => '600300',
                'name'        => 'Perawatan & Kecantikan',
                'description' => 'Perawatan Kecantikan & Pribadi mencakup semua produk yang ditujukan untuk riasan, kosmetik perawatan kulit, parfum, elektronik, & produk pembersih pribadi.',
                'icon'        => 'sparkle',
                'keywords'    => ['skincare', 'serum', 'cream', 'lotion', 'lipstik', 'parfum', 'sabun', 'shampoo', 'sunscreen', 'bedak', 'toner', 'facial wash', 'body wash', 'kosmetik'],
            ],
            [
                'id'          => '600400',
                'name'        => 'Ponsel & Elektronik',
                'description' => 'Ponsel & Elektronik, termasuk Ponsel, Kamera & Fotografi, Audio & Video, Tablet & Komputer, Game & Konsol, Perangkat Cerdas & Edukasi, serta Aksesori Universal',
                'icon'        => 'smartphone',
                'keywords'    => ['hp', 'handphone', 'smartphone', 'casing', 'charger', 'kabel data', 'powerbank', 'earphone', 'headset', 'tws', 'kamera', 'speaker'],
            ],
            [
                'id'          => '600401',
                'name'        => 'Komputer & Peralatan Kantor',
                'description' => 'Semua perangkat komputer dan peralatan kantor, seperti desktop, komponen laptop dan jaringan, penyimpanan data dan perangkat lunak, alat tulis kantor, serta peralatan kantor.',
                'icon'        => 'laptop',
                'keywords'    => ['laptop', 'pc', 'mouse', 'keyboard', 'printer', 'kertas', 'pulpen', 'buku tulis', 'flashdisk', 'harddisk', 'atk', 'stapler'],
            ],
            [
                'id'          => '600500',
                'name'        => 'Perlengkapan Hewan Peliharaan',
                'description' => 'Persediaan Hewan Peliharaan, termasuk Persediaan Anjing & Kucing',
                'icon'        => 'paw-print',
                'keywords'    => ['pet', 'kucing', 'anjing', 'cat food', 'dog food', 'pasir kucing', 'kandang', 'kalung kucing', 'shampoo hewan'],
            ],
            [
                'id'          => '600600',
                'name'        => 'Ibu & Bayi',
                'description' => 'Persediaan Ibu & Bayi, termasuk Susu Formula & Makanan Bayi, Perawatan & Kesehatan Bayi, Keselamatan, Mainan Bayi, Pakaian & Sepatu, Aksesori Fesyen, Keperluan Perjalanan, dan Furnitur; serta Persediaan Ibu Menyusui, MPASI, dan Bersalin',
                'icon'        => 'baby',
                'keywords'    => ['bayi', 'baby', 'popok', 'diapers', 'dot', 'botol susu', 'mpasi', 'gendongan', 'stroller', 'baju bayi', 'minyak telon'],
            ],
            [
                'id'          => '600700',
                'name'        => 'Olahraga & Outdoor',
                'description' => 'Olahraga & Luar Ruang, termasuk Olahraga Bola, Olahraga Air, Waktu Luang & Rekreasi Luar Ruang, Berkemah & Mendaki, Olahraga Musim Dingin, Perlengkapan Kebugaran; Pakaian Olahraga & Luar Ruang, Alas Kaki, Pakaian Renang, Pakaian Selancar & Pakaian Selam, Toko Penggemar, serta Aksesori Olahraga & Luar Ruang.',
                'icon'        => 'activity',
                'keywords'    => ['sport', 'olahraga', 'sepeda', 'jersey', 'matras', 'tenda', 'camping', 'raket', 'bola', 'gym', 'fitness', 'dumbbell'],
            ],
            [
                'id'          => '600800',
                'name'        => 'Mainan & Hobi',
                'description' => 'Mainan & Hobi, termasuk Elektrik & Kendali Jarak Jauh, Klasik & Baru, Mainan & Boneka, Mainan Edukasi, Mainan & Teka-Teki, DIY, Olahraga & Permainan Luar Ruang, Alat Musik & Aksesori',
                'icon'        => 'gamepad-2',
                'keywords'    => ['mainan', 'toy', 'lego', 'puzzle', 'boneka', 'diecast', 'action figure', 'drone', 'board game', 'gitar', 'alat musik'],
            ],
            [
                'id'          => '600104',
                'name'        => 'Furnitur',
                'description' => 'Furnitur, termasuk Furnitur Dalam & Luar Ruang, Furnitur Anak, Furnitur Komersial dan Komponen Furnitur',
                'icon'        => 'armchair',
                'keywords'    => ['meja', 'kursi', 'lemari', 'sofa', 'rak', 'tempat tidur', 'kasur', 'stool', 'nakas'],
            ],
            [
                'id'          => '600900',
                'name'        => 'Alat & Perangkat Keras',
                'description' => 'Peralatan & Perkakas, termasuk Alat Berkebun, Perkakas Listrik, Alat Ukur, Perkakas, dan Perapi Peralatan; Pompa & Leding, Perkakas, dan Perlengkapan Solder',
                'icon'        => 'wrench',
                'keywords'    => ['obeng', 'tang', 'palu', 'bor', 'meteran', 'pompa', 'solder', 'kunci pas', 'perkakas', 'baut', 'mur'],
            ],
            [
                'id'          => '600901',
                'name'        => 'Renovasi Rumah',
                'description' => 'Renovasi Rumah, termasuk Lampu & Pencahayaan, Keamanan & Keselamatan, Sistem Rumah Cerdas, Perlengkapan Kamar Mandi & Dapur, Tenaga Surya & Angin, Persediaan Kebun & Bangunan, serta Perlengkapan & Persediaan Listrik',
                'icon'        => 'hammer',
                'keywords'    => ['lampu', 'bohlam', 'cat', 'semen', 'keramik', 'kran', 'shower', 'stopkontak', 'kabel listrik', 'cctv'],
            ],
            [
                'id'          => '601000',
                'name'        => 'Otomotif & Motor',
                'description' => 'Termasuk aksesori pembersihan dan pemeliharaan Sepeda Motor & Mobil, suku cadang, dan persediaan ATV, Karavan, & Perahu',
                'icon'        => 'car',
                'keywords'    => ['oli', 'helm', 'ban', 'spion', 'wiper', 'shampoo mobil', 'poles', 'motor', 'mobil', 'sparepart', 'kampas rem', 'aki'],
            ],
            [
                'id'          => '600203',
                'name'        => 'Aksesori Pakaian',
                'description' => 'Termasuk Aksesori Rambut, Ekstensi Rambut & Wig, Perhiasan Kostum, Jam Tangan, Kacamata, dan Aksesori Busana',
                'icon'        => 'glasses',
                'keywords'    => ['kacamata', 'jam tangan', 'ikat pinggang', 'sabuk', 'topi', 'syal', 'dasi', 'bando', 'jepit rambut', 'masker kain'],
            ],
            [
                'id'          => '601200',
                'name'        => 'Kesehatan',
                'description' => 'Kesehatan, termasuk Pengobatan & Obat OTC, Suplemen Makanan, Suplai Medis, Kesehatan Seksual, dan Pengobatan & Obat Alternatif',
                'icon'        => 'heart-pulse',
                'keywords'    => ['vitamin', 'suplemen', 'madu', 'herbal', 'obat', 'plester', 'termometer', 'tensimeter', 'masker medis', 'koyo', 'minyak angin'],
            ],
            [
                'id'          => '601300',
                'name'        => 'Buku, Majalah, & Audio',
                'description' => 'Buku, Majalah & Audio, termasuk semua jenis buku kertas, Buku Anak & Bayi, Majalah & Surat Kabar, dan Video & Musik',
                'icon'        => 'book-open',
                'keywords'    => ['buku', 'novel', 'komik', 'majalah', 'kamus', 'ensiklopedia', 'cd', 'kaset', 'audiobook', 'manga'],
            ],
            [
                'id'          => '600204',
                'name'        => 'Pakaian Anak',
                'description' => 'Fesyen Anak, termasuk Busana Anak Laki & Perempuan, Alas Kaki, dan Aksesori Fesyen',
                'icon'        => 'smile',
                'keywords'    => ['anak', 'kids', 'baju anak', 'kaos anak', 'celana anak', 'dress anak', 'piyama anak', 'sepatu anak'],
            ],
            [
                'id'          => '600205',
                'name'        => 'Pakaian & Dalaman Pria',
                'description' => 'Semua Busana Pria, termasuk Atasan, Bawahan Pria, Gaun, Pakaian Dalam, Jas & Overal, Pakaian Tidur, dan Pakaian Spesial',
                'icon'        => 'user',
                'keywords'    => ['pria', 'kaos pria', 'kemeja pria', 'celana pria', 'chino', 'jeans', 'boxer', 'singlet', 'jaket pria', 'hoodie'],
            ],
            [
                'id'          => '600206',
                'name'        => 'Koper & Tas',
                'description' => 'Koper & Tas, termasuk Tas Wanita, Tas Pria, Tas Fungsional, Koper & Tas Perjalanan, dan Aksesori Tas',
                'icon'        => 'briefcase',
                'keywords'    => ['tas', 'ransel', 'backpack', 'tote bag', 'sling bag', 'koper', 'dompet', 'waist bag', 'duffle bag'],
            ],
            [
                'id'          => '601400',
                'name'        => 'Produk Virtual',
                'description' => 'Produk Non-Fisik / Digital, Voucher Elektronik, Tiket, dan Layanan Online',
                'icon'        => 'globe',
                'keywords'    => ['voucher', 'token', 'pulsa', 'tiket', 'e-book', 'digital', 'game voucher', 'top up'],
            ],
            [
                'id'          => '601500',
                'name'        => 'Barang Bekas',
                'description' => 'Produk bekas adalah produk bekas pakai. Ini termasuk tas, alas kaki, pakaian, ponsel & barang elektronik refurbished, aksesori pakaian, dan kartu koleksi.',
                'icon'        => 'repeat',
                'keywords'    => ['bekas', 'second', 'preloved', 'refurbished', 'thrifting'],
            ],
            [
                'id'          => '601600',
                'name'        => 'Koleksi',
                'description' => 'Koleksi, termasuk Koleksi Budaya Kontemporer, Hiburan, Koleksi Olahraga, Kartu Koleksi & Aksesori, dan Koin & Uang Koleksi',
                'icon'        => 'award',
                'keywords'    => ['koleksi', 'kartu pokemon', 'koin kuno', 'uang kuno', 'perangko', 'merchandise', 'memorabilia'],
            ],
            [
                'id'          => '601700',
                'name'        => 'Aksesori Perhiasan & Turunannya',
                'description' => 'Aksesori Perhiasan & Derivatif, termasuk bahan Perhiasan Emas, Platinum & Emas Karat, Giok, Perak, Mutiara, Batu Ambar, Berlian, Mellite, Kristal Alami, Batu Semiberharga, Batu Delima, Batu Safir & Zamrud, Kristal Nonalami, dan Batu Permata Buatan',
                'icon'        => 'gem',
                'keywords'    => ['emas', 'perak', 'cincin', 'kalung', 'gelang', 'anting', 'berlian', 'giok', 'mutiara', 'perhiasan', 'silver'],
            ],
            [
                'id'          => '601800',
                'name'        => 'Pemesanan & Voucher',
                'description' => 'Voucher Belanja, Kupon Diskon, Reservasi, dan Tiket Event',
                'icon'        => 'ticket',
                'keywords'    => ['kupon', 'voucher belanja', 'diskon', 'reservasi', 'booking', 'tiket konser'],
            ],
        ];
    }

    /**
     * Smart suggestion of marketplace category based on product name and category.
     *
     * @return array{id: string, name: string, description: string, icon: string}
     */
    public static function suggestCategory(Product $product): array
    {
        $categories = self::all();
        $searchStr  = strtolower($product->name . ' ' . ($product->category?->name ?? '') . ' ' . ($product->description ?? ''));

        $bestScore    = 0;
        $bestCategory = $categories[0]; // Default: Makanan & Minuman

        foreach ($categories as $cat) {
            $score = 0;
            foreach ($cat['keywords'] as $keyword) {
                if (str_contains($searchStr, strtolower($keyword))) {
                    $score += 2;
                }
            }

            if (str_contains(strtolower($cat['name']), strtolower($product->category?->name ?? ''))) {
                $score += 5;
            }

            if ($score > $bestScore) {
                $bestScore    = $score;
                $bestCategory = $cat;
            }
        }

        return $bestCategory;
    }

    /**
     * Find category by its ID or Name.
     */
    public static function find(string $idOrName): ?array
    {
        $all = self::all();
        foreach ($all as $cat) {
            if ($cat['id'] === $idOrName || strcasecmp($cat['name'], $idOrName) === 0) {
                return $cat;
            }
        }

        return null;
    }
}
