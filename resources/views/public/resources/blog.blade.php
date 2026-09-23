@extends('public.partials.subpage_layout', [
    'title' => 'Blog & Edukasi Bisnis',
    'category' => 'Resources',
    'badge' => 'Wawasan Bisnis',
    'icon' => 'book-open',
    'headline' => 'Artikel, Tips Operasional, & Berita Industri UMKM Terkini',
    'subtitle' => 'Pelajari strategi praktis meningkatkan penjualan toko, tips manajemen keuangan, studi kasus operasional, dan tren bisnis terkini.',
    'features' => [
        ['icon' => 'trending-up', 'title' => 'Strategi Scale-Up Bisnis', 'desc' => 'Panduan terbukti bagaimana pemilik bisnis mengembangkan usaha dari 1 toko menjadi multi-cabang.'],
        ['icon' => 'calculator', 'title' => 'Manajemen Arus Kas & Finansial', 'desc' => 'Teknik menjaga likuiditas kas toko, meminimalisir piutang macet, dan menghitung laba bersih.'],
        ['icon' => 'share-2', 'title' => 'Social Media & WhatsApp Marketing', 'desc' => 'Cara meningkatkan followers menjadi pembeli loyal lewat teknik promosi WhatsApp dan konten video.'],
        ['icon' => 'shopping-cart', 'title' => 'Optimalisasi Penjualan Kasir POS', 'desc' => 'Tips mempercepat transaksi checkout jam sibuk dan mengurangi tingkat antrean kasir.'],
        ['icon' => 'shield-alert', 'title' => 'Pencegahan Kebocoran Stok', 'desc' => 'Mengenali titik rawan kehilangan barang di gudang dan cara mengaudit fisik persediaan secara presisi.'],
        ['icon' => 'award', 'title' => 'Kisah Sukses Pengusaha Lokal', 'desc' => 'Inspirasi perjalanan wirausaha UMKM Indonesia yang sukses menembus omzet ratusan juta.'],
    ]
])

@section('subpage_content')
<div class="p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm text-center space-y-4">
    <h3 class="text-xl font-bold text-[#1D1D1F] dark:text-[#F5F5F7]">Kunjungi Arsip Artikel Blog Lengkap</h3>
    <p class="text-sm text-[#6E6E73] dark:text-[#86868B] max-w-xl mx-auto">
        Jelajahi seluruh koleksi panduan dan artikel mendalam yang ditulis oleh para praktisi bisnis kami.
    </p>
    <a href="{{ route('blog.index') }}"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-[14px] bg-[#007AFF] text-white font-semibold text-xs shadow-sm hover:bg-[#0071E3] transition">
        <span>Buka Halaman Blog Utama</span>
        <i data-lucide="arrow-right" class="w-4 h-4"></i>
    </a>
</div>
@endsection
