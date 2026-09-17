@extends('layouts.app', [
    'title' => 'Katalog Produk & Model HPP',
    'headerTitle' => 'Katalog Produk & Model HPP',
    'headerSubtitle' => 'Kelola produk jadi, kalkulasi biaya (BOM, ABC, Job Order), harga jual, dan saluran etalase'
])

@section('content')
<div class="max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="{
    showAddModal: false,
    showEditModal: {{ $editProduct ? 'true' : 'false' }},
    showAddCategoryModal: false,
    showAddUnitModal: false,
    quickAddTarget: 'add',
    quickCategoryName: '',
    quickCategoryDesc: '',
    quickCategoryLoading: false,
    quickUnitCode: '',
    quickUnitName: '',
    quickUnitCategory: 'quantity',
    quickUnitLoading: false,
    categoryList: {{ Js::from($categories->map(fn($c) => ['id' => (string)$c->id, 'name' => $c->name])) }},
    unitList: {{ Js::from($units->map(fn($u) => ['id' => (string)$u->id, 'name' => $u->name, 'code' => $u->code])) }},

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
    newIsPreorder: false,
    newPreorderMode: 'customer_schedule',
    newPreorderLeadDays: 1,
    newShowPriceOnWeb: true,

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
        'category_id' => (string) ($editProduct->category_id ?? ''),
        'output_unit_id' => (string) $editProduct->output_unit_id,
        'base_cost' => (float) $editProduct->base_cost,
        'selling_price' => (float) $editProduct->selling_price,
        'min_stock' => (float) $editProduct->min_stock,
        'is_active' => (bool) $editProduct->is_active,
        'show_in_website' => (bool) ($editProduct->show_in_website ?? true),
        'show_in_pos' => (bool) ($editProduct->show_in_pos ?? true),
        'show_in_sales_order' => (bool) ($editProduct->show_in_sales_order ?? true),
        'show_price_on_web' => (bool) ($editProduct->show_price_on_web ?? true),
        'is_preorder' => (bool) ($editProduct->is_preorder ?? false),
        'preorder_mode' => (string) ($editProduct->preorder_mode ?? 'customer_schedule'),
        'preorder_lead_days' => (int) ($editProduct->preorder_lead_days ?? 1),
        'description' => $editProduct->description ?? '',
        'image_url' => $editProduct->image_url,
    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) : "{ id: '', slug: '', name: '', sku: '', category_id: '', output_unit_id: '', base_cost: 0, selling_price: 0, min_stock: 0, is_active: true, show_in_website: true, show_in_pos: true, show_in_sales_order: true, show_price_on_web: true, is_preorder: false, preorder_mode: 'customer_schedule', preorder_lead_days: 1, description: '', image_url: '' }" !!},

    productToggles: {
        @foreach($products as $p)
            '{{ $p->id }}': {
                show_in_website: {{ ($p->show_in_website ?? true) ? 'true' : 'false' }},
                show_in_pos: {{ ($p->show_in_pos ?? true) ? 'true' : 'false' }},
                show_in_sales_order: {{ ($p->show_in_sales_order ?? true) ? 'true' : 'false' }},
                show_price_on_web: {{ ($p->show_price_on_web ?? true) ? 'true' : 'false' }},
                is_preorder: {{ ($p->is_preorder ?? false) ? 'true' : 'false' }},
                is_active: {{ $p->is_active ? 'true' : 'false' }},
                loading: null
            },
        @endforeach
    },

    async toggleProductField(productId, field) {
        if (!this.productToggles[productId]) {
            this.productToggles[productId] = {
                show_in_website: true,
                show_in_pos: true,
                show_in_sales_order: true,
                show_price_on_web: true,
                is_preorder: false,
                is_active: true,
                loading: null
            };
        }
        const item = this.productToggles[productId];
        if (item.loading) return;

        const prevVal = Boolean(item[field]);
        const nextVal = !prevVal;

        // Optimistic UI update
        item[field] = nextVal;
        item.loading = field;

        try {
            const res = await fetch('/products/' + productId + '/toggle-setting', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ field: field, value: nextVal })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal mengubah pengaturan.');
            item[field] = Boolean(data.value);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } catch (err) {
            item[field] = prevVal;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: err.message || 'Gagal memperbarui status.',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        } finally {
            item.loading = null;
        }
    },

    openEditModal(p) {
        const currentToggle = this.productToggles[p.id];
        this.editProduct = {
            ...p,
            category_id: String(p.category_id || ''),
            output_unit_id: String(p.output_unit_id || ''),
            show_in_website: currentToggle ? currentToggle.show_in_website : p.show_in_website,
            show_in_pos: currentToggle ? currentToggle.show_in_pos : p.show_in_pos,
            show_in_sales_order: currentToggle ? currentToggle.show_in_sales_order : p.show_in_sales_order,
            show_price_on_web: currentToggle ? currentToggle.show_price_on_web : p.show_price_on_web,
            is_preorder: currentToggle ? currentToggle.is_preorder : p.is_preorder,
            is_active: currentToggle ? currentToggle.is_active : p.is_active,
        };
        this.editProductPreview = '';
        this.showEditModal = true;
    },

    // Quick-Add Category AJAX
    async submitQuickCategory() {
        if (!this.quickCategoryName.trim() || this.quickCategoryLoading) return;
        this.quickCategoryLoading = true;
        try {
            const res = await fetch('{{ route('product-categories.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.quickCategoryName.trim(),
                    description: this.quickCategoryDesc.trim() || null
                })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menambahkan kategori.');
            const newCat = { id: String(data.category.id), name: data.category.name };
            this.categoryList.push(newCat);
            if (this.quickAddTarget === 'edit') {
                this.editProduct.category_id = newCat.id;
            } else if (this.$refs.newProductCategory) {
                this.$refs.newProductCategory.value = newCat.id;
            }
            this.quickCategoryName = '';
            this.quickCategoryDesc = '';
            this.showAddCategoryModal = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Kategori baru berhasil ditambahkan.',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: err.message, confirmButtonColor: '#FF3B30' });
            }
        } finally {
            this.quickCategoryLoading = false;
        }
    },

    // Quick-Add Unit AJAX
    async submitQuickUnit() {
        if (!this.quickUnitCode.trim() || !this.quickUnitName.trim() || this.quickUnitLoading) return;
        this.quickUnitLoading = true;
        try {
            const res = await fetch('{{ route('units.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    code: this.quickUnitCode.trim(),
                    name: this.quickUnitName.trim(),
                    category: this.quickUnitCategory,
                    default_precision: 0
                })
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menambahkan satuan.');
            const newUnit = { id: String(data.unit.id), name: data.unit.name, code: data.unit.code };
            this.unitList.push(newUnit);
            if (this.quickAddTarget === 'edit') {
                this.editProduct.output_unit_id = newUnit.id;
            } else if (this.$refs.newProductUnit) {
                this.$refs.newProductUnit.value = newUnit.id;
            }
            this.quickUnitCode = '';
            this.quickUnitName = '';
            this.quickUnitCategory = 'quantity';
            this.showAddUnitModal = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Satuan baru berhasil ditambahkan.',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } catch (err) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: err.message, confirmButtonColor: '#FF3B30' });
            }
        } finally {
            this.quickUnitLoading = false;
        }
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
    <!-- 0. UNIFIED SEGMENTED NAVIGATION (UI Unification)      -->
    <!-- ===================================================== -->
    <div class="flex items-center justify-between gap-4 overflow-x-auto no-scrollbar pb-1">
        <div class="inline-flex p-1 bg-black/[0.05] dark:bg-white/[0.08] rounded-[14px] border border-black/[0.04] dark:border-white/[0.06] shrink-0">
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 rounded-[10px] text-[13px] font-semibold bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm flex items-center gap-2 whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <span>Barang Fisik (Katalog)</span>
            </a>
            <a href="{{ route('services.index') }}"
               class="px-4 py-2 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.139 1.743" />
                </svg>
                <span>Jasa &amp; Layanan</span>
            </a>
            <a href="{{ route('pos.modifiers.index') }}"
               class="px-4 py-2 rounded-[10px] text-[13px] font-medium text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center gap-2 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Varian &amp; Modifiers</span>
            </a>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[20px] backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-7 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="min-w-0 flex flex-col justify-center">
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Inventori</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Katalog Produk</span>
            </nav>
            <h1 class="text-xl sm:text-2xl font-bold text-[#1C1C1E] dark:text-[#F2F2F7] tracking-tight leading-snug truncate">Katalog Produk &amp; Model HPP</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">Kelola produk fisik, kalkulasi biaya (BOM/ABC/Job Order), harga jual, dan etalase toko</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('products.edit'))
            <!-- Toggle Gambar POS -->
            <button type="button" @click="togglePosShowImages()" :disabled="posImageToggling"
                class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium transition-all border flex items-center justify-center gap-2 active:scale-[0.98] disabled:opacity-50"
                :class="posShowImages ? 'bg-[#34C759]/10 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.04] dark:bg-white/[0.06] border-black/[0.06] dark:border-white/[0.08] text-black/70 dark:text-white/70 hover:bg-black/[0.07] dark:hover:bg-white/[0.1]'"
                title="Ubah apakah foto produk ditampilkan pada terminal kasir POS">
                <span class="w-2 h-2 rounded-full transition-colors shrink-0" :class="posShowImages ? 'bg-[#34C759]' : 'bg-black/30 dark:bg-white/30'"></span>
                <span>Foto POS:</span>
                <span class="font-bold tabular-nums" x-text="posShowImages ? 'ON' : 'OFF'"></span>
            </button>
            @endif

            @if(\App\Support\Context::hasPermission('products.create') || \App\Support\Context::hasPermission('products.manage'))
            <!-- Import Excel -->
            <a href="{{ route('import.index', ['tab' => 'products']) }}"
                class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] border border-black/[0.06] dark:border-white/[0.08] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5"
                title="Import data produk massal dari file Excel / CSV">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Import</span>
            </a>

            <!-- Tambah Produk Baru -->
            <button @click="showAddModal = true"
                class="h-10 px-4 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-[#007AFF]/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Produk</span>
            </button>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Apple Bento Metric Cards)              -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
        <!-- Tile 1: Total Produk -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] font-medium text-black/50 dark:text-white/50">Total Produk</span>
                <div class="w-8 h-8 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-black dark:text-white">{{ $products->total() }}</span>
                <span class="text-[12px] text-black/40 dark:text-white/40">Katalog</span>
            </div>
        </div>

        <!-- Tile 2: Kategori Terdaftar -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] font-medium text-black/50 dark:text-white/50">Kategori Terdaftar</span>
                <div class="w-8 h-8 rounded-full bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-[#007AFF]">{{ $categories->count() }}</span>
                <span class="text-[12px] text-black/40 dark:text-white/40">Grup</span>
            </div>
        </div>

        <!-- Tile 3: Produk Aktif -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] font-medium text-black/50 dark:text-white/50">Produk Aktif</span>
                <div class="w-8 h-8 rounded-full bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-2xl sm:text-3xl font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">{{ $products->where('is_active', true)->count() }}</span>
                <span class="text-[12px] font-medium text-[#34C759] dark:text-[#30D158]">Siap Jual</span>
            </div>
        </div>

        <!-- Tile 4: Tampilan Foto POS -->
        <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[13px] font-medium text-black/50 dark:text-white/50">Visual Foto POS</span>
                <div class="w-8 h-8 rounded-full bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-xl sm:text-2xl font-bold tabular-nums" :class="posShowImages ? 'text-[#34C759] dark:text-[#30D158]' : 'text-black/60 dark:text-white/60'" x-text="posShowImages ? 'Aktif (ON)' : 'Mati (OFF)'"></span>
                <span class="text-[12px] text-black/40 dark:text-white/40">Kasir</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEARCH & CATEGORY CONTROLS                          -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Field -->
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama produk, SKU, atau barcode..."
                       class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] pl-10 pr-3.5 text-[16px] sm:text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
            </div>

            <!-- Category Filter -->
            <div class="flex items-center gap-2">
                <select name="category_id" onchange="this.form.submit()"
                        class="h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-[16px] sm:text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>

                @if(request('search') || request('category_id'))
                <a href="{{ route('products.index') }}" class="h-10 px-3.5 rounded-[12px] text-[13px] font-medium text-black/60 dark:text-white/60 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.09] flex items-center transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
        <div class="overflow-x-auto scrollbar-thin">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02]">
                        <th class="py-3 px-5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">Nama Produk &amp; Barcode</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">Kategori</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 whitespace-nowrap">Satuan Output</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">HPP Standar / Aktif</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">Harga Jual Aktif</th>
                        <th class="py-3 px-4 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-center whitespace-nowrap">Saluran &amp; Status</th>
                        <th class="py-3 px-5 text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($products as $prod)
                    @php
                        $activeModel = $prod->costModels->first();
                        $latestVer = $activeModel?->latestVersion;
                        $effectiveHpp = $latestVer ? (float)$latestVer->hpp_per_unit : (float)$prod->base_cost;
                    @endphp
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.025] transition-colors">
                        <td class="py-3.5 px-5 font-medium text-black dark:text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/5 overflow-hidden flex items-center justify-center shrink-0">
                                    @if($prod->image_url)
                                        <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                        <svg class="hidden w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-black dark:text-white text-[13.5px] truncate">{{ $prod->name }}</div>
                                    <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">{{ $prod->code ?? $prod->sku ?? 'Tanpa Barcode' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-black/60 dark:text-white/60">
                            {{ $prod->category?->name ?? 'Umum' }}
                        </td>
                        <td class="py-3.5 px-4 text-black/70 dark:text-white/70">
                            {{ $prod->outputUnit?->name ?? 'pcs' }} <span class="text-[11px] text-black/40 dark:text-white/40">({{ $prod->outputUnit?->code }})</span>
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums">
                            @if($effectiveHpp > 0)
                                <div class="font-semibold text-black dark:text-white">{{ $business->currency_symbol }} {{ number_format($effectiveHpp, 0, ',', '.') }}</div>
                                @if($latestVer)
                                    <div class="text-[10.5px] text-black/45 dark:text-white/45">{{ $latestVer->version_label }}</div>
                                @endif
                            @else
                                <span class="text-black/40 dark:text-white/40 italic">Belum dihitung</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right tabular-nums">
                            @if($prod->selling_price > 0)
                                <div class="font-bold text-[#34C759] dark:text-[#30D158]">{{ $business->currency_symbol }} {{ number_format((float)$prod->selling_price, 0, ',', '.') }}</div>
                                @if($effectiveHpp > 0)
                                    <div class="text-[10.5px] text-black/50 dark:text-white/50 font-medium">Margin {{ round((($prod->selling_price - $effectiveHpp) / $prod->selling_price) * 100) }}%</div>
                                @endif
                            @else
                                <span class="text-black/40 dark:text-white/40 italic">Rp 0</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                @if(\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                                <button type="button"
                                        @click="toggleProductField('{{ $prod->id }}', 'show_in_website')"
                                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_website'"
                                        :class="productToggles['{{ $prod->id }}']?.show_in_website ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                        class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                        title="Ubah ketersediaan di Etalase Web">
                                    <span>Web</span>
                                </button>
                                <button type="button"
                                        @click="toggleProductField('{{ $prod->id }}', 'show_in_pos')"
                                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_pos'"
                                        :class="productToggles['{{ $prod->id }}']?.show_in_pos ? 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                        class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                        title="Ubah ketersediaan di Kasir POS">
                                    <span>POS</span>
                                </button>
                                <button type="button"
                                        @click="toggleProductField('{{ $prod->id }}', 'show_in_sales_order')"
                                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_sales_order'"
                                        :class="productToggles['{{ $prod->id }}']?.show_in_sales_order ? 'bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border-[#5856D6]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                        class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                        title="Ubah ketersediaan di Faktur Sales Order">
                                    <span>SO</span>
                                </button>
                                <button type="button"
                                        @click="toggleProductField('{{ $prod->id }}', 'show_price_on_web')"
                                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_price_on_web'"
                                        :class="productToggles['{{ $prod->id }}']?.show_price_on_web ? 'bg-[#30B0C7]/10 text-[#0071A4] dark:text-[#70D7FF] border-[#30B0C7]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                                        class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                        title="Ubah tampilan harga publik (ON: Tampil, OFF: Tombol WhatsApp)">
                                    <span>Harga</span>
                                </button>
                                <button type="button"
                                        @click="toggleProductField('{{ $prod->id }}', 'is_preorder')"
                                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_preorder'"
                                        :class="productToggles['{{ $prod->id }}']?.is_preorder ? 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/30 font-bold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60'"
                                        class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                        title="Ubah status Pre-Order (PO)">
                                    <span>PO</span>
                                </button>
                                <button type="button"
                                        @click="toggleProductField('{{ $prod->id }}', 'is_active')"
                                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_active'"
                                        :class="productToggles['{{ $prod->id }}']?.is_active ? 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30 font-semibold' : 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/30 opacity-70'"
                                        class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium transition-all hover:scale-105 active:scale-95 flex items-center"
                                        title="Ubah status aktif global">
                                    <span x-text="productToggles['{{ $prod->id }}']?.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                </button>
                                @else
                                <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium" :class="productToggles['{{ $prod->id }}']?.show_in_website ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30' : 'opacity-40 line-through'">Web</span>
                                <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium" :class="productToggles['{{ $prod->id }}']?.show_in_pos ? 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30' : 'opacity-40 line-through'">POS</span>
                                <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium" :class="productToggles['{{ $prod->id }}']?.show_in_sales_order ? 'bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border-[#5856D6]/30' : 'opacity-40 line-through'">SO</span>
                                <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium" :class="productToggles['{{ $prod->id }}']?.show_price_on_web ? 'bg-[#30B0C7]/10 text-[#0071A4] dark:text-[#70D7FF] border-[#30B0C7]/30' : 'opacity-40 line-through'">Harga</span>
                                <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium" :class="productToggles['{{ $prod->id }}']?.is_preorder ? 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/30' : 'opacity-40'">PO</span>
                                <span class="h-6 px-1.5 rounded-[6px] border text-[11px] font-medium" :class="productToggles['{{ $prod->id }}']?.is_active ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF3B30]/10 text-[#FF3B30]'" x-text="productToggles['{{ $prod->id }}']?.is_active ? 'Aktif' : 'Nonaktif'"></span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-5 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('products.manage') || \App\Support\Context::hasPermission('costing.manage'))
                                <a href="{{ route('products.bom', $prod->slug) }}"
                                   class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-colors flex items-center gap-1">
                                    <span>BOM</span>
                                </a>
                                @endif

                                @if(\App\Support\Context::hasPermission('costing.manage') || \App\Support\Context::hasPermission('costing.view_margin'))
                                <a href="{{ route('calculator.index', ['product_id' => $prod->id, 'tab' => 'advanced']) }}"
                                   class="h-8 px-2.5 rounded-[8px] text-[12px] font-semibold text-[#AF52DE] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/15 transition-colors flex items-center gap-1"
                                   title="Hitung HPP & tetapkan margin harga jual di Kalkulator">
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
                                    show_in_website: {{ ($prod->show_in_website ?? true) ? 'true' : 'false' }},
                                    show_in_pos: {{ ($prod->show_in_pos ?? true) ? 'true' : 'false' }},
                                    show_in_sales_order: {{ ($prod->show_in_sales_order ?? true) ? 'true' : 'false' }},
                                    show_price_on_web: {{ ($prod->show_price_on_web ?? true) ? 'true' : 'false' }},
                                    is_preorder: {{ ($prod->is_preorder ?? false) ? 'true' : 'false' }},
                                    preorder_mode: '{{ $prod->preorder_mode ?? 'customer_schedule' }}',
                                    preorder_lead_days: {{ (int) ($prod->preorder_lead_days ?? 1) }},
                                    description: '{{ addslashes($prod->description ?? '') }}',
                                    image_url: '{{ addslashes($prod->image_url ?? '') }}'
                                })" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors flex items-center" title="Edit Produk">
                                    Edit
                                </button>
                                @endif

                                @if(\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
                                <button type="button" @click="openDelete('{{ $prod->id }}', {{ Js::from($prod->name) }})"
                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center" title="Hapus Produk">
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
                        <td colspan="7" class="py-14 text-center text-black/40 dark:text-white/40">
                            Belum ada produk terdaftar pada katalog ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="px-5 py-3.5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden space-y-3">
        @forelse($products as $prod)
        @php
            $activeModel = $prod->costModels->first();
            $latestVer = $activeModel?->latestVersion;
            $effectiveHpp = $latestVer ? (float)$latestVer->hpp_per_unit : (float)$prod->base_cost;
        @endphp
        <div class="p-4 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-3 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 overflow-hidden flex items-center justify-center shrink-0">
                        @if($prod->image_url)
                            <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-5 h-5 text-black/40 dark:text-white/40" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-[15px] font-semibold text-black dark:text-white truncate">{{ $prod->name }}</p>
                            <span class="w-2 h-2 rounded-full shrink-0" :class="productToggles['{{ $prod->id }}']?.is_active ? 'bg-[#34C759]' : 'bg-[#FF3B30]'"></span>
                        </div>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 tabular-nums">
                            {{ $prod->category?->name ?? 'Umum' }} · {{ $prod->outputUnit?->name ?? 'pcs' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
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
                        show_in_website: {{ ($prod->show_in_website ?? true) ? 'true' : 'false' }},
                        show_in_pos: {{ ($prod->show_in_pos ?? true) ? 'true' : 'false' }},
                        show_in_sales_order: {{ ($prod->show_in_sales_order ?? true) ? 'true' : 'false' }},
                        show_price_on_web: {{ ($prod->show_price_on_web ?? true) ? 'true' : 'false' }},
                        is_preorder: {{ ($prod->is_preorder ?? false) ? 'true' : 'false' }},
                        preorder_mode: '{{ $prod->preorder_mode ?? 'customer_schedule' }}',
                        preorder_lead_days: {{ (int) ($prod->preorder_lead_days ?? 1) }},
                        description: '{{ addslashes($prod->description ?? '') }}',
                        image_url: '{{ addslashes($prod->image_url ?? '') }}'
                    })" class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] flex items-center">
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

            <!-- Price & HPP Row -->
            <div class="flex items-center justify-between pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-[13px]">
                <span class="text-black/50 dark:text-white/50">Harga Jual:</span>
                <div class="text-right">
                    <span class="font-bold text-[#34C759] dark:text-[#30D158] tabular-nums text-[14px]">
                        {{ $business->currency_symbol }} {{ number_format((float)$prod->selling_price, 0, ',', '.') }}
                    </span>
                    @if($effectiveHpp > 0)
                        <span class="text-[11px] text-black/45 dark:text-white/45 block tabular-nums">
                            HPP: {{ $business->currency_symbol }} {{ number_format($effectiveHpp, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Quick Toggle Strip on Mobile -->
            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] flex items-center gap-1.5 flex-wrap">
                <span class="text-[10px] uppercase font-semibold text-black/40 dark:text-white/40 tracking-wider mr-1">Saluran:</span>
                @if(\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
                <button type="button" @click="toggleProductField('{{ $prod->id }}', 'show_in_website')"
                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_website'"
                        :class="productToggles['{{ $prod->id }}']?.show_in_website ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                        class="h-6 px-2 rounded-[6px] border text-[11px] font-medium">Web</button>
                <button type="button" @click="toggleProductField('{{ $prod->id }}', 'show_in_pos')"
                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_pos'"
                        :class="productToggles['{{ $prod->id }}']?.show_in_pos ? 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                        class="h-6 px-2 rounded-[6px] border text-[11px] font-medium">POS</button>
                <button type="button" @click="toggleProductField('{{ $prod->id }}', 'show_in_sales_order')"
                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_in_sales_order'"
                        :class="productToggles['{{ $prod->id }}']?.show_in_sales_order ? 'bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] border-[#5856D6]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                        class="h-6 px-2 rounded-[6px] border text-[11px] font-medium">SO</button>
                <button type="button" @click="toggleProductField('{{ $prod->id }}', 'show_price_on_web')"
                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'show_price_on_web'"
                        :class="productToggles['{{ $prod->id }}']?.show_price_on_web ? 'bg-[#30B0C7]/10 text-[#0071A4] dark:text-[#70D7FF] border-[#30B0C7]/30 font-semibold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60 line-through'"
                        class="h-6 px-2 rounded-[6px] border text-[11px] font-medium">Harga</button>
                <button type="button" @click="toggleProductField('{{ $prod->id }}', 'is_preorder')"
                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_preorder'"
                        :class="productToggles['{{ $prod->id }}']?.is_preorder ? 'bg-[#FF9500]/10 text-[#FF9500] border-[#FF9500]/30 font-bold' : 'bg-black/[0.03] dark:bg-white/[0.04] text-black/35 dark:text-white/35 border-black/5 dark:border-white/5 opacity-60'"
                        class="h-6 px-2 rounded-[6px] border text-[11px] font-medium">PO</button>
                <button type="button" @click="toggleProductField('{{ $prod->id }}', 'is_active')"
                        :disabled="productToggles['{{ $prod->id }}']?.loading === 'is_active'"
                        :class="productToggles['{{ $prod->id }}']?.is_active ? 'bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] border-[#34C759]/30' : 'bg-[#FF3B30]/10 text-[#FF3B30] border-[#FF3B30]/30 opacity-70'"
                        class="h-6 px-2 rounded-[6px] border text-[11px] font-medium">
                    <span x-text="productToggles['{{ $prod->id }}']?.is_active ? 'Aktif' : 'Nonaktif'"></span>
                </button>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-black/40 dark:text-white/40 text-[13px] bg-white dark:bg-[#1C1C1E] rounded-[16px] border border-black/[0.06] dark:border-white/[0.08]">
            Belum ada produk terdaftar.
        </div>
        @endforelse

        @if($products->hasPages())
        <div class="p-3">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL: TAMBAH PRODUK BARU (Full Layout XXL)        -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('products.create') || \App\Support\Context::hasPermission('products.manage'))
    <div x-show="showAddModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/30 backdrop-blur-md"
         @keydown.escape.window="showAddModal = false">
        <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="showAddModal = false">

            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

            <!-- Sticky Top Header -->
            <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate">Tambah Produk Baru</h2>
                        <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">Daftarkan barang jadi baru, model kalkulasi biaya, serta pengaturan etalase</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false"
                        class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0"
                        title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Content (Scrollable 12-Column Bento Grid) -->
            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-5 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- Kolom Kiri: Data Utama Produk (7 Kolom) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Informasi Dasar Produk</h3>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Nama Produk <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Roti Tawar Gandum / Kopi Susu Aren"
                                       class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="font-medium text-black/70 dark:text-white/70 text-[13px]">Satuan Output <span class="text-[#FF3B30]">*</span></label>
                                        <button type="button" @click="quickAddTarget = 'add'; showAddUnitModal = true" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>+ Satuan</span>
                                        </button>
                                    </div>
                                    <select name="output_unit_id" x-ref="newProductUnit" required class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <template x-for="u in unitList" :key="u.id">
                                            <option :value="u.id" x-text="u.name + ' (' + u.code + ')'" :selected="u.code === 'pcs'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="font-medium text-black/70 dark:text-white/70 text-[13px]">Kategori</label>
                                        <button type="button" @click="quickAddTarget = 'add'; showAddCategoryModal = true" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>+ Kategori</span>
                                        </button>
                                    </div>
                                    <select name="category_id" x-ref="newProductCategory" class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <option value="">-- Tanpa Kategori --</option>
                                        <template x-for="c in categoryList" :key="c.id">
                                            <option :value="c.id" x-text="c.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Metode Kalkulasi HPP <span class="text-[#FF3B30]">*</span></label>
                                <select name="costing_method" required class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
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
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Barcode / SKU (Opsional)</label>
                                <div class="flex gap-2">
                                    <input type="text" name="sku" x-ref="newProductBarcode" inputmode="numeric" autocomplete="off" placeholder="8991234567890 atau PRD-001"
                                           class="min-w-0 flex-1 h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <button type="button" @click="requestProductScanner('add')" class="shrink-0 h-11 px-3.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/15 flex items-center justify-center transition active:scale-95" title="Scan barcode dengan kamera ponsel/laptop">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Deskripsi Produk</label>
                                <textarea name="description" rows="3" placeholder="Deskripsi ringkas, komposisi, atau catatan produk..."
                                          class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] p-3 text-[16px] sm:text-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Finansial, Media & Saluran (5 Kolom) -->
                    <div class="lg:col-span-5 space-y-5">
                        <!-- Bento Box 1: Harga & Biaya -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Penetapan Harga &amp; Modal</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">HPP Standar (Modal)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                        <input type="number" step="any" name="base_cost" value="0" placeholder="0"
                                               class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-medium text-[#34C759] dark:text-[#30D158] mb-1 text-[12px]">Harga Jual Aktif</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                        <input type="number" step="any" name="selling_price" value="0" placeholder="0"
                                               class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">Batas Minimum Stok (Peringatan)</label>
                                <input type="number" step="any" name="min_stock" value="0" placeholder="0"
                                       class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                        </div>

                        <!-- Bento Box 2: Gambar Produk -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Foto Produk</h3>
                            <div x-show="newProductPreview" class="w-20 h-20 rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 relative">
                                <img :src="newProductPreview" alt="Preview Gambar Baru" class="w-full h-full object-cover">
                                <button type="button" @click="newProductPreview = ''; $refs.newProductImageInput.value = ''"
                                        class="absolute top-1 right-1 p-1 rounded-full bg-black/70 text-white hover:text-[#FF3B30] transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <input type="file" name="image" x-ref="newProductImageInput" @change="handleNewProductImage($event)" accept="image/jpeg,image/png,image/webp"
                                   class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-black/[0.06] dark:file:bg-white/[0.08] file:px-3 file:py-2 file:text-[12px] file:font-semibold">
                            <p class="text-[11px] text-black/45 dark:text-white/45">Maks. 4 MB (JPG, PNG, WebP). Tampil di etalase web dan kasir POS.</p>
                        </div>

                        <!-- Bento Box 3: Saluran Penjualan Multi-Channel -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Saluran Penjualan</h3>
                                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Multi-Channel</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                    <input type="hidden" name="show_in_pos" value="0">
                                    <input type="checkbox" name="show_in_pos" value="1" checked class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">Kasir POS</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                    <input type="hidden" name="show_in_sales_order" value="0">
                                    <input type="checkbox" name="show_in_sales_order" value="1" checked class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">Faktur SO</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                    <input type="hidden" name="show_in_website" value="0">
                                    <input type="checkbox" name="show_in_website" value="1" checked class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">Etalase Web</span>
                                </label>
                            </div>

                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="hidden" name="show_price_on_web" value="0">
                                    <input type="checkbox" name="show_price_on_web" value="1" checked class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <div>
                                        <span class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">Tampilkan Harga di Etalase Web</span>
                                        <span class="text-[11px] text-black/45 dark:text-white/45 block">Jika dinonaktifkan, harga disembunyikan dan dialihkan ke tombol konsultasi WhatsApp.</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Pre-Order Section -->
                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] space-y-2.5">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="hidden" name="is_preorder" value="0">
                                    <input type="checkbox" name="is_preorder" value="1" x-model="newIsPreorder" class="mt-0.5 rounded-[4px] border-black/20 text-[#FF9500] focus:ring-[#FF9500]">
                                    <div>
                                        <span class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">Sistem Pre-Order (PO)</span>
                                        <span class="text-[11px] text-black/45 dark:text-white/45 block">Wajibkan pemilihan tanggal pengambilan/pengiriman terjadwal saat checkout.</span>
                                    </div>
                                </label>

                                <div x-show="newIsPreorder" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                    <div>
                                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Skema PO</label>
                                        <select name="preorder_mode" x-model="newPreorderMode" class="w-full h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white focus:ring-1 focus:ring-[#FF9500]">
                                            <option value="customer_schedule">Jadwal Bebas Pelanggan</option>
                                            <option value="merchant_batch">Sesuai Jadwal Batch Toko</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Lead Time (Persiapan)</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="number" min="0" max="90" name="preorder_lead_days" x-model="newPreorderLeadDays" class="w-16 h-9 px-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white tabular-nums font-bold focus:ring-1 focus:ring-[#FF9500]">
                                            <span class="text-[12px] text-black/60 dark:text-white/60">Hari (H-x)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Bottom Action Footer -->
                <div class="sticky bottom-0 -mx-5 sm:-mx-8 -mb-5 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="showAddModal = false"
                            class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. MODAL: EDIT DATA PRODUK (Full Layout XXL)          -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('products.edit') || \App\Support\Context::hasPermission('products.manage'))
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/30 backdrop-blur-md"
         @keydown.escape.window="showEditModal = false">
        <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
             @click.outside="showEditModal = false">

            <!-- Mobile Grab Bar -->
            <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

            <!-- Sticky Top Header -->
            <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-10 h-10 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate">
                            Ubah Produk: <span x-text="editProduct.name" class="text-[#007AFF]"></span>
                        </h2>
                        <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">Perbarui rincian produk, harga jual, foto, dan status ketersediaan saluran</p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false"
                        class="w-8 sm:w-9 h-8 sm:h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] dark:hover:bg-white/[0.15] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0"
                        title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Content (Scrollable 12-Column Bento Grid) -->
            <form :action="'/products/' + editProduct.slug" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-5 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- Kolom Kiri: Data Utama Produk (7 Kolom) -->
                    <div class="lg:col-span-7 space-y-5">
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Informasi Dasar Produk</h3>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Nama Produk <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="name" x-model="editProduct.name" required
                                       class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="font-medium text-black/70 dark:text-white/70 text-[13px]">Satuan Output <span class="text-[#FF3B30]">*</span></label>
                                        <button type="button" @click="quickAddTarget = 'edit'; showAddUnitModal = true" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>+ Satuan</span>
                                        </button>
                                    </div>
                                    <select name="output_unit_id" x-model="editProduct.output_unit_id" required class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <template x-for="u in unitList" :key="u.id">
                                            <option :value="u.id" x-text="u.name + ' (' + u.code + ')'" :selected="String(u.id) === String(editProduct.output_unit_id)"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="font-medium text-black/70 dark:text-white/70 text-[13px]">Kategori</label>
                                        <button type="button" @click="quickAddTarget = 'edit'; showAddCategoryModal = true" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5">
                                            <span>+ Kategori</span>
                                        </button>
                                    </div>
                                    <select name="category_id" x-model="editProduct.category_id" class="w-full h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                        <option value="">-- Tanpa Kategori --</option>
                                        <template x-for="c in categoryList" :key="c.id">
                                            <option :value="c.id" x-text="c.name" :selected="String(c.id) === String(editProduct.category_id)"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Barcode / SKU</label>
                                <div class="flex gap-2">
                                    <input type="text" name="sku" x-model="editProduct.sku" inputmode="numeric" autocomplete="off" placeholder="8991234567890"
                                           class="min-w-0 flex-1 h-11 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                                    <button type="button" @click="requestProductScanner('edit')" class="shrink-0 h-11 px-3.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/15 flex items-center justify-center transition active:scale-95" title="Scan barcode dengan kamera">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[13px]">Deskripsi Produk</label>
                                <textarea name="description" rows="3" x-model="editProduct.description"
                                          class="w-full bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] p-3 text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Finansial, Media & Saluran (5 Kolom) -->
                    <div class="lg:col-span-5 space-y-5">
                        <!-- Bento Box 1: Harga & Biaya -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Penetapan Harga &amp; Modal</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">HPP Standar (Modal)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                        <input type="number" step="any" name="base_cost" x-model="editProduct.base_cost"
                                               class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-semibold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-medium text-[#34C759] dark:text-[#30D158] mb-1 text-[12px]">Harga Jual Aktif</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-medium text-[13px]">{{ $business->currency_symbol }}</span>
                                        <input type="number" step="any" name="selling_price" x-model="editProduct.selling_price"
                                               class="w-full h-10 pl-9 pr-3 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12px]">Batas Minimum Stok (Peringatan)</label>
                                <input type="number" step="any" name="min_stock" x-model="editProduct.min_stock"
                                       class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] px-3.5 text-[16px] sm:text-[14px] text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                            </div>
                        </div>

                        <!-- Bento Box 2: Gambar Produk -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                            <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Foto Produk</h3>
                            <div x-show="editProductPreview || editProduct.image_url" class="flex items-center gap-3.5">
                                <div class="w-16 h-16 rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 bg-black/[0.04] dark:bg-white/[0.06] relative shrink-0">
                                    <img :src="editProductPreview || editProduct.image_url" :alt="editProduct.name" class="w-full h-full object-cover">
                                </div>
                                <div class="text-[12px] text-black/50 dark:text-white/50">
                                    <span x-show="editProductPreview" class="text-[#007AFF] font-medium block">Foto baru dipilih</span>
                                    <span x-show="!editProductPreview && editProduct.image_url" class="block">Foto aktif saat ini</span>
                                    <span class="text-[11px] text-black/40 dark:text-white/40">Pilih berkas baru untuk mengganti.</span>
                                </div>
                            </div>
                            <input type="file" name="image" @change="handleEditProductImage($event)" accept="image/jpeg,image/png,image/webp"
                                   class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-3 file:rounded-[8px] file:border-0 file:bg-black/[0.06] dark:file:bg-white/[0.08] file:px-3 file:py-2 file:text-[12px] file:font-semibold">
                            <label x-show="editProduct.image_url" class="mt-2 flex items-center gap-2 text-[12px] text-black/60 dark:text-white/60 cursor-pointer">
                                <input type="checkbox" name="remove_image" value="1" class="rounded-[4px] border-black/20 text-[#FF3B30] focus:ring-[#FF3B30]">
                                <span>Hapus foto saat ini</span>
                            </label>
                        </div>

                        <!-- Bento Box 3: Saluran Penjualan Multi-Channel -->
                        <div class="p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-[14px] font-bold text-black dark:text-white tracking-tight">Saluran Penjualan</h3>
                                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">Multi-Channel</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                    <input type="hidden" name="show_in_pos" value="0">
                                    <input type="checkbox" name="show_in_pos" value="1" x-model="editProduct.show_in_pos" class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">Kasir POS</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                    <input type="hidden" name="show_in_sales_order" value="0">
                                    <input type="checkbox" name="show_in_sales_order" value="1" x-model="editProduct.show_in_sales_order" class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">Faktur SO</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] cursor-pointer hover:bg-black/[0.02] transition">
                                    <input type="hidden" name="show_in_website" value="0">
                                    <input type="checkbox" name="show_in_website" value="1" x-model="editProduct.show_in_website" class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <span class="text-[12px] font-semibold text-black/80 dark:text-white/80">Etalase Web</span>
                                </label>
                            </div>

                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="hidden" name="show_price_on_web" value="0">
                                    <input type="checkbox" name="show_price_on_web" value="1" x-model="editProduct.show_price_on_web" class="mt-0.5 rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                                    <div>
                                        <span class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">Tampilkan Harga di Etalase Web</span>
                                        <span class="text-[11px] text-black/45 dark:text-white/45 block">Jika dinonaktifkan, harga disembunyikan dan dialihkan ke tombol konsultasi WhatsApp.</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Pre-Order Section -->
                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06] space-y-2.5">
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="hidden" name="is_preorder" value="0">
                                    <input type="checkbox" name="is_preorder" value="1" x-model="editProduct.is_preorder" class="mt-0.5 rounded-[4px] border-black/20 text-[#FF9500] focus:ring-[#FF9500]">
                                    <div>
                                        <span class="text-[12px] font-semibold text-black/80 dark:text-white/80 block">Sistem Pre-Order (PO)</span>
                                        <span class="text-[11px] text-black/45 dark:text-white/45 block">Wajibkan pemilihan tanggal pengambilan/pengiriman terjadwal saat checkout.</span>
                                    </div>
                                </label>

                                <div x-show="editProduct.is_preorder" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                                    <div>
                                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Skema PO</label>
                                        <select name="preorder_mode" x-model="editProduct.preorder_mode" class="w-full h-9 px-2.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white focus:ring-1 focus:ring-[#FF9500]">
                                            <option value="customer_schedule">Jadwal Bebas Pelanggan</option>
                                            <option value="merchant_batch">Sesuai Jadwal Batch Toko</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60 mb-1">Lead Time (Persiapan)</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="number" min="0" max="90" name="preorder_lead_days" x-model="editProduct.preorder_lead_days" class="w-16 h-9 px-2 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[8px] text-[12px] text-black dark:text-white tabular-nums font-bold focus:ring-1 focus:ring-[#FF9500]">
                                            <span class="text-[12px] text-black/60 dark:text-white/60">Hari (H-x)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Active Toggle -->
                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" x-model="editProduct.is_active" class="rounded-[4px] border-black/20 text-[#34C759] focus:ring-[#34C759]">
                                    <span class="text-black/80 dark:text-white/80 font-semibold text-[13px]">Status Produk Aktif Secara Global</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Bottom Action Footer -->
                <div class="sticky bottom-0 -mx-5 sm:-mx-8 -mb-5 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" @click="showEditModal = false"
                            class="h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                        Batal
                    </button>
                    <button type="submit"
                            class="h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 8. SUB-MODALS: KATEGORI & SATUAN (AJAX Quick-Add)     -->
    <!-- ===================================================== -->
    <div x-show="showAddCategoryModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl" @click.outside="showAddCategoryModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Tambah Kategori Baru</h3>
                </div>
                <button type="button" @click="showAddCategoryModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form @submit.prevent="submitQuickCategory()" class="space-y-3.5 text-[13px]">
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Kategori <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" x-model="quickCategoryName" required placeholder="Contoh: Makanan Berat / Minuman Segar"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Deskripsi (Opsional)</label>
                    <input type="text" x-model="quickCategoryDesc" placeholder="Catatan kategori..."
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showAddCategoryModal = false" class="h-9 px-4 rounded-[10px] text-[12px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Batal</button>
                    <button type="submit" :disabled="quickCategoryLoading" class="h-9 px-4 rounded-[10px] text-[12px] font-semibold bg-[#007AFF] text-white disabled:opacity-50 flex items-center gap-1.5 shadow-sm">
                        <span x-show="quickCategoryLoading">Menyimpan...</span>
                        <span x-show="!quickCategoryLoading">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showAddUnitModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl" @click.outside="showAddUnitModal = false">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </div>
                    <h3 class="text-[15px] font-bold text-black dark:text-white">Tambah Satuan Baru</h3>
                </div>
                <button type="button" @click="showAddUnitModal = false" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form @submit.prevent="submitQuickUnit()" class="space-y-3.5 text-[13px]">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kode Simbol <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" x-model="quickUnitCode" required placeholder="pcs / cup"
                               class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3 text-[16px] sm:text-[13px] text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50">
                    </div>
                    <div>
                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Kategori <span class="text-[#FF3B30]">*</span></label>
                        <select x-model="quickUnitCategory" required class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-2.5 text-[16px] sm:text-[12px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                            <option value="quantity">Kuantitas (pcs)</option>
                            <option value="weight">Berat (g, kg)</option>
                            <option value="volume">Volume (ml, l)</option>
                            <option value="custom">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-medium text-black/70 dark:text-white/70 mb-1">Nama Satuan <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" x-model="quickUnitName" required placeholder="Contoh: Porsi / Botol / Lembar"
                           class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[16px] sm:text-[13px] text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50">
                </div>
                <div class="pt-2 flex justify-end gap-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showAddUnitModal = false" class="h-9 px-4 rounded-[10px] text-[12px] font-medium bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Batal</button>
                    <button type="submit" :disabled="quickUnitLoading" class="h-9 px-4 rounded-[10px] text-[12px] font-semibold bg-[#007AFF] text-white disabled:opacity-50 flex items-center gap-1.5 shadow-sm">
                        <span x-show="quickUnitLoading">Menyimpan...</span>
                        <span x-show="!quickUnitLoading">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 9. SCANNER MODALS (Apple Frosted Glass)                -->
    <!-- ===================================================== -->
    <div x-show="showProductScannerPermission" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
        <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /></svg>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Izinkan Akses Kamera?</h3>
                    <p class="text-[12.5px] text-black/60 dark:text-white/60 mt-1 leading-snug">Kamera digunakan untuk membaca barcode produk secara otomatis. Akses langsung dilepas setelah scanner ditutup.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2.5 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="showProductScannerPermission = false" class="h-9 px-4 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80">Batal</button>
                <button type="button" @click="confirmProductScannerAccess()" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold bg-[#007AFF] text-white shadow-sm">Izinkan &amp; Mulai</button>
            </div>
        </div>
    </div>

    <div x-show="showProductScanner" x-cloak @keydown.escape.window="closeProductScanner()" class="fixed inset-0 z-[75] flex items-center justify-center p-4 bg-black/40 backdrop-blur-md">
        <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4 shadow-2xl" @click.outside="closeProductScanner()">
            <div class="flex items-center justify-between">
                <h3 class="text-[15px] font-bold text-black dark:text-white">Pindai Barcode Produk</h3>
                <button type="button" @click="closeProductScanner()" class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-[14px] bg-black">
                <video x-ref="productBarcodeVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-[72%] h-[42%] rounded-[12px] border-2 border-[#007AFF] shadow-[0_0_15px_rgba(0,122,255,0.4)]"></div>
                </div>
                <div x-show="productScannerStarting" class="absolute inset-0 flex items-center justify-center bg-black/60 text-[13px] text-white">
                    Menyiapkan kamera scanner...
                </div>
            </div>
            <div x-show="productScannerError" class="p-3 rounded-[10px] bg-[#FF3B30]/10 text-[12px] text-[#FF3B30]" x-text="productScannerError"></div>
            <div class="flex justify-end pt-1">
                <button type="button" @click="closeProductScanner()" class="h-9 px-4 rounded-[10px] text-[13px] bg-black/[0.06] dark:bg-white/[0.08] text-black/80 dark:text-white/80 font-medium">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 10. APPLE ALERT DIALOG (Hapus Produk)                  -->
    <!-- ===================================================== -->
    @if(\App\Support\Context::hasPermission('products.delete') || \App\Support\Context::hasPermission('products.manage'))
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/30 backdrop-blur-sm">
        <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
             @click.outside="closeDelete()">
            <div class="px-5 pt-6 pb-4">
                <div class="w-10 h-10 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
                <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">Hapus Produk?</h4>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                    <span x-text="deleteTarget.name" class="font-semibold text-black dark:text-white"></span> akan dihapus beserta seluruh konfigurasi biayanya.
                </p>
                <div class="mt-3 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/50 dark:text-white/50 text-left flex items-start gap-1.5">
                    <svg class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    <span>Tenang: Riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan.</span>
                </div>
            </div>
            <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
