@extends('public.storefront.layouts.app')

@php
    $pTitle = $product->name . ' | ' . $business->name;
    $rawDesc = strip_tags($product->description ?: 'Beli ' . $product->name . ' original harga terbaik hanya di ' . $business->name . '. Kualitas terjamin, stok kasir resmi & pengiriman cepat.');
    $pDesc = \Illuminate\Support\Str::limit($rawDesc, 155);
    $pCanonical = url('/' . $business->slug . '/produk/' . ($product->slug ?: $product->id));
    // Wajib: Untuk produk, mutlak prioritaskan foto/gambar produk
    $rawProdImg = $product->image_url ?: ($product->image_path ? (str_starts_with($product->image_path, 'http') ? $product->image_path : \App\Domain\Storage\TenantStorage::url($product->image_path)) : null);
    if (!empty($rawProdImg)) {
        $pOgImage = (str_starts_with($rawProdImg, 'http://') || str_starts_with($rawProdImg, 'https://'))
            ? $rawProdImg
            : (str_starts_with($rawProdImg, '/') ? url($rawProdImg) : asset($rawProdImg));
    } else {
        $pOgImage = $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/seo/cooca-og-default.jpg')));
    }
    $pPrice = (float) $product->selling_price;

    // Fase 5: Resolve active theme preset for conditional sector-specific partials
    $activeTheme = $theme ?? app(\App\Domain\Storefront\StorefrontThemeService::class)->resolveTheme($landingPage);
    $themePreset = $activeTheme['id'] ?? 'artisan_brew';
    $isArtisanBrew = $themePreset === 'artisan_brew';
@endphp

@section('title', $pTitle)
@section('description', $pDesc)
@section('canonical', $pCanonical)
@section('og_title', $product->name . ' - ' . $business->name)
@section('og_description', $pDesc)
@section('og_image', $pOgImage)
@section('og_type', 'product')
@section('product_price', $pPrice)

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Product",
  "name": "{{ addslashes($product->name) }}",
  "image": ["{{ (str_starts_with((string)$pOgImage, 'http://') || str_starts_with((string)$pOgImage, 'https://')) ? $pOgImage : url($pOgImage) }}"],
  "description": "{{ addslashes($pDesc) }}",
  "offers": {
    "@type": "Offer",
    "url": "{{ $pCanonical }}",
    "priceCurrency": "IDR",
    "price": "{{ $pPrice }}",
    "availability": "https://schema.org/InStock",
    "seller": {
      "@type": "Organization",
      "name": "{{ addslashes($business->name) }}"
    }
  }
}
</script>
@endpush

@section('content')
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-12" x-data="{
        quantity: 1,
        addedNotice: false,
        shareCopied: false,
        copyLink() {
            navigator.clipboard.writeText(window.location.href);
            this.shareCopied = true;
            setTimeout(() => this.shareCopied = false, 2500);
        },
        addToCart(redirectNow = false) {
            $store.cart.add({
                id: '{{ $product->id }}',
                name: '{{ addslashes($product->name) }}',
                price: {{ (float) $product->selling_price }},
                image_url: '{{ $product->image_url }}'
            }, this.quantity);
    
            if (redirectNow) {
                window.location.href = '{{ url('/' . $business->slug . '/checkout') }}';
            } else {
                this.addedNotice = true;
                setTimeout(() => this.addedNotice = false, 3000);
            }
        }
    }">

        {{-- Breadcrumbs --}}
        <nav class="flex items-center gap-2 text-xs text-neutral-500">
            <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
            <span>/</span>
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-theme-primary transition">Katalog</a>
            @if ($product->category)
                <span>/</span>
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $product->category->id) }}"
                    class="hover:text-theme-primary transition">
                    {{ $product->category->name }}
                </a>
            @endif
            <span>/</span>
            <span class="text-neutral-900 dark:text-white font-medium line-clamp-1">{{ $product->name }}</span>
        </nav>

        {{-- Main PDP Layout: 2 Columns (Image Gallery on Left, Details & Buy Action on Right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">

            {{-- Left: Product Image & Multi-Image Gallery Showcase --}}
            <div class="lg:col-span-6 space-y-3" x-data="{
                images: @js($product->all_images),
                activeImage: '{{ $product->image_url ?: ($product->images->first()?->image_url ?? '') }}',
                activeIndex: 0,
                selectImage(index) {
                    if (this.images[index]) {
                        this.activeIndex = index;
                        this.activeImage = this.images[index].image_url;
                    }
                },
                nextImage() {
                    if (this.images.length > 1) {
                        this.selectImage((this.activeIndex + 1) % this.images.length);
                    }
                },
                prevImage() {
                    if (this.images.length > 1) {
                        this.selectImage((this.activeIndex - 1 + this.images.length) % this.images.length);
                    }
                }
            }">
                {{-- Main Active Image Viewport --}}
                <div
                    class="relative aspect-square rounded-[24px] overflow-hidden bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/10 shadow-lg group">
                    <template x-if="activeImage">
                        <img :src="activeImage" :alt="'{{ addslashes($product->name) }}'"
                            class="w-full h-full object-cover transition duration-300">
                    </template>
                    <template x-if="!activeImage">
                        <div
                            class="w-full h-full flex flex-col items-center justify-center text-neutral-400 bg-neutral-100 dark:bg-neutral-800">
                            <i data-lucide="package" class="w-20 h-20 stroke-1 mb-2"></i>
                            <span class="text-xs font-medium">Foto Produk Resmi</span>
                        </div>
                    </template>

                    {{-- Navigation Arrows on Main Image --}}
                    <template x-if="images.length > 1">
                        <div>
                            <button type="button" @click.stop="prevImage()"
                                class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-black/80 text-white backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-md"
                                aria-label="Foto Sebelumnya">
                                <i data-lucide="chevron-left" class="w-5 h-5"></i>
                            </button>
                            <button type="button" @click.stop="nextImage()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-black/50 hover:bg-black/80 text-white backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-md"
                                aria-label="Foto Selanjutnya">
                                <i data-lucide="chevron-right" class="w-5 h-5"></i>
                            </button>
                        </div>
                    </template>

                    @if ($product->is_preorder)
                        <div
                            class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-md">
                            Sistem Pre-Order (PO)
                        </div>
                    @endif

                    {{-- Image Counter Badge --}}
                    <template x-if="images.length > 1">
                        <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/60 backdrop-blur-md text-white shadow-sm"
                            style="font-variant-numeric: tabular-nums;">
                            <span x-text="activeIndex + 1"></span>/<span x-text="images.length"></span>
                        </div>
                    </template>
                </div>

                {{-- Gallery Thumbnails Row --}}
                <template x-if="images.length > 1">
                    <div class="relative flex items-center gap-2 pt-1">
                        <div class="flex items-center gap-2.5 overflow-x-auto pb-1.5 scrollbar-none no-scrollbar w-full">
                            <template x-for="(img, idx) in images" :key="img.id || idx">
                                <button type="button"
                                    @click="selectImage(idx)"
                                    @mouseenter="selectImage(idx)"
                                    class="relative shrink-0 w-16 sm:w-20 aspect-square rounded-[14px] overflow-hidden border-2 transition-all duration-200 cursor-pointer focus:outline-none"
                                    :class="activeIndex === idx 
                                        ? 'border-emerald-500 dark:border-emerald-400 ring-2 ring-emerald-500/30 shadow-md scale-105' 
                                        : 'border-black/10 dark:border-white/10 hover:border-neutral-400 opacity-70 hover:opacity-100'">
                                    <img :src="img.image_url" :alt="img.caption || '{{ addslashes($product->name) }}'"
                                        class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Right: Product Details, Stock, Pricing & Purchase Action --}}
            <div class="lg:col-span-6 space-y-6">

                {{-- Category & Badge --}}
                <div class="flex items-center gap-2">
                    @if ($product->category)
                        <span class="px-2.5 py-1 rounded-[8px] text-xs font-semibold theme-badge">
                            {{ $product->category->name }}
                        </span>
                    @endif
                    @if ($product->code)
                        <span class="text-xs text-neutral-400" style="font-variant-numeric: tabular-nums;">SKU:
                            {{ $product->code }}</span>
                    @endif
                </div>

                {{-- Title --}}
                <h1
                    class="font-heading font-extrabold text-2xl sm:text-4xl text-neutral-900 dark:text-white tracking-tight leading-tight">
                    {{ $product->name }}
                </h1>

                {{-- Rating & Verified Review Count --}}
                <div class="flex items-center gap-2 text-xs">
                    <div class="flex items-center gap-1 text-[#FF9500]">
                        <svg class="w-4 h-4 fill-[#FF9500] text-[#FF9500]" viewBox="0 0 24 24">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                        <span class="font-bold text-sm text-neutral-900 dark:text-white tabular-nums">{{ $product->reviews_count > 0 ? $product->rating_average : '5.0' }}</span>
                    </div>
                    <span class="text-neutral-300 dark:text-neutral-700">&bull;</span>
                    <a href="#ulasan-pembeli" class="text-neutral-500 hover:text-theme-primary transition underline decoration-dotted">
                        {{ $product->reviews_count }} Ulasan Pembeli Terverifikasi
                    </a>
                </div>

                {{-- Price Display --}}
                <div
                    class="p-5 rounded-[20px] bg-neutral-50 dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-neutral-500 font-medium mb-0.5">Harga Resmi</div>
                        <div class="font-heading font-bold text-2xl sm:text-3xl text-theme-primary tracking-tight"
                            style="font-variant-numeric: tabular-nums;">
                            @if (($product->show_price_on_web ?? true) && $product->selling_price > 0)
                                Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}
                            @else
                                Hubungi Toko
                            @endif
                        </div>
                    </div>

                    {{-- Stock Status Indicator --}}
                    <div>
                        @if ($product->type === 'service')
                            <span
                                class="px-3 py-1.5 rounded-[8px] text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                Layanan Jasa
                            </span>
                        @elseif ($product->is_preorder)
                            <span
                                class="px-3 py-1.5 rounded-[8px] text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                Buka Pre-Order
                            </span>
                        @else
                            <span
                                class="px-3 py-1.5 rounded-[8px] text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Tersedia / Siap Kirim</span>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Fase 5: Coffee-specific Tasting Notes & Grind Selector (artisan_brew only) --}}
                @if ($isArtisanBrew)
                    @include('public.storefront.themes.fnb_cafe.tasting_notes', ['product' => $product])
                    @include('public.storefront.themes.fnb_cafe.grind_selector')
                @endif

                {{-- Quantity Stepper & Quick Action --}}
                @if (($product->show_price_on_web ?? true) && $product->selling_price > 0)
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Jumlah Pesanan:</span>
                            <div
                                class="flex items-center border border-black/10 dark:border-white/10 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 overflow-hidden">
                                <button type="button" @click="if (quantity > 1) quantity--"
                                    class="p-2.5 min-w-[44px] min-h-[44px] flex items-center justify-center hover:bg-neutral-200 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-300 transition">
                                    <i data-lucide="minus" class="w-4 h-4"></i>
                                </button>
                                <input type="number" x-model="quantity" min="1"
                                    class="w-14 text-center font-bold text-sm bg-transparent border-0 focus:outline-none text-neutral-900 dark:text-white">
                                <button type="button" @click="quantity++"
                                    class="p-2.5 min-w-[44px] min-h-[44px] flex items-center justify-center hover:bg-neutral-200 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-300 transition">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Action Buttons: Add to Cart & Buy Now --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <button type="button" @click="addToCart(false)"
                                class="py-3.5 px-4 rounded-[12px] bg-white dark:bg-neutral-800 border-2 border-theme-primary text-theme-primary font-bold text-sm hover:bg-neutral-50 dark:hover:bg-neutral-700 transition flex items-center justify-center gap-2 shadow-sm active:scale-[0.97] min-h-[48px]">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                <span>Tambah ke Keranjang</span>
                            </button>

                            <button type="button" @click="addToCart(true)"
                                class="py-3.5 px-4 rounded-[12px] theme-btn-primary font-bold text-sm flex items-center justify-center gap-2 shadow-md active:scale-[0.97] min-h-[48px]">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                <span>Beli Sekarang</span>
                            </button>
                        </div>

                        {{-- Floating Toast Notification --}}
                        <div x-show="addedNotice" x-cloak x-transition
                            class="p-3 rounded-[12px] bg-emerald-500 text-white text-xs font-semibold flex items-center justify-between shadow-lg">
                            <div class="flex items-center gap-2">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Produk berhasil ditambahkan ke keranjang!</span>
                            </div>
                            <a href="{{ url('/' . $business->slug . '/checkout') }}" class="underline font-bold">
                                Lihat Keranjang &rarr;
                            </a>
                        </div>
                    </div>
                @endif

                {{-- WhatsApp Direct Inquiry Button --}}
                @if ($hasWhatsapp)
                    @php
                        $waProductText = urlencode(
                            "Halo {$business->name}, saya tertarik dengan produk {$product->name} seharga Rp " .
                                number_format((float) $product->selling_price, 0, ',', '.') .
                                ' di: ' .
                                url()->current(),
                        );
                        $waLink =
                            'https://wa.me/' .
                            preg_replace('/[^0-9]/', '', $landingPage->whatsapp_number ?: $business->phone) .
                            "?text={$waProductText}";
                    @endphp
                    <div class="pt-2">
                        <a href="{{ $waLink }}" target="_blank" rel="noopener"
                            class="w-full py-3 px-4 rounded-[12px] bg-[#25D366] text-white font-semibold text-sm flex items-center justify-center gap-2 shadow-sm hover:opacity-95 transition min-h-[48px]">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Tanya Ketersediaan via WhatsApp</span>
                        </a>
                    </div>
                @endif

                {{-- Share Link & OpenGraph Tools --}}
                <div
                    class="pt-4 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-xs text-neutral-500">
                    <span class="font-medium">Bagikan Produk Ini:</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="copyLink()"
                            class="p-2 rounded-lg bg-neutral-100 dark:bg-neutral-800 hover:bg-neutral-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-300 transition flex items-center gap-1.5"
                            title="Salin Tautan Produk">
                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                            <span x-text="shareCopied ? 'Tersalin!' : 'Salin Tautan'"></span>
                        </button>
                        @if ($hasWhatsapp)
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($product->name . ' - ' . url()->current()) }}"
                                target="_blank"
                                class="p-2 rounded-lg bg-[#25D366]/10 text-[#25D366] hover:bg-[#25D366]/20 transition"
                                title="Bagikan ke WhatsApp">
                                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Detailed Description --}}
                <div class="pt-6 border-t border-black/5 dark:border-white/10 space-y-3">
                    <h2 class="font-heading font-bold text-lg text-neutral-900 dark:text-white">Deskripsi Lengkap</h2>
                    <div class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed whitespace-pre-line">
                        {{ $product->description ?: 'Tidak ada deskripsi tambahan untuk produk ini.' }}
                    </div>
                </div>

            </div>

        </div>

        {{-- Customer Reviews Section (Shopee/Tokopedia Style Bento) --}}
        <div id="ulasan-pembeli" class="pt-12 border-t border-black/5 dark:border-white/10 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">
                        Ulasan Pembeli
                    </h2>
                    <p class="text-xs text-neutral-500 mt-0.5">Ulasan dari transaksi pembeli yang telah terverifikasi sistem.</p>
                </div>

                {{-- Rating Summary Badge --}}
                <div class="flex items-center gap-3 p-3 rounded-[16px] bg-neutral-50 dark:bg-neutral-800/60 border border-black/5 dark:border-white/10">
                    <div class="text-center pr-3 border-r border-black/10 dark:border-white/10">
                        <span class="font-heading font-black text-2xl text-neutral-900 dark:text-white tabular-nums">{{ $product->reviews_count > 0 ? $product->rating_average : '5.0' }}</span>
                        <span class="text-[10px] text-neutral-400 block font-medium">dari 5</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-0.5 text-[#FF9500]">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-3.5 h-3.5 {{ ($product->rating_average ?? 5) >= $i ? 'fill-[#FF9500] text-[#FF9500]' : 'text-neutral-300 dark:text-neutral-600' }}" viewBox="0 0 24 24">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-[11px] text-neutral-500 font-semibold mt-0.5 block">
                            {{ $product->reviews_count }} Penilaian Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            @if ($product->reviews->isEmpty())
                <div class="p-8 rounded-[20px] bg-neutral-50 dark:bg-neutral-800/40 border border-black/5 dark:border-white/10 text-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mx-auto text-neutral-400">
                        <i data-lucide="message-square" class="w-5 h-5"></i>
                    </div>
                    <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">Belum Ada Ulasan</p>
                    <p class="text-xs text-neutral-500 max-w-sm mx-auto">
                        Jadilah yang pertama mencoba produk ini dan membagikan pengalaman berbelanja Anda!
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($product->reviews as $rev)
                        <div class="p-5 rounded-[18px] bg-white dark:bg-neutral-800/60 border border-black/5 dark:border-white/10 space-y-3 shadow-xs">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-theme-primary/10 text-theme-primary font-bold text-xs flex items-center justify-center">
                                        {{ strtoupper(substr($rev->customer?->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-bold text-xs text-neutral-900 dark:text-white block">
                                            {{ $rev->customer?->name ? (strlen($rev->customer->name) > 3 ? substr($rev->customer->name, 0, 2) . '***' . substr($rev->customer->name, -1) : $rev->customer->name) : 'Pelanggan Terverifikasi' }}
                                        </span>
                                        <span class="text-[10px] text-neutral-400">
                                            {{ $rev->created_at->translatedFormat('d M Y') }}
                                        </span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 flex items-center gap-1">
                                    <i data-lucide="shield-check" class="w-3 h-3"></i>
                                    <span>Verified</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-0.5 text-[#FF9500]">
                                @for ($s = 1; $s <= 5; $s++)
                                    <svg class="w-3.5 h-3.5 {{ $rev->rating >= $s ? 'fill-[#FF9500] text-[#FF9500]' : 'text-neutral-300 dark:text-neutral-600' }}" viewBox="0 0 24 24">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                    </svg>
                                @endfor
                            </div>

                            @if ($rev->review_text)
                                <p class="text-xs text-neutral-700 dark:text-neutral-300 leading-relaxed italic">
                                    &ldquo;{{ $rev->review_text }}&rdquo;
                                </p>
                            @endif

                            @if ($rev->seller_reply)
                                <div class="p-3 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/60 border border-black/5 dark:border-white/5 space-y-1">
                                    <span class="text-[10.5px] font-bold text-neutral-900 dark:text-white flex items-center gap-1">
                                        <i data-lucide="store" class="w-3 h-3 text-theme-primary"></i>
                                        <span>Respon Penjual:</span>
                                    </span>
                                    <p class="text-[11.5px] text-neutral-600 dark:text-neutral-400 leading-relaxed">
                                        {{ $rev->seller_reply }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Related Products Section --}}
        @if ($relatedProducts->isNotEmpty())
            <div class="pt-16 border-t border-black/5 dark:border-white/10 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="font-heading font-bold text-xl sm:text-2xl text-neutral-900 dark:text-white">Produk Serupa
                    </h2>
                    <a href="{{ url('/' . $business->slug . '/katalog') }}"
                        class="text-xs sm:text-sm font-semibold text-theme-primary hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($relatedProducts as $rel)
                        @php
                            $relUrl = url('/' . $business->slug . '/produk/' . ($rel->slug ?: $rel->id));
                        @endphp
                        <div
                            class="group flex flex-col rounded-[20px] overflow-hidden bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm hover:shadow-lg transition">
                            <a href="{{ $relUrl }}"
                                class="aspect-square bg-neutral-100 dark:bg-neutral-900 block overflow-hidden">
                                @if ($rel->image_url)
                                    <img src="{{ $rel->image_url }}" alt="{{ $rel->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                        <i data-lucide="package" class="w-8 h-8"></i>
                                    </div>
                                @endif
                            </a>
                            <div class="p-3 flex flex-col flex-1">
                                <a href="{{ $relUrl }}"
                                    class="font-heading font-bold text-xs sm:text-sm text-neutral-900 dark:text-white group-hover:text-theme-primary transition line-clamp-2 mb-1">
                                    {{ $rel->name }}
                                </a>
                                <span class="text-xs font-bold text-neutral-700 dark:text-neutral-300 mt-auto"
                                    style="font-variant-numeric: tabular-nums;">
                                    Rp {{ number_format((float) $rel->selling_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
@endsection
