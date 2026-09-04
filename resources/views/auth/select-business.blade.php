@extends('layouts.guest', ['title' => 'Pilih Bisnis — Cooca UMKM'])

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-lg px-4" x-data="{ showCreateModal: false }">
    <!-- Brand Badge -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-xl shadow-emerald-500/25 mb-4">
            <i data-lucide="building-2" class="w-7 h-7 text-white"></i>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Pilih Workspace Bisnis</h2>
        <p class="mt-2 text-sm text-slate-400">Pilih entitas bisnis yang ingin Anda kelola atau buat bisnis baru</p>
    </div>

    <!-- Businesses Selection List -->
    <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl shadow-black/60 space-y-4">
        @if($businesses->isEmpty())
        <div class="text-center py-6 text-slate-400 text-sm">
            <p>Anda belum memiliki bisnis aktif.</p>
            <button type="button" @click="showCreateModal = true" class="mt-3 inline-block font-semibold text-emerald-400 hover:text-emerald-300">
                + Daftarkan Bisnis Baru
            </button>
        </div>
        @else
        <div class="space-y-3">
            @foreach($businesses as $biz)
            <form method="POST" action="{{ route('businesses.switch') }}">
                @csrf
                <input type="hidden" name="business_id" value="{{ $biz->id }}">
                <button type="submit"
                        class="w-full p-4 rounded-xl bg-slate-950/80 hover:bg-slate-800/90 border border-slate-800 hover:border-emerald-500/40 text-left flex items-center justify-between transition-all group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 group-hover:scale-105 transition-transform">
                            <i data-lucide="briefcase" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="font-bold text-white group-hover:text-emerald-400 transition-colors">{{ $biz->name }}</div>
                            <div class="text-xs text-slate-400">Mata Uang: {{ $biz->currency_code }} ({{ $biz->currency_symbol }})</div>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-5 h-5 text-slate-500 group-hover:text-emerald-400 group-hover:translate-x-0.5 transition-all"></i>
                </button>
            </form>
            @endforeach
        </div>
        @endif

        <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors">Keluar (Logout)</button>
            </form>
            <button type="button" @click="showCreateModal = true" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 transition-colors">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Bisnis Baru</span>
            </button>
        </div>
    </div>

    <!-- Modal Tambah Bisnis Baru -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4 bg-slate-900 border border-slate-800 text-left" @click.outside="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-5 h-5 text-emerald-400"></i>
                    <span>Tambah Bisnis Baru</span>
                </h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('businesses.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Bisnis / Entitas Usaha *</label>
                    <input type="text" name="name" required placeholder="Contoh: CV Bakery Enak / PT Kreasi Nusantara"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Template Jenis Industri</label>
                    <select name="template_code" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-emerald-500 focus:outline-none">
                        <option value="">-- Kosongkan (Setup Manual) --</option>
                        @foreach($templates as $tmpl)
                            <option value="{{ $tmpl->code }}">{{ $tmpl->name }} ({{ strtoupper($tmpl->industry_category) }})</option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Sistem akan meng-generate otomatis kategori, bahan baku, tenaga kerja, mesin, dan produk sesuai industri yang dipilih.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Mata Uang</label>
                    <select name="currency" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:border-emerald-500 focus:outline-none">
                        <option value="IDR">IDR (Rp - Rupiah Indonesia)</option>
                        <option value="USD">USD ($ - US Dollar)</option>
                        <option value="SGD">SGD (S$ - Singapore Dollar)</option>
                        <option value="MYR">MYR (RM - Malaysian Ringgit)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold flex items-center gap-1.5 shadow-lg shadow-emerald-500/20">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan & Buka Bisnis</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
