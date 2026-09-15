<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

final class ExcelTemplate extends Model
{
    use HasFactory;

    protected $table = 'excel_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'format',
        'description',
        'highlights',
        'file_path',
        'file_name',
        'file_size',
        'downloads_count',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'highlights' => 'array',
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'downloads_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public static function availableCategories(): array
    {
        return [
            'Buku Kas',
            'Laporan Finansial',
            'Inventori',
            'Faktur & Kasir',
            'Operasional & SDM',
            'Pajak UMKM',
            'Lainnya',
        ];
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 1) . ' ' . ($units[$power] ?? 'B');
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('template.file', $this->slug);
    }

    /**
     * Populate initial starter templates from public/downloads if table is empty.
     */
    public static function seedDefaultTemplatesIfEmpty(): void
    {
        if (self::count() > 0) {
            return;
        }

        $defaults = [
            [
                'slug' => 'pembukuan-warung-excel',
                'name' => 'Template Pembukuan Warung & Toko Kelontong (Excel)',
                'category' => 'Buku Kas',
                'format' => 'XLSX',
                'description' => 'Format siap pakai untuk mencatat kas masuk harian, kas keluar, belanja kulakan warung, dan saldo kas otomatis.',
                'highlights' => [
                    'Buku kas masuk & keluar otomatis',
                    'Formula rekap saldo harian tanpa rumus rumit',
                    'Daftar hutang / bon pelanggan warung',
                    'Grafik ringkas pengeluaran bulanan',
                ],
                'file_name' => 'Template_Pembukuan_Warung_COOCA.xlsx',
                'sort_order' => 1,
            ],
            [
                'slug' => 'laporan-keuangan-sederhana',
                'name' => 'Template Laporan Keuangan Sederhana UMKM',
                'category' => 'Laporan Finansial',
                'format' => 'XLSX',
                'description' => 'Template laporan laba rugi bulanan dan neraca mini untuk mengajukan pinjaman bank (KUR) atau evaluasi investor.',
                'highlights' => [
                    'Format standar Laporan Laba Rugi (Income Statement)',
                    'Ringkasan Aset, Kas, dan Kewajiban usaha',
                    'Grafik margin kotor vs margin bersih',
                    'Dapat langsung dicetak ke PDF',
                ],
                'file_name' => 'Template_Laporan_Keuangan_UMKM_COOCA.xlsx',
                'sort_order' => 2,
            ],
            [
                'slug' => 'stok-opname-excel',
                'name' => 'Template Stok Opname & Kartu Persediaan Barang',
                'category' => 'Inventori',
                'format' => 'XLSX',
                'description' => 'Cegah kehilangan barang dan deteksi selisih antara stok di catatan dengan stok fisik di rak toko Anda.',
                'highlights' => [
                    'Pencatatan saldo awal, masuk, keluar, dan sisa stok',
                    'Kolom selisih fisik otomatis (anomali stok)',
                    'Perhitungan nilai aset persediaan berdasarkan harga beli',
                    'Highlight otomatis untuk stok yang menipis (Restock alert)',
                ],
                'file_name' => 'Template_Stok_Opname_COOCA.xlsx',
                'sort_order' => 3,
            ],
            [
                'slug' => 'invoice-sederhana',
                'name' => 'Template Invoice & Nota Pembayaran Sederhana',
                'category' => 'Faktur & Kasir',
                'format' => 'XLSX',
                'description' => 'Buat invoice profesional dan nota digital dalam hitungan detik. Dilengkapi nomor rekening, QRIS, dan rincian pajak.',
                'highlights' => [
                    'Format invoice profesional berlogo toko',
                    'Kalkulasi otomatis subtotal, diskon, dan PPN',
                    'Nomor invoice unik dan tanggal jatuh tempo',
                    'Bisa langsung dibagikan ke WhatsApp pelanggan',
                ],
                'file_name' => 'Template_Invoice_COOCA.xlsx',
                'sort_order' => 4,
            ],
        ];

        Storage::disk('public')->makeDirectory('templates');

        foreach ($defaults as $item) {
            $sourcePath = public_path('downloads/' . $item['file_name']);
            $targetRelative = 'templates/' . $item['file_name'];
            $fileSize = 0;

            if (File::exists($sourcePath)) {
                $fileSize = File::size($sourcePath);
                Storage::disk('public')->put($targetRelative, File::get($sourcePath));
            }

            self::create([
                'slug' => $item['slug'],
                'name' => $item['name'],
                'category' => $item['category'],
                'format' => $item['format'],
                'description' => $item['description'],
                'highlights' => $item['highlights'],
                'file_path' => $targetRelative,
                'file_name' => $item['file_name'],
                'file_size' => $fileSize,
                'downloads_count' => 0,
                'is_active' => true,
                'sort_order' => $item['sort_order'],
            ]);
        }
    }
}
