@extends('public.storefront.layouts.app')

@section('content')

{{-- Checkout payload is injected via a <script> block, NOT inside the x-data="..."
     attribute. @json() emits literal double-quotes (e.g. [{"id":"..."}]) which would
     terminate the double-quoted HTML attribute and leak the Alpine JS as raw text. --}}
<script>
    window.__coocaCheckoutData = @json(['shippingRules' => $shippingRules]);
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
     x-data="{
         fulfillmentType: '{{ $storeSetting->allow_delivery ? 'delivery' : ($storeSetting->allow_pickup ? 'pickup' : 'dine_in') }}',
         customerName: '{{ auth('customer')->user()?->name ?? '' }}',
         customerPhone: '{{ auth('customer')->user()?->phone ?? '' }}',
         customerEmail: '{{ auth('customer')->user()?->email ?? '' }}',
         shippingAddress: '',
         notes: '',
         selectedTable: '',
         selectedPickupLocationId: '{{ isset($pickupLocations) ? ($pickupLocations->first()?->id ?? '') : '' }}',
         selectedPaymentMethod: '{{ $paymentMethods->first()?->id ?? '' }}',
         paymentGateway: 'manual',
         paymentChannel: 'QRIS',
         shippingCost: 0,
         selectedShippingRuleId: '{{ $shippingRules->first()?->id ?? '' }}',
         isSubmitting: false,
         errorMessage: '',

         calculateShipping() {
             if (this.fulfillmentType !== 'delivery') {
                 this.shippingCost = 0;
                 return;
             }
             const rule = this.shippingRules.find(r => r.id === this.selectedShippingRuleId);
             if (rule) {
                 this.shippingCost = parseFloat(rule.rate || 0);
             } else {
                 this.shippingCost = 0;
             }
         },

         shippingRules: window.__coocaCheckoutData.shippingRules ?? [],

         init() {
             this.calculateShipping();
         },

         grandTotal() {
             const sub = $store.cart.subtotal();
             const ship = this.fulfillmentType === 'delivery' ? this.shippingCost : 0;
             return Math.max(0, sub + ship);
         },

         async submitOrder() {
             if ($store.cart.items.length === 0) {
                 alert('Keranjang belanja Anda masih kosong.');
                 return;
             }
             if (!this.customerName || !this.customerPhone) {
                 alert('Nama dan Nomor WhatsApp wajib diisi.');
                 return;
             }
             if (this.fulfillmentType === 'delivery' && !this.shippingAddress) {
                 alert('Alamat pengiriman wajib diisi untuk pesanan antar/delivery.');
                 return;
             }
             if (this.fulfillmentType === 'pickup' && !this.selectedPickupLocationId && {{ isset($pickupLocations) && $pickupLocations->isNotEmpty() ? 'true' : 'false' }}) {
                 alert('Silakan pilih cabang / outlet untuk pengambilan pesanan.');
                 return;
             }

             this.isSubmitting = true;
             this.errorMessage = '';

             const payload = {
                 _token: '{{ csrf_token() }}',
                 customer_name: this.customerName,
                 customer_phone: this.customerPhone,
                 customer_email: this.customerEmail,
                 shipping_address: this.shippingAddress,
                 notes: this.notes,
                 fulfillment_type: this.fulfillmentType === 'delivery' ? 'merchant_delivery' : this.fulfillmentType,
                 pickup_location_id: this.fulfillmentType === 'pickup' ? this.selectedPickupLocationId : null,
                 pos_table_id: this.fulfillmentType === 'dine_in' ? (this.selectedTable || null) : null,
                 shipping_rule_id: this.fulfillmentType === 'delivery' ? this.selectedShippingRuleId : null,
                 shipping_fee: this.fulfillmentType === 'delivery' ? this.shippingCost : 0,
                 payment_gateway: this.paymentGateway,
                 payment_channel: this.paymentChannel,
                 payment_method_id: this.selectedPaymentMethod || null,
                 items: $store.cart.items.map(i => ({
                     product_id: i.id,
                     quantity: i.quantity,
                     notes: i.notes || ''
                 }))
             };

             try {
                 const res = await fetch('{{ url('/' . $business->slug . '/checkout') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify(payload)
                 });

                 const data = await res.json();

                 if (data.success && data.order && data.order.tracking_url) {
                     $store.cart.clear();
                     window.location.href = data.order.tracking_url;
                 } else {
                     this.errorMessage = data.message || 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.';
                     this.isSubmitting = false;
                 }
             } catch (err) {
                 this.errorMessage = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
                 this.isSubmitting = false;
             }
         }
     }">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs text-neutral-500 mb-2">
            <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
            <span>/</span>
            <a href="{{ url('/' . $business->slug . '/katalog') }}" class="hover:text-theme-primary transition">Katalog</a>
            <span>/</span>
            <span class="text-neutral-900 dark:text-white font-medium">Checkout Pembayaran</span>
        </div>
        <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-neutral-900 dark:text-white">
            Checkout Pesanan
        </h1>
        <p class="text-sm text-neutral-500">Lengkapi detail pengiriman dan selesaikan pesanan Anda tanpa repot.</p>
    </div>

    {{-- Error Notice --}}
    <div x-show="errorMessage" 
         x-cloak 
         class="mb-6 p-4 rounded-[12px] bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
        <span x-text="errorMessage"></span>
    </div>

    {{-- Empty Cart Guard --}}
    <div x-show="$store.cart.count() === 0" x-cloak class="py-16 text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-400 mx-auto flex items-center justify-center">
            <i data-lucide="shopping-bag" class="w-8 h-8"></i>
        </div>
        <h2 class="font-heading font-bold text-xl text-neutral-800 dark:text-neutral-200">
            Keranjang belanja Anda masih kosong
        </h2>
        <p class="text-sm text-neutral-500 max-w-sm mx-auto">
            Silakan pilih produk favorit Anda dari katalog untuk melanjutkan ke proses pembayaran.
        </p>
        <a href="{{ url('/' . $business->slug . '/katalog') }}"
           class="inline-flex items-center gap-2 px-6 py-3 rounded-[12px] theme-btn-primary font-semibold text-sm shadow-md min-h-[44px]">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Mulai Belanja</span>
        </a>
    </div>

    {{-- Customer Login Gate (Marketplace Scheme — wajib login untuk checkout) --}}
    @if (auth('customer')->guest())
        <div x-show="$store.cart.count() > 0" class="py-12 text-center space-y-5 max-w-md mx-auto">
            <div class="w-16 h-16 rounded-full bg-[#007AFF]/10 text-[#007AFF] mx-auto flex items-center justify-center">
                <i data-lucide="user-check" class="w-8 h-8"></i>
            </div>
            <h2 class="font-heading font-bold text-xl text-neutral-800 dark:text-neutral-200">
                Masuk untuk Melanjutkan Checkout
            </h2>
            <p class="text-sm text-neutral-500 max-w-sm mx-auto leading-relaxed">
                Untuk memproses pesanan, Anda perlu masuk atau membuat akun terlebih dahulu. Data pesanan di keranjang Anda tetap tersimpan.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                <a href="{{ route('customer.login', ['store' => $business->slug, 'redirect' => url('/' . $business->slug . '/checkout')]) }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066CC] text-white font-semibold text-sm shadow-md active:scale-[0.98] transition min-h-[48px] w-full sm:w-auto justify-center">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk Akun</span>
                </a>
                <a href="{{ route('customer.register', ['store' => $business->slug, 'redirect' => url('/' . $business->slug . '/checkout')]) }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-[12px] bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border border-black/10 dark:border-white/10 font-semibold text-sm hover:bg-neutral-50 dark:hover:bg-neutral-700 active:scale-[0.98] transition min-h-[48px] w-full sm:w-auto justify-center">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Daftar Baru</span>
                </a>
            </div>
            <p class="text-xs text-neutral-400 pt-2">
                <i data-lucide="info" class="w-3.5 h-3.5 inline-block mr-1"></i>
                Tenang, keranjang belanja Anda tetap tersimpan setelah masuk.
            </p>
        </div>
    @endif

    {{-- Dedicated 2-Column Standalone Checkout (Only shown for authenticated customers) --}}
    @if (auth('customer')->check())
    <div x-show="$store.cart.count() > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        
        {{-- ================================================================= --}}
        {{-- LEFT COLUMN: FORM DETAIL PENGIRIMAN & PEMBAYARAN                  --}}
        {{-- ================================================================= --}}
        <div class="lg:col-span-7 space-y-6">
            
            {{-- Step 1: Tipe Pesanan / Pemenuhan --}}
            <div class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                <div class="flex items-center gap-2 font-heading font-bold text-base text-neutral-900 dark:text-white">
                    <span class="w-6 h-6 rounded-[8px] bg-theme-primary text-white text-xs flex items-center justify-center">1</span>
                    <span>Metode Penerimaan Pesanan</span>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    @if ($storeSetting->allow_delivery)
                        <button type="button" 
                                @click="fulfillmentType = 'delivery'; calculateShipping()"
                                :class="fulfillmentType === 'delivery' ? 'border-theme-primary bg-theme-primary/5 text-theme-primary font-bold' : 'border-black/10 dark:border-white/10 text-neutral-600 dark:text-neutral-300'"
                                class="p-3.5 rounded-[12px] border text-center text-xs flex flex-col items-center gap-1.5 transition min-h-[64px]">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                            <span>Kirim ke Alamat</span>
                        </button>
                    @endif

                    @if ($storeSetting->allow_pickup)
                        <button type="button" 
                                @click="fulfillmentType = 'pickup'; calculateShipping()"
                                :class="fulfillmentType === 'pickup' ? 'border-theme-primary bg-theme-primary/5 text-theme-primary font-bold' : 'border-black/10 dark:border-white/10 text-neutral-600 dark:text-neutral-300'"
                                class="p-3.5 rounded-[12px] border text-center text-xs flex flex-col items-center gap-1.5 transition min-h-[64px]">
                            <i data-lucide="store" class="w-5 h-5"></i>
                            <span>Ambil di Toko</span>
                        </button>
                    @endif

                    @if ($posTables->isNotEmpty())
                        <button type="button" 
                                @click="fulfillmentType = 'dine_in'; calculateShipping()"
                                :class="fulfillmentType === 'dine_in' ? 'border-theme-primary bg-theme-primary/5 text-theme-primary font-bold' : 'border-black/10 dark:border-white/10 text-neutral-600 dark:text-neutral-300'"
                                class="p-3.5 rounded-[12px] border text-center text-xs flex flex-col items-center gap-1.5 transition min-h-[64px]">
                            <i data-lucide="utensils" class="w-5 h-5"></i>
                            <span>Makan di Tempat</span>
                        </button>
                    @endif
                </div>

                {{-- Dine-in Table Selector --}}
                <div x-show="fulfillmentType === 'dine_in'" x-cloak class="pt-2">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Nomor Meja</label>
                    <select x-model="selectedTable" class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
                        <option value="">-- Pilih Meja Anda --</option>
                        @foreach ($posTables as $tbl)
                            <option value="{{ $tbl->id }}">{{ $tbl->name ?: 'Meja ' . $tbl->table_number }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Store Pickup Location Selector --}}
                <div x-show="fulfillmentType === 'pickup'" x-cloak class="pt-2">
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Pilih Lokasi Outlet / Cabang Pengambilan <span class="text-red-500">*</span>
                    </label>
                    @if(isset($pickupLocations) && $pickupLocations->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($pickupLocations as $pLoc)
                                <label class="flex items-start gap-3 p-3 rounded-[12px] border transition-all cursor-pointer text-left"
                                       :class="selectedPickupLocationId === '{{ $pLoc->id }}' ? 'border-theme-primary bg-theme-primary/5 ring-1 ring-theme-primary' : 'border-black/10 dark:border-white/10 hover:border-black/20 bg-neutral-50/50 dark:bg-neutral-900/50'">
                                    <input type="radio" name="pickup_location" value="{{ $pLoc->id }}" x-model="selectedPickupLocationId" class="mt-1 text-theme-primary focus:ring-theme-primary">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-xs text-neutral-900 dark:text-white">{{ $pLoc->name }}</span>
                                            @if($pLoc->is_primary)
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Pusat</span>
                                            @endif
                                        </div>
                                        @if($pLoc->address)
                                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-1">{{ $pLoc->address }}</p>
                                        @endif
                                        @if($pLoc->phone)
                                            <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-0.5">Telp/WA: {{ $pLoc->phone }}</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="p-3 bg-neutral-100 dark:bg-neutral-900 rounded-[12px] text-xs text-neutral-600 dark:text-neutral-400">
                            Pengambilan langsung di toko / outlet utama.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Step 2: Customer Identity & Shipping Address --}}
            <div class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                <div class="flex items-center gap-2 font-heading font-bold text-base text-neutral-900 dark:text-white">
                    <span class="w-6 h-6 rounded-[8px] bg-theme-primary text-white text-xs flex items-center justify-center">2</span>
                    <span>Informasi Pembeli &amp; Pengiriman</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               x-model="customerName" 
                               required
                               placeholder="Contoh: Budi Santoso"
                               class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Nomor WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" 
                               x-model="customerPhone" 
                               required
                               placeholder="081234567890"
                               class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Email (Opsional untuk bukti digital)
                        </label>
                        <input type="email" 
                               x-model="customerEmail" 
                               placeholder="nama@email.com"
                               class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                    </div>

                    {{-- Address (Only if Delivery) --}}
                    <div x-show="fulfillmentType === 'delivery'" class="sm:col-span-2">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Alamat Lengkap Tujuan <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="shippingAddress" 
                                  rows="3"
                                  placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten..."
                                  class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary"></textarea>
                    </div>

                    {{-- Shipping Rule Option --}}
                    <div x-show="fulfillmentType === 'delivery' && shippingRules.length > 0" class="sm:col-span-2">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Pilihan Kurir &amp; Ongkos Kirim</label>
                        <select x-model="selectedShippingRuleId" 
                                @change="calculateShipping()"
                                class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
                            <template x-for="r in shippingRules" :key="r.id">
                                <option :value="r.id" x-text="r.name + ' (Rp ' + parseFloat(r.rate).toLocaleString('id-ID') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Catatan Tambahan untuk Toko</label>
                        <input type="text" 
                               x-model="notes" 
                               placeholder="Contoh: Jangan terlalu pedas, titipkan di pos satpam..."
                               class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                    </div>
                </div>
            </div>

            {{-- Step 3: Payment Method Selection --}}
            <div class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                <div class="flex items-center gap-2 font-heading font-bold text-base text-neutral-900 dark:text-white">
                    <span class="w-6 h-6 rounded-[8px] bg-theme-primary text-white text-xs flex items-center justify-center">3</span>
                    <span>Pilihan Metode Pembayaran</span>
                </div>

                <div class="space-y-3">
                    @forelse ($paymentMethods as $pm)
                        <label class="flex items-center justify-between p-3.5 rounded-[12px] border border-black/10 dark:border-white/10 hover:border-theme-primary cursor-pointer transition"
                               :class="selectedPaymentMethod === '{{ $pm->id }}' ? 'border-theme-primary bg-theme-primary/5' : ''">
                            <div class="flex items-center gap-3">
                                <input type="radio" 
                                       name="payment_method_select" 
                                       value="{{ $pm->id }}" 
                                       x-model="selectedPaymentMethod" 
                                       class="text-theme-primary focus:ring-theme-primary">
                                <div>
                                    <div class="font-bold text-sm text-neutral-900 dark:text-white">{{ $pm->name }}</div>
                                    <div class="text-xs text-neutral-500">{{ $pm->instructions ?: ($pm->account_number ? ($pm->account_number . ' a.n ' . $pm->account_holder) : 'Verifikasi pembayaran otomatis / manual') }}</div>
                                </div>
                            </div>
                            @if ($pm->type === 'qris')
                                <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-emerald-100 text-emerald-700">QRIS Instan</span>
                            @elseif ($pm->type === 'bank_transfer')
                                <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-blue-100 text-blue-700">Transfer</span>
                            @endif
                        </label>
                    @empty
                        <div class="p-4 rounded-[12px] bg-neutral-50 dark:bg-neutral-900 text-xs text-neutral-500 text-center">
                            Pembayaran akan dikonfirmasi manual melalui CS WhatsApp setelah pesanan dibuat.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ================================================================= --}}
        {{-- RIGHT COLUMN: CART ITEMS SUMMARY & SUBMISSION                     --}}
        {{-- ================================================================= --}}
        <div class="lg:col-span-5 space-y-6 sticky top-24">
            
            <div class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-lg space-y-6">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-4">
                    <h2 class="font-heading font-bold text-lg text-neutral-900 dark:text-white">Rincian Belanja</h2>
                    <span class="text-xs font-semibold px-2 py-1 rounded-[8px] theme-badge" x-text="$store.cart.count() + ' Item'"></span>
                </div>

                {{-- Items List --}}
                <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                    <template x-for="item in $store.cart.items" :key="item.id">
                        <div class="flex items-center gap-3 p-2 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/50">
                            <div class="w-12 h-12 rounded-[8px] bg-neutral-200 dark:bg-neutral-800 shrink-0 overflow-hidden">
                                <template x-if="item.image_url">
                                    <img :src="item.image_url" :alt="item.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.image_url">
                                    <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                        <i data-lucide="package" class="w-5 h-5"></i>
                                    </div>
                                </template>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-bold text-neutral-900 dark:text-white truncate" x-text="item.name"></div>
                                <div class="text-[11px] text-neutral-500" style="font-variant-numeric: tabular-nums;" x-text="'Rp ' + item.price.toLocaleString('id-ID')"></div>
                            </div>

                            {{-- Quantity Buttons --}}
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        @click="$store.cart.updateQty(item.id, item.quantity - 1)"
                                        class="w-6 h-6 rounded-md bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200 flex items-center justify-center text-xs font-bold hover:bg-neutral-300">
                                    -
                                </button>
                                <span class="w-6 text-center text-xs font-bold" style="font-variant-numeric: tabular-nums;" x-text="item.quantity"></span>
                                <button type="button" 
                                        @click="$store.cart.updateQty(item.id, item.quantity + 1)"
                                        class="w-6 h-6 rounded-md bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200 flex items-center justify-center text-xs font-bold hover:bg-neutral-300">
                                    +
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Cost Breakdown --}}
                <div class="space-y-2 border-t border-black/5 dark:border-white/10 pt-4 text-xs">
                    <div class="flex justify-between text-neutral-600 dark:text-neutral-400">
                        <span>Subtotal Produk</span>
                        <span style="font-variant-numeric: tabular-nums;" class="font-semibold" x-text="'Rp ' + $store.cart.subtotal().toLocaleString('id-ID')"></span>
                    </div>

                    <div class="flex justify-between text-neutral-600 dark:text-neutral-400" x-show="fulfillmentType === 'delivery'">
                        <span>Ongkos Kirim</span>
                        <span style="font-variant-numeric: tabular-nums;" class="font-semibold" x-text="'Rp ' + shippingCost.toLocaleString('id-ID')"></span>
                    </div>

                    <div class="flex justify-between text-neutral-900 dark:text-white font-bold text-base pt-3 border-t border-black/5 dark:border-white/10">
                        <span>Total Bayar</span>
                        <span class="text-theme-primary" style="font-variant-numeric: tabular-nums;" x-text="'Rp ' + grandTotal().toLocaleString('id-ID')"></span>
                    </div>
                </div>

                {{-- Confirm Order Button --}}
                <button type="button" 
                        @click="submitOrder()" 
                        :disabled="isSubmitting"
                        class="w-full py-4 rounded-[12px] theme-btn-primary font-bold text-sm shadow-xl flex items-center justify-center gap-2 active:scale-[0.97] disabled:opacity-50 min-h-[52px]">
                    <span x-show="!isSubmitting">Konfirmasi &amp; Pesan Sekarang</span>
                    <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        <span>Memproses Pesanan...</span>
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4" x-show="!isSubmitting"></i>
                </button>

                <p class="text-[11px] text-neutral-400 text-center leading-relaxed">
                    Data transaksi Anda dienkripsi dan diproses secara aman sesuai standar perlindungan konsumen.
                </p>
            </div>

        </div>

    </div>
    @endif

</div>
@endsection
