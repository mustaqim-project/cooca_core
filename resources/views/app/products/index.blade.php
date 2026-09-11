@extends('layouts.app', [
    'title' => 'Produk & Model Biaya',
    'headerTitle' => 'Katalog Produk & Model HPP',
    'headerSubtitle' => 'Kelola produk jadi, metode kalkulasi (BOM, ABC, Job Order), dan struktur resep'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddModal: false,
    showEditModal: {{ $editProduct ? 'true' : 'false' }},
    showAddCategoryModal: false,
    showAddUnitModal: false,
    showProductScannerPermission: false,
    showProductScanner: false,
    productScannerStarting: false,
    productScannerError: '',
    productScannerStream: null,
    productScannerDetector: null,
    productScannerFrameId: null,
    productScannerBusy: false,
    productScannerTarget: 'add',
    init() {
        window.addEventListener('pageshow', () => {
            this.showAddModal = false;
            this.showAddCategoryModal = false;
            this.showAddUnitModal = false;
            this.showProductScannerPermission = false;
            this.closeProductScanner();
            this.deleteModalOpen = false;
        });
    },
    posShowImages: {{ $business->pos_show_product_images ? 'true' : 'false' }},
    posImageToggling: false,
    newProductPreview: '',
    editProductPreview: '',
    deleteModalOpen: false,
    deleteTarget: { id: '', name: '' },
    openDelete(id, name) {
        this.deleteTarget = { id, name };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { id: '', name: '' };
    },
    submitDelete() {
        if (this.deleteTarget.id) {
            const form = document.getElementById('form-delete-' + this.deleteTarget.id);
            if (form) form.submit();
        }
    },
    async togglePosShowImages() {
        if (this.posImageToggling) return;
        this.posImageToggling = true;
        const nextState = !this.posShowImages;
        try {
            const res = await fetch('{{ route('products.toggle-pos-images') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ show_images: nextState })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal mengubah pengaturan POS.');
            this.posShowImages = Boolean(data.pos_show_product_images);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2500
                });
            }
        } catch (e) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: e.message,
                    confirmButtonColor: '#FF3B30'
                });
            }
        } finally {
            this.posImageToggling = false;
        }
    },
    handleNewProductImage(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: 'Gunakan format JPG, PNG, atau WebP.', confirmButtonColor: '#FF3B30' });
            }
            e.target.value = '';
            this.newProductPreview = '';
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file maksimal 4 MB.', confirmButtonColor: '#FF3B30' });
            }
            e.target.value = '';
            this.newProductPreview = '';
            return;
        }
        this.newProductPreview = URL.createObjectURL(file);
    },
    handleEditProductImage(e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: 'Gunakan format JPG, PNG, atau WebP.', confirmButtonColor: '#FF3B30' });
            }
            e.target.value = '';
            this.editProductPreview = '';
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file maksimal 4 MB.', confirmButtonColor: '#FF3B30' });
            }
            e.target.value = '';
            this.editProductPreview = '';
            return;
        }
        this.editProductPreview = URL.createObjectURL(file);
    },
    editProduct: {!! $editProduct ? json_encode([
        'id' => $editProduct->id,
        'slug' => $editProduct->slug,
        'name' => $editProduct->name,
        'sku' => $editProduct->code ?? '',
        'category_id' => $editProduct->category_id ?? '',
        'output_unit_id' => $editProduct->output_unit_id,
        'base_cost' => (float) $editProduct->base_cost,
        'selling_price' => (float) $editProduct->selling_price,
        'min_stock' => (float) $editProduct->min_stock,
        'is_active' => (bool) $editProduct->is_active,
        'description' => $editProduct->description ?? '',
        'image_url' => $editProduct->image_url,
    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) : "{ id: '', slug: '', name: '', sku: '', category_id: '', output_unit_id: '', base_cost: 0, selling_price: 0, min_stock: 0, is_active: true, description: '' }" !!},
    openEditModal(p) {
        this.editProduct = { ...p };
        this.editProductPreview = '';
        this.showEditModal = true;
    },
    requestProductScanner(target) {
        this.productScannerTarget = target;
        if (localStorage.getItem('cooca-product-camera-permission-intro-seen') === '1') {
            this.openProductScanner();
            return;
        }
        this.showProductScannerPermission = true;
    },
    async confirmProductScannerAccess() {
        localStorage.setItem('cooca-product-camera-permission-intro-seen', '1');
        this.showProductScannerPermission = false;
        await this.openProductScanner();
    },
    async openProductScanner() {
        this.showProductScanner = true;
        this.productScannerError = '';
        await this.$nextTick();
        if (!('BarcodeDetector' in window)) {
            this.productScannerError = 'Browser ini belum mendukung scan barcode kamera. Gunakan Chrome/Android terbaru atau input barcode manual.';
            return;
        }
        try {
            const supported = await BarcodeDetector.getSupportedFormats();
            const formats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'codabar', 'itf'].filter(format => supported.includes(format));
            this.productScannerDetector = new BarcodeDetector({ formats });
            this.productScannerStarting = true;
            this.productScannerStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false });
            this.$refs.productBarcodeVideo.srcObject = this.productScannerStream;
            await this.$refs.productBarcodeVideo.play();
            this.productScannerStarting = false;
            this.scanProductBarcodeFrame();
        } catch (error) {
            this.productScannerStarting = false;
            this.productScannerError = this.productScannerMessage(error);
        }
    },
    async scanProductBarcodeFrame() {
        if (!this.showProductScanner || !this.productScannerDetector || !this.$refs.productBarcodeVideo) return;
        if (!this.productScannerBusy && this.$refs.productBarcodeVideo.readyState >= 2) {
            this.productScannerBusy = true;
            try {
                const results = await this.productScannerDetector.detect(this.$refs.productBarcodeVideo);
                const value = results.find(result => result.rawValue)?.rawValue;
                if (value) {
                    this.setProductBarcode(value);
                    return;
                }
            } catch (error) {
                this.productScannerError = 'Barcode belum terbaca. Posisikan barcode di dalam kotak.';
            } finally {
                this.productScannerBusy = false;
            }
        }
        this.productScannerFrameId = requestAnimationFrame(() => this.scanProductBarcodeFrame());
    },
    setProductBarcode(value) {
        if (this.productScannerTarget === 'edit') {
            this.editProduct.sku = value;
        } else if (this.$refs.newProductBarcode) {
            this.$refs.newProductBarcode.value = value;
        }
        this.closeProductScanner();
    },
    closeProductScanner() {
        this.showProductScanner = false;
        if (this.productScannerFrameId) cancelAnimationFrame(this.productScannerFrameId);
        this.productScannerFrameId = null;
        this.productScannerBusy = false;
        if (this.productScannerStream) this.productScannerStream.getTracks().forEach(track => track.stop());
        this.productScannerStream = null;
        if (this.$refs.productBarcodeVideo) this.$refs.productBarcodeVideo.srcObject = null;
    },
    productScannerMessage(error) {
        if (error?.name === 'NotAllowedError' || error?.name === 'PermissionDeniedError') return 'Akses kamera ditolak. Izinkan kamera di browser lalu coba lagi.';
        if (error?.name === 'NotFoundError') return 'Kamera tidak ditemukan pada perangkat ini.';
        if (error?.name === 'NotReadableError') return 'Kamera sedang digunakan aplikasi lain.';
        if (window.isSecureContext === false) return 'Scanner kamera memerlukan HTTPS atau localhost.';
        return 'Kamera tidak dapat dibuka. Periksa izin kamera lalu coba lagi.';
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Inventori</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Katalog Produk</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Katalog Produk &amp; Model HPP</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">Kelola produk jadi, metode kalkulasi biaya (BOM, ABC, Job Order), dan struktur resep</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('products.edit'))
            <!-- Toggle Gambar POS -->
            <button type="button" @click="togglePosShowImages()" :disabled="posImageToggling"
                class="h-9 px-3 rounded-[10px] text-[12px] font-medium transition-all border flex items-center justify-center gap-1.5 disabled:opacity-50"
                :class="posShowImages ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.06] dark:bg-white/[0.08] border-transparent text-black/70 dark:text-white/70 hover:bg-black/[0.09] dark:hover:bg-white/[0.12]'"
                title="Klik untuk mengubah apakah gambar produk ditampilkan pada POS">
                <span class="w-2 h-2 rounded-full transition-colors" :class="posShowImages ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30'"></span>
                <span>Gambar di POS:</span>
                <span class="font-bold tabular-nums" x-text="posShowImages ? 'ON' : 'OFF'"></span>
            </button>
            @endif

            @if(\App\Support\Context::hasPermission('products.create') || \App\Support\Context::hasPermission('products.manage'))
            <!-- Import Excel -->
            <a href="{{ route('import.index', ['tab' => 'products']) }}"
                class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5"
                title="Import data produk massal dari file Excel / CSV">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Import</span>
            </a>

            <!-- Tambah Produk Baru -->
            <button @click="showAddModal = true"
                class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Produk</span>
            </button>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Material, No Harsh Shadow)-->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total SKU -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Produk</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">{{ $products->total() }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Katalog</span>
            </div>
        </div>

        <!-- Tile 2: Kategori Terdaftar -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Kategori Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#007AFF]">{{ $categories->count() }}</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Grup</span>
            </div>
        </div>

        <!-- Tile 3: Produk Aktif -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Produk Aktif</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $products->where('is_active', true)->count() }}</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">Siap Jual</span>
            </div>
        </div>

        <!-- Tile 4: Tampilan Gambar POS -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Visual Gambar POS</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[20px] font-bold tabular-nums" :class="posShowImages ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/60 dark:text-white/60'" x-text="posShowImages ? 'Aktif (ON)' : 'Mati (OFF)'"></span>
                <span class="text-[11px] text-black/40 dark:text-white/40">Kasir</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEARCH & CATEGORY CONTROLS (macOS Capsule Bar)      -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Field -->
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari produk, SKU, atau barcode..."
                       class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Category Filter -->
            <div class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()"
                        class="h-9 px-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                @if(request('search') || request('category_id'))
                <a href="{{ route('products.index') }}" class="h-9 px-3 rounded-[10px] text-[12px] font-medium text-black/60 dark:text-white/60 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] flex items-center">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Nama Produk &amp; Barcode</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Kategori</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 whitespace-nowrap">Satuan Output</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">HPP Standar / Aktif</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Harga Jual Aktif</th>
                        <th class="py-2.5 px-4 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($products as $prod)
                    @php
                        $activeModel = $prod->costModels->first();
                        $latestVer = $activeModel?->latestVersion;
                        $effectiveHpp = $latestVer ? (float)$latestVer->hpp_per_unit : (float)$prod->base_cost;
                    @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="py-3 px-4 font-medium text-black dark:text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 overflow-hidden flex items-center justify-center shrink-0">
                                    @if($prod->image_url)
                                        <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                        <svg class="hidden w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-semibold text-black dark:text-white text-[13px]">{{ $prod->name }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $prod->code ?? $prod->sku ?? 'Tanpa SKU' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-black/60 dark:text-white/60">
                            {{ $prod->category?->name ?? 'Umum' }}
                        </td>
                        <td class="py-3 px-4 text-black/70 dark:text-white/70">
                            {{ $prod->outputUnit?->name ?? 'pcs' }} <span class="text-[11px] text-black/40 dark:text-white/40">({{ $prod->outputUnit?->code }})</span>
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums">
                            @if($effectiveHpp > 0)
                                <div class="font-semibold text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($effectiveHpp, 0, ',', '.') }}</div>
                                @if($latestVer)
                                    <div class="text-[10px] text-black/45 dark:text-white/45">{{ $latestVer->version_label }}</div>
                                @endif
                            @else
                                <span class="text-black/40 dark:text-white/40 italic">Belum dihitung</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right tabular-nums">
                            @if($prod->selling_price > 0)
                                <div class="font-bold text-[#34C759] dark:text-[#30D158]">{{ $business->currency_symbol }} {{ number_format((float)$prod->selling_price, 0, ',', '.') }}</div>
                                @if($effectiveHpp > 0)
                                    <div class="text-[10px] text-black/50 dark:text-white/50 font-medium">Margin {{ round((($prod->selling_price - $effectiveHpp) / $prod->selling_price) * 100) }}%</div>
                                @endif
                            @else
                                <span class="text-black/40 dark:text-white/40 italic">Rp 0</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if(\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('costing.manage'))
                                <a href="{{ route('products.bom', $prod->slug) }}"
                                   class="h-7 px-2 rounded-[6px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors flex items-center gap-1">
                                    <span>BOM</span>
                                </a>
                                @endif

                                @if(\App\Support\Context::hasPermission('costing.manage') || \App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('calculator.index', ['product_id' => $prod->id, 'tab' => 'advanced']) }}"
                                   class="h-7 px-2 rounded-[6px] text-[12px] font-semibold text-[#AF52DE] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/15 transition-colors flex items-center gap-1"
                                   title="Hitung HPP & tetapkan harga jual di Kalkulator">
                                    <span>Kalkulasi</span>
                                </a>
                                @endif

                                @if(\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                                <button type="button" @click="openEditModal({
                                    id: '{{ $prod->id }}',
                                    slug: '{{ $prod->slug }}',
                                    name: '{{ addslashes($prod->name) }}',
                                    sku: '{{ addslashes($prod->code ?? '') }}',
                                    category_id: '{{ $prod->category_id ?? '' }}',
                                    output_unit_id: '{{ $prod->output_unit_id }}',
                                    base_cost: {{ (float) $prod->base_cost }},
                                    selling_price: {{ (float) $prod->selling_price }},
                                    min_stock: {{ (float) $prod->min_stock }},
                                    is_active: {{ $prod->is_active ? 'true' : 'false' }},
                                    description: '{{ addslashes($prod->description ?? '') }}',
                                    image_url: '{{ addslashes($prod->image_url ?? '') }}'
                                })" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors flex items-center" title="Edit Produk">
                                    Edit
                                </button>
                                @endif

                                @if(\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
                                <button type="button" @click="openDelete('{{ $prod->id }}', {{ Js::from($prod->name) }})"
                                        class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center" title="Hapus Produk">
                                    Hapus
                                </button>
                                <form id="form-delete-{{ $prod->id }}" method="POST" action="{{ route('products.destroy', $prod->id) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-black/45 dark:text-white/45">
                            Belum ada produk terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
        @forelse($products as $prod)
        @php
            $activeModel = $prod->costModels->first();
            $latestVer = $activeModel?->latestVersion;
            $effectiveHpp = $latestVer ? (float)$latestVer->hpp_per_unit : (float)$prod->base_cost;
        @endphp
        <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.02] dark:active:bg-white/[0.03] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[14px] font-semibold text-black dark:text-white truncate">{{ $prod->name }}</p>
                    <span class="w-1.5 h-1.5 rounded-full {{ $prod->is_active ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30' }} shrink-0"></span>
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                    Jual: <strong class="text-[#34C759] dark:text-[#30D158]">{{ $business->currency_symbol }} {{ number_format((float)$prod->selling_price, 0, ',', '.') }}</strong>
                    @if($effectiveHpp > 0)
                        · HPP: {{ $business->currency_symbol }} {{ number_format($effectiveHpp, 0, ',', '.') }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                @if(\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('costing.manage'))
                <a href="{{ route('products.bom', $prod->slug) }}" class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                    BOM
                </a>
                @endif
                @if(\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                <button type="button" @click="openEditModal({
                    id: '{{ $prod->id }}',
                    slug: '{{ $prod->slug }}',
                    name: '{{ addslashes($prod->name) }}',
                    sku: '{{ addslashes($prod->code ?? '') }}',
                    category_id: '{{ $prod->category_id ?? '' }}',
                    output_unit_id: '{{ $prod->output_unit_id }}',
                    base_cost: {{ (float) $prod->base_cost }},
                    selling_price: {{ (float) $prod->selling_price }},
                    min_stock: {{ (float) $prod->min_stock }},
                    is_active: {{ $prod->is_active ? 'true' : 'false' }},
                    description: '{{ addslashes($prod->description ?? '') }}',
                    image_url: '{{ addslashes($prod->image_url ?? '') }}'
                })" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.06] dark:bg-white/[0.08] flex items-center">
                    Edit
                </button>
                @endif
                @if(\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
                <button type="button" @click="openDelete('{{ $prod->id }}', {{ Js::from($prod->name) }})" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 flex items-center">
                    Hapus
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px]">
            Belum ada produk terdaftar.
        </div>
        @endforelse

        @if($products->hasPages())
        <div class="p-3 border-t border-black/5 dark:border-white/10">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: TAMBAH PRODUK BARU (Apple Sheet)            -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('products.create') || \App\Support\Context::hasPermission('products.manage'))
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-lg rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[88vh] overflow-y-auto" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[16px] font-semibold text-black dark:text-white">Buat Produk Baru</h3>
                <button @click="showAddModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-3.5 text-[13px]">
                @csrf

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Produk <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Roti Tawar Gandum / Kopi Latte"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-medium text-black/70 dark:text-white/70">Satuan Output <span class="text-[#FF3B30]">*</span></label>
                            <button type="button" @click="showAddUnitModal = true" class="text-[11px] text-[#007AFF] hover:underline">+ Satuan</button>
                        </div>
                        <select name="output_unit_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $u->code === 'pcs' ? 'selected' : '' }}>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-medium text-black/70 dark:text-white/70">Kategori</label>
                            <button type="button" @click="showAddCategoryModal = true" class="text-[11px] text-[#007AFF] hover:underline">+ Kategori</button>
                        </div>
                        <select name="category_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Metode Kalkulasi HPP <span class="text-[#FF3B30]">*</span></label>
                    <select name="costing_method" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <option value="recipe_bom">Recipe / Bill of Materials (F&amp;B &amp; Manufaktur)</option>
                        <option value="simple">Simple / Standar Flat</option>
                        <option value="job">Job Order Costing (Proyek / Custom Order)</option>
                        <option value="process">Process Costing (Batch / Massal)</option>
                        <option value="abc">Activity-Based Costing (ABC)</option>
                        <option value="service">Service / Jasa Man-Hour</option>
                        <option value="retail">Retail / Landed Cost Grosir</option>
                    </select>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Barcode / SKU (Opsional)</label>
                    <div class="flex gap-2">
                        <input type="text" name="sku" x-ref="newProductBarcode" inputmode="numeric" autocomplete="off" placeholder="8991234567890 atau PRD-001"
                               class="min-w-0 flex-1 h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        <button type="button" @click="requestProductScanner('add')" class="shrink-0 h-10 px-3 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/15 flex items-center justify-center transition" title="Scan barcode dengan kamera">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Deskripsi Produk (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Deskripsi ringkas atau komposisi produk..."
                              class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Gambar Produk (Opsional)</label>
                    <div x-show="newProductPreview" class="mb-2 w-16 h-16 rounded-[10px] overflow-hidden border border-black/10 dark:border-white/10 relative">
                        <img :src="newProductPreview" alt="Preview Gambar Baru" class="w-full h-full object-cover">
                        <button type="button" @click="newProductPreview = ''; $refs.newProductImageInput.value = ''" class="absolute top-1 right-1 p-0.5 rounded-full bg-black/70 text-white hover:text-[#FF3B30]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <input type="file" name="image" x-ref="newProductImageInput" @change="handleNewProductImage($event)" accept="image/jpeg,image/png,image/webp" class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-black/[0.06] dark:file:bg-white/[0.08] file:px-3 file:py-1.5 file:text-[12px]">
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showAddModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Buat Produk</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. MODAL: EDIT PRODUK (Apple Sheet)                   -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-lg rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] max-h-[90vh] overflow-y-auto" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[16px] font-semibold text-black dark:text-white">Edit Data Produk</h3>
                <button @click="showEditModal = false" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form :action="'/products/' + editProduct.slug" method="POST" enctype="multipart/form-data" class="space-y-4 text-[13px]">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Produk <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" x-model="editProduct.name" required
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Barcode / SKU</label>
                        <div class="flex gap-2">
                            <input type="text" name="sku" x-model="editProduct.sku" inputmode="numeric" autocomplete="off" placeholder="8991234567890"
                                   class="min-w-0 flex-1 h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <button type="button" @click="requestProductScanner('edit')" class="shrink-0 h-10 px-3 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/15 flex items-center justify-center transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kategori</label>
                        <select name="category_id" x-model="editProduct.category_id" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Satuan Output <span class="text-[#FF3B30]">*</span></label>
                        <select name="output_unit_id" x-model="editProduct.output_unit_id" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Minimum Stok</label>
                        <input type="number" step="any" name="min_stock" x-model="editProduct.min_stock" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">HPP Standar (Modal)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium">{{ $business->currency_symbol }}</span>
                            <input type="number" step="any" name="base_cost" x-model="editProduct.base_cost" class="w-full h-9 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                    <div>
                        <label class="block font-medium text-[#34C759] dark:text-[#30D158] mb-1">Harga Jual Aktif</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium">{{ $business->currency_symbol }}</span>
                            <input type="number" step="any" name="selling_price" x-model="editProduct.selling_price" class="w-full h-9 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[8px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Deskripsi Produk</label>
                    <textarea name="description" rows="2" x-model="editProduct.description" class="w-full bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] p-3 text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                </div>

                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Gambar Produk</label>
                    <div x-show="editProductPreview || editProduct.image_url" class="mb-2 flex items-center gap-3">
                        <div class="w-16 h-16 rounded-[10px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/[0.04] dark:bg-white/[0.06] relative shrink-0">
                            <img :src="editProductPreview || editProduct.image_url" :alt="editProduct.name" class="w-full h-full object-cover">
                        </div>
                        <div class="text-[11px] text-black/50 dark:text-white/50">
                            <span x-show="editProductPreview" class="text-[#007AFF] font-medium block">Pratinjau gambar baru</span>
                            <span x-show="!editProductPreview && editProduct.image_url" class="block">Gambar aktif saat ini</span>
                            <span class="text-[10px]">Pilih berkas baru jika ingin mengganti.</span>
                        </div>
                    </div>
                    <input type="file" name="image" @change="handleEditProductImage($event)" accept="image/jpeg,image/png,image/webp" class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-black/[0.06] dark:file:bg-white/[0.08] file:px-3 file:py-1.5 file:text-[12px]">
                    <label x-show="editProduct.image_url" class="mt-2 flex items-center gap-2 text-[12px] text-black/60 dark:text-white/60 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                        <span>Hapus gambar saat ini</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" :checked="editProduct.is_active" class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                    <label for="edit_is_active" class="text-black/80 dark:text-white/80 font-medium text-[13px] cursor-pointer">Produk Aktif untuk Dijual &amp; Masuk Faktur</label>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/10 dark:border-white/10">
                    <button type="button" @click="showEditModal = false" class="h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] transition">Batal</button>
                    <button type="submit" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition shadow-[0_1px_2px_rgba(0,122,255,0.25)]">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 8. SUB-MODALS: KATEGORI & SATUAN (Apple Sheet)        -->
    <!-- ===================================================== -->
    <div x-show="showAddCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-sm rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.outside="showAddCategoryModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Tambah Kategori Produk</h3>
                <button @click="showAddCategoryModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('product-categories.store') }}" class="space-y-3.5 text-[13px]">
                @csrf
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Kategori <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Minuman Segar / Makanan" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Deskripsi</label>
                    <input type="text" name="description" placeholder="Catatan kategori..." class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddCategoryModal = false" class="h-8 px-3 rounded-[8px] text-[12px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Tutup</button>
                    <button type="submit" class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showAddUnitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-sm rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)]" @click.outside="showAddUnitModal = false">
            <div class="flex items-center justify-between border-b border-black/10 dark:border-white/10 pb-3">
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Tambah Satuan Baru</h3>
                <button @click="showAddUnitModal = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('units.store') }}" class="space-y-3.5 text-[13px]">
                @csrf
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kode Simbol <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="code" required placeholder="pcs / box" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kategori <span class="text-[#FF3B30]">*</span></label>
                        <select name="category" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-2.5 text-black dark:text-white text-[12px] focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="quantity">Kuantitas (pcs)</option>
                            <option value="weight">Berat (g, kg)</option>
                            <option value="volume">Volume (ml, l)</option>
                            <option value="custom">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Satuan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Porsi / Botol" class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddUnitModal = false" class="h-8 px-3 rounded-[8px] text-[12px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Tutup</button>
                    <button type="submit" class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 9. SCANNER MODALS (macOS Frosted Glass Modals)         -->
    <!-- ===================================================== -->
    <div x-show="showProductScannerPermission" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-sm rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 space-y-3.5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /></svg>
                </div>
                <div>
                    <h3 class="text-[15px] font-semibold text-black dark:text-white">Izinkan Akses Kamera?</h3>
                    <p class="text-[12px] text-black/60 dark:text-white/60 mt-1 leading-snug">Kamera digunakan untuk membaca barcode produk. Kamera otomatis dinonaktifkan setelah scanner ditutup.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-black/10 dark:border-white/10">
                <button type="button" @click="showProductScannerPermission = false" class="h-8 px-3 rounded-[8px] text-[12px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Batal</button>
                <button type="button" @click="confirmProductScannerAccess()" class="h-8 px-3.5 rounded-[8px] text-[12px] font-semibold bg-[#007AFF] text-white">Lanjutkan</button>
            </div>
        </div>
    </div>

    <div x-show="showProductScanner" x-cloak @keydown.escape.window="closeProductScanner()" @click.self="closeProductScanner()" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/25 backdrop-blur-[2px]">
        <div class="w-full max-w-md rounded-[16px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl border border-black/10 dark:border-white/10 p-5 space-y-3.5 shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-black dark:text-white">Pindai Barcode Produk</h3>
                <button type="button" @click="closeProductScanner()" class="p-1 rounded-[6px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">✕</button>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-[12px] bg-black">
                <video x-ref="productBarcodeVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-[70%] h-[40%] rounded-[10px] border-2 border-[#007AFF]"></div>
                </div>
                <div x-show="productScannerStarting" class="absolute inset-0 flex items-center justify-center bg-black/60 text-[12px] text-white">
                    Menyiapkan kamera...
                </div>
            </div>
            <div x-show="productScannerError" class="p-2.5 rounded-[8px] bg-[#FF3B30]/10 text-[12px] text-[#FF3B30]" x-text="productScannerError"></div>
            <div class="flex justify-end pt-1">
                <button type="button" @click="closeProductScanner()" class="h-8 px-3.5 rounded-[8px] text-[12px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 font-medium">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 10. APPLE ALERT DIALOG (Hapus Produk)                  -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]">
        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDelete()">
            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Produk?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus beserta seluruh model biayanya. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
