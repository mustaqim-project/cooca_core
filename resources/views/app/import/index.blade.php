@extends('layouts.app', [
    'title' => 'Import Bahan Baku, Produk & Resep Excel',
    'headerTitle' => 'Import Massal Data Bisnis',
    'headerSubtitle' => 'Unggah katalog bahan baku, produk jadi, dan formula resep/BOM dari file Excel atau CSV dengan validasi anti-duplikat',
])

@section('content')
    <div class="max-w-[1360px] mx-auto space-y-5 pb-12" x-data="{
        activeTab: '{{ session('active_tab', $activeTab) }}',
        canImport: {{ $canImport ? 'true' : 'false' }},

        // Material Import State
        materialFile: null,
        materialFileName: '',
        materialLoading: false,
        materialPreview: {{ session('material_preview') ? Js::from(session('material_preview')) : 'null' }},
        materialFilter: 'all',
        materialDuplicateStrategy: 'skip',
        materialExecuting: false,

        // Product Import State
        productFile: null,
        productFileName: '',
        productLoading: false,
        productPreview: {{ session('product_preview') ? Js::from(session('product_preview')) : 'null' }},
        productFilter: 'all',
        productDuplicateStrategy: 'skip',
        productExecuting: false,

        // Recipe Import State
        recipeFile: null,
        recipeFileName: '',
        recipeLoading: false,
        recipePreview: {{ session('recipe_preview') ? Js::from(session('recipe_preview')) : 'null' }},
        recipeFilter: 'all',
        recipeDuplicateStrategy: 'skip',
        recipeExecuting: false,

        // Inventory Import State
        inventoryFile: null,
        inventoryFileName: '',
        inventoryLoading: false,
        inventoryPreview: {{ session('inventory_preview') ? Js::from(session('inventory_preview')) : 'null' }},
        inventoryFilter: 'all',
        inventoryDuplicateStrategy: 'skip',
        inventoryExecuting: false,

        // Material Methods
        handleMaterialFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.materialFile = file;
                this.materialFileName = file.name;
                this.uploadAndPreviewMaterial();
            }
        },

        uploadAndPreviewMaterial() {
            if (!this.canImport) return;
            if (!this.materialFile) return;

            this.materialLoading = true;
            const formData = new FormData();
            formData.append('file', this.materialFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('import.materials.preview') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel.'); });
                    }
                    return response.json();
                })
                .then(res => {
                    this.materialLoading = false;
                    if (res.success) {
                        this.materialPreview = res.data;
                    } else {
                        AppAlert.error(res.message || 'Gagal menganalisis file.');
                    }
                })
                .catch(err => {
                    this.materialLoading = false;
                    AppAlert.error(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        async executeMaterialImport() {
            if (!this.materialPreview || !this.materialPreview.rows || this.materialPreview.rows.length === 0) {
                AppAlert.warning('Tidak ada data bahan baku yang siap diimport.');
                return;
            }

            const confirmed = await AppAlert.confirm({
                title: 'Import Bahan Baku?',
                message: 'Apakah Anda yakin ingin memproses import bahan baku ini ke database bisnis?',
                type: 'info',
                confirmText: 'Ya, Proses Import',
                cancelText: 'Batal'
            });
            if (!confirmed) {
                return;
            }

            this.materialExecuting = true;
            fetch('{{ route('import.materials.execute') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        rows: this.materialPreview.rows,
                        duplicate_strategy: this.materialDuplicateStrategy
                    })
                })
                .then(response => response.json())
                .then(res => {
                    this.materialExecuting = false;
                    if (res.success) {
                        window.location.href = res.redirect_url || '{{ route('materials.index') }}';
                    } else {
                        AppAlert.error(res.message || 'Gagal mengimport bahan baku.');
                    }
                })
                .catch(err => {
                    this.materialExecuting = false;
                    AppAlert.error('Terjadi kesalahan saat mengeksekusi import bahan baku.');
                });
        },

        // Product Methods
        handleProductFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.productFile = file;
                this.productFileName = file.name;
                this.uploadAndPreviewProduct();
            }
        },

        uploadAndPreviewProduct() {
            if (!this.canImport) return;
            if (!this.productFile) return;

            this.productLoading = true;
            const formData = new FormData();
            formData.append('file', this.productFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('import.products.preview') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel.'); });
                    }
                    return response.json();
                })
                .then(res => {
                    this.productLoading = false;
                    if (res.success) {
                        this.productPreview = res.data;
                    } else {
                        AppAlert.error(res.message || 'Gagal menganalisis file.');
                    }
                })
                .catch(err => {
                    this.productLoading = false;
                    AppAlert.error(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        async executeProductImport() {
            if (!this.productPreview || !this.productPreview.rows || this.productPreview.rows.length === 0) {
                AppAlert.warning('Tidak ada data yang siap diimport.');
                return;
            }

            const confirmed = await AppAlert.confirm({
                title: 'Import Produk?',
                message: 'Apakah Anda yakin ingin memproses import produk ini ke database bisnis?',
                type: 'info',
                confirmText: 'Ya, Proses Import',
                cancelText: 'Batal'
            });
            if (!confirmed) {
                return;
            }

            this.productExecuting = true;
            fetch('{{ route('import.products.execute') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        rows: this.productPreview.rows,
                        duplicate_strategy: this.productDuplicateStrategy
                    })
                })
                .then(response => response.json())
                .then(res => {
                    this.productExecuting = false;
                    if (res.success) {
                        window.location.href = res.redirect_url || '{{ route('products.index') }}';
                    } else {
                        AppAlert.error(res.message || 'Gagal mengimport produk.');
                    }
                })
                .catch(err => {
                    this.productExecuting = false;
                    AppAlert.error('Terjadi kesalahan saat mengeksekusi import.');
                });
        },

        // Recipe Methods
        handleRecipeFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.recipeFile = file;
                this.recipeFileName = file.name;
                this.uploadAndPreviewRecipe();
            }
        },

        uploadAndPreviewRecipe() {
            if (!this.canImport) return;
            if (!this.recipeFile) return;

            this.recipeLoading = true;
            const formData = new FormData();
            formData.append('file', this.recipeFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('import.recipes.preview') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel.'); });
                    }
                    return response.json();
                })
                .then(res => {
                    this.recipeLoading = false;
                    if (res.success) {
                        this.recipePreview = res.data;
                    } else {
                        AppAlert.error(res.message || 'Gagal menganalisis file.');
                    }
                })
                .catch(err => {
                    this.recipeLoading = false;
                    AppAlert.error(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        async executeRecipeImport() {
            if (!this.recipePreview || !this.recipePreview.rows || this.recipePreview.rows.length === 0) {
                AppAlert.warning('Tidak ada data resep yang siap diimport.');
                return;
            }

            const confirmed = await AppAlert.confirm({
                title: 'Import Resep / BOM?',
                message: 'Apakah Anda yakin ingin memproses import resep / BOM ini ke database bisnis?',
                type: 'info',
                confirmText: 'Ya, Proses Import',
                cancelText: 'Batal'
            });
            if (!confirmed) {
                return;
            }

            this.recipeExecuting = true;
            fetch('{{ route('import.recipes.execute') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        rows: this.recipePreview.rows,
                        duplicate_strategy: this.recipeDuplicateStrategy
                    })
                })
                .then(response => response.json())
                .then(res => {
                    this.recipeExecuting = false;
                    if (res.success) {
                        window.location.href = res.redirect_url || '{{ route('products.index') }}';
                    } else {
                        AppAlert.error(res.message || 'Gagal mengimport resep.');
                    }
                })
                .catch(err => {
                    this.recipeExecuting = false;
                    AppAlert.error('Terjadi kesalahan saat mengeksekusi import resep.');
                });
        },

        // Inventory Methods
        handleInventoryFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.inventoryFile = file;
                this.inventoryFileName = file.name;
                this.uploadAndPreviewInventory();
            }
        },

        uploadAndPreviewInventory() {
            if (!this.canImport) return;
            if (!this.inventoryFile) return;

            this.inventoryLoading = true;
            const formData = new FormData();
            formData.append('file', this.inventoryFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('import.inventory.preview') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel.'); });
                    }
                    return response.json();
                })
                .then(res => {
                    this.inventoryLoading = false;
                    if (res.success) {
                        this.inventoryPreview = res.data;
                    } else {
                        AppAlert.error(res.message || 'Gagal menganalisis file saldo stok.');
                    }
                })
                .catch(err => {
                    this.inventoryLoading = false;
                    AppAlert.error(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        async executeInventoryImport() {
            if (!this.inventoryPreview || !this.inventoryPreview.rows || this.inventoryPreview.rows.length === 0) {
                AppAlert.warning('Tidak ada data saldo stok yang siap diimport.');
                return;
            }

            const confirmed = await AppAlert.confirm({
                title: 'Catat Saldo Stok?',
                message: 'Apakah Anda yakin ingin mencatat saldo stok ini ke dalam sistem inventori? Proses ini TIDAK dapat diurungkan.',
                type: 'danger',
                confirmText: 'Ya, Catat Saldo Stok',
                cancelText: 'Batal'
            });
            if (!confirmed) {
                return;
            }

            this.inventoryExecuting = true;
            fetch('{{ route('import.inventory.execute') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        rows: this.inventoryPreview.rows,
                        duplicate_strategy: this.inventoryDuplicateStrategy
                    })
                })
                .then(response => response.json())
                .then(res => {
                    this.inventoryExecuting = false;
                    if (res.success) {
                        window.location.href = res.redirect_url || '{{ route('inventory.stocks') }}';
                    } else {
                        AppAlert.error(res.message || 'Gagal mengimport saldo stok.');
                    }
                })
                .catch(err => {
                    this.inventoryExecuting = false;
                    AppAlert.error('Terjadi kesalahan saat mengeksekusi import saldo stok.');
                });
        }
    }">

        {{-- ========================================================== --}}
        {{-- TOOLBAR / PAGE HEADER --}}
        {{-- ========================================================== --}}
        <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                    <span>›</span>
                    <span class="text-black/70 dark:text-white/70 font-medium">Data Operasional</span>
                    <span>›</span>
                    <span class="text-black dark:text-white font-medium">Import Massal</span>
                </nav>
                <div class="flex items-center gap-2">
                    <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Import Massal Data Bisnis</h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20">
                        {{ $business->name }}
                    </span>
                </div>
                <p class="text-[13px] text-black/50 dark:text-white/50">Unggah ratusan katalog bahan baku, produk jadi, formula resep BOM, dan saldo stok via Excel/CSV</p>
            </div>

            <div class="flex items-center flex-wrap gap-2">
                <a href="{{ route('materials.index') }}"
                    class="h-9 px-3.5 rounded-[10px] text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3"/></svg>
                    <span>Master Bahan</span>
                </a>
                <a href="{{ route('products.index') }}"
                    class="h-9 px-3.5 rounded-[10px] text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                    <span>Katalog Produk</span>
                </a>
                <a href="{{ route('inventory.stocks') }}"
                    class="h-9 px-3.5 rounded-[10px] text-[12px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#AF52DE]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    <span>Inventori</span>
                </a>
            </div>
        </header>

        {{-- ========================================================== --}}
        {{-- PRO PAYWALL BANNER (IF FREE PLAN) --}}
        {{-- ========================================================== --}}
        @if (!$canImport)
            <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-[#FF9500]/30 dark:border-[#FF9F0A]/30 p-5 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                    <div class="space-y-2 max-w-3xl">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/10 text-[#B26A00] dark:text-[#FF9F0A] border border-[#FF9500]/20">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                <span>Fitur Paket Pro / Patungan</span>
                            </span>
                            <span class="text-[12px] text-black/50 dark:text-white/50 font-medium">Cooca Unlimited Suite</span>
                        </div>

                        <h3 class="text-[16px] font-semibold text-black dark:text-white tracking-tight">Import Massal Excel & CSV Terkunci untuk Akun Free</h3>
                        <p class="text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                            Fitur Import Bahan, Produk & Resep Massal dirancang untuk memangkas waktu input data ratusan baris menjadi hitungan detik. Anda tetap dapat <strong>mengunduh template Excel</strong> di bawah untuk mempersiapkan data Anda.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1.5">
                            <div class="px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[12px] text-black/70 dark:text-white/70 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#FF9500] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                                <span>Import ratusan item sekaligus</span>
                            </div>
                            <div class="px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[12px] text-black/70 dark:text-white/70 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#34C759] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-7.5-6.5h10.5A2.25 2.25 0 0120.25 5.5v13a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.5V5.5A2.25 2.25 0 015.25 3.25z"/></svg>
                                <span>Proteksi Anti-Duplikat SKU</span>
                            </div>
                            <div class="px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[12px] text-black/70 dark:text-white/70 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#007AFF] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Live Preview & Verifikasi</span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <a href="{{ route('billing.limits') }}"
                            class="h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-[0_1px_2px_rgba(255,149,0,0.3)]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                            <span>Upgrade ke Pro / Patungan</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{-- APPLE SEGMENTED TAB BAR --}}
        {{-- ========================================================== --}}
        <div class="p-1 rounded-[12px] bg-black/[0.06] dark:bg-white/[0.08] border border-black/5 dark:border-white/5 inline-flex flex-wrap gap-1 max-w-full overflow-x-auto">
            <button type="button" @click="activeTab = 'materials'"
                class="h-9 px-4 rounded-[9px] text-[13px] transition-all flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'materials' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-sm' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'">
                <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3"/></svg>
                <span>1. Bahan Baku</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-mono bg-black/[0.05] dark:bg-white/[0.1]">{{ $totalMaterials }}</span>
            </button>

            <button type="button" @click="activeTab = 'products'"
                class="h-9 px-4 rounded-[9px] text-[13px] transition-all flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'products' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-sm' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'">
                <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                <span>2. Katalog Produk</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-mono bg-black/[0.05] dark:bg-white/[0.1]">{{ $totalProducts }}</span>
            </button>

            <button type="button" @click="activeTab = 'recipes'"
                class="h-9 px-4 rounded-[9px] text-[13px] transition-all flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'recipes' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-sm' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'">
                <svg class="w-4 h-4 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5m4.75-11.396a24.646 24.646 0 014.5 0m-4.5 0L5 14.5m9.75-11.396v5.714c0 .597.237 1.17.659 1.591L19 14.5m-4.25-11.396L19 14.5m-14 0l1.75 5.25A2.25 2.25 0 008.884 21h6.232a2.25 2.25 0 002.134-1.25L19 14.5m-14 0h14"/></svg>
                <span>3. Resep / BOM</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-mono bg-black/[0.05] dark:bg-white/[0.1]">{{ $totalRecipes }}</span>
            </button>

            <button type="button" @click="activeTab = 'inventory'"
                class="h-9 px-4 rounded-[9px] text-[13px] transition-all flex items-center gap-2 whitespace-nowrap"
                :class="activeTab === 'inventory' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-semibold shadow-sm' :
                    'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'">
                <svg class="w-4 h-4 text-[#AF52DE]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                <span>4. Saldo Stok Awal</span>
                <span class="px-1.5 py-0.2 rounded-full text-[11px] tabular-nums font-mono bg-black/[0.05] dark:bg-white/[0.1]">{{ $totalStocks }}</span>
            </button>
        </div>

        {{-- ========================================================== --}}
        {{-- TAB 1: IMPORT BAHAN BAKU --}}
        {{-- ========================================================== --}}
        <div x-show="activeTab === 'materials'" class="space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Step 1: Download Template --}}
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-[#34C759] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#34C759]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                            <span>Download Template Excel</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px]">Format Master Bahan</h3>
                        <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                            Format kolom mencakup Nama Bahan Baku, Kode / SKU, Kategori, Satuan Dasar, Harga Beli Standar, dan Pemasok.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <a href="{{ route('import.materials.template', ['format' => 'xlsx']) }}"
                            class="w-full h-9 rounded-[10px] bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20 text-[13px] font-semibold transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.375a1.125 1.125 0 01-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z"/></svg>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.materials.template', ['format' => 'csv']) }}"
                            class="w-full h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 text-[12px] font-medium transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span>Download CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                {{-- Step 2: Upload File Zone --}}
                <div class="lg:col-span-2 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#34C759] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#34C759]/15 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>Upload File & Analisis Otomatis</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#34C759] dark:hover:border-[#30D158] rounded-[12px] p-6 text-center transition-all bg-black/[0.01] dark:bg-white/[0.01] relative group">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleMaterialFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="space-y-2 pointer-events-none">
                                <div class="w-11 h-11 rounded-[12px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] mx-auto flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                                </div>
                                <div class="text-[13px] text-black/80 dark:text-white/80">
                                    <span class="font-semibold text-[#007AFF]">Klik untuk memilih file</span> atau seret dokumen ke sini
                                </div>
                                <p class="text-[11px] text-black/40 dark:text-white/40">Format: Excel (.xlsx, .xls) atau CSV</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-black/10 dark:border-white/10 rounded-[12px] p-6 text-center bg-black/[0.02] dark:bg-white/[0.02] space-y-3">
                            <div class="w-10 h-10 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-black/40 dark:text-white/40 mx-auto flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            </div>
                            <div class="text-[12px] text-black/60 dark:text-white/60">
                                Upload bahan terkunci pada paket Free. Silakan upgrade ke Pro / Patungan.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 h-8 px-3.5 rounded-[9px] bg-[#FF9500] text-white font-semibold text-[12px] hover:bg-[#E08500] transition-all">
                                <span>Buka Kunci Fitur Import</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    @endif

                    <div x-show="materialLoading"
                        class="p-3 rounded-[10px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center gap-3">
                        <div class="w-4 h-4 border-2 border-[#34C759] border-t-transparent rounded-full animate-spin shrink-0"></div>
                        <div class="text-[12px] text-[#248A3D] dark:text-[#30D158] font-medium">Menganalisis file bahan baku & mengecek duplikat...</div>
                    </div>

                    <div x-show="materialFileName && !materialLoading"
                        class="flex items-center justify-between text-[12px] px-3.5 py-2.5 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80">
                            <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-mono font-medium" x-text="materialFileName"></span>
                        </div>
                        <button type="button" @click="materialPreview = null; materialFile = null; materialFileName = ''"
                            class="text-black/50 hover:text-[#FF3B30] dark:text-white/50 dark:hover:text-[#FF453A] text-[12px] font-medium transition-colors">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3: Material Preview Table --}}
            <div x-show="materialPreview" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4" x-transition>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-black/5 dark:border-white/5">
                    <div>
                        <div class="flex items-center gap-2 text-[#34C759] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#34C759]/15 flex items-center justify-center text-[11px] font-bold">3</span>
                            <span>Preview & Konfirmasi Import Bahan Baku</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px] mt-0.5">Hasil Analisis Data Bahan Baku</h3>
                    </div>

                    {{-- Filter segmented pills --}}
                    <div class="flex items-center flex-wrap gap-1.5">
                        <button type="button" @click="materialFilter = 'all'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="materialFilter === 'all' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white border-transparent font-semibold' :
                                'bg-transparent text-black/60 dark:text-white/60 border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5'">
                            <span>Semua</span>
                            <span class="font-mono tabular-nums font-bold" x-text="materialPreview ? materialPreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="materialFilter = 'valid'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="materialFilter === 'valid' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                'bg-transparent text-[#34C759] border-black/10 dark:border-white/10 hover:bg-[#34C759]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>Siap Import</span>
                            <span class="font-mono tabular-nums font-bold" x-text="materialPreview ? materialPreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="materialFilter = 'duplicate'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="materialFilter === 'duplicate' ? 'bg-[#FF9500]/15 text-[#B26A00] dark:text-[#FF9F0A] border-[#FF9500]/30 font-semibold' :
                                'bg-transparent text-[#FF9500] border-black/10 dark:border-white/10 hover:bg-[#FF9500]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                            <span>Duplikat</span>
                            <span class="font-mono tabular-nums font-bold" x-text="materialPreview ? materialPreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="materialFilter = 'error'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="materialFilter === 'error' ? 'bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] border-[#FF3B30]/30 font-semibold' :
                                'bg-transparent text-[#FF3B30] border-black/10 dark:border-white/10 hover:bg-[#FF3B30]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                            <span>Error</span>
                            <span class="font-mono tabular-nums font-bold" x-text="materialPreview ? materialPreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                {{-- Duplicate Strategy Options --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-[12px]">
                    <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-medium">
                        <svg class="w-4 h-4 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-7.5-6.5h10.5A2.25 2.25 0 0120.25 5.5v13a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.5V5.5A2.25 2.25 0 015.25 3.25z"/></svg>
                        <span>Tindakan jika ditemukan bahan baku duplikat (SKU / Nama sama):</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="mat_dup_strategy" value="skip" x-model="materialDuplicateStrategy" class="text-[#007AFF] focus:ring-[#007AFF]">
                            <span>Lewati Duplikat (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="mat_dup_strategy" value="update" x-model="materialDuplicateStrategy" class="text-[#007AFF] focus:ring-[#007AFF]">
                            <span>Perbarui Data & Catat Harga Baru</span>
                        </label>
                    </div>
                </div>

                {{-- Preview Table Content --}}
                <div class="overflow-x-auto max-h-96 rounded-[10px] border border-black/5 dark:border-white/5">
                    <table class="w-full text-left text-[13px]">
                        <thead class="sticky top-0 bg-[#F2F2F7] dark:bg-[#2C2C2E] text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider border-b border-black/5 dark:border-white/5 z-10">
                            <tr>
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Nama Bahan Baku</th>
                                <th class="py-2.5 px-3">SKU</th>
                                <th class="py-2.5 px-3">Kategori</th>
                                <th class="py-2.5 px-3">Satuan</th>
                                <th class="py-2.5 px-3 text-right">Harga Beli</th>
                                <th class="py-2.5 px-3">Pemasok</th>
                                <th class="py-2.5 px-3">Keterangan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            <template x-for="row in (materialPreview ? materialPreview.rows : [])" :key="row.row_number">
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors"
                                    x-show="materialFilter === 'all' || materialFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-black/50 dark:text-white/50" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>Siap Import</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF9500]/10 text-[#B26A00] dark:text-[#FF9F0A] border border-[#FF9500]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                <span>Duplikat</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                                <span>Error</span>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.name || '-'"></td>
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-black/60 dark:text-white/60" x-text="row.sku || '-'"></td>
                                    <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.category || 'Umum'"></td>
                                    <td class="py-2.5 px-3 font-mono text-black/70 dark:text-white/70" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums text-[#34C759] dark:text-[#30D158] font-semibold"
                                        x-text="'Rp ' + (Number(row.purchase_price) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.supplier_name || '-'"></td>
                                    <td class="py-2.5 px-3 text-[12px]"
                                        :class="row.status === 'error' ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : (row.status === 'duplicate' ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black/50 dark:text-white/50')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/5">
                    <div class="text-[12px] text-black/60 dark:text-white/60">
                        Total <strong class="text-black dark:text-white tabular-nums" x-text="materialPreview ? materialPreview.total_rows : 0"></strong> bahan baku dianalisis.
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="materialPreview = null; materialFile = null"
                            class="h-9 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[13px] font-medium transition-all active:scale-[0.97]">
                            Batal
                        </button>

                        <button type="button" @click="executeMaterialImport()" :disabled="materialExecuting || (materialPreview && materialPreview.error_count > 0)"
                            class="h-9 px-5 rounded-[10px] bg-[#34C759] hover:bg-[#2DB34F] active:scale-[0.97] text-white font-semibold text-[13px] transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(52,199,89,0.25)] disabled:opacity-50 disabled:pointer-events-none">
                            <template x-if="materialExecuting">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </template>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-show="!materialExecuting"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Konfirmasi & Eksekusi Import Bahan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- TAB 2: IMPORT KATALOG PRODUK --}}
        {{-- ========================================================== --}}
        <div x-show="activeTab === 'products'" class="space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Step 1: Download Template --}}
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-[#007AFF] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                            <span>Download Template Produk</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px]">Format Spreadsheet Produk</h3>
                        <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                            Format kolom baku mencakup Nama Produk, SKU, Kategori, Satuan Output, Harga Jual, dan HPP Awal.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <a href="{{ route('import.products.template', ['format' => 'xlsx']) }}"
                            class="w-full h-9 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20 text-[13px] font-semibold transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.375a1.125 1.125 0 01-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z"/></svg>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.products.template', ['format' => 'csv']) }}"
                            class="w-full h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 text-[12px] font-medium transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span>Download CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                {{-- Step 2: Upload File Zone --}}
                <div class="lg:col-span-2 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#007AFF] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>Upload File & Analisis Otomatis</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] dark:hover:border-[#0A84FF] rounded-[12px] p-6 text-center transition-all bg-black/[0.01] dark:bg-white/[0.01] relative group">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleProductFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="space-y-2 pointer-events-none">
                                <div class="w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] mx-auto flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                                </div>
                                <div class="text-[13px] text-black/80 dark:text-white/80">
                                    <span class="font-semibold text-[#007AFF]">Klik untuk memilih file produk</span> atau seret dokumen ke sini
                                </div>
                                <p class="text-[11px] text-black/40 dark:text-white/40">Format: Microsoft Excel (.xlsx, .xls) atau CSV UTF-8</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-black/10 dark:border-white/10 rounded-[12px] p-6 text-center bg-black/[0.02] dark:bg-white/[0.02] space-y-3">
                            <div class="w-10 h-10 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-black/40 dark:text-white/40 mx-auto flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            </div>
                            <div class="text-[12px] text-black/60 dark:text-white/60">
                                Upload produk terkunci pada paket Free. Silakan upgrade ke Pro / Patungan.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 h-8 px-3.5 rounded-[9px] bg-[#FF9500] text-white font-semibold text-[12px] hover:bg-[#E08500] transition-all">
                                <span>Buka Kunci Fitur Import</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    @endif

                    <div x-show="productLoading"
                        class="p-3 rounded-[10px] bg-[#007AFF]/10 border border-[#007AFF]/20 flex items-center gap-3">
                        <div class="w-4 h-4 border-2 border-[#007AFF] border-t-transparent rounded-full animate-spin shrink-0"></div>
                        <div class="text-[12px] text-[#007AFF] dark:text-[#0A84FF] font-medium">Menganalisis file dan mengecek duplikat di database...</div>
                    </div>

                    <div x-show="productFileName && !productLoading"
                        class="flex items-center justify-between text-[12px] px-3.5 py-2.5 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80">
                            <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-mono font-medium" x-text="productFileName"></span>
                        </div>
                        <button type="button" @click="productPreview = null; productFile = null; productFileName = ''"
                            class="text-black/50 hover:text-[#FF3B30] dark:text-white/50 dark:hover:text-[#FF453A] text-[12px] font-medium transition-colors">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3: Product Preview Table --}}
            <div x-show="productPreview" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4" x-transition>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-black/5 dark:border-white/5">
                    <div>
                        <div class="flex items-center gap-2 text-[#007AFF] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 flex items-center justify-center text-[11px] font-bold">3</span>
                            <span>Preview & Konfirmasi Import Produk</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px] mt-0.5">Hasil Analisis Data Produk</h3>
                    </div>

                    {{-- Filter segmented pills --}}
                    <div class="flex items-center flex-wrap gap-1.5">
                        <button type="button" @click="productFilter = 'all'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="productFilter === 'all' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white border-transparent font-semibold' :
                                'bg-transparent text-black/60 dark:text-white/60 border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5'">
                            <span>Semua</span>
                            <span class="font-mono tabular-nums font-bold" x-text="productPreview ? productPreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="productFilter = 'valid'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="productFilter === 'valid' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                'bg-transparent text-[#34C759] border-black/10 dark:border-white/10 hover:bg-[#34C759]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>Siap Import</span>
                            <span class="font-mono tabular-nums font-bold" x-text="productPreview ? productPreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="productFilter = 'duplicate'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="productFilter === 'duplicate' ? 'bg-[#FF9500]/15 text-[#B26A00] dark:text-[#FF9F0A] border-[#FF9500]/30 font-semibold' :
                                'bg-transparent text-[#FF9500] border-black/10 dark:border-white/10 hover:bg-[#FF9500]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                            <span>Duplikat</span>
                            <span class="font-mono tabular-nums font-bold" x-text="productPreview ? productPreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="productFilter = 'error'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="productFilter === 'error' ? 'bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] border-[#FF3B30]/30 font-semibold' :
                                'bg-transparent text-[#FF3B30] border-black/10 dark:border-white/10 hover:bg-[#FF3B30]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                            <span>Error</span>
                            <span class="font-mono tabular-nums font-bold" x-text="productPreview ? productPreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                {{-- Duplicate Strategy Options --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-[12px]">
                    <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-medium">
                        <svg class="w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-7.5-6.5h10.5A2.25 2.25 0 0120.25 5.5v13a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.5V5.5A2.25 2.25 0 015.25 3.25z"/></svg>
                        <span>Tindakan jika ditemukan produk duplikat (SKU / Nama sama):</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="prod_dup_strategy" value="skip" x-model="productDuplicateStrategy" class="text-[#007AFF] focus:ring-[#007AFF]">
                            <span>Lewati Duplikat (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="prod_dup_strategy" value="update" x-model="productDuplicateStrategy" class="text-[#007AFF] focus:ring-[#007AFF]">
                            <span>Perbarui Data Lama (Update)</span>
                        </label>
                    </div>
                </div>

                {{-- Preview Table Content --}}
                <div class="overflow-x-auto max-h-96 rounded-[10px] border border-black/5 dark:border-white/5">
                    <table class="w-full text-left text-[13px]">
                        <thead class="sticky top-0 bg-[#F2F2F7] dark:bg-[#2C2C2E] text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider border-b border-black/5 dark:border-white/5 z-10">
                            <tr>
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Nama Produk</th>
                                <th class="py-2.5 px-3">SKU</th>
                                <th class="py-2.5 px-3">Kategori</th>
                                <th class="py-2.5 px-3">Satuan</th>
                                <th class="py-2.5 px-3 text-right">Harga Jual</th>
                                <th class="py-2.5 px-3 text-right">HPP Modal</th>
                                <th class="py-2.5 px-3">Keterangan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            <template x-for="row in (productPreview ? productPreview.rows : [])" :key="row.row_number">
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors"
                                    x-show="productFilter === 'all' || productFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-black/50 dark:text-white/50" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>Siap Import</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF9500]/10 text-[#B26A00] dark:text-[#FF9F0A] border border-[#FF9500]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                <span>Duplikat</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                                <span>Error</span>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.name || '-'"></td>
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-black/60 dark:text-white/60" x-text="row.sku || '-'"></td>
                                    <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.category || 'Umum'"></td>
                                    <td class="py-2.5 px-3 font-mono text-black/70 dark:text-white/70" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums text-[#007AFF] dark:text-[#0A84FF] font-semibold"
                                        x-text="'Rp ' + (Number(row.selling_price) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums text-black/70 dark:text-white/70"
                                        x-text="'Rp ' + (Number(row.base_cost) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-[12px]"
                                        :class="row.status === 'error' ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : (row.status === 'duplicate' ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black/50 dark:text-white/50')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/5">
                    <div class="text-[12px] text-black/60 dark:text-white/60">
                        Total <strong class="text-black dark:text-white tabular-nums" x-text="productPreview ? productPreview.total_rows : 0"></strong> produk dianalisis.
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="productPreview = null; productFile = null"
                            class="h-9 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[13px] font-medium transition-all active:scale-[0.97]">
                            Batal
                        </button>

                        <button type="button" @click="executeProductImport()" :disabled="productExecuting"
                            class="h-9 px-5 rounded-[10px] bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.97] text-white font-semibold text-[13px] transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(0,122,255,0.25)] disabled:opacity-50 disabled:pointer-events-none">
                            <template x-if="productExecuting">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </template>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-show="!productExecuting"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Konfirmasi & Eksekusi Import Produk</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- TAB 3: IMPORT RESEP / BOM --}}
        {{-- ========================================================== --}}
        <div x-show="activeTab === 'recipes'" class="space-y-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Step 1: Download Template --}}
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-[#5856D6] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#5856D6]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                            <span>Download Template Resep</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px]">Format BOM Formula</h3>
                        <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                            Format template mencakup Produk Jadi, Bahan Baku, Jumlah Takaran, Satuan Bahan, dan Susut (Waste %).
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <a href="{{ route('import.recipes.template', ['format' => 'xlsx']) }}"
                            class="w-full h-9 rounded-[10px] bg-[#5856D6]/10 hover:bg-[#5856D6]/20 text-[#5856D6] dark:text-[#5E5CE6] border border-[#5856D6]/20 text-[13px] font-semibold transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.375a1.125 1.125 0 01-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z"/></svg>
                            <span>Download Template Resep (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.recipes.template', ['format' => 'csv']) }}"
                            class="w-full h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 text-[12px] font-medium transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span>Download CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                {{-- Step 2: Upload File Zone --}}
                <div class="lg:col-span-2 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#5856D6] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#5856D6]/15 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>Upload File Resep & Analisis</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#5856D6] dark:hover:border-[#5E5CE6] rounded-[12px] p-6 text-center transition-all bg-black/[0.01] dark:bg-white/[0.01] relative group">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleRecipeFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="space-y-2 pointer-events-none">
                                <div class="w-11 h-11 rounded-[12px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] mx-auto flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                                </div>
                                <div class="text-[13px] text-black/80 dark:text-white/80">
                                    <span class="font-semibold text-[#5856D6]">Klik untuk memilih file resep</span> atau seret dokumen ke sini
                                </div>
                                <p class="text-[11px] text-black/40 dark:text-white/40">Pastikan Produk Jadi dan Bahan Baku sudah terdaftar di sistem</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-black/10 dark:border-white/10 rounded-[12px] p-6 text-center bg-black/[0.02] dark:bg-white/[0.02] space-y-3">
                            <div class="w-10 h-10 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-black/40 dark:text-white/40 mx-auto flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            </div>
                            <div class="text-[12px] text-black/60 dark:text-white/60">
                                Upload resep terkunci pada paket Free. Silakan upgrade ke Pro / Patungan.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 h-8 px-3.5 rounded-[9px] bg-[#FF9500] text-white font-semibold text-[12px] hover:bg-[#E08500] transition-all">
                                <span>Buka Kunci Fitur Import</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    @endif

                    <div x-show="recipeLoading"
                        class="p-3 rounded-[10px] bg-[#5856D6]/10 border border-[#5856D6]/20 flex items-center gap-3">
                        <div class="w-4 h-4 border-2 border-[#5856D6] border-t-transparent rounded-full animate-spin shrink-0"></div>
                        <div class="text-[12px] text-[#5856D6] dark:text-[#5E5CE6] font-medium">Mencocokkan produk jadi & bahan baku di sistem...</div>
                    </div>

                    <div x-show="recipeFileName && !recipeLoading"
                        class="flex items-center justify-between text-[12px] px-3.5 py-2.5 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80">
                            <svg class="w-4 h-4 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-mono font-medium" x-text="recipeFileName"></span>
                        </div>
                        <button type="button" @click="recipePreview = null; recipeFile = null; recipeFileName = ''"
                            class="text-black/50 hover:text-[#FF3B30] dark:text-white/50 dark:hover:text-[#FF453A] text-[12px] font-medium transition-colors">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3: Recipe Preview Table --}}
            <div x-show="recipePreview" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4" x-transition>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-black/5 dark:border-white/5">
                    <div>
                        <div class="flex items-center gap-2 text-[#5856D6] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#5856D6]/15 flex items-center justify-center text-[11px] font-bold">3</span>
                            <span>Preview & Konfirmasi Import Resep</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px] mt-0.5">Hasil Analisis Formula Resep (BOM)</h3>
                    </div>

                    {{-- Filter segmented pills --}}
                    <div class="flex items-center flex-wrap gap-1.5">
                        <button type="button" @click="recipeFilter = 'all'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="recipeFilter === 'all' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white border-transparent font-semibold' :
                                'bg-transparent text-black/60 dark:text-white/60 border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5'">
                            <span>Semua</span>
                            <span class="font-mono tabular-nums font-bold" x-text="recipePreview ? recipePreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="recipeFilter = 'valid'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="recipeFilter === 'valid' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                'bg-transparent text-[#34C759] border-black/10 dark:border-white/10 hover:bg-[#34C759]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>Siap Import</span>
                            <span class="font-mono tabular-nums font-bold" x-text="recipePreview ? recipePreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="recipeFilter = 'duplicate'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="recipeFilter === 'duplicate' ? 'bg-[#FF9500]/15 text-[#B26A00] dark:text-[#FF9F0A] border-[#FF9500]/30 font-semibold' :
                                'bg-transparent text-[#FF9500] border-black/10 dark:border-white/10 hover:bg-[#FF9500]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                            <span>Duplikat</span>
                            <span class="font-mono tabular-nums font-bold" x-text="recipePreview ? recipePreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="recipeFilter = 'error'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="recipeFilter === 'error' ? 'bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] border-[#FF3B30]/30 font-semibold' :
                                'bg-transparent text-[#FF3B30] border-black/10 dark:border-white/10 hover:bg-[#FF3B30]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                            <span>Error</span>
                            <span class="font-mono tabular-nums font-bold" x-text="recipePreview ? recipePreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                {{-- Duplicate Strategy Options --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-[12px]">
                    <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-medium">
                        <svg class="w-4 h-4 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-7.5-6.5h10.5A2.25 2.25 0 0120.25 5.5v13a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.5V5.5A2.25 2.25 0 015.25 3.25z"/></svg>
                        <span>Tindakan jika bahan sudah ada dalam resep produk:</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="rec_dup_strategy" value="skip" x-model="recipeDuplicateStrategy" class="text-[#5856D6] focus:ring-[#5856D6]">
                            <span>Lewati Duplikat (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="rec_dup_strategy" value="update" x-model="recipeDuplicateStrategy" class="text-[#5856D6] focus:ring-[#5856D6]">
                            <span>Perbarui Takaran (Update)</span>
                        </label>
                    </div>
                </div>

                {{-- Preview Table Content --}}
                <div class="overflow-x-auto max-h-96 rounded-[10px] border border-black/5 dark:border-white/5">
                    <table class="w-full text-left text-[13px]">
                        <thead class="sticky top-0 bg-[#F2F2F7] dark:bg-[#2C2C2E] text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider border-b border-black/5 dark:border-white/5 z-10">
                            <tr>
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Produk Jadi</th>
                                <th class="py-2.5 px-3">Bahan Baku</th>
                                <th class="py-2.5 px-3 text-right">Takaran</th>
                                <th class="py-2.5 px-3">Satuan</th>
                                <th class="py-2.5 px-3 text-right">Waste %</th>
                                <th class="py-2.5 px-3">Catatan</th>
                                <th class="py-2.5 px-3">Keterangan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            <template x-for="row in (recipePreview ? recipePreview.rows : [])" :key="row.row_number">
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors"
                                    x-show="recipeFilter === 'all' || recipeFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-black/50 dark:text-white/50" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>Siap Import</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF9500]/10 text-[#B26A00] dark:text-[#FF9F0A] border border-[#FF9500]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                <span>Duplikat</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                                <span>Error</span>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.product_name"></td>
                                    <td class="py-2.5 px-3 font-medium text-black/80 dark:text-white/80" x-text="row.material_name"></td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums text-[#007AFF] dark:text-[#0A84FF] font-semibold"
                                        x-text="row.quantity"></td>
                                    <td class="py-2.5 px-3 font-mono text-black/70 dark:text-white/70" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums text-black/60 dark:text-white/60"
                                        x-text="row.waste_percentage + '%'"></td>
                                    <td class="py-2.5 px-3 text-black/60 dark:text-white/60 text-[12px]" x-text="row.notes || '-'"></td>
                                    <td class="py-2.5 px-3 text-[12px]"
                                        :class="row.status === 'error' ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : (row.status === 'duplicate' ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black/50 dark:text-white/50')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/5">
                    <div class="text-[12px] text-black/60 dark:text-white/60">
                        Total <strong class="text-black dark:text-white tabular-nums" x-text="recipePreview ? recipePreview.total_rows : 0"></strong> formula dianalisis.
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="recipePreview = null; recipeFile = null"
                            class="h-9 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[13px] font-medium transition-all active:scale-[0.97]">
                            Batal
                        </button>

                        <button type="button" @click="executeRecipeImport()" :disabled="recipeExecuting"
                            class="h-9 px-5 rounded-[10px] bg-[#5856D6] hover:bg-[#4745C2] active:scale-[0.97] text-white font-semibold text-[13px] transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(88,86,214,0.25)] disabled:opacity-50 disabled:pointer-events-none">
                            <template x-if="recipeExecuting">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </template>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-show="!recipeExecuting"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Konfirmasi & Eksekusi Import Resep</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- TAB 4: IMPORT SALDO STOK / INVENTORY --}}
        {{-- ========================================================== --}}
        <div x-show="activeTab === 'inventory'" class="space-y-5" x-transition>
            {{-- Info Notice Callout --}}
            <div class="rounded-[14px] bg-[#AF52DE]/10 border border-[#AF52DE]/20 p-4 flex items-start gap-3 text-[12px]">
                <div class="w-5 h-5 rounded-full bg-[#AF52DE]/20 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center shrink-0 mt-0.5 font-bold">!</div>
                <div class="space-y-1">
                    <p class="font-semibold text-[#8E32BC] dark:text-[#BF5AF2]">Aturan Penting Sebelum Import Saldo Stok</p>
                    <ul class="text-black/70 dark:text-white/70 space-y-0.5 list-disc list-inside">
                        <li>Material / Produk <strong>WAJIB sudah terdaftar</strong> di Master Data sebelum bisa di-import saldo stoknya.</li>
                        <li>Jika item tidak ditemukan di Master Data, baris akan ditolak dengan status <span class="text-[#FF3B30] dark:text-[#FF453A] font-semibold">Error</span>.</li>
                        <li>Import stok akan mencatat <strong>transaksi mutasi stok awal</strong> ke kartu stok sistem secara otomatis.</li>
                        <li>Pastikan Lokasi / Outlet / Gudang sudah terdaftar di pengaturan bisnis Anda.</li>
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                {{-- Step 1: Download Template --}}
                <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-[#AF52DE] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#AF52DE]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                            <span>Download Template Saldo Stok</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px]">Format Spreadsheet Inventori</h3>
                        <p class="text-[12px] text-black/55 dark:text-white/55 leading-relaxed">
                            Format kolom: Nama Produk/Bahan, Kode/SKU, Lokasi/Gudang, Jumlah Stok, Satuan, HPP per Unit, dan Catatan.
                        </p>
                        <div class="pt-2 text-[11px] text-black/50 dark:text-white/50 space-y-1">
                            <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span> Lokasi / Outlet / Gudang tervalidasi</div>
                            <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span> Qty & Satuan otomatis teridentifikasi</div>
                            <div class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#AF52DE]"></span> Validasi duplikat per item & lokasi</div>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-black/5 dark:border-white/5">
                        <a href="{{ route('import.inventory.template', ['format' => 'xlsx']) }}"
                            class="w-full h-9 rounded-[10px] bg-[#AF52DE]/10 hover:bg-[#AF52DE]/20 text-[#8E32BC] dark:text-[#BF5AF2] border border-[#AF52DE]/20 text-[13px] font-semibold transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125H3.375a1.125 1.125 0 01-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z"/></svg>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.inventory.template', ['format' => 'csv']) }}"
                            class="w-full h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 text-[12px] font-medium transition-all flex items-center justify-center gap-1.5 active:scale-[0.98]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <span>Download CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                {{-- Step 2: Upload File Zone --}}
                <div class="lg:col-span-2 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-[#AF52DE] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#AF52DE]/15 flex items-center justify-center text-[11px] font-bold">2</span>
                            <span>Upload File Saldo Stok & Analisis</span>
                        </div>
                        <span class="text-[11px] text-black/40 dark:text-white/40">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div class="border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#AF52DE] dark:hover:border-[#BF5AF2] rounded-[12px] p-6 text-center transition-all bg-black/[0.01] dark:bg-white/[0.01] relative group">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleInventoryFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="space-y-2 pointer-events-none">
                                <div class="w-11 h-11 rounded-[12px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] mx-auto flex items-center justify-center group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                                </div>
                                <div class="text-[13px] text-black/80 dark:text-white/80">
                                    <span class="font-semibold text-[#AF52DE]">Klik untuk memilih file saldo stok</span> atau seret dokumen ke sini
                                </div>
                                <p class="text-[11px] text-black/40 dark:text-white/40">Pastikan Material/Produk sudah terdaftar di Master Data terlebih dahulu</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-black/10 dark:border-white/10 rounded-[12px] p-6 text-center bg-black/[0.02] dark:bg-white/[0.02] space-y-3">
                            <div class="w-10 h-10 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-black/40 dark:text-white/40 mx-auto flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                            </div>
                            <div class="text-[12px] text-black/60 dark:text-white/60">
                                Import saldo stok terkunci pada paket Free. Silakan upgrade ke Pro / Patungan.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 h-8 px-3.5 rounded-[9px] bg-[#FF9500] text-white font-semibold text-[12px] hover:bg-[#E08500] transition-all">
                                <span>Buka Kunci Fitur Import</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        </div>
                    @endif

                    <div x-show="inventoryLoading"
                        class="p-3 rounded-[10px] bg-[#AF52DE]/10 border border-[#AF52DE]/20 flex items-center gap-3">
                        <div class="w-4 h-4 border-2 border-[#AF52DE] border-t-transparent rounded-full animate-spin shrink-0"></div>
                        <div class="text-[12px] text-[#8E32BC] dark:text-[#BF5AF2] font-medium">Menganalisis file, mencocokkan item & lokasi...</div>
                    </div>

                    <div x-show="inventoryFileName && !inventoryLoading"
                        class="flex items-center justify-between text-[12px] px-3.5 py-2.5 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-2 text-black/80 dark:text-white/80">
                            <svg class="w-4 h-4 text-[#AF52DE]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-mono font-medium" x-text="inventoryFileName"></span>
                        </div>
                        <button type="button" @click="inventoryPreview = null; inventoryFile = null; inventoryFileName = ''"
                            class="text-black/50 hover:text-[#FF3B30] dark:text-white/50 dark:hover:text-[#FF453A] text-[12px] font-medium transition-colors">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            {{-- Step 3: Inventory Preview Table --}}
            <div x-show="inventoryPreview" class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-5 space-y-4" x-transition>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-black/5 dark:border-white/5">
                    <div>
                        <div class="flex items-center gap-2 text-[#AF52DE] font-semibold text-[12px]">
                            <span class="w-5 h-5 rounded-full bg-[#AF52DE]/15 flex items-center justify-center text-[11px] font-bold">3</span>
                            <span>Preview & Konfirmasi Import Saldo Stok</span>
                        </div>
                        <h3 class="font-semibold text-black dark:text-white text-[15px] mt-0.5">Hasil Analisis Data Inventori</h3>
                    </div>

                    {{-- Filter segmented pills --}}
                    <div class="flex items-center flex-wrap gap-1.5">
                        <button type="button" @click="inventoryFilter = 'all'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="inventoryFilter === 'all' ? 'bg-black/10 dark:bg-white/15 text-black dark:text-white border-transparent font-semibold' :
                                'bg-transparent text-black/60 dark:text-white/60 border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5'">
                            <span>Semua</span>
                            <span class="font-mono tabular-nums font-bold" x-text="inventoryPreview ? inventoryPreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="inventoryFilter = 'valid'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="inventoryFilter === 'valid' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border-[#34C759]/30 font-semibold' :
                                'bg-transparent text-[#34C759] border-black/10 dark:border-white/10 hover:bg-[#34C759]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>Siap Import</span>
                            <span class="font-mono tabular-nums font-bold" x-text="inventoryPreview ? inventoryPreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="inventoryFilter = 'duplicate'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="inventoryFilter === 'duplicate' ? 'bg-[#FF9500]/15 text-[#B26A00] dark:text-[#FF9F0A] border-[#FF9500]/30 font-semibold' :
                                'bg-transparent text-[#FF9500] border-black/10 dark:border-white/10 hover:bg-[#FF9500]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                            <span>Sudah Ada Stok</span>
                            <span class="font-mono tabular-nums font-bold" x-text="inventoryPreview ? inventoryPreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="inventoryFilter = 'error'"
                            class="h-8 px-3 rounded-[8px] text-[12px] font-medium border transition-all flex items-center gap-1.5"
                            :class="inventoryFilter === 'error' ? 'bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] border-[#FF3B30]/30 font-semibold' :
                                'bg-transparent text-[#FF3B30] border-black/10 dark:border-white/10 hover:bg-[#FF3B30]/5'">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                            <span>Error / Ditolak</span>
                            <span class="font-mono tabular-nums font-bold" x-text="inventoryPreview ? inventoryPreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                {{-- Duplicate Strategy Options --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-[10px] bg-[#F2F2F7] dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 text-[12px]">
                    <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-medium">
                        <svg class="w-4 h-4 text-[#AF52DE]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-7.5-6.5h10.5A2.25 2.25 0 0120.25 5.5v13a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18.5V5.5A2.25 2.25 0 015.25 3.25z"/></svg>
                        <span>Tindakan jika item di lokasi tersebut sudah memiliki saldo stok:</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="inv_dup_strategy" value="skip" x-model="inventoryDuplicateStrategy" class="text-[#AF52DE] focus:ring-[#AF52DE]">
                            <span>Lewati (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="inv_dup_strategy" value="adjust" x-model="inventoryDuplicateStrategy" class="text-[#AF52DE] focus:ring-[#AF52DE]">
                            <span>Tambah / Sesuaikan</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-black/80 dark:text-white/80">
                            <input type="radio" name="inv_dup_strategy" value="initial" x-model="inventoryDuplicateStrategy" class="text-[#AF52DE] focus:ring-[#AF52DE]">
                            <span>Set Saldo Awal Baru</span>
                        </label>
                    </div>
                </div>

                {{-- Preview Table Content --}}
                <div class="overflow-x-auto max-h-96 rounded-[10px] border border-black/5 dark:border-white/5">
                    <table class="w-full text-left text-[13px]">
                        <thead class="sticky top-0 bg-[#F2F2F7] dark:bg-[#2C2C2E] text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider border-b border-black/5 dark:border-white/5 z-10">
                            <tr>
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Nama Item (Produk / Bahan)</th>
                                <th class="py-2.5 px-3">SKU</th>
                                <th class="py-2.5 px-3">Lokasi / Outlet</th>
                                <th class="py-2.5 px-3 text-right">Jumlah</th>
                                <th class="py-2.5 px-3">Satuan</th>
                                <th class="py-2.5 px-3 text-right">HPP / Unit</th>
                                <th class="py-2.5 px-3">Keterangan / Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            <template x-for="row in (inventoryPreview ? inventoryPreview.rows : [])" :key="row.row_number">
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors"
                                    x-show="inventoryFilter === 'all' || inventoryFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-black/50 dark:text-white/50" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span>Siap Import</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF9500]/10 text-[#B26A00] dark:text-[#FF9F0A] border border-[#FF9500]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                                <span>Sudah Ada Stok</span>
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] border border-[#FF3B30]/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                                <span>Error / Ditolak</span>
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.item_name || '-'"></td>
                                    <td class="py-2.5 px-3 font-mono tabular-nums text-black/60 dark:text-white/60" x-text="row.sku || '-'"></td>
                                    <td class="py-2.5 px-3 text-black/80 dark:text-white/80">
                                        <span class="px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06] font-medium text-[12px]" x-text="row.location_name || '-'"></span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums text-[#AF52DE] dark:text-[#BF5AF2] font-semibold"
                                        x-text="row.quantity"></td>
                                    <td class="py-2.5 px-3 font-mono text-black/70 dark:text-white/70" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono tabular-nums text-black/70 dark:text-white/70"
                                        x-text="'Rp ' + (Number(row.unit_cost) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-[12px]"
                                        :class="row.status === 'error' ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : (row.status === 'duplicate' ? 'text-[#FF9500] dark:text-[#FF9F0A]' : 'text-black/50 dark:text-white/50')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar --}}
                <div class="flex items-center justify-between pt-3 border-t border-black/5 dark:border-white/5">
                    <div class="text-[12px] text-black/60 dark:text-white/60">
                        Total <strong class="text-black dark:text-white tabular-nums" x-text="inventoryPreview ? inventoryPreview.total_rows : 0"></strong> baris stok dianalisis.
                        <span class="text-[#AF52DE] dark:text-[#BF5AF2] font-medium" x-text="inventoryPreview ? '(' + inventoryPreview.valid_count + ' siap dicatat)' : ''"></span>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="inventoryPreview = null; inventoryFile = null"
                            class="h-9 px-4 rounded-[10px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 text-[13px] font-medium transition-all active:scale-[0.97]">
                            Batal
                        </button>

                        <button type="button" @click="executeInventoryImport()" :disabled="inventoryExecuting"
                            class="h-9 px-5 rounded-[10px] bg-[#AF52DE] hover:bg-[#9B38CA] active:scale-[0.97] text-white font-semibold text-[13px] transition-all flex items-center gap-2 shadow-[0_1px_2px_rgba(175,82,222,0.25)] disabled:opacity-50 disabled:pointer-events-none">
                            <template x-if="inventoryExecuting">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </template>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-show="!inventoryExecuting"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Konfirmasi & Catat Saldo Stok</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
