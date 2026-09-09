@extends('layouts.app', [
    'title' => 'Bahan Baku & Harga',
    'headerTitle' => 'Katalog Bahan Baku & Pemasok',
    'headerSubtitle' => 'Kelola harga akuisisi efektif, rendemen (yield), susut (waste), dan riwayat harga bahan'
])

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    showPriceModal: false,
    showAddSupplierModal: false,
    showAddCategoryModal: false,
    showAddUnitModal: false,
    selectedMaterial: null,
    editMaterial: { id: '', slug: '', name: '', sku: '', category_id: '', supplier_id: '', unit_id: '' },
    
    openPriceModal(mat) {
        this.selectedMaterial = mat;
        this.showPriceModal = true;
    },

    openEditModal(mat) {
        this.editMaterial = {
            id: mat.id,
            slug: mat.slug,
            name: mat.name,
            sku: mat.code || mat.sku || '',
            category_id: mat.category_id || '',
            supplier_id: mat.supplier_id || '',
            unit_id: mat.unit_id
        };
        this.showEditModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('materials.index') }}" class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama bahan atau SKU..." 
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
            <a href="{{ route('import.index', ['tab' => 'materials']) }}" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 text-xs font-semibold flex items-center justify-center gap-2 transition-all"
               title="Import data bahan baku massal dari file Excel / CSV">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-400"></i>
                <span>Import Excel</span>
            </a>

            <button @click="showAddModal = true" 
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Bahan Baku</span>
            </button>
        </div>
    </div>

    <!-- Materials Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="table-responsive">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Nama Bahan & SKU</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Kategori</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Satuan Beli</th>
                        <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap">Harga Beli Terakhir</th>
                        <th class="py-3.5 px-4 font-semibold text-center whitespace-nowrap">Yield / Waste</th>
                        <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap">Biaya Efektif/Unit</th>
                        <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($materials as $mat)
                    @php
                        $latestPrice = $mat->prices->first();
                    @endphp
                    <tr class="hover:bg-slate-900/40 transition-colors">
                        <td class="py-3.5 px-4 font-medium text-white">
                            <div class="font-bold">{{ $mat->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $mat->sku ?? 'No SKU' }} • Supplier: {{ $mat->supplier?->name ?? '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-slate-300">
                            {{ $mat->category?->name ?? 'Umum' }}
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-300">
                            {{ $mat->unit?->name ?? 'pcs' }} ({{ $mat->unit?->code }})
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                            {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->purchase_price ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-emerald-400 font-mono font-bold text-[10px]">
                                {{ $mat->yield_percentage }}% Yield
                            </span>
                            @if($mat->waste_percentage > 0)
                            <span class="px-2 py-0.5 rounded-full bg-red-500/10 text-red-400 font-mono font-bold text-[10px] ml-1">
                                {{ $mat->waste_percentage }}% Waste
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-400">
                            {{ $business->currency_symbol }} {{ number_format((float) ($latestPrice?->effective_cost ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="openPriceModal({{ Js::from($mat) }})" 
                                        class="px-2.5 py-1 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 font-semibold text-[11px] transition-colors">
                                    Update Harga
                                </button>
                                <button type="button" @click="openEditModal({{ Js::from($mat) }})" 
                                        class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Edit Spesifikasi Bahan">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <form method="POST" action="{{ route('materials.destroy', $mat->slug) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus bahan baku ini?', 'Hapus Bahan?', 'danger')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-slate-500 hover:text-red-400 rounded transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Belum ada data bahan baku ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($materials->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $materials->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Tambah Bahan Baru -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-lg w-full p-6 rounded-2xl space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah Bahan Baku Baru</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" action="{{ route('materials.store') }}" class="space-y-4 text-xs">
                @csrf
                
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Bahan Baku *</label>
                    <input type="text" name="name" required placeholder="Contoh: Tepung Terigu Protein Tinggi"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-semibold text-slate-300">Satuan Beli *</label>
                            <button type="button" @click="showAddUnitModal = true" class="text-[10px] text-emerald-400 hover:underline flex items-center gap-0.5">
                                <i data-lucide="plus" class="w-3 h-3"></i> Buat Satuan
                            </button>
                        </div>
                        <select name="unit_id" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $u->code === 'kg' ? 'selected' : '' }}>{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-semibold text-slate-300">Kategori Bahan</label>
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

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-semibold text-slate-300">Supplier / Pemasok</label>
                            <button type="button" @click="showAddSupplierModal = true" class="text-[10px] text-emerald-400 hover:underline flex items-center gap-0.5">
                                <i data-lucide="plus" class="w-3 h-3"></i> Buat Supplier
                            </button>
                        </div>
                        <select name="supplier_id" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="">-- Tanpa Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">SKU / Kode Bahan</label>
                        <input type="text" name="sku" placeholder="MAT-001" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                </div>

                <!-- Yield & Waste -->
                <div class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Rendemen (Yield %)</label>
                        <input type="number" name="yield_percentage" value="100" min="1" max="500" required
                               class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Susut (Waste %)</label>
                        <input type="number" name="waste_percentage" value="0" min="0" max="100" required
                               class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                    </div>
                </div>

                <!-- Initial Price -->
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5">
                    <div class="font-bold text-emerald-400">Harga Akuisisi Awal</div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Harga Beli</label>
                            <input type="number" name="purchase_price" required min="1" placeholder="50000"
                                   class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Ongkir</label>
                            <input type="number" name="shipping_cost" value="0" min="0"
                                   class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400 mb-1">Diskon</label>
                            <input type="number" name="discount_amount" value="0" min="0"
                                   class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20">Simpan Bahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Harga -->
    <div x-show="showPriceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4 max-h-[88dvh] overflow-y-auto" @click.outside="showPriceModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-white">Update Harga Bahan</h3>
                    <p class="text-xs text-slate-400" x-text="selectedMaterial ? selectedMaterial.name : ''"></p>
                </div>
                <button @click="showPriceModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <template x-if="selectedMaterial">
                <form :action="'/materials/' + selectedMaterial.slug + '/prices'" method="POST" class="space-y-3.5 text-xs">
                    @csrf
                    
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Harga Beli Baru (Rp) *</label>
                        <input type="number" name="purchase_price" required min="1" step="100"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Ongkos Kirim</label>
                            <input type="number" name="shipping_cost" value="0" min="0"
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Diskon Pembelian</label>
                            <input type="number" name="discount_amount" value="0" min="0"
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Penyesuaian harga supplier"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>

                    <div class="pt-2 flex justify-end gap-2">
                        <button type="button" @click="showPriceModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold">Simpan Riwayat</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- Modal CMS Supplier -->
    <div x-show="showAddSupplierModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-sm w-full p-6 rounded-2xl space-y-4 max-h-[88dvh] overflow-y-auto" @click.outside="showAddSupplierModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Supplier Baru</span>
                </h3>
                <button @click="showAddSupplierModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Supplier / Vendor *</label>
                    <input type="text" name="name" required placeholder="Contoh: PT Bogasari / Toko Sumber Makmur"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kontak Person</label>
                        <input type="text" name="contact_person" placeholder="Pak Budi"
                               class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">No. WhatsApp/HP</label>
                        <input type="text" name="phone" placeholder="08123456789"
                               class="w-full px-3 py-1.5 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Alamat / Lokasi</label>
                    <input type="text" name="address" placeholder="Pasar Induk Kramat Jati"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddSupplierModal = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Tutup</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal CMS Kategori Bahan -->
    <div x-show="showAddCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-sm w-full p-6 rounded-2xl space-y-4 max-h-[88dvh] overflow-y-auto" @click.outside="showAddCategoryModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="folder-plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Kategori Bahan</span>
                </h3>
                <button @click="showAddCategoryModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form method="POST" action="{{ route('material-categories.store') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Kategori Bahan *</label>
                    <input type="text" name="name" required placeholder="Contoh: Tepung & Biji / Kemasan / Bumbu"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Deskripsi</label>
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

    <!-- Modal CMS Satuan Beli Baru -->
    <div x-show="showAddUnitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-sm w-full p-6 rounded-2xl space-y-4 max-h-[88dvh] overflow-y-auto" @click.outside="showAddUnitModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="scale" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Satuan Beli Baru</span>
                </h3>
                <button @click="showAddUnitModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form method="POST" action="{{ route('units.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode Simbol *</label>
                        <input type="text" name="code" required placeholder="sak / jerigen / roll"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kategori Satuan *</label>
                        <select name="category" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="weight">Berat (kg, g, sak)</option>
                            <option value="volume">Volume (l, ml, jerigen)</option>
                            <option value="quantity">Kuantitas (pcs, pack, dus)</option>
                            <option value="length">Panjang (m, cm, roll)</option>
                            <option value="custom">Kustom / Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Satuan Lengkap *</label>
                    <input type="text" name="name" required placeholder="Contoh: Sak 25kg / Jerigen 5L"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showAddUnitModal = false" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Tutup</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold">Simpan Satuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Bahan Baku -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-lg w-full p-6 rounded-2xl space-y-4 border border-slate-700 max-h-[90vh] overflow-y-auto" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-5 h-5 text-emerald-400"></i>
                    <span>Edit Spesifikasi Bahan Baku</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/materials/' + editMaterial.slug" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Bahan Baku *</label>
                    <input type="text" name="name" x-model="editMaterial.name" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode SKU / Barcode</label>
                        <input type="text" name="sku" x-model="editMaterial.sku" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kategori Bahan</label>
                        <select name="category_id" x-model="editMaterial.category_id" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                            <option value="">-- Tanpa Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Satuan Beli / Dasar *</label>
                        <select name="unit_id" x-model="editMaterial.unit_id" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Pemasok Utama</label>
                        <select name="supplier_id" x-model="editMaterial.supplier_id" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                            <option value="">-- Tanpa Pemasok --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-slate-800">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
