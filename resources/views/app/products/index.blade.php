@extends('layouts.app', [
    'title' => 'Produk & Model Biaya',
    'headerTitle' => 'Katalog Produk & Model HPP',
    'headerSubtitle' => 'Kelola produk jadi, metode kalkulasi (BOM, ABC, Job Order), dan struktur resep'
])

@section('content')
<div class="space-y-6" x-data="{
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
    posShowImages: {{ $business->pos_show_product_images ? 'true' : 'false' }},
    posImageToggling: false,
    newProductPreview: '',
    editProductPreview: '',
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
                    confirmButtonColor: '#ef4444'
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
                Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: 'Gunakan format JPG, PNG, atau WebP.', confirmButtonColor: '#ef4444' });
            }
            e.target.value = '';
            this.newProductPreview = '';
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file maksimal 4 MB.', confirmButtonColor: '#ef4444' });
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
                Swal.fire({ icon: 'error', title: 'Format Tidak Didukung', text: 'Gunakan format JPG, PNG, atau WebP.', confirmButtonColor: '#ef4444' });
            }
            e.target.value = '';
            this.editProductPreview = '';
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Ukuran Terlalu Besar', text: 'Ukuran file maksimal 4 MB.', confirmButtonColor: '#ef4444' });
            }
            e.target.value = '';
            this.editProductPreview = '';
            return;
        }
        this.editProductPreview = URL.createObjectURL(file);
    },
    confirmDeleteProduct(formEl) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data produk ini beserta model biayanya akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    formEl.submit();
                }
            });
        } else {
            formEl.submit();
        }
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

    <!-- Top Action Bar -->
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari produk, SKU, atau barcode..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
            </div>

            <select name="category_id" onchange="this.form.submit()"
                    class="px-3 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </form>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="togglePosShowImages()" :disabled="posImageToggling"
               class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-all border disabled:opacity-50"
               :class="posShowImages ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300 shadow-sm' : 'bg-slate-800 hover:bg-slate-700 border-slate-700 text-slate-300'"
               title="Klik untuk mengubah apakah gambar produk ditampilkan pada POS">
                <i data-lucide="image" class="w-4 h-4" :class="posShowImages ? 'text-emerald-400' : 'text-slate-400'"></i>
                <span class="hidden sm:inline">Gambar di POS:</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold transition-all"
                      :class="posShowImages ? 'bg-emerald-500 text-slate-950 shadow' : 'bg-slate-700 text-slate-300'"
                      x-text="posShowImages ? 'ON' : 'OFF'"></span>
            </button>
            <a href="{{ route('import.index', ['tab' => 'products']) }}"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-teal-300 border border-teal-500/30 text-xs font-semibold flex items-center justify-center gap-2 transition-all"
               title="Import data produk massal dari file Excel / CSV">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-teal-400"></i>
                <span>Import Excel</span>
            </a>

            <button @click="showAddModal = true"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Produk Baru</span>
            </button>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="table-responsive">
            <table class="w-full text-left text-xs min-w-[640px]">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Nama Produk & Barcode</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Kategori</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Satuan Output</th>
                        <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap">HPP Standar / Aktif</th>
                        <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap">Harga Jual Aktif</th>
                        <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($products as $prod)
                    @php
                        $activeModel = $prod->costModels->first();
                        $latestVer = $activeModel?->latestVersion;
                        $effectiveHpp = $latestVer ? (float)$latestVer->hpp_per_unit : (float)$prod->base_cost;
                    @endphp
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4 font-medium text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 overflow-hidden flex items-center justify-center shrink-0">
                                    @if($prod->image_url)
                                        <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                        <i data-lucide="package" class="hidden w-5 h-5 text-slate-500"></i>
                                    @else
                                        <i data-lucide="package" class="w-5 h-5 text-slate-500"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-sm">{{ $prod->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $prod->code ?? $prod->sku ?? 'Tanpa SKU' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-300">
                            {{ $prod->category?->name ?? 'Umum' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-300">
                            {{ $prod->outputUnit?->name ?? 'pcs' }} ({{ $prod->outputUnit?->code }})
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono">
                            @if($effectiveHpp > 0)
                                <div class="font-extrabold text-emerald-400">{{ $business->currency_symbol }} {{ number_format($effectiveHpp, 0, ',', '.') }}</div>
                                @if($latestVer)
                                    <div class="text-[10px] text-slate-400 font-semibold">{{ $latestVer->version_label }}</div>
                                @endif
                            @else
                                <span class="text-slate-500 italic">Belum dihitung</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono">
                            @if($prod->selling_price > 0)
                                <div class="font-extrabold text-teal-300">{{ $business->currency_symbol }} {{ number_format((float)$prod->selling_price, 0, ',', '.') }}</div>
                                @if($effectiveHpp > 0)
                                    <div class="text-[10px] text-emerald-400 font-semibold">Margin {{ round((($prod->selling_price - $effectiveHpp) / $prod->selling_price) * 100) }}%</div>
                                @endif
                            @else
                                <span class="text-slate-500 italic">Rp 0</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('products.bom', $prod->slug) }}"
                                   class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs transition-colors flex items-center gap-1">
                                    <i data-lucide="git-fork" class="w-3.5 h-3.5"></i>
                                    <span>BOM</span>
                                </a>

                                <a href="{{ route('calculator.index', ['product_id' => $prod->id, 'tab' => 'advanced']) }}"
                                   class="px-2.5 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 font-semibold text-xs transition-colors flex items-center gap-1"
                                   title="Hitung HPP & tetapkan harga jual di Kalkulator">
                                    <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                                    <span>Kalkulasi HPP</span>
                                </a>

                                <button @click="openEditModal({
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
                                })" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Edit Produk">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>

                                <form method="POST" action="{{ route('products.destroy', $prod->slug) }}" @submit.prevent="confirmDeleteProduct($el)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-red-400 rounded transition-colors" title="Hapus Produk">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            Belum ada produk terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Tambah Produk -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4 max-h-[88dvh] overflow-y-auto" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Buat Produk Baru</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Produk *</label>
                    <input type="text" name="name" required placeholder="Contoh: Roti Tawar Gandum / Kopi Latte"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-semibold text-slate-300">Satuan Output *</label>
                            <button type="button" @click="showAddUnitModal = true" class="text-[10px] text-emerald-400 hover:underline flex items-center gap-0.5">
                                <i data-lucide="plus" class="w-3 h-3"></i> Buat Satuan
                            </button>
                        </div>
                        <select name="output_unit_id" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $u->code === 'pcs' ? 'selected' : '' }}>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-semibold text-slate-300">Kategori</label>
                            <button type="button" @click="showAddCategoryModal = true" class="text-[10px] text-emerald-400 hover:underline flex items-center gap-0.5">
                                <i data-lucide="plus" class="w-3 h-3"></i> Buat Kategori
                            </button>
                        </div>
                        <select name="category_id" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Metode Kalkulasi HPP *</label>
                    <select name="costing_method" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <option value="recipe_bom">Recipe / Bill of Materials (F&B & Manufaktur)</option>
                        <option value="simple">Simple / Standar Flat</option>
                        <option value="job">Job Order Costing (Proyek / Custom Order)</option>
                        <option value="process">Process Costing (Batch / Massal)</option>
                        <option value="abc">Activity-Based Costing (ABC)</option>
                        <option value="service">Service / Jasa Man-Hour</option>
                        <option value="retail">Retail / Landed Cost Grosir</option>
                    </select>
                </div>

                <div>
                      <label class="block font-semibold text-slate-300 mb-1">Barcode / SKU (Opsional)</label>
                    <div class="flex gap-2">
                        <input type="text" name="sku" x-ref="newProductBarcode" inputmode="numeric" autocomplete="off" placeholder="8991234567890 atau PRD-001"
                               class="min-w-0 flex-1 px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                        <button type="button" @click="requestProductScanner('add')" class="shrink-0 px-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950" title="Scan barcode dengan kamera" aria-label="Scan barcode dengan kamera">
                            <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                        </button>
                    </div>
                      <p class="text-[10px] text-slate-500 mt-1">Isi dengan nomor barcode produk agar dapat dipindai di POS.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi Produk (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Deskripsi ringkas atau komposisi produk..."
                              class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Gambar Produk (Opsional)</label>
                    <div x-show="newProductPreview" class="mb-2 w-20 h-20 rounded-xl overflow-hidden border border-emerald-500/40 bg-slate-900 relative">
                        <img :src="newProductPreview" alt="Preview Gambar Baru" class="w-full h-full object-cover">
                        <button type="button" @click="newProductPreview = ''; $refs.newProductImageInput.value = ''" class="absolute top-1 right-1 p-0.5 rounded-full bg-black/70 text-rose-400 hover:text-white" title="Hapus pilihan">
                            <i data-lucide="x" class="w-3 h-3"></i>
                        </button>
                    </div>
                    <input type="file" name="image" x-ref="newProductImageInput" @change="handleNewProductImage($event)" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500/15 file:px-3 file:py-2 file:text-emerald-300">
                    <p class="text-[10px] text-slate-500 mt-1">JPG, PNG, atau WebP maksimal 4 MB.</p>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20">Buat Produk</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal CMS Kategori Produk -->
    <div x-show="showAddCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-sm w-full p-6 rounded-2xl space-y-4 max-h-[88dvh] overflow-y-auto" @click.outside="showAddCategoryModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="folder-plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Kategori Produk</span>
                </h3>
                <button @click="showAddCategoryModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form method="POST" action="{{ route('product-categories.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Kategori *</label>
                    <input type="text" name="name" required placeholder="Contoh: Makanan Berat / Minuman / Pakaian"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi (Opsional)</label>
                    <input type="text" name="description" placeholder="Catatan kategori..."
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddCategoryModal = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Tutup</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal CMS Satuan Output Baru -->
    <div x-show="showAddUnitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-sm w-full p-6 rounded-2xl space-y-4 max-h-[88dvh] overflow-y-auto" @click.outside="showAddUnitModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="scale" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Satuan Output Baru</span>
                </h3>
                <button @click="showAddUnitModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form method="POST" action="{{ route('units.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode Simbol *</label>
                        <input type="text" name="code" required placeholder="porsi / btl / box"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kategori Satuan *</label>
                        <select name="category" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="quantity">Kuantitas / Unit (pcs, porsi)</option>
                            <option value="weight">Berat (g, kg)</option>
                            <option value="volume">Volume (ml, l)</option>
                            <option value="length">Panjang (cm, m)</option>
                            <option value="time">Waktu (jam, menit)</option>
                            <option value="custom">Kustom / Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Satuan Lengkap *</label>
                    <input type="text" name="name" required placeholder="Contoh: Porsi Hidangan / Botol 250ml"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddUnitModal = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Tutup</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold">Simpan Satuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-lg w-full p-6 rounded-2xl space-y-4 border border-slate-700 max-h-[90vh] overflow-y-auto" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-5 h-5 text-emerald-400"></i>
                    <span>Edit Data Produk</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/products/' + editProduct.slug" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Produk *</label>
                    <input type="text" name="name" x-model="editProduct.name" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Barcode / SKU</label>
                        <div class="flex gap-2">
                            <input type="text" name="sku" x-model="editProduct.sku" inputmode="numeric" autocomplete="off" placeholder="8991234567890 atau PRD-001" class="min-w-0 flex-1 px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                            <button type="button" @click="requestProductScanner('edit')" class="shrink-0 px-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500 hover:text-slate-950" title="Scan barcode dengan kamera" aria-label="Scan barcode dengan kamera">
                                <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Nomor ini digunakan scanner barcode di POS.</p>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kategori</label>
                        <select name="category_id" x-model="editProduct.category_id" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Satuan Output *</label>
                        <select name="output_unit_id" x-model="editProduct.output_unit_id" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Minimum Stok</label>
                        <input type="number" step="any" name="min_stock" x-model="editProduct.min_stock" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 rounded-xl bg-slate-900/60 border border-slate-800">
                    <div>
                        <label class="block font-semibold text-emerald-400 mb-1">HPP Standar (Modal)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-mono">{{ $business->currency_symbol }}</span>
                            <input type="number" step="any" name="base_cost" x-model="editProduct.base_cost" class="w-full pl-10 pr-3 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-teal-400 mb-1">Harga Jual Aktif</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 font-mono">{{ $business->currency_symbol }}</span>
                            <input type="number" step="any" name="selling_price" x-model="editProduct.selling_price" class="w-full pl-10 pr-3 py-2 bg-slate-950 border border-slate-800 focus:border-teal-500 rounded-xl text-white font-mono font-bold">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi Produk</label>
                    <textarea name="description" rows="2" x-model="editProduct.description" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Gambar Produk</label>
                    <div x-show="editProductPreview || editProduct.image_url" class="mb-2 flex items-center gap-3">
                        <div class="w-20 h-20 rounded-xl overflow-hidden border border-slate-700 bg-slate-900 relative shrink-0">
                            <img :src="editProductPreview || editProduct.image_url" :alt="editProduct.name" class="w-full h-full object-cover">
                        </div>
                        <div class="text-[11px] text-slate-400">
                            <span x-show="editProductPreview" class="text-emerald-400 font-semibold block">Pratinjau gambar baru</span>
                            <span x-show="!editProductPreview && editProduct.image_url" class="text-slate-400 block">Gambar saat ini</span>
                            <span class="text-[10px] text-slate-500">Pilih file baru di bawah jika ingin mengganti.</span>
                        </div>
                    </div>
                    <input type="file" name="image" @change="handleEditProductImage($event)" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-slate-400 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-500/15 file:px-3 file:py-2 file:text-emerald-300">
                    <label x-show="editProduct.image_url" class="mt-2 flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="rounded bg-slate-900 border-slate-700 text-rose-500 focus:ring-rose-500">
                        <span>Hapus gambar saat ini</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" :checked="editProduct.is_active" class="rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                    <label for="edit_is_active" class="text-slate-300 font-medium cursor-pointer">Produk Aktif untuk Dijual & Masuk Faktur</label>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-800">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal izin kamera pertama kali -->
    <div x-show="showProductScannerPermission" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4 border border-slate-700">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="camera" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Izinkan Akses Kamera?</h3>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">Kamera hanya digunakan untuk membaca barcode produk saat Anda memilih Scan Barcode. Kamera berhenti setelah scanner ditutup.</p>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" @click="showProductScannerPermission = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                <button type="button" @click="confirmProductScannerAccess()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs">Lanjutkan</button>
            </div>
        </div>
    </div>

    <!-- Modal scanner barcode produk -->
    <div x-show="showProductScanner" x-cloak @keydown.escape.window="closeProductScanner()" @click.self="closeProductScanner()" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/85 backdrop-blur-sm">
        <div class="glass-card max-w-lg w-full p-4 sm:p-6 rounded-2xl space-y-4 border border-slate-700">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-white">Scan Barcode Produk</h3>
                    <p class="text-xs text-slate-400 mt-1">Arahkan kamera ke barcode sampai terbaca.</p>
                </div>
                <button type="button" @click="closeProductScanner()" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800" title="Tutup scanner" aria-label="Tutup scanner">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-2xl bg-slate-950 border border-slate-800">
                <video x-ref="productBarcodeVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-[72%] h-[42%] rounded-xl border-2 border-emerald-400 shadow-[0_0_0_9999px_rgba(2,6,23,.38)]"></div>
                </div>
                <div x-show="productScannerStarting" class="absolute inset-0 flex items-center justify-center bg-slate-950/70 text-xs text-slate-300">
                    <span class="flex items-center gap-2"><i data-lucide="loader-circle" class="w-4 h-4 animate-spin"></i> Menyiapkan kamera...</span>
                </div>
            </div>
            <div x-show="productScannerError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-300" x-text="productScannerError"></div>
            <div class="flex justify-end pt-2 border-t border-slate-800">
                <button type="button" @click="closeProductScanner()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white font-semibold text-xs">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
