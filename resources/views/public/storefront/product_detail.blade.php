@extends('public.storefront.layouts.app')

@php
    $pTitle = $product->name . ' | ' . $business->name;
    $rawDesc = strip_tags($product->description ?: 'Beli ' . $product->name . ' original harga terbaik hanya di ' . $business->name . '. Kualitas terjamin, stok kasir resmi & pengiriman cepat.');
    $pDesc = \Illuminate\Support\Str::limit($rawDesc, 155);
    $pCanonical = url('/' . $business->slug . '/produk/' . ($product->slug ?: $product->id));
    $pOgImage = $product->image_url ?: ($landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/image/cooca.png'))));
    $pPrice = (float) $product->selling_price;
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

            {{-- Left: Product Image Showcase --}}
            <div class="lg:col-span-6 space-y-4">
                <div
                    class="relative aspect-square rounded-theme overflow-hidden bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/10 shadow-lg">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                            class="w-full h-full object-cover">
                    @else
                        <div
                            class="w-full h-full flex flex-col items-center justify-center text-neutral-400 bg-neutral-100 dark:bg-neutral-800">
                            <i data-lucide="package" class="w-20 h-20 stroke-1 mb-2"></i>
                            <span class="text-xs font-medium">Foto Produk Resmi</span>
                        </div>
                    @endif

                    @if ($product->is_preorder)
                        <div
                            class="absolute top-4 left-4 px-3 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-md">
                            Sistem Pre-Order (PO)
                        </div>
                    @endif
                </div>
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
