@extends('layouts.app', [
    'title' => 'Produk & Model Biaya',
    'headerTitle' => 'Katalog Produk & Model HPP',
    'headerSubtitle' => 'Kelola produk jadi, metode kalkulasi (BOM, ABC, Job Order), dan struktur resep'
])

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    showAddCategoryModal: false,
    showAddUnitModal: false,
    editProduct: { id: '', slug: '', name: '', sku: '', category_id: '', output_unit_id: '', base_cost: 0, selling_price: 0, min_stock: 0, is_active: true, description: '' },
    openEditModal(p) {
        this.editProduct = { ...p };
        this.showEditModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex-1 flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari produk atau SKU..." 
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

        <button @click="showAddModal = true" 
                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Produk Baru</span>
        </button>
    </div>

    <!-- Products Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Nama Produk & SKU</th>
                        <th class="py-3.5 px-4 font-semibold">Kategori</th>
                        <th class="py-3.5 px-4 font-semibold">Satuan Output</th>
                        <th class="py-3.5 px-4 font-semibold text-right">HPP Standar / Aktif</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Harga Jual Aktif</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
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
                            <div class="font-bold text-sm">{{ $prod->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $prod->code ?? $prod->sku ?? 'Tanpa SKU' }}</div>
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
                                    description: '{{ addslashes($prod->description ?? '') }}'
                                })" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Edit Produk">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>

                                <form method="POST" action="{{ route('products.destroy', $prod->slug) }}" onsubmit="return confirm('Hapus produk ini beserta model biayanya?')">
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
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Buat Produk Baru</h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" action="{{ route('products.store') }}" class="space-y-3.5 text-xs">
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
                    <label class="block font-semibold text-slate-300 mb-1">SKU Produk (Opsional)</label>
                    <input type="text" name="sku" placeholder="PRD-001"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
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
        <div class="glass-card max-w-sm w-full p-6 rounded-2xl space-y-4" @click.outside="showAddCategoryModal = false">
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
        <div class="glass-card max-w-sm w-full p-6 rounded-2xl space-y-4" @click.outside="showAddUnitModal = false">
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

            <form :action="'/products/' + editProduct.slug" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Produk *</label>
                    <input type="text" name="name" x-model="editProduct.name" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode SKU</label>
                        <input type="text" name="sku" x-model="editProduct.sku" class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
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
</div>
@endsection
