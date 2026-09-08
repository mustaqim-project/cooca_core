<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class PublicSolutionController extends Controller
{
    /**
     * Vertical solution metadata and copy.
     */
    private array $solutions = [
        'kasir-warung' => [
            'slug' => 'kasir-warung',
            'title' => 'Software Kasir Toko Kelontong & Warung Sembako',
            'badge' => 'Solusi Toko Kelontong',
            'headline' => 'Kelola Penjualan Warung, Catat Bon Hutang, & Pantau Stok Kulakan — 100% Gratis',
            'subheadline' => 'Aplikasi kasir warung sembako paling praktis. Bisa jual eceran maupun grosir (renceng/dus), catat hutang pelanggan via WhatsApp, dan pantau laba harian tanpa kalkulator manual.',
            'pain_points' => [
                'Uang kas toko sering tekor karena bon hutang pelanggan hilang atau lupa dicatat.',
                'Bingung menentukan harga jual saat harga minyak goreng atau beras naik dari agen.',
                'Stok barang sering selisih dan rusak di gudang tanpa terdeteksi sejak dini.'
            ],
            'features' => [
                ['title' => 'Pencatatan Bon / Kasbon Digital', 'desc' => 'Kirim tagihan dan rincian belanja hutang pelanggan langsung ke WhatsApp dalam 1 klik.'],
                ['title' => 'Multi-Satuan (Dus, Pak, Pcs)', 'desc' => 'Beli 1 dus dari distributor, jual eceran per bungkus secara otomatis tanpa repot konversi manual.'],
                ['title' => 'Barcode Scanner Kamera HP', 'desc' => 'Scan barcode produk pabrikan cukup menggunakan kamera smartphone Anda tanpa perlu beli alat scanner mahal.'],
                ['title' => 'Kalkulator Kulakan & Margin', 'desc' => 'Otomatis hitung margin laba setiap kali harga beli barang dari distributor naik.'],
            ],
            'testimonial' => [
                'quote' => 'Dulu sering pusing nagih hutang tetangga karena catatannya di buku tulis kucel. Sekarang pakai Cooca UMKM, tinggal kirim rekap nota WA, bayarnya jadi tepat waktu.',
                'author' => 'Pak Budi Waluyo',
                'business' => 'Toko Sembako Berkah, Klaten'
            ]
        ],
        'kasir-cafe-kecil' => [
            'slug' => 'kasir-cafe-kecil',
            'title' => 'Aplikasi Kasir Kedai Kopi, Kafe Kecil & Warkop Modern',
            'badge' => 'Solusi F&B & Coffeeshop',
            'headline' => 'Hitung HPP Resep per Cup, Cetak Struk Meja, & Terima QRIS Instan',
            'subheadline' => 'Tinggalkan nota manual di meja barista. Kelola resep kopi (beans, susu, cup, sirup), kelola nomor meja, split bill, dan analisa menu terlaris secara real-time.',
            'pain_points' => [
                'Bahan baku susu segar dan sirup sering terbuang sia-sia karena tidak ada takaran standar.',
                'Kasir kewalahan saat jam ramai (rush hour) dan pesanan pelanggan sering tertukar.',
                'Tidak tahu pasti keuntungan bersih per cup kopi setelah dipotong cup, sedotan, dan es batu.'
            ],
            'features' => [
                ['title' => 'HPP Resep (Food Costing)', 'desc' => 'Setiap 1 cup espresso terjual, stok beans terpotong otomatis sesuai gramatur resep.'],
                ['title' => 'Manajemen Meja & Split Bill', 'desc' => 'Dukung pesan antar meja, simpan pesanan sementara, dan pisah tagihan antar pengunjung.'],
                ['title' => 'Dukungan Printer Thermal Bluetooth', 'desc' => 'Cetak struk kasir dan order tiket dapur (Kitchen Slip) langsung dari HP atau tablet.'],
                ['title' => 'Laporan Jam Sibuk (Peak Hours)', 'desc' => 'Ketahui jam berapa kedai Anda paling ramai untuk mengatur jadwal shift barista dengan optimal.'],
            ],
            'testimonial' => [
                'quote' => 'Food cost kafe kami akhirnya terkontrol rapi di angka 27%. Susu dan beans tidak pernah lagi bocor misterius di akhir shift.',
                'author' => 'Rian Hidayat',
                'business' => 'Kopi Sudut Santai, Bandung'
            ]
        ],
        'kasir-kios' => [
            'slug' => 'kasir-kios',
            'title' => 'Aplikasi Kasir Konter HP, Pulsa & Kios Aksesoris',
            'badge' => 'Solusi Kios & Konter',
            'headline' => 'Kasir Cepat Transaksi Kilat, Pantau Ribuan Aksesoris Tanpa Salah Harga',
            'subheadline' => 'Mudahkan kasir konter HP dan kios aksesoris mencari ribuan tipe casing, kabel data, tempered glass, dan catat jasa servis HP dalam satu layar praktis.',
            'pain_points' => [
                'Karyawan kasir sering salah kasih harga karena tipe tempered glass dan casing terlalu banyak.',
                'Status penerimaan servis HP berantakan dan rawan tertukar onderdilnya.',
                'Stok barang kecil-kecil sering hilang atau tidak cocok dengan uang di laci kasir.'
            ],
            'features' => [
                ['title' => 'Pencarian Cepat Nama / SKU', 'desc' => 'Ketik 2 huruf langsung muncul rekomendasi tipe HP dan harga jual grosir vs eceran.'],
                ['title' => 'Modul Tanda Terima Servis', 'desc' => 'Cetak nota penerimaan servis HP lengkap dengan keluhan pelanggan dan estimasi biaya.'],
                ['title' => 'Cegah Kasir Curang', 'desc' => 'Kunci akses diskon dan laporan omzet agar hanya pemilik toko yang bisa melihat keuntungan.'],
                ['title' => 'Rekap Kas Harian Tanpa Selisih', 'desc' => 'Sistem shift kasir yang otomatis menghitung selisih uang fisik vs mutasi penjualan.'],
            ],
            'testimonial' => [
                'quote' => 'Penjualan aksesoris dan servis HP sekarang terpantau rapi. Karyawan baru tidak perlu lagi menghafal harga ratusan tipe casing.',
                'author' => 'Deni Pratama',
                'business' => 'Galaxy Cell & Accessories, Surabaya'
            ]
        ],
        'kasir-laundry' => [
            'slug' => 'kasir-laundry',
            'title' => 'Aplikasi Kasir Laundry Kiloan & Satuan Modern',
            'badge' => 'Solusi Laundry',
            'headline' => 'Kelola Status Cucian, Kirim Nota WhatsApp Otomatis, & Cegah Baju Tertukar',
            'subheadline' => 'Tingkatkan kepercayaan pelanggan laundry Anda. Pelanggan otomatis dapat notifikasi WhatsApp saat cucian selesai, timbangan kiloan akurat, dan rak penyimpanan terdata rapi.',
            'pain_points' => [
                'Nota kertas hilang atau luntur terkena air deterjen, memicu komplain baju hilang.',
                'Pelanggan bolak-balik tanya di WhatsApp apakah cucian mereka sudah selesai atau belum.',
                'Kesulitan melacak performa setrika dan cuci karyawan borongan.'
            ],
            'features' => [
                ['title' => 'Tracking Status Pengerjaan', 'desc' => 'Status jelas: Diterima → Dicuci → Disetrika → Siap Ambil → Selesai.'],
                ['title' => 'Nota WhatsApp Otomatis', 'desc' => 'Kirim nota digital beserta rincian jumlah potong pakaian langsung ke nomor WA pelanggan.'],
                ['title' => 'Manajemen Rak Pengambilan', 'desc' => 'Catat nomor rak penyimpanan cucian agar kasir dapat mengambil paket dalam hitungan detik.'],
                ['title' => 'Paket Deposit & Langganan', 'desc' => 'Dukung sistem kupon deposit laundry (misal: bayar 50kg di awal) dengan saldo potong otomatis.'],
            ],
            'testimonial' => [
                'quote' => 'Pelanggan senang banget karena dapat pesan otomatis saat cuciannya sudah rapi dan wangi. Komplain baju hilang turun hingga 0%.',
                'author' => 'Ibu Maya Larasati',
                'business' => 'Fresh Clean Laundry, Sleman'
            ]
        ],
        'kasir-salon' => [
            'slug' => 'kasir-salon',
            'title' => 'Sistem Kasir Salon Kecantikan & Perawatan Wanita',
            'badge' => 'Solusi Salon & Beauty',
            'headline' => 'Hitung Komisi Kapster Otomatis, Jadwal Treatment, & Kasir Layanan',
            'subheadline' => 'Hilangkan perdebatan pembagian komisi karyawan salon di akhir bulan. Otomatiskan komisi treatment, kelola riwayat perawatan pelanggan, dan pantau stok produk kecantikan.',
            'pain_points' => [
                'Penghitungan komisi terapis/kapster di akhir bulan memakan waktu berhari-hari.',
                'Lupa riwayat formula cat rambut atau jenis facial yang pernah dipakai pelanggan tetap.',
                'Stok serum, cat rambut, dan vitamin sering terpakai tanpa tercatat pemakaiannya.'
            ],
            'features' => [
                ['title' => 'Bagi Hasil & Komisi Fleksibel', 'desc' => 'Tentukan persentase atau nominal komisi per jenis layanan secara otomatis per kapster.'],
                ['title' => 'Rekam Medis & Riwayat Pelanggan', 'desc' => 'Catat preferensi pelanggan, alergi, atau warna cat rambut terakhir yang digunakan.'],
                ['title' => 'Paket Treatment Bundling', 'desc' => 'Buat paket hemat (misal: Creambath + Manicure) dengan harga khusus promosi.'],
                ['title' => 'Pengurangan Bahan Treatment', 'desc' => 'Stok produk perawatan salon terpotong otomatis setiap kali layanan treatment selesai.'],
            ],
            'testimonial' => [
                'quote' => 'Dulu tiap akhir bulan pusing hitung buku komisi 8 kapster. Sekarang laporan komisi langsung keluar dalam satu klik.',
                'author' => 'Sisca Wijaya',
                'business' => 'Glow Beauty Salon, Tangerang'
            ]
        ],
        'kasir-barbershop' => [
            'slug' => 'kasir-barbershop',
            'title' => 'Aplikasi Kasir Barbershop & Pangkas Rambut Pria',
            'badge' => 'Solusi Barbershop',
            'headline' => 'Kelola Antrean Cepat, Catat Komisi Barberman, & Jual Pomade Lebih Laris',
            'subheadline' => 'Aplikasi POS modern khusus pangkas rambut pria. Layar kasir cepat, pencatatan komisi per kepala potong, dan laporan penjualan pomade/hair tonic.',
            'pain_points' => [
                'Barberman sering mencurigai pencatatan kasir saat bagi hasil mingguan.',
                'Pelanggan bosan mengantre tanpa kejelasan giliran kursi pangkas.',
                'Produk grooming pria (pomade, clay) tidak terpantau stok dan profitnya.'
            ],
            'features' => [
                ['title' => 'Antrean Kursi & Pemilihan Barber', 'desc' => 'Pelanggan bisa memilih barberman favorit mereka di layar kasir.'],
                ['title' => 'Laporan Komisi Barberman Real-time', 'desc' => 'Setiap barberman bisa mengecek jumlah kepala potong yang mereka selesaikan hari ini.'],
                ['title' => 'Penjualan Produk Retail Grooming', 'desc' => 'Gabungkan tagihan jasa potong rambut dan pembelian pomade dalam satu struk transaksi.'],
                ['title' => 'Program Member & Poin Loyalty', 'desc' => 'Beri reward potong rambut gratis ke-10 untuk meningkatkan retensi pelanggan tetap.'],
            ],
            'testimonial' => [
                'quote' => 'Semua barberman puas karena sistemnya transparan. Tidak ada lagi saling tuduh salah hitung komisi harian.',
                'author' => 'Aldo Pramono',
                'business' => 'The Gentleman Barbershop, Semarang'
            ]
        ],
        'kasir-bengkel-kecil' => [
            'slug' => 'kasir-bengkel-kecil',
            'title' => 'Aplikasi Kasir Bengkel Motor & Toko Sparepart',
            'badge' => 'Solusi Bengkel Kecil',
            'headline' => 'Gabungkan Jasa Servis Mekanik dan Penjualan Sparepart dalam Satu Struk',
            'subheadline' => 'Software bengkel motor paling mudah digunakan. Pisahkan komisi ongkos pasang montir dengan laba penjualan oli dan onderdil motor secara presisi.',
            'pain_points' => [
                'Oli dan sparepart sering hilang dari rak karena tidak pernah dihitung persediaannya.',
                'Struk tulisan tangan montir sering tidak terbaca atau hilang saat konsumen komplain.',
                'Sulit memisahkan pendapatan murni toko dengan upah bagi hasil mekanik.'
            ],
            'features' => [
                ['title' => 'Struk Gabungan Jasa + Sparepart', 'desc' => 'Tampilkan rincian biaya oli + kampas rem + jasa servis mekanik dalam satu struk rapi.'],
                ['title' => 'Komisi Jasa Mekanik Otomatis', 'desc' => 'Atur komisi montir per jasa (misal: ganti oli dapat Rp 5.000, servis besar Rp 35.000).'],
                ['title' => 'Pencatatan Nomor Polisi & Motor', 'desc' => 'Simpan riwayat servis berdasarkan plat nomor kendaraan untuk pengingat servis berkala.'],
                ['title' => 'Peringatan Restock Sparepart Menipis', 'desc' => 'Dapatkan notifikasi saat stok busi atau kampas rem tersisa di bawah batas minimum.'],
            ],
            'testimonial' => [
                'quote' => 'Buku montir yang belepotan oli sekarang sudah diganti tablet kasir Cooca. Pelanggan kagum bengkel motor kecil kami struknya sangat profesional.',
                'author' => 'Hengky Kurniawan',
                'business' => 'Maju Jaya Motor, Malang'
            ]
        ],
        'kasir-irt' => [
            'slug' => 'kasir-irt',
            'title' => 'Software Usaha Industri Rumah Tangga (IRT) & Produsen Snack',
            'badge' => 'Solusi Produsen IRT',
            'headline' => 'Hitung HPP Resep Produksi, Catat Penjualan Konsinyasi, & Kunci Keuntungan',
            'subheadline' => 'Didesain khusus untuk produsen keripik, kue basah, katering, dan kerajinan rumah tangga. Hitung modal bahan baku, kemasan, gas, dan pantau barang titipan di toko mitra.',
            'pain_points' => [
                'Merasa jualan laku keras ratusan bungkus, tapi uang modal selalu habis tidak tahu ke mana.',
                'Barang konsinyasi yang dititipkan di toko atau warung mitra sering tidak jelas rekapan uangnya.',
                'Kenaikan harga tepung, telur, dan minyak membuat harga jual rugi tanpa disadari.'
            ],
            'features' => [
                ['title' => 'Kalkulator HPP Produksi Resep', 'desc' => 'Hitung modal bersih per toples atau per bungkus snack hingga detail kemasan dan label stiker.'],
                ['title' => 'Manajemen Titip Jual (Konsinyasi)', 'desc' => 'Catat berapa bungkus keripik yang dititipkan, berapa yang laku, dan sisa yang diretur.'],
                ['title' => 'Analisa Margin Reseller & Agen', 'desc' => 'Atur skema harga bertingkat: harga konsumen langsung, harga reseller, dan harga agen grosir.'],
                ['title' => 'Laporan Laba Kotor per Varian Rasa', 'desc' => 'Ketahui varian snack mana yang paling mendatangkan keuntungan terbesar bagi dapur Anda.'],
            ],
            'testimonial' => [
                'quote' => 'Dulu asal tembak harga jual kue kering Rp 35.000 per toples. Setelah hitung HPP di Cooca, ternyata modalnya Rp 28.000! Untung cepat sadar sebelum rugi besar.',
                'author' => 'Ibu Ratna Dewi',
                'business' => 'Dapur Kue Mama Ratna, Solo'
            ]
        ],
    ];

    /**
     * Show specific vertical solution page.
     */
    public function show(string $slug): View
    {
        abort_unless(isset($this->solutions[$slug]), 404);

        return view('public.solutions.show', [
            'solution' => $this->solutions[$slug],
            'otherSolutions' => collect($this->solutions)->where('slug', '!=', $slug)->take(4),
        ]);
    }
}
