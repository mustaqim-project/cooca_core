<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TemplateLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicTemplateController extends Controller
{
    /**
     * Predefined list of high-value templates.
     */
    private array $templates = [
        'pembukuan-warung-excel' => [
            'slug' => 'pembukuan-warung-excel',
            'name' => 'Template Pembukuan Warung & Toko Kelontong (Excel)',
            'category' => 'Buku Kas',
            'format' => 'XLSX / Google Sheets',
            'description' => 'Format siap pakai untuk mencatat kas masuk harian, kas keluar, belanja kulakan warung, dan saldo kas otomatis.',
            'highlights' => [
                'Buku kas masuk & keluar otomatis',
                'Formula rekap saldo harian tanpa rumus rumit',
                'Daftar hutang / bon pelanggan warung',
                'Grafik ringkas pengeluaran bulanan'
            ],
            'file_name' => 'Template_Pembukuan_Warung_COOCA.xlsx',
        ],
        'laporan-keuangan-sederhana' => [
            'slug' => 'laporan-keuangan-sederhana',
            'name' => 'Template Laporan Keuangan Sederhana UMKM',
            'category' => 'Laporan Finansial',
            'format' => 'XLSX / Google Sheets',
            'description' => 'Template laporan laba rugi bulanan dan neraca mini untuk mengajukan pinjaman bank (KUR) atau evaluasi investor.',
            'highlights' => [
                'Format standar Laporan Laba Rugi (Income Statement)',
                'Ringkasan Aset, Kas, dan Kewajiban usaha',
                'Grafik margin kotor vs margin bersih',
                'Dapat langsung dicetak ke PDF'
            ],
            'file_name' => 'Template_Laporan_Keuangan_UMKM_COOCA.xlsx',
        ],
        'stok-opname-excel' => [
            'slug' => 'stok-opname-excel',
            'name' => 'Template Stok Opname & Kartu Persediaan Barang',
            'category' => 'Inventori',
            'format' => 'XLSX / Google Sheets',
            'description' => 'Cegah kehilangan barang dan deteksi selisih antara stok di catatan dengan stok fisik di rak toko Anda.',
            'highlights' => [
                'Pencatatan saldo awal, masuk, keluar, dan sisa stok',
                'Kolom selisih fisik otomatis (anomali stok)',
                'Perhitungan nilai aset persediaan berdasarkan harga beli',
                'Highlight otomatis untuk stok yang menipis (Restock alert)'
            ],
            'file_name' => 'Template_Stok_Opname_COOCA.xlsx',
        ],
        'invoice-sederhana' => [
            'slug' => 'invoice-sederhana',
            'name' => 'Template Invoice & Nota Pembayaran Sederhana',
            'category' => 'Faktur & Kasir',
            'format' => 'Web Generator / Direct App',
            'description' => 'Buat invoice profesional dan nota digital dalam hitungan detik. Dilengkapi nomor rekening, QRIS, dan rincian pajak.',
            'highlights' => [
                'Format invoice profesional berlogo toko',
                'Kalkulasi otomatis subtotal, diskon, dan PPN',
                'Nomor invoice unik dan tanggal jatuh tempo',
                'Bisa langsung dibagikan ke WhatsApp pelanggan'
            ],
            'file_name' => 'Template_Invoice_COOCA.xlsx',
        ],
    ];

    /**
     * Index of all free templates.
     */
    public function index(): View
    {
        return view('public.templates.index', [
            'templates' => $this->templates,
        ]);
    }

    /**
     * Detail & lead capture page for a specific template.
     */
    public function show(string $slug): View
    {
        abort_unless(isset($this->templates[$slug]), 404);

        return view('public.templates.show', [
            'template' => $this->templates[$slug],
            'otherTemplates' => collect($this->templates)->where('slug', '!=', $slug)->take(3),
        ]);
    }

    /**
     * Submit lead capture form and deliver template.
     */
    public function captureLead(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        abort_unless(isset($this->templates[$slug]), 404);
        $template = $this->templates[$slug];

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:120',
            'business_name' => 'nullable|string|max:120',
        ]);

        TemplateLead::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'business_name' => $validated['business_name'] ?? null,
            'template_slug' => $slug,
            'template_name' => $template['name'],
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih! File template siap diunduh.',
                'download_url' => asset('downloads/' . $template['file_name']),
                'file_name' => $template['file_name'],
            ]);
        }

        return back()->with('download_success', true)
            ->with('download_url', asset('downloads/' . $template['file_name']))
            ->with('file_name', $template['file_name']);
    }
}
