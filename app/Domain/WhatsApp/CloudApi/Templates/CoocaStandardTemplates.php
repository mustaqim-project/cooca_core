<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi\Templates;

use App\Domain\WhatsApp\CloudApi\WhatsAppClient;
use App\Models\WhatsAppMessageTemplate;
use Illuminate\Support\Facades\Log;

/**
 * Class CoocaStandardTemplates
 *
 * Repositori dan spesifikasi resmi Message Templates Meta WhatsApp Cloud API v26.0
 * untuk seluruh ekosistem platform COOCA ERP & POS.
 *
 * Menjamin kepatuhan standar Meta:
 * - Kategori ketat (UTILITY, MARKETING, AUTHENTICATION)
 * - Penamaan alfanumerik huruf kecil & garis bawah (regex: /^[a-z0-9_]+$/)
 * - Variabel berurut ascending ({{1}}, {{2}}, dst.)
 * - Contoh kontekstual wajib (examples payload) untuk persetujuan otomatis AI Meta
 * - Footer Opt-Out wajib untuk kategori MARKETING ("Balas STOP untuk berhenti")
 * - Proteksi Anti-Blokir: mencegah penolakan pesan re-engagement di luar 24 jam.
 */
class CoocaStandardTemplates
{
    public const RECEIPT              = 'cooca_pos_receipt';
    public const INVOICE              = 'cooca_sales_invoice';
    public const ORDER_STATUS         = 'cooca_order_status_update';
    public const PROMO_BROADCAST      = 'cooca_promo_broadcast';
    public const CUSTOMER_WELCOME     = 'cooca_customer_welcome';
    public const PAYMENT_REMINDER     = 'cooca_payment_reminder';
    public const OTP                  = 'cooca_otp';
    public const RESERVATION_REMINDER = 'cooca_reservation_reminder';
    public const MARKETPLACE_RECEIPT  = 'cooca_marketplace_receipt';
    public const SHIPPING_TRACKING    = 'cooca_shipping_tracking';
    public const CART_REMINDER        = 'cooca_cart_reminder';

    /**
     * Dapatkan definisi seluruh template resmi standar Cooca untuk Meta Cloud API.
     *
     * @return array<string, array{
     *   name: string,
     *   category: 'UTILITY'|'MARKETING'|'AUTHENTICATION',
     *   language: string,
     *   display_title: string,
     *   description: string,
     *   components: array,
     *   sample_values: array<int, string>,
     *   variables: array<string, string>,
     *   applicable_modules: array<int, string>
     * }>
     */
    public static function all(): array
    {
        return [
            self::RECEIPT => [
                'name'               => self::RECEIPT,
                'category'           => 'UTILITY',
                'language'           => 'id',
                'display_title'      => 'Struk Pembelian Kasir POS (Resmi)',
                'description'        => 'Struk digital transaksi kasir POS otomatis. Dikirim langsung setelah pembayaran selesai.',
                'applicable_modules' => ['pos', 'orders', 'sales'],
                'variables'          => [
                    '{{1}}' => 'Nama Pelanggan',
                    '{{2}}' => 'Nama Toko / Bisnis',
                    '{{3}}' => 'Nomor Struk Transaksi',
                    '{{4}}' => 'Waktu Transaksi (Tanggal & Jam)',
                    '{{5}}' => 'Total Pembayaran',
                ],
                'sample_values' => [
                    'Budi Santoso',
                    'Kopi Kenangan Sejahtera',
                    'POS-2026/10/001',
                    '01/10/2026 14:30',
                    'Rp 45.000',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Struk Pembelian Kasir',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Terima kasih telah berbelanja di *{{2}}*.\n\nRincian transaksi Anda:\n• No. Struk: *{{3}}*\n• Waktu: *{{4}}*\n• Total Bayar: *{{5}}*\n\nStruk digital transaksi Anda tersimpan aman di sistem kami.",
                        'example' => [
                            'body_text' => [
                                ['Budi Santoso', 'Kopi Kenangan Sejahtera', 'POS-2026/10/001', '01/10/2026 14:30', 'Rp 45.000'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Layanan Kasir Resmi Cooca POS',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Buka Struk Digital',
                                'url'  => 'https://cooca.id/r/{{1}}',
                                'example' => ['https://cooca.id/r/POS-2026-10-001'],
                            ],
                        ],
                    ],
                ],
            ],

            self::INVOICE => [
                'name'               => self::INVOICE,
                'category'           => 'UTILITY',
                'language'           => 'id',
                'display_title'      => 'Faktur Penjualan & Tagihan (Invoice)',
                'description'        => 'Notifikasi faktur penjualan B2B dan invoice tempo pelanggan lengkap dengan rincian jatuh tempo.',
                'applicable_modules' => ['b2b_sales', 'invoices', 'purchasing'],
                'variables'          => [
                    '{{1}}' => 'Nama Klien / Pembeli',
                    '{{2}}' => 'Nama Perusahaan Penerbit',
                    '{{3}}' => 'Nomor Invoice Faktur',
                    '{{4}}' => 'Tanggal Jatuh Tempo',
                    '{{5}}' => 'Total Nilai Tagihan',
                ],
                'sample_values' => [
                    'CV Maju Jaya Abadi',
                    'PT Logistik Nusantara',
                    'INV-2026/09/108',
                    '15/10/2026',
                    'Rp 2.500.000',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Faktur Tagihan Resmi',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Yth. *{{1}}*, berikut rincian tagihan faktur dari *{{2}}*:\n• No. Invoice: *{{3}}*\n• Jatuh Tempo: *{{4}}*\n• Total Tagihan: *{{5}}*\n\nSilakan lakukan pembayaran sebelum tanggal jatuh tempo. Terima kasih atas kerja samanya.",
                        'example' => [
                            'body_text' => [
                                ['CV Maju Jaya Abadi', 'PT Logistik Nusantara', 'INV-2026/09/108', '15/10/2026', 'Rp 2.500.000'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Sistem Akuntansi Terpadu Cooca',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Lihat / Bayar Invoice',
                                'url'  => 'https://cooca.id/inv/{{1}}',
                                'example' => ['https://cooca.id/inv/INV-2026-09-108'],
                            ],
                        ],
                    ],
                ],
            ],

            self::ORDER_STATUS => [
                'name'               => self::ORDER_STATUS,
                'category'           => 'UTILITY',
                'language'           => 'id',
                'display_title'      => 'Pembaruan Status Pesanan (Multi-Industri)',
                'description'        => 'Notifikasi progres pesanan toko online, pengerjaan servis bengkel, SPK produksi, laundry, dsb.',
                'applicable_modules' => ['services', 'storefront', 'orders', 'workshop'],
                'variables'          => [
                    '{{1}}' => 'Nama Pelanggan',
                    '{{2}}' => 'Nama Bisnis / Toko',
                    '{{3}}' => 'No. Referensi / SPK / Nopol',
                    '{{4}}' => 'Status Pengerjaan',
                    '{{5}}' => 'Catatan / Tindak Lanjut',
                ],
                'sample_values' => [
                    'Siti Rahma',
                    'Bengkel Motor Berkah',
                    'SPK-9921 (B 1234 XYZ)',
                    'Selesai Dikerjakan',
                    'Kendaraan telah lulus uji & siap diambil di bengkel',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Pembaruan Status Pesanan',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Berikut update status pesanan Anda di *{{2}}*:\n• Referensi: *{{3}}*\n• Status Saat Ini: *{{4}}*\n• Detail / Catatan: *{{5}}*\n\nTerima kasih atas kepercayaan Anda!",
                        'example' => [
                            'body_text' => [
                                ['Siti Rahma', 'Bengkel Motor Berkah', 'SPK-9921 (B 1234 XYZ)', 'Selesai Dikerjakan', 'Kendaraan telah lulus uji & siap diambil di bengkel'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Notifikasi Otomatis Bisnis Cooca',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Lihat Detail Pesanan',
                                'url'  => 'https://cooca.id/order/{{1}}',
                                'example' => ['https://cooca.id/order/SPK-9921'],
                            ],
                        ],
                    ],
                ],
            ],

            self::PROMO_BROADCAST => [
                'name'               => self::PROMO_BROADCAST,
                'category'           => 'MARKETING',
                'language'           => 'id',
                'display_title'      => 'Siaran Promosi & Voucher Diskon (Anti-Blokir Meta)',
                'description'        => 'Kirim blast penawaran diskon, flash sale, dan promo gajian ke seluruh pelanggan dengan jaminan lolos review Meta.',
                'applicable_modules' => ['broadcast', 'marketing', 'crm'],
                'variables'          => [
                    '{{1}}' => 'Nama Pelanggan',
                    '{{2}}' => 'Nama Bisnis / Toko',
                    '{{3}}' => 'Rincian Penawaran Promo',
                    '{{4}}' => 'Kode Kupon / Voucher',
                    '{{5}}' => 'Batas Waktu Berlaku',
                ],
                'sample_values' => [
                    'Pelanggan Setia',
                    'Kopi Kenangan Sejahtera',
                    'Dapatkan diskon 30% untuk seluruh menu makanan & minuman pilihan!',
                    'HEMAT30',
                    '05/10/2026',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Promo Spesial Pelanggan Setia',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Nikmati penawaran spesial eksklusif dari *{{2}}*:\n\n{{3}}\n\nGunakan kode voucher: *{{4}}* saat bertransaksi untuk menikmati diskon ini.\n\nPromo berlaku hingga *{{5}}*. Jangan sampai terlewat!",
                        'example' => [
                            'body_text' => [
                                ['Pelanggan Setia', 'Kopi Kenangan Sejahtera', 'Dapatkan diskon 30% untuk seluruh menu makanan & minuman pilihan!', 'HEMAT30', '05/10/2026'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Balas STOP untuk berhenti menerima info promo',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Kunjungi Toko',
                                'url'  => 'https://cooca.id/shop/{{1}}',
                                'example' => ['https://cooca.id/shop/kopi-kenangan'],
                            ],
                        ],
                    ],
                ],
            ],

            self::CUSTOMER_WELCOME => [
                'name'               => self::CUSTOMER_WELCOME,
                'category'           => 'MARKETING',
                'language'           => 'id',
                'display_title'      => 'Sambutan Member Baru & Loyalitas Poin',
                'description'        => 'Sambut pelanggan baru yang terdaftar di toko dan informasikan saldo reward loyalty poin mereka.',
                'applicable_modules' => ['crm', 'loyalty', 'pos'],
                'variables'          => [
                    '{{1}}' => 'Nama Member Baru',
                    '{{2}}' => 'Nama Toko / Bisnis',
                    '{{3}}' => 'Tier Keanggotaan',
                    '{{4}}' => 'Saldo Poin Awal',
                ],
                'sample_values' => [
                    'Budi Santoso',
                    'Kopi Kenangan Sejahtera',
                    'Gold Member',
                    '250',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Selamat Datang Member Baru',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Terima kasih telah bergabung sebagai member setia di *{{2}}*.\n\nStatus keanggotaan Anda: *{{3}}* dengan total perolehan *{{4}} Poin*.\n\nKumpulkan poin di setiap transaksi dan tukarkan dengan berbagai reward menarik!",
                        'example' => [
                            'body_text' => [
                                ['Budi Santoso', 'Kopi Kenangan Sejahtera', 'Gold Member', '250'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Balas STOP untuk berhenti berlangganan',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Cek Poin & Reward',
                                'url'  => 'https://cooca.id/rewards/{{1}}',
                                'example' => ['https://cooca.id/rewards/MEM-001'],
                            ],
                        ],
                    ],
                ],
            ],

            self::PAYMENT_REMINDER => [
                'name'               => self::PAYMENT_REMINDER,
                'category'           => 'UTILITY',
                'language'           => 'id',
                'display_title'      => 'Pengingat Tagihan Pembayaran Jatuh Tempo',
                'description'        => 'Kirimkan notifikasi tagihan tempo pembayaran kepada pelanggan secara sopan dan terotomatisasi.',
                'applicable_modules' => ['invoices', 'finance', 'subscription'],
                'variables'          => [
                    '{{1}}' => 'Nama Pelanggan',
                    '{{2}}' => 'Nama Toko / Perusahaan',
                    '{{3}}' => 'No. Tagihan / Ref',
                    '{{4}}' => 'Jumlah Tagihan',
                    '{{5}}' => 'Batas Waktu Pembayaran',
                ],
                'sample_values' => [
                    'Bapak Iwan',
                    'Klinik Medika Sehat',
                    'TAG-2026/10/012',
                    'Rp 350.000',
                    '03/10/2026',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Pengingat Tagihan Pembayaran',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Yth. *{{1}}*, kami menginformasikan tagihan Anda di *{{2}}*:\n• No. Tagihan: *{{3}}*\n• Jumlah Tagihan: *{{4}}*\n• Batas Waktu: *{{5}}*\n\nMohon lakukan konfirmasi apabila Anda telah menyelesaikan pembayaran. Terima kasih.",
                        'example' => [
                            'body_text' => [
                                ['Bapak Iwan', 'Klinik Medika Sehat', 'TAG-2026/10/012', 'Rp 350.000', '03/10/2026'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Layanan Keuangan Otomatis Cooca',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Bayar Sekarang',
                                'url'  => 'https://cooca.id/pay/{{1}}',
                                'example' => ['https://cooca.id/pay/TAG-012'],
                            ],
                        ],
                    ],
                ],
            ],

            self::OTP => [
                'name'               => self::OTP,
                'category'           => 'AUTHENTICATION',
                'language'           => 'id',
                'display_title'      => 'Kode Verifikasi Keamanan OTP',
                'description'        => 'Kode verifikasi sekali pakai (One-Time Password) untuk login, reset PIN, atau otorisasi sensitif.',
                'applicable_modules' => ['auth', 'security'],
                'variables'          => [
                    '{{1}}' => 'Kode OTP (6 digit)',
                ],
                'sample_values' => [
                    '749102',
                ],
                'components' => [
                    [
                        'type' => 'BODY',
                        'text' => "*{{1}}* adalah kode verifikasi Cooca Anda. Demi keamanan akun, JANGAN bagikan kode ini kepada siapa pun termasuk pihak Cooca.",
                        'example' => [
                            'body_text' => [
                                ['749102'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Berlaku 5 menit. Jangan berikan kepada siapa pun.',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type'     => 'OTP',
                                'otp_type' => 'COPY_CODE',
                                'text'     => 'Salin Kode',
                            ],
                        ],
                    ],
                ],
            ],

            self::RESERVATION_REMINDER => [
                'name'               => self::RESERVATION_REMINDER,
                'category'           => 'UTILITY',
                'language'           => 'id',
                'display_title'      => 'Pengingat Reservasi Meja & Booking Layanan',
                'description'        => 'Pengingat jadwal reservasi meja F&B, booking servis bengkel, atau janji temu salon/klinik.',
                'applicable_modules' => ['tables', 'reservations', 'services', 'fnb', 'workshop', 'appointments'],
                'variables'          => [
                    '{{1}}' => 'Nama Pelanggan',
                    '{{2}}' => 'Nama Toko / Resto / Bisnis',
                    '{{3}}' => 'Kode Reservasi / Meja',
                    '{{4}}' => 'Jadwal Reservasi (Hari, Tgl, Jam)',
                    '{{5}}' => 'Detail Layanan / Jumlah Tamu',
                ],
                'sample_values' => [
                    'Budi Santoso',
                    'Resto Rasa Nusantara',
                    'RSV-8821',
                    'Jumat, 02/10/2026 19:00 WIB',
                    'Meja VIP 4 Orang',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Pengingat Reservasi Anda',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Mengingatkan kembali jadwal reservasi Anda di *{{2}}*:\n• Kode Booking: *{{3}}*\n• Jadwal: *{{4}}*\n• Detail: *{{5}}*\n\nMohon hadir 10 menit sebelum waktu reservasi. Hubungi kami jika ingin melakukan perubahan jadwal.",
                        'example' => [
                            'body_text' => [
                                ['Budi Santoso', 'Resto Rasa Nusantara', 'RSV-8821', 'Jumat, 02/10/2026 19:00 WIB', 'Meja VIP 4 Orang'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Layanan Reservasi Resmi Cooca',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Lihat Reservasi',
                                'url'  => 'https://cooca.id/res/{{1}}',
                                'example' => ['https://cooca.id/res/RSV-8821'],
                            ],
                        ],
                    ],
                ],
            ],

            self::MARKETPLACE_RECEIPT => [
                'name'               => self::MARKETPLACE_RECEIPT,
                'category'           => 'UTILITY',
                'language'           => 'id',
                'display_title'      => 'Bukti Bayar & Konfirmasi Pesanan Marketplace / Storefront',
                'description'        => 'Notifikasi konfirmasi pembayaran pesanan marketplace / toko online yang berhasil diverifikasi.',
                'applicable_modules' => ['marketplace', 'storefront', 'orders', 'ecommerce'],
                'variables'          => [
                    '{{1}}' => 'Nama Pembeli',
                    '{{2}}' => 'Nomor Pesanan Toko Online',
                    '{{3}}' => 'Nama Toko / Penjual',
                    '{{4}}' => 'Total Pembayaran',
                    '{{5}}' => 'Metode Pembayaran',
                ],
                'sample_values' => [
                    'Siti Rahma',
                    'ORD-2026/10/088',
                    'Toko Roti Makmur',
                    'Rp 175.000',
                    'QRIS Mandiri',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Bukti Pembayaran Berhasil',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Pembayaran Anda untuk pesanan *{{2}}* di *{{3}}* sebesar *{{4}}* via *{{5}}* telah berhasil diverifikasi.\n\nPesanan Anda saat ini sedang dikemas dan dipersiapkan oleh tim toko kami. Terima kasih atas pesanan Anda!",
                        'example' => [
                            'body_text' => [
                                ['Siti Rahma', 'ORD-2026/10/088', 'Toko Roti Makmur', 'Rp 175.000', 'QRIS Mandiri'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Konfirmasi Otomatis Cooca Storefront',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Pantau Pesanan',
                                'url'  => 'https://cooca.id/order/{{1}}',
                                'example' => ['https://cooca.id/order/ORD-2026-10-088'],
                            ],
                        ],
                    ],
                ],
            ],

            self::SHIPPING_TRACKING => [
                'name'               => self::SHIPPING_TRACKING,
                'category'           => 'UTILITY',
                'language'           => 'id',
                'display_title'      => 'Konfirmasi Pengiriman & Resi Kurir Ekspedisi',
                'description'        => 'Notifikasi paket pesanan telah dikirim via ekspedisi kurir lengkap dengan nomor resi pelacakan.',
                'applicable_modules' => ['shipping', 'logistics', 'storefront', 'marketplace'],
                'variables'          => [
                    '{{1}}' => 'Nama Penerima',
                    '{{2}}' => 'Nomor Pesanan',
                    '{{3}}' => 'Nama Toko Penjual',
                    '{{4}}' => 'Nama Ekspedisi / Kurir',
                    '{{5}}' => 'Nomor Resi Pelacakan',
                ],
                'sample_values' => [
                    'Siti Rahma',
                    'ORD-2026/10/088',
                    'Toko Roti Makmur',
                    'J&T Express',
                    'JT9988221100',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Pesanan Telah Dikirim',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Paket pesanan nomor *{{2}}* dari *{{3}}* telah diserahkan ke ekspedisi *{{4}}* dengan nomor resi: *{{5}}*.\n\nEstimasi pengiriman 1-3 hari kerja. Lacak pergerakan kurir secara langsung melalui tautan di bawah.",
                        'example' => [
                            'body_text' => [
                                ['Siti Rahma', 'ORD-2026/10/088', 'Toko Roti Makmur', 'J&T Express', 'JT9988221100'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Integrasi Logistik Resmi Cooca',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Lacak Resi Pengiriman',
                                'url'  => 'https://cooca.id/track/{{1}}',
                                'example' => ['https://cooca.id/track/JT9988221100'],
                            ],
                        ],
                    ],
                ],
            ],

            self::CART_REMINDER => [
                'name'               => self::CART_REMINDER,
                'category'           => 'MARKETING',
                'language'           => 'id',
                'display_title'      => 'Pengingat Keranjang Belanja Toko Online (Abandoned Cart)',
                'description'        => 'Follow-up otomatis ke pembeli yang belum menyelesaikan pembayaran pesanan di keranjang belanja toko online.',
                'applicable_modules' => ['storefront', 'marketplace', 'crm', 'marketing'],
                'variables'          => [
                    '{{1}}' => 'Nama Calon Pembeli',
                    '{{2}}' => 'Nama Toko Online',
                    '{{3}}' => 'Ringkasan Item Produk',
                    '{{4}}' => 'Penawaran Spesial / Voucher',
                    '{{5}}' => 'Batas Waktu Penawaran',
                ],
                'sample_values' => [
                    'Kak Siti',
                    'Toko Roti Makmur',
                    'Paket Roti Gandum & Croissant',
                    'Gratis Ongkir kupon ONGKIRFREE',
                    'Hari Ini 23:59 WIB',
                ],
                'components' => [
                    [
                        'type'   => 'HEADER',
                        'format' => 'TEXT',
                        'text'   => 'Keranjang Belanja Menunggu',
                    ],
                    [
                        'type' => 'BODY',
                        'text' => "Halo *{{1}}*! Anda masih memiliki item favorit yang tersimpan di keranjang belanja *{{2}}*:\n\n• Produk: *{{3}}*\n• Penawaran Spesial: *{{4}}*\n• Berlaku hingga: *{{5}}*\n\nYuk selesaikan pesanan Anda sekarang sebelum kehabisan stok!",
                        'example' => [
                            'body_text' => [
                                ['Kak Siti', 'Toko Roti Makmur', 'Paket Roti Gandum & Croissant', 'Gratis Ongkir kupon ONGKIRFREE', 'Hari Ini 23:59 WIB'],
                            ],
                        ],
                    ],
                    [
                        'type' => 'FOOTER',
                        'text' => 'Balas STOP untuk berhenti menerima info promo',
                    ],
                    [
                        'type'    => 'BUTTONS',
                        'buttons' => [
                            [
                                'type' => 'URL',
                                'text' => 'Lanjutkan Pembayaran',
                                'url'  => 'https://cooca.id/cart/{{1}}',
                                'example' => ['https://cooca.id/cart/CRT-001'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Dapatkan spesifikasi template spesifik berdasarkan nama.
     */
    public static function get(string $name): ?array
    {
        return self::all()[$name] ?? null;
    }

    /**
     * Kirimkan (Deploy) seluruh template standar ke Meta Cloud API v26.0 untuk WABA yang ditentukan.
     *
     * @return array{success: bool, deployed: int, errors: array<string, string>}
     */
    public static function deployAllToMeta(WhatsAppClient $client, string $wabaId): array
    {
        $all = self::all();
        $deployed = 0;
        $errors = [];

        foreach ($all as $key => $template) {
            $payload = [
                'name'                  => $template['name'],
                'category'              => $template['category'],
                'language'              => $template['language'],
                'components'            => $template['components'],
                'allow_category_change' => true,
            ];

            try {
                $res = $client->createMessageTemplate($wabaId, $payload);
                if ($res['success'] ?? false) {
                    $deployed++;
                    // Upsert ke database lokal sebagai template sistem
                    self::upsertLocalRecord($wabaId, $template, $res['id'] ?? null, 'PENDING');
                } else {
                    $errors[$template['name']] = $res['error'] ?? 'Gagal diajukan ke Meta.';
                    Log::channel('daily')->warning("[CoocaStandardTemplates] Gagal deploy template {$template['name']}: " . json_encode($res));
                }
            } catch (\Throwable $e) {
                $errors[$template['name']] = $e->getMessage();
                Log::channel('daily')->error("[CoocaStandardTemplates] Exception deploy template {$template['name']}: {$e->getMessage()}");
            }
        }

        return [
            'success'  => $deployed > 0 || empty($errors),
            'deployed' => $deployed,
            'errors'   => $errors,
        ];
    }

    /**
     * Simpan / seed seluruh template standar ke database lokal agar langsung dapat diakses merchant.
     *
     * @param string|null $wabaId
     * @param string $initialStatus Status default (default: 'APPROVED' untuk penggunaan sistem)
     * @return int Jumlah template yang disimpan / diperbarui
     */
    public static function seedLocalTemplates(?string $wabaId = null, string $initialStatus = 'APPROVED'): int
    {
        $waba = $wabaId ?: 'platform_default';
        $count = 0;

        foreach (self::all() as $template) {
            self::upsertLocalRecord($waba, $template, null, $initialStatus);
            $count++;
        }

        return $count;
    }

    /**
     * Upsert record template lokal.
     */
    public static function upsertLocalRecord(string $wabaId, array $template, ?string $metaId = null, string $status = 'APPROVED'): WhatsAppMessageTemplate
    {
        $record = WhatsAppMessageTemplate::where('name', $template['name'])
            ->where('language', $template['language'])
            ->first();

        $attributes = [
            'waba_id'          => $wabaId,
            'business_id'      => null, // Null = Global Platform Standard Template
            'meta_template_id' => $metaId ?? ($record?->meta_template_id),
            'name'             => $template['name'],
            'category'         => $template['category'],
            'language'         => $template['language'],
            'status'           => $record ? $record->status : $status,
            'components'       => $template['components'],
            'quality_score'    => 'GREEN',
            'synced_at'        => now(),
        ];

        if ($record) {
            $record->update($attributes);
            return $record;
        }

        return WhatsAppMessageTemplate::create($attributes);
    }
}
