@extends('layouts.app', ['title' => 'Voucher & Promo'])

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Voucher & Kode Promo</h1>
            <p class="text-sm text-slate-400 mt-1">Buat kode kupon diskon persentase atau nominal untuk pelanggan setia saat checkout kasir.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.members.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                ← Kembali ke Member CRM
            </a>
            <button @click="showCreateModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Voucher Baru</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Vouchers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($vouchers as $v)
        <div class="glass-card rounded-2xl p-5 border border-slate-800 flex flex-col justify-between space-y-4 relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase">{{ $v->name }}</div>
                    <div class="text-xl font-black text-white font-mono tracking-wider mt-1 text-emerald-400">
                        {{ $v->code }}
                    </div>
                </div>
                <form action="{{ route('crm.vouchers.toggle', $v->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition
                        {{ $v->is_active ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30 hover:bg-rose-500/20 hover:text-rose-300' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-emerald-500/20' }}">
                        {{ $v->is_active ? 'Aktif' : 'Non-Aktif' }}
                    </button>
                </form>
            </div>

            <div class="space-y-1.5 text-xs text-slate-300 border-t border-b border-slate-800/80 py-3">
                <div class="flex justify-between">
                    <span class="text-slate-400">Potongan Diskon:</span>
                    <span class="font-bold font-mono text-emerald-400">
                        {{ $v->discount_type === 'percentage' ? $v->discount_value . '%' : 'Rp ' . number_format($v->discount_value, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Min. Belanja:</span>
                    <span class="font-mono">Rp {{ number_format($v->min_order_amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Maks. Diskon:</span>
                    <span class="font-mono">{{ $v->max_discount_amount ? 'Rp ' . number_format($v->max_discount_amount, 0, ',', '.') : 'Tanpa Batas' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Tier Member:</span>
                    <span class="uppercase font-bold text-amber-300">{{ $v->tier_eligibility ?? 'Semua' }}</span>
                </div>
            </div>

            <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span>Terpakai: <strong class="text-white">{{ $v->used_count }}</strong>{{ $v->usage_limit ? ' / ' . $v->usage_limit : '' }} kali</span>
                <span>{{ $v->valid_until ? 's/d ' . $v->valid_until->format('d/m/Y') : 'Tanpa Kadaluarsa' }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center py-12 glass-card rounded-2xl border border-slate-800 text-slate-500">
            Belum ada voucher promo yang dibuat.
        </div>
        @endforelse
    </div>

    <!-- Modal Buat Voucher -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Buat Voucher Promo Baru</h3>
            <form action="{{ route('crm.vouchers.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Kode Voucher</label>
                        <input type="text" name="code" required placeholder="MISAL: PROMO10" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 uppercase font-mono text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Nama Promo</label>
                        <input type="text" name="name" required placeholder="Diskon Awal Tahun" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Tipe Diskon</label>
                        <select name="discount_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Nilai Diskon</label>
                        <input type="number" step="any" name="discount_value" required placeholder="Misal: 10 atau 20000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Min. Belanja (Rp)</label>
                        <input type="number" name="min_order_amount" value="0" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Maks. Diskon (Rp)</label>
                        <input type="number" name="max_discount_amount" placeholder="Kosongkan jika bebas" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Berlaku Sampai</label>
                        <input type="date" name="valid_until" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase mb-1">Batas Kuota Pakai</label>
                        <input type="number" name="usage_limit" placeholder="Bebas" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">Buat Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
