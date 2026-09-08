<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            // Cluster K: Tutorial "Cara"
            [
                'title' => 'Cara Menghitung HPP (Harga Pokok Penjualan) Makanan & Minuman untuk UMKM',
                'slug' => 'cara-menghitung-hpp-makanan-minuman-umkm',
                'cluster' => 'tutorial',
                'category' => 'HPP & Biaya',
                'excerpt' => 'Pelajari rumus praktis menghitung HPP (COGS) usaha kuliner mulai dari bahan baku, porsi bumbu, biaya tenaga kerja, hingga overhead kemasan.',
                'content' => '<h2>Mengapa Menghitung HPP Kuliner Sangat Krusial?</h2>
<p>Banyak pengusaha kuliner merasa omzetnya besar, warung atau kafenya selalu ramai pembeli, namun di akhir bulan saldo rekening tidak bertambah. Masalah klasik ini hampir selalu bersumber dari <strong>kesalahan perhitungan Harga Pokok Penjualan (HPP)</strong> atau <em>Food Cost</em>.</p>

<h3>1. Rumus Dasar HPP Kuliner per Porsi</h3>
<p>HPP per porsi dihitung dengan menjumlahkan seluruh biaya yang melekat langsung pada satu porsi hidangan:</p>
<blockquote><strong>HPP per Porsi = Biaya Bahan Baku (Raw Material) + Biaya Tenaga Kerja Langsung + Biaya Overhead & Kemasan</strong></blockquote>

<h3>2. Studi Kasus: Menghitung HPP Nasi Goreng Spesial</h3>
<ul>
    <li>Beras + Bumbu dasar: Rp 3.500</li>
    <li>Telur (1 butir): Rp 2.000</li>
    <li>Ayam suwir (50 gram): Rp 3.500</li>
    <li>Minyak, kecap, pelengkap: Rp 1.000</li>
    <li>Kotak kemasan + sendok: Rp 1.200</li>
    <li>Alokasi gas & listrik per porsi: Rp 800</li>
</ul>
<p><strong>Total HPP = Rp 12.000</strong>. Jika Anda menginginkan margin kotor 40%, maka harga jual ideal adalah Rp 12.000 / (1 - 0.4) = <strong>Rp 20.000</strong>.</p>

<h3>3. Gunakan Kalkulator HPP Otomatis</h3>
<p>Menghitung resep ratusan menu menggunakan buku catatan manual sangat memakan waktu. Anda dapat memanfaatkan <a href="/kalkulator-hpp" class="text-cyan-400 font-bold underline">Kalkulator HPP Online Cooca</a> secara gratis untuk mengunci margin laba akurat.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Cara Menghitung HPP Makanan & Minuman UMKM | Panduan Praktis COOCA',
                'meta_description' => 'Rumus praktis menghitung HPP (Food Cost) usaha kuliner makanan & minuman. Lengkap dengan contoh perhitungan per porsi dan template gratis.',
                'author_name' => 'Tim Finansial COOCA',
                'views_count' => 342,
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Cara Menghitung BEP (Break Even Point) Warung & Toko Kelontong',
                'slug' => 'cara-menghitung-bep-warung-toko-kelontong',
                'cluster' => 'tutorial',
                'category' => 'Strategi Keuangan',
                'excerpt' => 'Ketahui berapa rupiah omzet minimum yang harus Anda capai setiap hari agar bisnis tidak merugi dan mulai menghasilkan keuntungan bersih.',
                'content' => '<h2>Apa itu Break Even Point (BEP)?</h2>
<p>Break Even Point (BEP) atau Titik Impas adalah titik di mana total pendapatan usaha Anda persis sama dengan total biaya yang dikeluarkan. Pada titik ini, usaha Anda <strong>tidak untung dan tidak rugi</strong> (laba = 0).</p>

<h3>Klasifikasi Biaya untuk Menghitung BEP</h3>
<ol>
    <li><strong>Biaya Tetap (Fixed Cost):</strong> Biaya yang jumlahnya tetap meski penjualan Anda nol, seperti sewa tempat, gaji karyawan tetap, dan kuota internet toko.</li>
    <li><strong>Biaya Variabel (Variable Cost):</strong> Biaya yang naik turun sejalan dengan volume transaksi, seperti harga kulakan barang dagangan dan kantong kresek.</li>
</ol>

<h3>Rumus BEP Nominal Rupiah</h3>
<p>Rumus menghitung BEP dalam rupiah:</p>
<blockquote><strong>BEP (Rp) = Biaya Tetap / (1 - (Biaya Variabel / Total Penjualan))</strong></blockquote>
<p>Gunakan <a href="/kalkulator-bep" class="text-cyan-400 font-bold underline">Kalkulator BEP Cooca</a> untuk menghitung titik impas usaha Anda dalam 3 detik tanpa perlu rumus manual.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1556742049-0a67c5574f73?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Cara Menghitung BEP Usaha Kecil & Warung | Panduan COOCA',
                'meta_description' => 'Panduan cara menghitung Break Even Point (BEP) rupiah dan unit untuk toko kelontong, kafe, dan warung UMKM.',
                'author_name' => 'Konsultan Bisnis COOCA',
                'views_count' => 280,
                'is_published' => true,
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Cara Membuat Laporan Keuangan Sederhana untuk UMKM (Buku Kas & Laba Rugi)',
                'slug' => 'cara-membuat-laporan-keuangan-sederhana-umkm',
                'cluster' => 'tutorial',
                'category' => 'Pembukuan',
                'excerpt' => 'Langkah mudah menyusun buku kas harian, mencatat arus kas masuk dan keluar, serta membuat laporan laba rugi sederhana tanpa latar belakang akuntansi.',
                'content' => '<h2>Kenapa UMKM Wajib Punya Pembukuan Rapi?</h2>
<p>Banyak pemilik UMKM mencampurkan uang pribadi dengan uang usaha. Akibatnya, uang kas terasa habis untuk keperluan rumah tangga dan tidak ada modal untuk kulakan barang.</p>

<h3>3 Langkah Menyusun Laporan Keuangan Sederhana</h3>
<ol>
    <li><strong>Pisahkan Rekening Bank:</strong> Jangan gunakan rekening belanja pribadi untuk menerima transferan konsumen.</li>
    <li><strong>Catat Arus Kas Harian (Cashflow):</strong> Buat tabel sederhana berisi Tanggal, Keterangan, Kas Masuk, Kas Keluar, dan Saldo Terakhir.</li>
    <li><strong>Buat Laporan Laba Rugi Akhir Bulan:</strong> Pendapatan Penjualan dikurangi HPP = Laba Kotor. Laba Kotor dikurangi biaya operasional (listrik, gaji, sewa) = Laba Bersih.</li>
</ol>
<p>Unduh gratis <a href="/template-pembukuan-gratis" class="text-cyan-400 font-bold underline">Template Excel Pembukuan Warung</a> atau gunakan aplikasi Cooca UMKM untuk pencatatan otomatis.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Cara Membuat Laporan Keuangan Sederhana UMKM | COOCA',
                'meta_description' => 'Tutorial lengkap membuat pembukuan buku kas dan laporan laba rugi sederhana untuk pelaku usaha kecil dan UMKM.',
                'author_name' => 'Tim Finansial COOCA',
                'views_count' => 410,
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Panduan Pajak UMKM: Cara Menghitung PPh Final 0.5% Sesuai PP 55/2022',
                'slug' => 'panduan-pajak-umkm-cara-hitung-pph-final-setengah-persen',
                'cluster' => 'tutorial',
                'category' => 'Pajak & Legalitas',
                'excerpt' => 'Ketahui aturan insentif omzet s.d Rp 500 juta bebas pajak bagi UMKM orang pribadi dan cara mudah menghitung setoran PPh Final 0.5% setiap bulan.',
                'content' => '<h2>Aturan Baru Pajak UMKM: Omzet di Bawah 500 Juta Bebas Pajak!</h2>
<p>Berdasarkan UU Harmonisasi Peraturan Perpajakan (HPP) dan PP Nomor 55 Tahun 2022, Wajib Pajak Orang Pribadi UMKM mendapatkan fasilitas pembebasan pajak untuk <strong>peredaran bruto (omzet) kumulatif hingga Rp 500.000.000 per tahun</strong>.</p>

<h3>Contoh Simulasi Perhitungan</h3>
<p>Misal Pak Budi memiliki warung makan dengan omzet bulanan rata-rata Rp 50.000.000:</p>
<ul>
    <li>Bulan ke-1 s.d ke-10 (Total omzet Rp 500.000.000): <strong>PPh Final = Rp 0</strong> (Bebas pajak).</li>
    <li>Bulan ke-11 (Omzet Rp 50.000.000, omzet kumulatif jadi Rp 550.000.000): Pajak hanya dikenakan atas kelebihannya (Rp 50.000.000 x 0.5% = <strong>Rp 250.000</strong>).</li>
</ul>
<p>Hitung pajak usaha Anda secara instan menggunakan <a href="/kalkulator-pph-final" class="text-cyan-400 font-bold underline">Kalkulator PPh Final 0.5% Cooca</a>.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Cara Menghitung PPh Final UMKM 0.5% PP 55/2022 | COOCA',
                'meta_description' => 'Panduan lengkap cara menghitung PPh Final UMKM 0.5%, batas omzet bebas pajak 500 juta, dan batas waktu penyetoran bulanan.',
                'author_name' => 'Konsultan Pajak COOCA',
                'views_count' => 195,
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],

            // Cluster O: Edukasi Topikal
            [
                'title' => 'Apa itu Food Cost? Standar Persentase Ideal untuk Usaha F&B dan Kafe',
                'slug' => 'apa-itu-food-cost-standar-persentase-ideal-fb-kafe',
                'cluster' => 'edukasi',
                'category' => 'Industri F&B',
                'excerpt' => 'Pelajari definisi Food Cost, rumus persentase, dan standar aman industri F&B (28% - 35%) agar kafe atau kedai kopi Anda tetap mencetak laba sehat.',
                'content' => '<h2>Memahami Food Cost dalam Industri Restoran & Kafe</h2>
<p>Food Cost adalah persentase biaya bahan baku makanan dibandingkan dengan harga jual menu tersebut. Indikator ini merupakan kompas utama profitabilitas pengusaha kuliner di seluruh dunia.</p>

<h3>Berapa Standar Food Cost yang Sehat?</h3>
<p>Secara umum, standar industri F&B di Indonesia membagi food cost sebagai berikut:</p>
<ul>
    <li><strong>Kafe & Minuman Kopi:</strong> 18% - 25% (margin minuman cenderung sangat tinggi).</li>
    <li><strong>Makanan Berat & Resto Kasual:</strong> 28% - 35% (standar industri paling ideal).</li>
    <li><strong>Steakhouse / Seafood Segar:</strong> 35% - 40% (bahan baku premium bernilai tinggi).</li>
</ul>
<p>Jika food cost Anda menembus di atas 45%, hampir dapat dipastikan bisnis Anda akan merugi setelah dipotong biaya sewa, listrik, dan gaji karyawan.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'Apa itu Food Cost? Standar Ideal Kafe & Resto F&B | COOCA',
                'meta_description' => 'Pengertian food cost persentase, cara menghitung biaya bahan baku, dan strategi menjaga margin restoran tetap sehat.',
                'author_name' => 'Tim Edukasi Kuliner COOCA',
                'views_count' => 520,
                'is_published' => true,
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => '5 Alasan Mengapa Kasir Digital (POS) Jauh Lebih Menguntungkan daripada Catatan Kertas',
                'slug' => '5-alasan-mengapa-kasir-digital-pos-lebih-menguntungkan-dari-kertas',
                'cluster' => 'edukasi',
                'category' => 'Teknologi Kasir',
                'excerpt' => 'Mengapa toko modern beralih dari nota kertas ke aplikasi kasir POS digital: cegah kebocoran kasir, pantau stok real-time, dan kecepatan transaksi.',
                'content' => '<h2>Zaman Sudah Berubah: Nota Manual Membuka Celah Kerugian</h2>
<p>Masih menggunakan nota manual atau kalkulator meja di kasir toko Anda? Penelitian menunjukkan bahwa lebih dari <strong>12% kebocoran omzet UMKM</strong> terjadi akibat kelalaian hitung kasir dan nota yang tidak tercatat.</p>

<h3>Keunggulan Kasir Digital (Point of Sale):</h3>
<ol>
    <li><strong>Mencegah Kasir Curang:</strong> Setiap item yang keluar dari rak langsung terpotong dari stok inventori.</li>
    <li><strong>Dukungan Pembayaran QRIS:</strong> Konsumen zaman sekarang semakin malas membawa uang tunai. Kasir POS modern mendukung QRIS instan.</li>
    <li><strong>Laporan Penjualan Real-time:</strong> Pemilik bisnis bisa memantau penjualan harian dari mana saja tanpa harus menunggui toko seharian.</li>
    <li><strong>Database Pelanggan:</strong> Kumpulkan nomor WhatsApp pembeli untuk promosi dan promo loyalitas.</li>
</ol>
<p>Mulai gunakan <a href="/solusi/kasir-warung" class="text-cyan-400 font-bold underline">Aplikasi Kasir Cooca UMKM</a> gratis selamanya tanpa biaya bulanan.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => '5 Keunggulan Kasir Digital POS untuk Toko & UMKM | COOCA',
                'meta_description' => 'Alasan penting mengapa warung dan toko wajib menggunakan aplikasi kasir POS digital untuk mencegah kebocoran kas dan stok.',
                'author_name' => 'Tim Digitalisasi UMKM',
                'views_count' => 315,
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Peran AI Assistant dalam Membantu Pengusaha UMKM Mengambil Keputusan Bisnis',
                'slug' => 'peran-ai-assistant-membantu-umkm-mengambil-keputusan',
                'cluster' => 'edukasi',
                'category' => 'Kecerdasan Buatan (AI)',
                'excerpt' => 'Kini UMKM tidak perlu menyewa konsultan mahal untuk menganalisa laporan keuangan. Cukup tanyakan pada AI Assistant bisnis Anda.',
                'content' => '<h2>Demokratisasi Teknologi untuk Usaha Mikro</h2>
<p>Dahulu, analisis data bisnis mendalam seperti <em>sales forecasting</em>, analisis produk terlaris, dan optimasi harga hanya bisa dinikmati oleh perusahaan korporasi besar dengan tim analis data.</p>

<h3>Bagaimana AI Assistant Membantu Bisnis Sehari-hari?</h3>
<p>Dengan integrasi AI di Cooca UMKM, pemilik usaha dapat berdialog langsung layaknya memiliki asisten pribadi:</p>
<ul>
    <li><em>“Menu apa yang menghasilkan margin terbesar bulan ini?”</em></li>
    <li><em>“Kapan waktu puncak kunjungan pelanggan terbanyak di toko saya?”</em></li>
    <li><em>“Berapa stok bahan baku yang harus saya siapkan untuk akhir pekan ini?”</em></li>
</ul>
<p>Semua jawaban disajikan secara instan berdasarkan data transaksi riil usaha Anda.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80',
                'meta_title' => 'AI Assistant untuk UMKM Indonesia | Inovasi COOCA.ID',
                'meta_description' => 'Bagaimana asisten kecerdasan buatan (AI) membantu pelaku UMKM menganalisa penjualan, prediksi stok, dan penetapan harga jual.',
                'author_name' => 'Tech & Innovation Team',
                'views_count' => 450,
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($posts as $postData) {
            Post::updateOrCreate(
                ['slug' => $postData['slug']],
                $postData
            );
        }
    }
}
