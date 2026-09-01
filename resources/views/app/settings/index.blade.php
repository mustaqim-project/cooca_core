@extends('layouts.app', [
    'title' => 'Pengaturan Bisnis & Template',
    'headerTitle' => 'Pengaturan Bisnis & Template Industri',
    'headerSubtitle' => 'Konfigurasi pembulatan output akhir, mata uang, tim kerja, dan 20 preset template industri (§35 Blueprint)'
])

@section('content')
<div class="space-y-8" x-data="{ activeTab: 'templates' }">

    <!-- Tab Selector -->
    <div class="flex items-center gap-2 border-b border-slate-800 pb-3 text-xs font-semibold overflow-x-auto">
        <button type="button" @click="activeTab = 'masterdata'"
                :class="activeTab === 'masterdata' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0">
            <i data-lucide="database" class="w-4 h-4"></i>
            <span>Master Data (CMS)</span>
        </button>

        <button type="button" @click="activeTab = 'templates'"
                :class="activeTab === 'templates' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0">
            <i data-lucide="layers" class="w-4 h-4"></i>
            <span>20 Template Industri (§35)</span>
        </button>

        <button type="button" @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0">
            <i data-lucide="settings" class="w-4 h-4"></i>
            <span>Profil & Pembulatan</span>
        </button>

        <button type="button" @click="activeTab = 'members'"
                :class="activeTab === 'members' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20' : 'bg-slate-900 text-slate-400 hover:text-white'"
                class="px-4 py-2 rounded-xl transition-all flex items-center gap-2 shrink-0">
            <i data-lucide="users" class="w-4 h-4"></i>
            <span>Anggota Tim & Hak Akses</span>
        </button>
    </div>

    <!-- Tab 0: Master Data CMS (Suppliers, Units, Categories) -->
    <div x-show="activeTab === 'masterdata'" class="space-y-8" style="display: none;">
        
        <!-- Grid 1: Suppliers & Custom Units -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- CMS Suppliers -->
            <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4 text-emerald-400"></i>
                            <span>Daftar Supplier / Pemasok</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Kelola daftar kontak vendor dan pemasok bahan</p>
                    </div>
                </div>

                <!-- Form Tambah Supplier -->
                <div class="p-4 bg-slate-950/60 border-b border-slate-800">
                    <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-3 text-xs">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <input type="text" name="name" required placeholder="Nama Supplier *" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                            <div>
                                <input type="text" name="contact_person" placeholder="Kontak Person" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <input type="text" name="phone" placeholder="No. HP / WhatsApp" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                            </div>
                            <div>
                                <input type="text" name="address" placeholder="Alamat / Kota" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Supplier</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Suppliers -->
                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                                <th class="py-2.5 px-4 font-semibold">Nama Supplier</th>
                                <th class="py-2.5 px-4 font-semibold">Kontak</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($suppliers as $sup)
                            <tr class="hover:bg-slate-900/40">
                                <td class="py-2.5 px-4 font-bold text-white">
                                    {{ $sup->name }}
                                    @if($sup->address)
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $sup->address }}</div>
                                    @endif
                                </td>
                                <td class="py-2.5 px-4 text-slate-300 font-mono text-[11px]">
                                    {{ $sup->phone ?? $sup->contact_person ?? '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-right">
                                    <form method="POST" action="{{ route('suppliers.destroy', $sup->id) }}" onsubmit="return confirm('Hapus supplier ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-500 hover:text-red-400 rounded transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-500">Belum ada supplier kustom.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CMS Satuan Output & Beli (Units) -->
            <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="scale" class="w-4 h-4 text-emerald-400"></i>
                            <span>Daftar Satuan Ukur (Units)</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Satuan beli bahan baku & satuan output produk</p>
                    </div>
                </div>

                <!-- Form Tambah Satuan -->
                <div class="p-4 bg-slate-950/60 border-b border-slate-800">
                    <form method="POST" action="{{ route('units.store') }}" class="space-y-3 text-xs">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <input type="text" name="code" required placeholder="Simbol (cth: porsi/sak) *" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                            </div>
                            <div>
                                <input type="text" name="name" required placeholder="Nama Lengkap *" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                            <div>
                                <select name="category" required class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                                    <option value="quantity">Kuantitas (pcs)</option>
                                    <option value="weight">Berat (kg, g)</option>
                                    <option value="volume">Volume (l, ml)</option>
                                    <option value="length">Panjang (m, cm)</option>
                                    <option value="time">Waktu (jam)</option>
                                    <option value="custom">Kustom</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Satuan</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Custom Units -->
                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                                <th class="py-2.5 px-4 font-semibold">Simbol & Nama</th>
                                <th class="py-2.5 px-4 font-semibold">Tipe / Kategori</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($customUnits as $u)
                            <tr class="hover:bg-slate-900/40">
                                <td class="py-2.5 px-4 font-bold text-white">
                                    <span class="font-mono text-emerald-400">{{ $u->code }}</span> — {{ $u->name }}
                                </td>
                                <td class="py-2.5 px-4 text-slate-300 capitalize text-[11px]">
                                    {{ $u->category }}
                                </td>
                                <td class="py-2.5 px-4 text-right">
                                    <form method="POST" action="{{ route('units.destroy', $u->id) }}" onsubmit="return confirm('Hapus satuan kustom ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-500 hover:text-red-400 rounded transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-500">Belum ada satuan kustom (20 satuan standar sistem aktif).</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Grid 2: Product Categories & Material Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- CMS Kategori Produk -->
            <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="folder" class="w-4 h-4 text-emerald-400"></i>
                            <span>Kategori Produk Jadi</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Klasifikasi menu & katalog barang jadi</p>
                    </div>
                </div>

                <!-- Form Tambah Kategori Produk -->
                <div class="p-4 bg-slate-950/60 border-b border-slate-800">
                    <form method="POST" action="{{ route('product-categories.store') }}" class="space-y-3 text-xs">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <input type="text" name="name" required placeholder="Nama Kategori Produk *" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                            <div>
                                <input type="text" name="description" placeholder="Deskripsi Kategori" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Kategori Produk</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Product Categories -->
                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                                <th class="py-2.5 px-4 font-semibold">Nama Kategori</th>
                                <th class="py-2.5 px-4 font-semibold">Deskripsi</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($productCategories as $pCat)
                            <tr class="hover:bg-slate-900/40">
                                <td class="py-2.5 px-4 font-bold text-white">{{ $pCat->name }}</td>
                                <td class="py-2.5 px-4 text-slate-300 text-[11px]">{{ $pCat->description ?? '-' }}</td>
                                <td class="py-2.5 px-4 text-right">
                                    <form method="POST" action="{{ route('product-categories.destroy', $pCat->id) }}" onsubmit="return confirm('Hapus kategori produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-500 hover:text-red-400 rounded transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-500">Belum ada kategori produk.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CMS Kategori Bahan Baku -->
            <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between">
                <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <i data-lucide="layers" class="w-4 h-4 text-emerald-400"></i>
                            <span>Kategori Bahan Baku & Material</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Klasifikasi kelompok material & bahan mentah</p>
                    </div>
                </div>

                <!-- Form Tambah Kategori Bahan -->
                <div class="p-4 bg-slate-950/60 border-b border-slate-800">
                    <form method="POST" action="{{ route('material-categories.store') }}" class="space-y-3 text-xs">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <input type="text" name="name" required placeholder="Nama Kategori Bahan *" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                            <div>
                                <input type="text" name="description" placeholder="Deskripsi Kategori" class="w-full px-3 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-white">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Tambah Kategori Bahan</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Table Material Categories -->
                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                                <th class="py-2.5 px-4 font-semibold">Nama Kategori</th>
                                <th class="py-2.5 px-4 font-semibold">Deskripsi</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($materialCategories as $mCat)
                            <tr class="hover:bg-slate-900/40">
                                <td class="py-2.5 px-4 font-bold text-white">{{ $mCat->name }}</td>
                                <td class="py-2.5 px-4 text-slate-300 text-[11px]">{{ $mCat->description ?? '-' }}</td>
                                <td class="py-2.5 px-4 text-right">
                                    <form method="POST" action="{{ route('material-categories.destroy', $mCat->id) }}" onsubmit="return confirm('Hapus kategori bahan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 text-slate-500 hover:text-red-400 rounded transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-500">Belum ada kategori bahan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <!-- Tab 1: 20 Industry Templates (§35) -->
    <div x-show="activeTab === 'templates'" class="space-y-6">
        <div class="glass-card p-6 rounded-2xl">
            <h3 class="text-base font-bold text-white mb-1">Preset Template Industri Siap Pakai</h3>
            <p class="text-xs text-slate-400">Pilih salah satu dari 20 template di bawah untuk menerapkan struktur kategori dan komponen biaya default secara instan tanpa mengunci kebebasan kustomisasi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($templates as $tmpl)
            <div class="glass-card p-5 rounded-2xl flex flex-col justify-between space-y-4 hover:border-emerald-500/40 transition-colors">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-2 py-0.5 rounded-full bg-slate-800 text-emerald-400 font-mono font-bold text-[10px] uppercase">
                            {{ $tmpl->industry_category }}
                        </span>
                        <span class="text-[11px] text-slate-500 font-mono">{{ $tmpl->recommended_costing_method }}</span>
                    </div>
                    <h4 class="text-sm font-bold text-white">{{ $tmpl->name }}</h4>
                    <p class="text-xs text-slate-400 mt-1.5 line-clamp-2">{{ $tmpl->description }}</p>
                </div>

                <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between">
                    <div class="text-[11px] text-slate-400">
                        {{ count($tmpl->default_cost_components) }} Komponen
                    </div>

                    <form method="POST" action="{{ route('settings.apply-template') }}" onsubmit="return confirm('Terapkan template {{ $tmpl->name }} ke bisnis Anda?')">
                        @csrf
                        <input type="hidden" name="template_code" value="{{ $tmpl->code }}">
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-semibold text-xs transition-colors flex items-center gap-1">
                            <i data-lucide="download-cloud" class="w-3.5 h-3.5"></i>
                            <span>Terapkan</span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Tab 2: Profil Usaha, Logo & Pembulatan (§37) -->
    <div x-show="activeTab === 'general'" class="space-y-6" style="display: none;" x-data="{
        logoPreview: '{{ $business->logo_url }}',
        handleLogoChange(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (ev) => { this.logoPreview = ev.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }">
        <div class="glass-card p-6 rounded-2xl max-w-3xl space-y-6">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-5 h-5 text-emerald-400"></i>
                    <span>Profil Bisnis, Logo Resmi & Pembulatan</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">Informasi ini otomatis tampil sebagai kop surat dan instruksi transfer pada cetak Faktur Penjualan (Invoice) dan Purchase Order (PO)</p>
            </div>

            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-5 text-xs">
                @csrf
                @method('PUT')

                <!-- Logo Bisnis Section -->
                <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800 space-y-3">
                    <label class="block font-bold text-white uppercase tracking-wider text-[11px]">Logo Resmi Usaha / Perusahaan</label>
                    <div class="flex flex-col sm:flex-row items-center gap-5">
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-700 bg-slate-900 flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" alt="Logo Bisnis" class="w-full h-full object-contain p-1.5">
                            </template>
                            <template x-if="!logoPreview">
                                <div class="text-center p-2">
                                    <i data-lucide="image" class="w-8 h-8 text-slate-600 mx-auto mb-1"></i>
                                    <span class="text-[9px] text-slate-500 font-semibold block">Belum ada logo</span>
                                </div>
                            </template>
                        </div>

                        <div class="flex-1 space-y-2 text-left w-full">
                            <input type="file" name="logo" id="biz_logo_input" accept="image/*" @change="handleLogoChange($event)"
                                   class="block w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 file:cursor-pointer cursor-pointer">
                            <p class="text-[11px] text-slate-500">Format: PNG, JPG, WEBP, atau SVG. Maksimal 2MB. Resolusi transparan direkomendasikan.</p>

                            @if($business->logo_path)
                            <label class="inline-flex items-center gap-2 mt-1 cursor-pointer">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded bg-slate-900 border-slate-700 text-rose-500">
                                <span class="text-xs text-rose-400 font-medium">Hapus logo saat ini</span>
                            </label>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Dasar & Kontak -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Usaha / Perusahaan *</label>
                        <input type="text" name="name" value="{{ $business->name }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">NPWP / Identitas Pajak</label>
                        <input type="text" name="tax_identification_number" value="{{ $business->tax_identification_number }}" placeholder="01.234.567.8-901.000"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">No. Telepon / WhatsApp Resmi</label>
                        <input type="text" name="phone" value="{{ $business->phone }}" placeholder="0812-3456-7890"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Email Penagihan & Operasional</label>
                        <input type="email" name="email" value="{{ $business->email }}" placeholder="finance@perusahaan.com"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Alamat Kantor / Workshop (Kop Surat)</label>
                    <textarea name="address" rows="2" placeholder="Jl. Sudirman No. 45, Gedung Cyber Lt. 5, Jakarta Selatan"
                              class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">{{ $business->address }}</textarea>
                </div>

                <!-- Informasi Rekening Bank (Ditampilkan pada Faktur) -->
                <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-800 space-y-3">
                    <label class="block font-bold text-emerald-400 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>Instruksi Transfer Pembayaran Faktur (Rekening Resmi)</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nama Bank</label>
                            <input type="text" name="bank_name" value="{{ $business->bank_name }}" placeholder="BCA / Mandiri / BNI"
                                   class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Nomor Rekening</label>
                            <input type="text" name="bank_account_number" value="{{ $business->bank_account_number }}" placeholder="123-456-7890"
                                   class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Atas Nama (Pemilik Rekening)</label>
                            <input type="text" name="bank_account_holder" value="{{ $business->bank_account_holder }}" placeholder="PT Usaha Bersama"
                                   class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white">
                        </div>
                    </div>
                </div>

                <!-- Mata Uang & Pembulatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kode Mata Uang</label>
                        <input type="text" name="currency_code" value="{{ $business->currency_code }}" required
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Simbol Mata Uang</label>
                        <input type="text" name="currency_symbol" value="{{ $business->currency_symbol }}" required
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <label class="block font-bold text-emerald-400 mb-1">Strategi Pembulatan Output Akhir (§37 Blueprint)</label>
                    <p class="text-[11px] text-slate-400 mb-2">Hanya diterapkan pada HPP per unit final dan Harga Jual final. Kalkulasi internal tetap menjaga presisi desimal murni.</p>
                    
                    <select name="rounding_strategy" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-white font-mono text-xs">
                        <option value="ROUND" {{ $business->rounding_strategy === 'ROUND' ? 'selected' : '' }}>ROUND — Pembulatan Standar Terdekat</option>
                        <option value="CEIL" {{ $business->rounding_strategy === 'CEIL' ? 'selected' : '' }}>CEIL — Pembulatan Ke Atas (Plafon)</option>
                        <option value="FLOOR" {{ $business->rounding_strategy === 'FLOOR' ? 'selected' : '' }}>FLOOR — Pembulatan Ke Bawah</option>
                        <option value="ROUND_50" {{ $business->rounding_strategy === 'ROUND_50' ? 'selected' : '' }}>ROUND_50 — Kelipatan 50 Terdekat</option>
                        <option value="ROUND_100" {{ $business->rounding_strategy === 'ROUND_100' ? 'selected' : '' }}>ROUND_100 — Kelipatan 100 Terdekat (Default)</option>
                        <option value="ROUND_500" {{ $business->rounding_strategy === 'ROUND_500' ? 'selected' : '' }}>ROUND_500 — Kelipatan 500 Terdekat</option>
                        <option value="ROUND_1000" {{ $business->rounding_strategy === 'ROUND_1000' ? 'selected' : '' }}>ROUND_1000 — Kelipatan 1.000 Terdekat</option>
                    </select>
                </div>

                <div class="pt-3 flex justify-end border-t border-slate-800">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition-all">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Profil & Logo Bisnis</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 3: Team Members & Roles -->
    <div x-show="activeTab === 'members'" class="space-y-6" style="display: none;">
        
        <!-- Add Member Form or Plan Upgrade Card -->
        @if($canAddMember)
        <div class="glass-card p-6 rounded-3xl max-w-4xl border border-slate-800 space-y-4">
            <div class="border-b border-slate-800 pb-3">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4 text-emerald-400"></i>
                    <span>Tambah Karyawan / Buat Akun Pengguna Baru</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Tambahkan staf baru ke workspace bisnis Anda. Jika email belum pernah terdaftar di Cooca Core, sistem akan langsung membuatkan akun otomatis dengan nama dan kata sandi yang Anda tentukan di bawah.
                </p>
            </div>

            <form method="POST" action="{{ route('settings.members.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Lengkap Karyawan</label>
                        <input type="text" name="name" placeholder="Contoh: Siti Rahma"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Alamat Email Karyawan *</label>
                        <input type="email" name="email" required placeholder="kasir@tokosaya.com"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Password Akun (Jika Akun Baru)</label>
                        <input type="text" name="password" placeholder="Default: password123"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Peran Akses (Role) *</label>
                        <select name="role" required class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-semibold">
                            <option value="cashier">Kasir POS (HPP & Margin Dirahasiakan)</option>
                            <option value="inventory">Staf Gudang (Stok & Penerimaan PO)</option>
                            <option value="admin">Administrator / Manajer Toko</option>
                            <option value="staff">Staf Operasional</option>
                            <option value="owner">Co-Owner (Akses Penuh)</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-2">
                    <span class="text-[11px] text-slate-400">
                        <i data-lucide="info" class="w-3.5 h-3.5 inline text-emerald-400 mr-1"></i>
                        Karyawan dapat langsung masuk di halaman <strong>/login</strong> menggunakan email dan password yang Anda berikan.
                    </span>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 transition whitespace-nowrap">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Tambahkan Karyawan</span>
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="p-6 rounded-3xl bg-gradient-to-r from-purple-950/30 via-slate-900 to-indigo-950/30 border border-purple-500/30 max-w-4xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="p-2.5 rounded-xl bg-purple-500/20 border border-purple-500/30 text-purple-300 shrink-0">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>Buka Akses Multi-User & Tim Karyawan</span>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-purple-500/20 text-purple-300 border border-purple-500/30">
                            Fitur Core
                        </span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Paket saat ini adalah <strong>Free Plan (Mode Solo Owner 1 Pengguna)</strong>. Untuk mendelegasikan tugas ke kasir, staf gudang, dan admin tanpa batas, tingkatkan ke <strong>Cooca Core</strong>.
                    </p>
                </div>
            </div>
            <a href="{{ route('billing.checkout') }}" 
               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-500 hover:from-purple-400 hover:to-indigo-400 text-white font-black text-xs shadow-lg shadow-purple-500/25 flex items-center gap-1.5 transition whitespace-nowrap">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span>Upgrade Cooca Core</span>
            </a>
        </div>
        @endif

        <!-- Team Members Table -->
        <div class="glass-card rounded-3xl overflow-hidden max-w-4xl border border-slate-800 space-y-4">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-white">Daftar Anggota Tim & Peran Akses ({{ $members->count() }})</h3>
                    <p class="text-xs text-slate-400">Pengguna yang memiliki otorisasi mengakses workspace {{ $business->name }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                            <th class="py-3 px-4 font-semibold">Nama Pengguna</th>
                            <th class="py-3 px-4 font-semibold">Email</th>
                            <th class="py-3 px-4 font-semibold">Ubah Role Akses</th>
                            <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($members as $m)
                        <tr class="hover:bg-slate-900/40">
                            <td class="py-3.5 px-4 font-bold text-white">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-[11px]">
                                        {{ substr($m->user?->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span>{{ $m->user?->name ?? 'User' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300 font-mono">{{ $m->user?->email }}</td>
                            <td class="py-3.5 px-4">
                                @if($m->role !== 'owner' || $members->where('role', 'owner')->count() > 1)
                                <form method="POST" action="{{ route('settings.members.role', $m->id) }}" class="flex items-center gap-1.5">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" onchange="this.form.submit()"
                                            class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-xs font-semibold text-white focus:border-emerald-500">
                                        <option value="cashier" {{ $m->role === 'cashier' ? 'selected' : '' }}>Kasir (Cashier)</option>
                                        <option value="inventory" {{ $m->role === 'inventory' ? 'selected' : '' }}>Gudang (Inventory)</option>
                                        <option value="admin" {{ $m->role === 'admin' ? 'selected' : '' }}>Admin / Manajer</option>
                                        <option value="staff" {{ $m->role === 'staff' ? 'selected' : '' }}>Staff</option>
                                        <option value="owner" {{ $m->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                    </select>
                                </form>
                                @else
                                <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    Owner Utama
                                </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($m->role !== 'owner' || $members->where('role', 'owner')->count() > 1)
                                <form method="POST" action="{{ route('settings.members.destroy', $m->id) }}"
                                      onsubmit="return confirm('Hapus anggota tim {{ $m->user?->name }} dari workspace ini?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition" title="Hapus Anggota">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] text-slate-500">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Role & Permission Reference Matrix -->
        <div class="glass-card p-6 rounded-3xl max-w-4xl border border-slate-800 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
                <i data-lucide="shield-check" class="w-4 h-4 text-cyan-400"></i>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Matriks Hak Akses & Pembatasan Role (RBAC)</h4>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <!-- Role Kasir -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-emerald-500/20 space-y-2">
                    <div class="font-bold text-emerald-400 flex items-center gap-1.5">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                        <span>Kasir (Cashier)</span>
                    </div>
                    <ul class="space-y-1 text-[11px] text-slate-300 list-disc list-inside">
                        <li>Buka & tutup kasir (POS)</li>
                        <li>Input transaksi & cetak struk</li>
                        <li>Penerimaan QRIS & Tunai</li>
                        <li class="text-rose-400 list-none font-semibold">❌ HPP & Biaya dirahasiakan</li>
                    </ul>
                </div>

                <!-- Role Gudang -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-amber-500/20 space-y-2">
                    <div class="font-bold text-amber-400 flex items-center gap-1.5">
                        <i data-lucide="boxes" class="w-4 h-4"></i>
                        <span>Gudang (Inventory)</span>
                    </div>
                    <ul class="space-y-1 text-[11px] text-slate-300 list-disc list-inside">
                        <li>Multi-lokasi gudang & outlet</li>
                        <li>Penerimaan Purchase Order</li>
                        <li>Stok opname & penyesuaian</li>
                        <li>Mutasi antar outlet</li>
                    </ul>
                </div>

                <!-- Role Admin -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-blue-500/20 space-y-2">
                    <div class="font-bold text-blue-400 flex items-center gap-1.5">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                        <span>Admin / Manajer</span>
                    </div>
                    <ul class="space-y-1 text-[11px] text-slate-300 list-disc list-inside">
                        <li>Katalog produk & kategori</li>
                        <li>Kelola supplier & pelanggan</li>
                        <li>Faktur & invoice piutang</li>
                        <li>Operasional seluruh outlet</li>
                    </ul>
                </div>

                <!-- Role Owner -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-indigo-500/20 space-y-2">
                    <div class="font-bold text-indigo-400 flex items-center gap-1.5">
                        <i data-lucide="crown" class="w-4 h-4"></i>
                        <span>Owner / Co-Owner</span>
                    </div>
                    <ul class="space-y-1 text-[11px] text-slate-300 list-disc list-inside">
                        <li>Akses 100% semua fitur</li>
                        <li>Laporan laba rugi & margin</li>
                        <li>Kalkulator HPP 3-Pilar</li>
                        <li>Pengaturan tim & billing</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
