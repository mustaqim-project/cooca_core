@extends('layouts.app', [
    'title' => 'Jasa & Layanan',
    'headerTitle' => 'Jasa & Layanan',
    'headerSubtitle' => 'Kelola tarif ongkos jasa, biaya servis, atau perawatan tanpa repot mengatur stok gudang'
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    showAddModal: false,
    showEditModal: {{ $editService ? 'true' : 'false' }},
    showNewCategoryInput: false,
    deleteModalOpen: false,
    deleteTarget: { id: '', name: '' },
    sellingPriceRaw: '',
    sellingPriceFormatted: '',
    baseCostRaw: '',
    baseCostFormatted: '',
    editSellingPriceRaw: '{{ $editService ? (int)$editService->selling_price : '' }}',
    editSellingPriceFormatted: '{{ $editService ? number_format((float)$editService->selling_price, 0, ',', '.') : '' }}',

    init() {
        window.addEventListener('pageshow', () => {
            this.showAddModal = false;
            this.deleteModalOpen = false;
        });
    },

    formatNumber(val) {
        if (!val) return '';
        const num = String(val).replace(/\D/g, '');
        return num ? Number(num).toLocaleString('id-ID') : '';
    },

    onPriceInput(e, target) {
        const raw = e.target.value.replace(/\D/g, '');
        if (target === 'add_selling') {
            this.sellingPriceRaw = raw;
            this.sellingPriceFormatted = this.formatNumber(raw);
        } else if (target === 'add_cost') {
            this.baseCostRaw = raw;
            this.baseCostFormatted = this.formatNumber(raw);
        } else if (target === 'edit_selling') {
            this.editSellingPriceRaw = raw;
            this.editSellingPriceFormatted = this.formatNumber(raw);
        }
    },

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
    }
}">

    <!-- ===================================================== -->
    <!-- 1. SENIOR-FRIENDLY INFORMATIVE BANNER                -->
    <!-- ===================================================== -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/30 dark:to-indigo-950/20 border border-blue-200/80 dark:border-blue-900/50 rounded-2xl p-4 sm:p-5 shadow-sm">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.139 1.743" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">
                    Layanan Jasa Bebas Stok (Selalu Siap Dijual)
                </h3>
                <p class="text-sm sm:text-base text-slate-700 dark:text-slate-300 mt-1 leading-relaxed">
                    Menu ini khusus untuk usaha jasa seperti <strong>potong rambut, servis bengkel, klinik, cuci kendaraan, perbaikan, atau laundry</strong>. Anda tidak perlu memasukkan jumlah stok gudang karena jasa selalu siap ditransaksikan di Kasir POS dan Faktur Penjualan.
                </p>
            </div>
            <div class="hidden lg:block shrink-0">
                <button type="button" @click="showAddModal = true"
                        class="h-12 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-base shadow-md hover:shadow-lg transition-all flex items-center gap-2 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>+ Tambah Layanan Baru</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 2. QUICK STATS SUMMARY                                -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25-2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
            </div>
            <div>
                <div class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Layanan</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($totalServicesCount) }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status di Kasir</div>
                <div class="text-lg sm:text-xl font-bold text-emerald-600 dark:text-emerald-400">Selalu Siap Dijual</div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
            </div>
            <div>
                <div class="text-xs sm:text-sm font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori Jasa</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ $categories->count() }}</div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. ACTION BAR & FILTER                                -->
    <!-- ===================================================== -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('services.index') }}" class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
            <div class="flex-1 flex flex-col sm:flex-row gap-3">
                <!-- Large Search Input (Min 16px to prevent auto-zoom on mobile) -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Ketik nama layanan atau kata kunci..."
                           class="w-full h-12 pl-11 pr-4 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 text-base text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                </div>

                <!-- Category Filter -->
                <div class="w-full sm:w-64">
                    <select name="category_id" onchange="this.form.submit()"
                            class="w-full h-12 px-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-300 dark:border-slate-700 text-base text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2 shrink-0">
                @if(request('search') || request('category_id'))
                    <a href="{{ route('services.index') }}"
                       class="h-12 px-4 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-sm flex items-center justify-center transition">
                        Reset Filter
                    </a>
                @endif
                <button type="button" @click="showAddModal = true"
                        class="w-full sm:w-auto h-12 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-base shadow-md transition-all flex items-center justify-center gap-2 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>+ Tambah Layanan Baru</span>
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================== -->
    <!-- 4. SERVICES LIST TABLE                                -->
    <!-- ===================================================== -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-sm sm:text-base font-bold">
                        <th class="py-4 px-4 sm:px-6">Nama Jasa / Layanan</th>
                        <th class="py-4 px-4 hidden md:table-cell">Kategori</th>
                        <th class="py-4 px-4 text-right">Tarif (Biaya Jual)</th>
                        <th class="py-4 px-4 text-center hidden sm:table-cell">Status Stok</th>
                        <th class="py-4 px-4 sm:px-6 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-800 dark:text-slate-200 text-base">
                    @forelse($services as $item)
                        <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-800/40 transition">
                            <!-- Nama Layanan -->
                            <td class="py-4 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 font-bold text-sm">
                                        🛠️
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-base sm:text-lg text-slate-900 dark:text-white leading-tight">
                                            {{ $item->name }}
                                        </div>
                                        <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-2">
                                            @if($item->code)
                                                <span class="font-mono bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300">{{ $item->code }}</span>
                                            @endif
                                            <span>{{ $item->outputUnit?->name ?? 'Jasa' }}</span>
                                        </div>
                                        @if($item->description)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">
                                                {{ $item->description }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Kategori -->
                            <td class="py-4 px-4 hidden md:table-cell">
                                @if($item->category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                                        {{ $item->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Tanpa Kategori</span>
                                @endif
                            </td>

                            <!-- Tarif -->
                            <td class="py-4 px-4 text-right">
                                <div class="font-extrabold text-base sm:text-lg text-blue-600 dark:text-blue-400 tabular-nums">
                                    Rp {{ number_format((float)$item->selling_price, 0, ',', '.') }}
                                </div>
                                @if($item->base_cost > 0)
                                    <div class="text-xs text-slate-400 tabular-nums">
                                        Modal: Rp {{ number_format((float)$item->base_cost, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Status Stok (Penghilang Cemas) -->
                            <td class="py-4 px-4 text-center hidden sm:table-cell">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-300/50">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    <span>Bebas Stok (Siap)</span>
                                </span>
                            </td>

                            <!-- Aksi (Tombol Nyaman Diklik) -->
                            <td class="py-4 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <a href="{{ route('services.index', ['edit' => $item->id]) }}"
                                       class="h-10 px-3.5 rounded-xl border border-slate-300 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-sm flex items-center gap-1.5 transition active:scale-95"
                                       title="Ubah Layanan">
                                        <span>✏️ Ubah</span>
                                    </a>

                                    <!-- Delete Button -->
                                    <button type="button" @click="openDelete('{{ $item->id }}', '{{ addslashes($item->name) }}')"
                                            class="h-10 px-3.5 rounded-xl border border-rose-200 dark:border-rose-900/50 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 font-bold text-sm flex items-center gap-1.5 transition active:scale-95"
                                            title="Hapus Layanan">
                                        <span>🗑️ Hapus</span>
                                    </button>

                                    <!-- Hidden Delete Form -->
                                    <form id="form-delete-{{ $item->id }}" action="{{ route('services.destroy', $item) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="max-w-md mx-auto space-y-3">
                                    <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto text-2xl font-bold">
                                        🛠️
                                    </div>
                                    <h4 class="text-lg font-bold text-slate-900 dark:text-white">
                                        Belum Ada Layanan / Jasa yang Terdaftar
                                    </h4>
                                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 leading-relaxed">
                                        Daftarkan layanan pertama Anda seperti <em>Potong Rambut</em>, <em>Ganti Oli Motor</em>, <em>Servis AC</em>, atau <em>Jasa Foto</em>.
                                    </p>
                                    <div class="pt-2">
                                        <button type="button" @click="showAddModal = true"
                                                class="h-12 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-base shadow-md transition-all inline-flex items-center gap-2 active:scale-95">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                            <span>+ Daftarkan Layanan Sekarang</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50">
                {{ $services->links() }}
            </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODAL TAMBAH LAYANAN BARU (3 INPUT UTAMA)          -->
    <!-- ===================================================== -->
    <div x-show="showAddModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto"
         @keydown.escape.window="showAddModal = false">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 w-full max-w-lg shadow-2xl overflow-hidden my-8"
             @click.outside="showAddModal = false">

            <!-- Modal Header -->
            <div class="p-5 sm:p-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold flex items-center gap-2">
                        <span>🛠️ Tambah Layanan Baru</span>
                    </h3>
                    <p class="text-xs sm:text-sm text-blue-100 mt-1">
                        Cukup isi 3 informasi pokok di bawah ini
                    </p>
                </div>
                <button type="button" @click="showAddModal = false"
                        class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('services.store') }}" method="POST" class="p-5 sm:p-6 space-y-5">
                @csrf

                <!-- Input 1: Nama Layanan (Wajib, Font Besar 16px) -->
                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-slate-900 dark:text-white">
                        1. Nama Jasa / Layanan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required autofocus
                           placeholder="Contoh: Potong Rambut Pria, Servis AC, Ganti Oli"
                           class="w-full h-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 text-base text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white transition">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Tuliskan nama jasa yang biasa dikenal oleh pelanggan Anda.</p>
                </div>

                <!-- Input 2: Kategori Layanan (Wajib/Opsional, Mudah Dipilih) -->
                <div class="space-y-1.5" x-data="{ isTypingNew: false }">
                    <div class="flex items-center justify-between">
                        <label class="block text-base font-bold text-slate-900 dark:text-white">
                            2. Kategori Layanan
                        </label>
                        <button type="button" @click="isTypingNew = !isTypingNew"
                                class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                            <span x-text="isTypingNew ? '← Pilih dari Kategori Ada' : '+ Ketik Kategori Baru'"></span>
                        </button>
                    </div>

                    <!-- Pilihan Dropdown Kategori yang Sudah Ada -->
                    <template x-if="!isTypingNew">
                        <select name="category_id"
                                class="w-full h-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 text-base text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 transition cursor-pointer">
                            <option value="">Pilih Kategori (Boleh Kosong)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </template>

                    <!-- Input Ketik Kategori Baru Langsung -->
                    <template x-if="isTypingNew">
                        <input type="text" name="new_category_name"
                               placeholder="Ketik kategori baru, misal: Jasa Salon, Perawatan, Bengkel..."
                               class="w-full h-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-800 border-2 border-blue-400 text-base text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-blue-600 transition">
                    </template>
                </div>

                <!-- Input 3: Tarif yang Dibayar Pelanggan (Wajib, Live Rupiah Formatting) -->
                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-slate-900 dark:text-white">
                        3. Tarif / Biaya yang Dibayar Pelanggan (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base font-bold text-slate-500">
                            Rp
                        </div>
                        <input type="text" required
                               @input="onPriceInput($event, 'add_selling')"
                               :value="sellingPriceFormatted"
                               placeholder="50.000"
                               class="w-full h-12 pl-12 pr-4 rounded-xl bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 text-lg font-extrabold text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white transition tabular-nums">
                        <input type="hidden" name="selling_price" :value="sellingPriceRaw">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Harga atau ongkos yang akan ditagihkan ke pelanggan di kasir.</p>
                </div>

                <!-- Bagian Pengaturan Tambahan (Terlipat / Tidak Mengintimidasi) -->
                <div x-data="{ expanded: false }" class="border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 bg-slate-50/50 dark:bg-slate-800/30">
                    <button type="button" @click="expanded = !expanded"
                            class="w-full flex items-center justify-between text-sm font-bold text-slate-700 dark:text-slate-300 hover:text-blue-600 transition">
                        <span class="flex items-center gap-1.5">
                            <span>⚙️ Pengaturan Tambahan (Boleh Dikosongkan)</span>
                        </span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>

                    <div x-show="expanded" x-collapse class="mt-4 space-y-4 pt-3 border-t border-slate-200 dark:border-slate-700">
                        <!-- Kode Singkatan -->
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Kode / Singkatan Layanan (Opsional)
                            </label>
                            <input type="text" name="code"
                                   placeholder="Contoh: JSA-01, OLI-01"
                                   class="w-full h-10 px-3 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm text-slate-900 dark:text-white uppercase">
                        </div>

                        <!-- Estimasi Modal / Biaya Pokok -->
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Estimasi Biaya Pokok / Komisi Tukang (Rp, Opsional)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">Rp</div>
                                <input type="text"
                                       @input="onPriceInput($event, 'add_cost')"
                                       :value="baseCostFormatted"
                                       placeholder="0"
                                       class="w-full h-10 pl-9 pr-3 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm font-bold text-slate-900 dark:text-white tabular-nums">
                                <input type="hidden" name="base_cost" :value="baseCostRaw">
                            </div>
                        </div>

                        <!-- Catatan Layanan -->
                        <div class="space-y-1">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Catatan / Deskripsi (Opsional)
                            </label>
                            <textarea name="description" rows="2"
                                      placeholder="Keterangan singkat mengenai layanan ini..."
                                      class="w-full p-2.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm text-slate-900 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Penjelasan Penenang -->
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-xs sm:text-sm text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Layanan ini akan otomatis siap dijual di Kasir POS tanpa perlu mencatat stok.</span>
                </div>

                <!-- Tombol Aksi (Tinggi 50px, Nyaman Ditekan) -->
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="showAddModal = false"
                            class="w-1/3 h-12 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-base hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 h-12 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-base shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Simpan Layanan Ini</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. MODAL UBAH LAYANAN (EDIT)                          -->
    <!-- ===================================================== -->
    @if($editService)
    <div x-show="showEditModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4 overflow-y-auto"
         @keydown.escape.window="showEditModal = false">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 w-full max-w-lg shadow-2xl overflow-hidden my-8"
             @click.outside="showEditModal = false">

            <!-- Modal Header -->
            <div class="p-5 sm:p-6 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-xl sm:text-2xl font-extrabold flex items-center gap-2">
                        <span>✏️ Ubah Layanan</span>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">
                        Perbarui nama atau tarif untuk layanan {{ $editService->name }}
                    </p>
                </div>
                <a href="{{ route('services.index') }}"
                   class="w-10 h-10 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('services.update', $editService) }}" method="POST" class="p-5 sm:p-6 space-y-5">
                @csrf
                @method('PUT')

                <!-- Nama Layanan -->
                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-slate-900 dark:text-white">
                        Nama Jasa / Layanan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $editService->name) }}" required
                           class="w-full h-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 text-base text-slate-900 dark:text-white font-bold focus:outline-none focus:border-blue-500">
                </div>

                <!-- Kategori -->
                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-slate-900 dark:text-white">
                        Kategori Layanan
                    </label>
                    <select name="category_id"
                            class="w-full h-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 text-base text-slate-900 dark:text-white focus:outline-none focus:border-blue-500">
                        <option value="">Tanpa Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $editService->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tarif Jual (Rp) -->
                <div class="space-y-1.5">
                    <label class="block text-base font-bold text-slate-900 dark:text-white">
                        Tarif / Biaya Jual (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-base font-bold text-slate-500">Rp</div>
                        <input type="text" required
                               @input="onPriceInput($event, 'edit_selling')"
                               :value="editSellingPriceFormatted"
                               class="w-full h-12 pl-12 pr-4 rounded-xl bg-slate-50 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 text-lg font-extrabold text-slate-900 dark:text-white focus:outline-none focus:border-blue-500 tabular-nums">
                        <input type="hidden" name="selling_price" :value="editSellingPriceRaw">
                    </div>
                </div>

                <!-- Deskripsi & Kode -->
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Kode Layanan (Opsional)</label>
                        <input type="text" name="code" value="{{ old('code', $editService->code) }}"
                               class="w-full h-10 px-3 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm text-slate-900 dark:text-white uppercase">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Deskripsi (Opsional)</label>
                        <textarea name="description" rows="2"
                                  class="w-full p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm text-slate-900 dark:text-white">{{ old('description', $editService->description) }}</textarea>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center gap-3 pt-2">
                    <a href="{{ route('services.index') }}"
                       class="w-1/3 h-12 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-base flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex-1 h-12 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-base shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Perbarui Layanan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================================================== -->
    <!-- 7. MODAL HAPUS DENGAN PESAN PENENANG (ANTI-KHAWATIR)  -->
    <!-- ===================================================== -->
    <div x-show="deleteModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
         @keydown.escape.window="closeDelete()">
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 w-full max-w-md p-6 shadow-2xl space-y-4"
             @click.outside="closeDelete()">
            
            <div class="w-14 h-14 rounded-2xl bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto text-2xl font-bold">
                ⚠️
            </div>

            <div class="text-center space-y-2">
                <h3 class="text-xl font-extrabold text-slate-900 dark:text-white">
                    Hapus Layanan Ini?
                </h3>
                <p class="text-base text-slate-700 dark:text-slate-300 leading-relaxed">
                    Anda akan menghapus layanan <strong class="text-slate-900 dark:text-white" x-text="'“' + deleteTarget.name + '”'"></strong>.
                </p>
                <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    💡 <strong>Tenang</strong>: Riwayat transaksi kasir atau faktur penjualan masa lalu yang sudah selesai <strong>tidak akan hilang</strong>.
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="button" @click="closeDelete()"
                        class="flex-1 h-12 rounded-xl border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold text-base hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal (Kembali)
                </button>
                <button type="button" @click="submitDelete()"
                        class="flex-1 h-12 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-base shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
