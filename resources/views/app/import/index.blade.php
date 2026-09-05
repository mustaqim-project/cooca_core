@extends('layouts.app', [
    'title' => 'Import Bahan Baku, Produk & Resep Excel',
    'headerTitle' => 'Import Massal Data Bisnis',
    'headerSubtitle' => 'Unggah katalog bahan baku, produk jadi, dan formula resep/BOM dari file Excel atau CSV dengan validasi anti-duplikat',
])

@section('content')
    <div class="max-w-7xl mx-auto space-y-6" x-data="{
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
                        alert(res.message || 'Gagal menganalisis file.');
                    }
                })
                .catch(err => {
                    this.materialLoading = false;
                    alert(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        executeMaterialImport() {
            if (!this.materialPreview || !this.materialPreview.rows || this.materialPreview.rows.length === 0) {
                alert('Tidak ada data bahan baku yang siap diimport.');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin memproses import bahan baku ini ke database bisnis?')) {
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
                        alert(res.message || 'Gagal mengimport bahan baku.');
                    }
                })
                .catch(err => {
                    this.materialExecuting = false;
                    alert('Terjadi kesalahan saat mengeksekusi import bahan baku.');
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
                        alert(res.message || 'Gagal menganalisis file.');
                    }
                })
                .catch(err => {
                    this.productLoading = false;
                    alert(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        executeProductImport() {
            if (!this.productPreview || !this.productPreview.rows || this.productPreview.rows.length === 0) {
                alert('Tidak ada data yang siap diimport.');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin memproses import produk ini ke database bisnis?')) {
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
                        alert(res.message || 'Gagal mengimport produk.');
                    }
                })
                .catch(err => {
                    this.productExecuting = false;
                    alert('Terjadi kesalahan saat mengeksekusi import.');
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
                        alert(res.message || 'Gagal menganalisis file.');
                    }
                })
                .catch(err => {
                    this.recipeLoading = false;
                    alert(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        executeRecipeImport() {
            if (!this.recipePreview || !this.recipePreview.rows || this.recipePreview.rows.length === 0) {
                alert('Tidak ada data resep yang siap diimport.');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin memproses import resep / BOM ini ke database bisnis?')) {
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
                        alert(res.message || 'Gagal mengimport resep.');
                    }
                })
                .catch(err => {
                    this.recipeExecuting = false;
                    alert('Terjadi kesalahan saat mengeksekusi import resep.');
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
                        alert(res.message || 'Gagal menganalisis file saldo stok.');
                    }
                })
                .catch(err => {
                    this.inventoryLoading = false;
                    alert(err.message || 'Terjadi kesalahan saat mengunggah file.');
                });
        },

        executeInventoryImport() {
            if (!this.inventoryPreview || !this.inventoryPreview.rows || this.inventoryPreview.rows.length === 0) {
                alert('Tidak ada data saldo stok yang siap diimport.');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin mencatat saldo stok ini ke dalam sistem inventori? Proses ini TIDAK dapat diurungkan.')) {
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
                        alert(res.message || 'Gagal mengimport saldo stok.');
                    }
                })
                .catch(err => {
                    this.inventoryExecuting = false;
                    alert('Terjadi kesalahan saat mengeksekusi import saldo stok.');
                });
        }
    }">

        <!-- Header & Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30">
                        EXCEL & CSV IMPORT SUITE
                    </span>
                    <span class="text-xs text-slate-400 font-mono">{{ $business->name }}</span>
                </div>
                <h1 class="text-2xl font-black text-white tracking-tight mt-1">Import Data Bisnis (Bahan, Produk, Resep &
                    Stok)</h1>
                <p class="text-xs text-slate-400 mt-0.5">Unggah ratusan data bahan baku, produk jadi, formula resep BOM, dan
                    saldo stok awal dalam hitungan detik dengan verifikasi otomatis.</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('materials.index') }}"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-4 h-4 text-emerald-400"></i>
                    <span>Ke Master Bahan</span>
                </a>
                <a href="{{ route('products.index') }}"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-teal-300 border border-teal-500/30 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="package" class="w-4 h-4 text-teal-400"></i>
                    <span>Ke Katalog Produk</span>
                </a>
                <a href="{{ route('inventory.stocks') }}"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-violet-300 border border-violet-500/30 text-xs font-bold transition flex items-center gap-1.5">
                    <i data-lucide="warehouse" class="w-4 h-4 text-violet-400"></i>
                    <span>Ke Inventori</span>
                </a>
            </div>
        </div>

        <!-- Free Tier Paywall / Upgrade Banner -->
        @if (!$canImport)
            <div
                class="glass-card rounded-3xl p-6 border border-amber-500/40 bg-gradient-to-br from-amber-950/40 via-slate-900/90 to-amber-950/20 shadow-2xl relative overflow-hidden">
                <div
                    class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-amber-500/10 blur-3xl pointer-events-none">
                </div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2.5 max-w-3xl">
                        <div class="flex items-center gap-2">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                                <i data-lucide="lock" class="w-3 h-3"></i>
                                FITUR KHUSUS PAKET PRO / PATUNGAN
                            </span>
                            <span class="text-xs text-slate-400">Cooca UMKM Unlimited Core</span>
                        </div>

                        <h3 class="text-lg font-black text-white">Import Massal Excel & CSV Terkunci untuk Akun Free</h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Fitur Import Bahan, Produk & Resep Massal dirancang khusus untuk memangkas waktu input data dari
                            berjam-jam menjadi beberapa detik. Anda tetap dapat <strong>mengunduh template Excel</strong> di
                            bawah untuk mempersiapkan data Anda.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                            <div
                                class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-300 flex items-center gap-2">
                                <i data-lucide="zap" class="w-4 h-4 text-amber-400 shrink-0"></i>
                                <span>Import ratusan item sekaligus</span>
                            </div>
                            <div
                                class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-300 flex items-center gap-2">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>Proteksi Anti-Duplikat Otomatis</span>
                            </div>
                            <div
                                class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-300 flex items-center gap-2">
                                <i data-lucide="eye" class="w-4 h-4 text-cyan-400 shrink-0"></i>
                                <span>Interactive Live Preview & Validasi</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0">
                        <a href="{{ route('billing.limits') }}"
                            class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-400 hover:to-orange-500 text-slate-950 font-black text-xs shadow-xl shadow-amber-500/25 transition-all flex items-center justify-center gap-2 text-center">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Upgrade ke Pro / Patungan</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-800 pb-1 overflow-x-auto">
            <button type="button" @click="activeTab = 'materials'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border whitespace-nowrap"
                :class="activeTab === 'materials' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' :
                    'bg-transparent text-slate-400 border-transparent hover:text-white hover:bg-slate-900'">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>1. Import Bahan Baku ({{ $totalMaterials }} Terdaftar)</span>
            </button>

            <button type="button" @click="activeTab = 'products'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border whitespace-nowrap"
                :class="activeTab === 'products' ? 'bg-teal-500/15 text-teal-300 border-teal-500/30' :
                    'bg-transparent text-slate-400 border-transparent hover:text-white hover:bg-slate-900'">
                <i data-lucide="package" class="w-4 h-4"></i>
                <span>2. Import Katalog Produk ({{ $totalProducts }} Terdaftar)</span>
            </button>

            <button type="button" @click="activeTab = 'recipes'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border whitespace-nowrap"
                :class="activeTab === 'recipes' ? 'bg-blue-500/15 text-blue-300 border-blue-500/30' :
                    'bg-transparent text-slate-400 border-transparent hover:text-white hover:bg-slate-900'">
                <i data-lucide="git-fork" class="w-4 h-4"></i>
                <span>3. Import Resep / BOM ({{ $totalRecipes }} Formula)</span>
            </button>

            <button type="button" @click="activeTab = 'inventory'"
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 border whitespace-nowrap"
                :class="activeTab === 'inventory' ? 'bg-violet-500/15 text-violet-300 border-violet-500/30' :
                    'bg-transparent text-slate-400 border-transparent hover:text-white hover:bg-slate-900'">
                <i data-lucide="warehouse" class="w-4 h-4"></i>
                <span>4. Import Saldo Stok ({{ $totalStocks }} Tercatat)</span>
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: IMPORT BAHAN BAKU -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'materials'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Download Material Template -->
                <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">1</span>
                            <span>Download Template Bahan Baku</span>
                        </div>
                        <h3 class="font-bold text-white text-sm">Unduh Format Master Bahan</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Format kolom meliputi Nama Bahan Baku, Kode / SKU, Kategori Bahan, Satuan Dasar, Harga Beli
                            Standar, dan Nama Pemasok.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-800">
                        <a href="{{ route('import.materials.template', ['format' => 'xlsx']) }}"
                            class="w-full py-2.5 px-3 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition flex items-center justify-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.materials.template', ['format' => 'csv']) }}"
                            class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-medium transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Download Format CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                <!-- Upload Material File Zone -->
                <div class="lg:col-span-2 glass-card rounded-2xl p-5 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">2</span>
                            <span>Upload File & Analisis Bahan Baku</span>
                        </div>
                        <span class="text-[11px] text-slate-500">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div
                            class="border-2 border-dashed border-slate-700 hover:border-emerald-500/60 rounded-2xl p-6 text-center transition-all bg-slate-950/40 relative">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleMaterialFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-2 pointer-events-none">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 mx-auto flex items-center justify-center">
                                    <i data-lucide="layers" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs text-slate-300">
                                    <span class="font-bold text-emerald-400">Klik untuk memilih file bahan baku</span> atau
                                    seret file ke sini
                                </div>
                                <p class="text-[11px] text-slate-500">Format: Excel (.xlsx, .xls) atau CSV</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-slate-800 rounded-2xl p-6 text-center bg-slate-950/40 space-y-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-slate-800 text-slate-500 mx-auto flex items-center justify-center">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <div class="text-xs text-slate-400">
                                Upload bahan terkunci pada paket Free. Silakan upgrade ke Pro / Patungan.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition">
                                <span>Buka Kunci Fitur Import</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @endif

                    <div x-show="materialLoading"
                        class="p-3.5 rounded-xl bg-emerald-950/30 border border-emerald-500/30 flex items-center gap-3">
                        <div class="w-5 h-5 border-2 border-emerald-400 border-t-transparent rounded-full animate-spin">
                        </div>
                        <div class="text-xs text-emerald-300 font-medium">Menganalisis file bahan baku & mengecek
                            duplikat...</div>
                    </div>

                    <div x-show="materialFileName && !materialLoading"
                        class="flex items-center justify-between text-xs px-3 py-2 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="flex items-center gap-2 text-slate-300">
                            <i data-lucide="file-check" class="w-4 h-4 text-emerald-400"></i>
                            <span class="font-mono" x-text="materialFileName"></span>
                        </div>
                        <button type="button" @click="materialPreview = null; materialFile = null; materialFileName = ''"
                            class="text-slate-500 hover:text-rose-400 text-xs">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            <!-- Material Preview Table -->
            <div x-show="materialPreview" class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5"
                x-transition>
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                    <div>
                        <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center text-xs">3</span>
                            <span>Preview & Konfirmasi Import Bahan Baku</span>
                        </div>
                        <h3 class="font-bold text-white text-base mt-0.5">Hasil Analisis Data Bahan Baku</h3>
                    </div>

                    <div class="flex items-center flex-wrap gap-2">
                        <button type="button" @click="materialFilter = 'all'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="materialFilter === 'all' ? 'bg-slate-700 text-white border-slate-600' :
                                'bg-slate-900 text-slate-400 border-slate-800'">
                            <span>Semua</span>
                            <span class="font-mono font-bold"
                                x-text="materialPreview ? materialPreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="materialFilter = 'valid'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="materialFilter === 'valid' ?
                                'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' :
                                'bg-slate-900 text-emerald-400 border-slate-800'">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            <span>Siap Import</span>
                            <span class="font-mono font-bold"
                                x-text="materialPreview ? materialPreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="materialFilter = 'duplicate'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="materialFilter === 'duplicate' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' :
                                'bg-slate-900 text-amber-400 border-slate-800'">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            <span>Duplikat</span>
                            <span class="font-mono font-bold"
                                x-text="materialPreview ? materialPreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="materialFilter = 'error'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="materialFilter === 'error' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' :
                                'bg-slate-900 text-rose-400 border-slate-800'">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                            <span>Error</span>
                            <span class="font-mono font-bold"
                                x-text="materialPreview ? materialPreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                <!-- Duplicate Strategy Options -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                    <div class="flex items-center gap-2 text-slate-300 font-semibold">
                        <i data-lucide="shield" class="w-4 h-4 text-emerald-400"></i>
                        <span>Tindakan jika ditemukan bahan baku duplikat (SKU / Nama sama):</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="mat_dup_strategy" value="skip"
                                x-model="materialDuplicateStrategy" class="text-emerald-500">
                            <span>Lewati Duplikat (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="mat_dup_strategy" value="update"
                                x-model="materialDuplicateStrategy" class="text-emerald-500">
                            <span>Perbarui Data & Catat Harga Baru</span>
                        </label>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-96 rounded-xl border border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-slate-900 text-slate-400 border-b border-slate-800">
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
                        <tbody class="divide-y divide-slate-800/60 bg-slate-950/40">
                            <template x-for="row in (materialPreview ? materialPreview.rows : [])" :key="row.row_number">
                                <tr class="hover:bg-slate-900/50 transition"
                                    x-show="materialFilter === 'all' || materialFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono text-slate-500" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                Siap Import
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                Duplikat
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                Error
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-bold text-white" x-text="row.name || '-'"></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-400" x-text="row.sku || '-'"></td>
                                    <td class="py-2.5 px-3 text-slate-300" x-text="row.category || 'Umum'"></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-300" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono text-emerald-300 font-bold"
                                        x-text="'Rp ' + (Number(row.purchase_price) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-slate-300" x-text="row.supplier_name || '-'"></td>
                                    <td class="py-2.5 px-3 text-[11px]"
                                        :class="row.status === 'error' ? 'text-rose-400 font-semibold' : (row
                                            .status === 'duplicate' ? 'text-amber-300' : 'text-slate-400')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                    <div class="text-xs text-slate-400">
                        Total <strong class="text-white"
                            x-text="materialPreview ? materialPreview.total_rows : 0"></strong> bahan baku dianalisis.
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="materialPreview = null; materialFile = null"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                            Batal
                        </button>

                        <button type="button" @click="executeMaterialImport()" :disabled="materialExecuting || (materialPreview && materialPreview.error_count > 0)"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                            <template x-if="materialExecuting">
                                <div
                                    class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin">
                                </div>
                            </template>
                            <i data-lucide="check" class="w-4 h-4" x-show="!materialExecuting"></i>
                            <span>Konfirmasi & Eksekusi Import Bahan Baku</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: IMPORT PRODUK -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'products'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Step 1: Download Template -->
                <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-teal-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-teal-500/20 flex items-center justify-center text-xs">1</span>
                            <span>Download Template Produk</span>
                        </div>
                        <h3 class="font-bold text-white text-sm">Unduh Format Spreadsheet</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Gunakan format kolom baku agar sistem dapat mengenali Nama Produk, SKU, Kategori, Satuan Output,
                            Harga Jual, dan HPP Awal dengan tepat.
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-800">
                        <a href="{{ route('import.products.template', ['format' => 'xlsx']) }}"
                            class="w-full py-2.5 px-3 rounded-xl bg-teal-500/15 hover:bg-teal-500/25 text-teal-300 border border-teal-500/30 text-xs font-bold transition flex items-center justify-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.products.template', ['format' => 'csv']) }}"
                            class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-medium transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Download Format CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                <!-- Step 2: Upload File Zone -->
                <div class="lg:col-span-2 glass-card rounded-2xl p-5 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-cyan-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-cyan-500/20 flex items-center justify-center text-xs">2</span>
                            <span>Upload File & Analisis Otomatis</span>
                        </div>
                        <span class="text-[11px] text-slate-500">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div
                            class="border-2 border-dashed border-slate-700 hover:border-cyan-500/60 rounded-2xl p-6 text-center transition-all bg-slate-950/40 relative">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleProductFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-2 pointer-events-none">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 mx-auto flex items-center justify-center">
                                    <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs text-slate-300">
                                    <span class="font-bold text-cyan-400">Klik untuk memilih file produk</span> atau seret
                                    file ke sini
                                </div>
                                <p class="text-[11px] text-slate-500">Format yang didukung: Microsoft Excel (.xlsx, .xls)
                                    atau CSV UTF-8</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-slate-800 rounded-2xl p-6 text-center bg-slate-950/40 space-y-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-slate-800 text-slate-500 mx-auto flex items-center justify-center">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <div class="text-xs text-slate-400">
                                Upload terkunci pada paket Free. Silakan upgrade ke Pro / Patungan untuk mengunggah file.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition">
                                <span>Buka Kunci Fitur Import</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @endif

                    <div x-show="productLoading"
                        class="p-3.5 rounded-xl bg-cyan-950/30 border border-cyan-500/30 flex items-center gap-3">
                        <div class="w-5 h-5 border-2 border-cyan-400 border-t-transparent rounded-full animate-spin"></div>
                        <div class="text-xs text-cyan-300 font-medium">Menganalisis file dan mengecek duplikat di
                            database...</div>
                    </div>

                    <div x-show="productFileName && !productLoading"
                        class="flex items-center justify-between text-xs px-3 py-2 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="flex items-center gap-2 text-slate-300">
                            <i data-lucide="file-check" class="w-4 h-4 text-emerald-400"></i>
                            <span class="font-mono" x-text="productFileName"></span>
                        </div>
                        <button type="button" @click="productPreview = null; productFile = null; productFileName = ''"
                            class="text-slate-500 hover:text-rose-400 text-xs">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Table for Products -->
            <div x-show="productPreview" class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5"
                x-transition>
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                    <div>
                        <div class="flex items-center gap-2 text-teal-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-teal-500/20 flex items-center justify-center text-xs">3</span>
                            <span>Preview & Konfirmasi Import Produk</span>
                        </div>
                        <h3 class="font-bold text-white text-base mt-0.5">Hasil Analisis Data Produk</h3>
                    </div>

                    <div class="flex items-center flex-wrap gap-2">
                        <button type="button" @click="productFilter = 'all'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="productFilter === 'all' ? 'bg-slate-700 text-white border-slate-600' :
                                'bg-slate-900 text-slate-400 border-slate-800'">
                            <span>Semua</span>
                            <span class="font-mono font-bold"
                                x-text="productPreview ? productPreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="productFilter = 'valid'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="productFilter === 'valid' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' :
                                'bg-slate-900 text-emerald-400 border-slate-800'">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            <span>Siap Import</span>
                            <span class="font-mono font-bold"
                                x-text="productPreview ? productPreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="productFilter = 'duplicate'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="productFilter === 'duplicate' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' :
                                'bg-slate-900 text-amber-400 border-slate-800'">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            <span>Duplikat</span>
                            <span class="font-mono font-bold"
                                x-text="productPreview ? productPreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="productFilter = 'error'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="productFilter === 'error' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' :
                                'bg-slate-900 text-rose-400 border-slate-800'">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                            <span>Error</span>
                            <span class="font-mono font-bold"
                                x-text="productPreview ? productPreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                <!-- Duplicate Strategy Options -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                    <div class="flex items-center gap-2 text-slate-300 font-semibold">
                        <i data-lucide="shield" class="w-4 h-4 text-cyan-400"></i>
                        <span>Tindakan jika ditemukan data duplikat (SKU / Nama sama):</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="prod_dup_strategy" value="skip"
                                x-model="productDuplicateStrategy" class="text-cyan-500">
                            <span>Lewati Duplikat (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="prod_dup_strategy" value="update"
                                x-model="productDuplicateStrategy" class="text-cyan-500">
                            <span>Perbarui Data Lama (Update)</span>
                        </label>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="overflow-x-auto max-h-96 rounded-xl border border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-slate-900 text-slate-400 border-b border-slate-800">
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
                        <tbody class="divide-y divide-slate-800/60 bg-slate-950/40">
                            <template x-for="row in (productPreview ? productPreview.rows : [])" :key="row.row_number">
                                <tr class="hover:bg-slate-900/50 transition"
                                    x-show="productFilter === 'all' || productFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono text-slate-500" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                Siap Import
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                Duplikat
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                Error
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-bold text-white" x-text="row.name || '-'"></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-400" x-text="row.sku || '-'"></td>
                                    <td class="py-2.5 px-3 text-slate-300" x-text="row.category || 'Umum'"></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-300" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono text-teal-300 font-bold"
                                        x-text="'Rp ' + (Number(row.selling_price) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-300"
                                        x-text="'Rp ' + (Number(row.base_cost) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-[11px]"
                                        :class="row.status === 'error' ? 'text-rose-400 font-semibold' : (row
                                            .status === 'duplicate' ? 'text-amber-300' : 'text-slate-400')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                    <div class="text-xs text-slate-400">
                        Total <strong class="text-white" x-text="productPreview ? productPreview.total_rows : 0"></strong>
                        produk dianalisis.
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="productPreview = null; productFile = null"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                            Batal
                        </button>

                        <button type="button" @click="executeProductImport()" :disabled="productExecuting"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 hover:from-teal-400 hover:to-emerald-500 text-slate-950 font-black text-xs shadow-lg shadow-teal-500/20 transition flex items-center gap-2">
                            <template x-if="productExecuting">
                                <div
                                    class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin">
                                </div>
                            </template>
                            <i data-lucide="check" class="w-4 h-4" x-show="!productExecuting"></i>
                            <span>Konfirmasi & Eksekusi Import Produk</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: IMPORT RESEP / BOM -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'recipes'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Download Recipe Template -->
                <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-blue-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-blue-500/20 flex items-center justify-center text-xs">1</span>
                            <span>Download Template Resep</span>
                        </div>
                        <h3 class="font-bold text-white text-sm">Unduh Format BOM Formula</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Format template mencakup Produk Jadi, Bahan Baku, Jumlah Takaran, Satuan Bahan, dan Persentase
                            Susut (Waste %).
                        </p>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-800">
                        <a href="{{ route('import.recipes.template', ['format' => 'xlsx']) }}"
                            class="w-full py-2.5 px-3 rounded-xl bg-blue-500/15 hover:bg-blue-500/25 text-blue-300 border border-blue-500/30 text-xs font-bold transition flex items-center justify-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <span>Download Template Resep (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.recipes.template', ['format' => 'csv']) }}"
                            class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-medium transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Download Format CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                <!-- Upload Recipe File Zone -->
                <div class="lg:col-span-2 glass-card rounded-2xl p-5 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-cyan-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-cyan-500/20 flex items-center justify-center text-xs">2</span>
                            <span>Upload File Resep & Analisis</span>
                        </div>
                        <span class="text-[11px] text-slate-500">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div
                            class="border-2 border-dashed border-slate-700 hover:border-blue-500/60 rounded-2xl p-6 text-center transition-all bg-slate-950/40 relative">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleRecipeFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-2 pointer-events-none">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-400 mx-auto flex items-center justify-center">
                                    <i data-lucide="git-merge" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs text-slate-300">
                                    <span class="font-bold text-blue-400">Klik untuk memilih file resep</span> atau seret
                                    file ke sini
                                </div>
                                <p class="text-[11px] text-slate-500">Pastikan Produk Jadi dan Bahan Baku sudah terdaftar
                                    di sistem</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-slate-800 rounded-2xl p-6 text-center bg-slate-950/40 space-y-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-slate-800 text-slate-500 mx-auto flex items-center justify-center">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <div class="text-xs text-slate-400">
                                Upload resep terkunci pada paket Free. Silakan upgrade ke Pro / Patungan untuk mengunggah
                                formula resep massal.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition">
                                <span>Buka Kunci Fitur Import</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @endif

                    <div x-show="recipeLoading"
                        class="p-3.5 rounded-xl bg-blue-950/30 border border-blue-500/30 flex items-center gap-3">
                        <div class="w-5 h-5 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></div>
                        <div class="text-xs text-blue-300 font-medium">Mencocokkan produk jadi & bahan baku di sistem...
                        </div>
                    </div>

                    <div x-show="recipeFileName && !recipeLoading"
                        class="flex items-center justify-between text-xs px-3 py-2 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="flex items-center gap-2 text-slate-300">
                            <i data-lucide="file-check" class="w-4 h-4 text-emerald-400"></i>
                            <span class="font-mono" x-text="recipeFileName"></span>
                        </div>
                        <button type="button" @click="recipePreview = null; recipeFile = null; recipeFileName = ''"
                            class="text-slate-500 hover:text-rose-400 text-xs">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recipe Preview Table -->
            <div x-show="recipePreview" class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5" x-transition>
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                    <div>
                        <div class="flex items-center gap-2 text-blue-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-blue-500/20 flex items-center justify-center text-xs">3</span>
                            <span>Preview & Konfirmasi Import Resep</span>
                        </div>
                        <h3 class="font-bold text-white text-base mt-0.5">Hasil Analisis Formula Resep (BOM)</h3>
                    </div>

                    <div class="flex items-center flex-wrap gap-2">
                        <button type="button" @click="recipeFilter = 'all'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="recipeFilter === 'all' ? 'bg-slate-700 text-white border-slate-600' :
                                'bg-slate-900 text-slate-400 border-slate-800'">
                            <span>Semua</span>
                            <span class="font-mono font-bold"
                                x-text="recipePreview ? recipePreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="recipeFilter = 'valid'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="recipeFilter === 'valid' ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' :
                                'bg-slate-900 text-emerald-400 border-slate-800'">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            <span>Siap Import</span>
                            <span class="font-mono font-bold"
                                x-text="recipePreview ? recipePreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="recipeFilter = 'duplicate'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="recipeFilter === 'duplicate' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' :
                                'bg-slate-900 text-amber-400 border-slate-800'">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            <span>Duplikat</span>
                            <span class="font-mono font-bold"
                                x-text="recipePreview ? recipePreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="recipeFilter = 'error'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="recipeFilter === 'error' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' :
                                'bg-slate-900 text-rose-400 border-slate-800'">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                            <span>Error</span>
                            <span class="font-mono font-bold"
                                x-text="recipePreview ? recipePreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                    <div class="flex items-center gap-2 text-slate-300 font-semibold">
                        <i data-lucide="shield" class="w-4 h-4 text-blue-400"></i>
                        <span>Tindakan jika bahan sudah ada dalam resep produk:</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="rec_dup_strategy" value="skip"
                                x-model="recipeDuplicateStrategy" class="text-blue-500">
                            <span>Lewati Duplikat (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="rec_dup_strategy" value="update"
                                x-model="recipeDuplicateStrategy" class="text-blue-500">
                            <span>Perbarui Takaran (Update)</span>
                        </label>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-96 rounded-xl border border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-slate-900 text-slate-400 border-b border-slate-800">
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
                        <tbody class="divide-y divide-slate-800/60 bg-slate-950/40">
                            <template x-for="row in (recipePreview ? recipePreview.rows : [])" :key="row.row_number">
                                <tr class="hover:bg-slate-900/50 transition"
                                    x-show="recipeFilter === 'all' || recipeFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono text-slate-500" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                Siap Import
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                Duplikat
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                Error
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-bold text-white" x-text="row.product_name"></td>
                                    <td class="py-2.5 px-3 font-semibold text-slate-200" x-text="row.material_name"></td>
                                    <td class="py-2.5 px-3 text-right font-mono text-cyan-300 font-bold"
                                        x-text="row.quantity"></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-300" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-400"
                                        x-text="row.waste_percentage + '%'"></td>
                                    <td class="py-2.5 px-3 text-slate-400 text-[11px]" x-text="row.notes || '-'"></td>
                                    <td class="py-2.5 px-3 text-[11px]"
                                        :class="row.status === 'error' ? 'text-rose-400 font-semibold' : (row
                                            .status === 'duplicate' ? 'text-amber-300' : 'text-slate-400')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                    <div class="text-xs text-slate-400">
                        Total <strong class="text-white" x-text="recipePreview ? recipePreview.total_rows : 0"></strong>
                        formula dianalisis.
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="recipePreview = null; recipeFile = null"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                            Batal
                        </button>

                        <button type="button" @click="executeRecipeImport()" :disabled="recipeExecuting"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 text-slate-950 font-black text-xs shadow-lg shadow-blue-500/20 transition flex items-center gap-2">
                            <template x-if="recipeExecuting">
                                <div
                                    class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin">
                                </div>
                            </template>
                            <i data-lucide="check" class="w-4 h-4" x-show="!recipeExecuting"></i>
                            <span>Konfirmasi & Eksekusi Import Resep</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: IMPORT SALDO STOK / INVENTORY -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'inventory'" class="space-y-6" x-transition>
            <!-- Warning: Material-first rule -->
            <div class="p-4 rounded-2xl bg-violet-950/30 border border-violet-500/30 flex items-start gap-3 text-xs">
                <i data-lucide="info" class="w-5 h-5 text-violet-400 shrink-0 mt-0.5"></i>
                <div class="space-y-1">
                    <p class="font-bold text-violet-300">Aturan Penting Sebelum Import Saldo Stok</p>
                    <ul class="text-slate-300 space-y-0.5 list-disc list-inside">
                        <li>Material / Produk <strong>WAJIB sudah terdaftar</strong> di Master Data sebelum bisa di-import
                            saldo stoknya.</li>
                        <li>Jika item tidak ditemukan di Master Data, baris akan ditolak dengan status <span
                                class="text-rose-400 font-bold">Error</span>.</li>
                        <li>Import stok akan mencatat <strong>transaksi stok masuk</strong> menggunakan mekanisme pergerakan
                            stok existing.</li>
                        <li>Pastikan Lokasi / Outlet / Gudang sudah terdaftar di pengaturan bisnis Anda.</li>
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Download Inventory Template -->
                <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-violet-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-violet-500/20 flex items-center justify-center text-xs">1</span>
                            <span>Download Template Saldo Stok</span>
                        </div>
                        <h3 class="font-bold text-white text-sm">Unduh Format Inventori</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Format kolom: Nama Produk/Bahan, Kode/SKU, Lokasi/Gudang, Jumlah Stok, Satuan, HPP per Unit, dan
                            Catatan.
                        </p>
                        <div class="pt-2 text-[11px] text-slate-500 space-y-1">
                            <div class="flex items-center gap-1.5"><i data-lucide="check"
                                    class="w-3 h-3 text-violet-400"></i> Lokasi / Outlet / Gudang</div>
                            <div class="flex items-center gap-1.5"><i data-lucide="check"
                                    class="w-3 h-3 text-violet-400"></i> Qty & Satuan otomatis teridentifikasi</div>
                            <div class="flex items-center gap-1.5"><i data-lucide="check"
                                    class="w-3 h-3 text-violet-400"></i> Validasi duplikat per item & lokasi</div>
                        </div>
                    </div>

                    <div class="space-y-2 pt-2 border-t border-slate-800">
                        <a href="{{ route('import.inventory.template', ['format' => 'xlsx']) }}"
                            class="w-full py-2.5 px-3 rounded-xl bg-violet-500/15 hover:bg-violet-500/25 text-violet-300 border border-violet-500/30 text-xs font-bold transition flex items-center justify-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                            <span>Download Template Excel (.xlsx)</span>
                        </a>
                        <a href="{{ route('import.inventory.template', ['format' => 'csv']) }}"
                            class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-medium transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Download Format CSV (.csv)</span>
                        </a>
                    </div>
                </div>

                <!-- Upload Inventory File Zone -->
                <div class="lg:col-span-2 glass-card rounded-2xl p-5 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-violet-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-violet-500/20 flex items-center justify-center text-xs">2</span>
                            <span>Upload File & Analisis Saldo Stok</span>
                        </div>
                        <span class="text-[11px] text-slate-500">Maks. 5 MB (.xlsx, .xls, .csv)</span>
                    </div>

                    @if ($canImport)
                        <div
                            class="border-2 border-dashed border-slate-700 hover:border-violet-500/60 rounded-2xl p-6 text-center transition-all bg-slate-950/40 relative">
                            <input type="file" accept=".xlsx,.xls,.csv" @change="handleInventoryFileSelect"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                            <div class="space-y-2 pointer-events-none">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-violet-400 mx-auto flex items-center justify-center">
                                    <i data-lucide="warehouse" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs text-slate-300">
                                    <span class="font-bold text-violet-400">Klik untuk memilih file saldo stok</span> atau
                                    seret file ke sini
                                </div>
                                <p class="text-[11px] text-slate-500">Pastikan Material/Produk sudah terdaftar di Master
                                    Data terlebih dahulu</p>
                            </div>
                        </div>
                    @else
                        <div class="border border-slate-800 rounded-2xl p-6 text-center bg-slate-950/40 space-y-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-slate-800 text-slate-500 mx-auto flex items-center justify-center">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <div class="text-xs text-slate-400">
                                Import saldo stok terkunci pada paket Free. Silakan upgrade ke Pro / Patungan.
                            </div>
                            <a href="{{ route('billing.limits') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition">
                                <span>Buka Kunci Fitur Import</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    @endif

                    <div x-show="inventoryLoading"
                        class="p-3.5 rounded-xl bg-violet-950/30 border border-violet-500/30 flex items-center gap-3">
                        <div class="w-5 h-5 border-2 border-violet-400 border-t-transparent rounded-full animate-spin">
                        </div>
                        <div class="text-xs text-violet-300 font-medium">Menganalisis file, mencocokkan item & lokasi...
                        </div>
                    </div>

                    <div x-show="inventoryFileName && !inventoryLoading"
                        class="flex items-center justify-between text-xs px-3 py-2 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="flex items-center gap-2 text-slate-300">
                            <i data-lucide="file-check" class="w-4 h-4 text-emerald-400"></i>
                            <span class="font-mono" x-text="inventoryFileName"></span>
                        </div>
                        <button type="button"
                            @click="inventoryPreview = null; inventoryFile = null; inventoryFileName = ''"
                            class="text-slate-500 hover:text-rose-400 text-xs">
                            Ganti File
                        </button>
                    </div>
                </div>
            </div>

            <!-- Inventory Preview Table -->
            <div x-show="inventoryPreview" class="glass-card rounded-2xl p-6 border border-slate-800 space-y-5"
                x-transition>
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                    <div>
                        <div class="flex items-center gap-2 text-violet-400 font-bold text-xs">
                            <span
                                class="w-6 h-6 rounded-full bg-violet-500/20 flex items-center justify-center text-xs">3</span>
                            <span>Preview & Konfirmasi Import Saldo Stok</span>
                        </div>
                        <h3 class="font-bold text-white text-base mt-0.5">Hasil Analisis Data Inventori</h3>
                    </div>

                    <div class="flex items-center flex-wrap gap-2">
                        <button type="button" @click="inventoryFilter = 'all'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="inventoryFilter === 'all' ? 'bg-slate-700 text-white border-slate-600' :
                                'bg-slate-900 text-slate-400 border-slate-800'">
                            <span>Semua</span>
                            <span class="font-mono font-bold"
                                x-text="inventoryPreview ? inventoryPreview.total_rows : 0"></span>
                        </button>

                        <button type="button" @click="inventoryFilter = 'valid'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="inventoryFilter === 'valid' ?
                                'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' :
                                'bg-slate-900 text-emerald-400 border-slate-800'">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                            <span>Siap Import</span>
                            <span class="font-mono font-bold"
                                x-text="inventoryPreview ? inventoryPreview.valid_count : 0"></span>
                        </button>

                        <button type="button" @click="inventoryFilter = 'duplicate'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="inventoryFilter === 'duplicate' ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' :
                                'bg-slate-900 text-amber-400 border-slate-800'">
                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                            <span>Sudah Ada Stok</span>
                            <span class="font-mono font-bold"
                                x-text="inventoryPreview ? inventoryPreview.duplicate_count : 0"></span>
                        </button>

                        <button type="button" @click="inventoryFilter = 'error'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                            :class="inventoryFilter === 'error' ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' :
                                'bg-slate-900 text-rose-400 border-slate-800'">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                            <span>Error / Ditolak</span>
                            <span class="font-mono font-bold"
                                x-text="inventoryPreview ? inventoryPreview.error_count : 0"></span>
                        </button>
                    </div>
                </div>

                <!-- Duplicate Strategy for Inventory -->
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                    <div class="flex items-center gap-2 text-slate-300 font-semibold">
                        <i data-lucide="shield" class="w-4 h-4 text-violet-400"></i>
                        <span>Tindakan jika item di lokasi tersebut sudah memiliki saldo stok:</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="inv_dup_strategy" value="skip"
                                x-model="inventoryDuplicateStrategy" class="text-violet-500">
                            <span>Lewati (Aman)</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="inv_dup_strategy" value="adjust"
                                x-model="inventoryDuplicateStrategy" class="text-violet-500">
                            <span>Tambah / Sesuaikan</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300">
                            <input type="radio" name="inv_dup_strategy" value="initial"
                                x-model="inventoryDuplicateStrategy" class="text-violet-500">
                            <span>Set Saldo Awal Baru</span>
                        </label>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-96 rounded-xl border border-slate-800">
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-slate-900 text-slate-400 border-b border-slate-800">
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
                        <tbody class="divide-y divide-slate-800/60 bg-slate-950/40">
                            <template x-for="row in (inventoryPreview ? inventoryPreview.rows : [])"
                                :key="row.row_number">
                                <tr class="hover:bg-slate-900/50 transition"
                                    x-show="inventoryFilter === 'all' || inventoryFilter === row.status">
                                    <td class="py-2.5 px-3 font-mono text-slate-500" x-text="row.row_number"></td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="row.status === 'valid'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                Siap Import
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'duplicate'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                Sudah Ada Stok
                                            </span>
                                        </template>
                                        <template x-if="row.status === 'error'">
                                            <span
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                                Error / Ditolak
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-2.5 px-3 font-bold text-white" x-text="row.item_name || '-'"></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-400" x-text="row.sku || '-'"></td>
                                    <td class="py-2.5 px-3 text-slate-300">
                                        <span
                                            class="px-1.5 py-0.5 rounded-lg bg-slate-900 border border-slate-800 font-medium"
                                            x-text="row.location_name || '-'"></span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono text-violet-300 font-bold"
                                        x-text="row.quantity"></td>
                                    <td class="py-2.5 px-3 font-mono text-slate-300" x-text="row.unit"></td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-300"
                                        x-text="'Rp ' + (Number(row.unit_cost) || 0).toLocaleString('id-ID')"></td>
                                    <td class="py-2.5 px-3 text-[11px]"
                                        :class="row.status === 'error' ? 'text-rose-400 font-semibold' : (row
                                            .status === 'duplicate' ? 'text-amber-300' : 'text-slate-400')"
                                        x-text="row.status_reason"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-800">
                    <div class="text-xs text-slate-400">
                        Total <strong class="text-white"
                            x-text="inventoryPreview ? inventoryPreview.total_rows : 0"></strong> baris stok dianalisis.
                        <span class="text-violet-400 font-semibold"
                            x-text="inventoryPreview ? '(' + inventoryPreview.valid_count + ' siap dicatat)' : ''"></span>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="inventoryPreview = null; inventoryFile = null"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                            Batal
                        </button>

                        <button type="button" @click="executeInventoryImport()" :disabled="inventoryExecuting"
                            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-400 hover:to-purple-500 text-white font-black text-xs shadow-lg shadow-violet-500/20 transition flex items-center gap-2">
                            <template x-if="inventoryExecuting">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin">
                                </div>
                            </template>
                            <i data-lucide="check" class="w-4 h-4" x-show="!inventoryExecuting"></i>
                            <span>Konfirmasi & Catat Saldo Stok</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
