@extends('layouts.app', ['title' => 'Sesi Shift Kasir'])

@section('content')
<div class="space-y-6" x-data="{
    showOpenModal: false,
    showCloseModal: false,
    showMovementModal: false,
    selectedShiftId: null,
    actualCash: 0,
    expectedCash: 0,
    openCloseModal(shiftId, expected) {
        this.selectedShiftId = shiftId;
        this.expectedCash = expected;
        this.actualCash = expected;
        this.showCloseModal = true;
    },
    openMovement(shiftId) {
        this.selectedShiftId = shiftId;
        this.showMovementModal = true;
    }
}">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Sesi Shift Kasir</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola pembukaan shift kasir, mutasi kas, dan rekonsiliasi laci uang fisik (*cash drawer*).</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('pos.terminal') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs transition flex items-center gap-2">
                <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-400"></i>
                <span>Terminal POS</span>
            </a>
            <button @click="showOpenModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buka Shift Baru</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Active Shift Banner if open -->
    @if($activeShift)
    <div class="p-5 rounded-2xl glass-card border-emerald-500/30 bg-gradient-to-r from-emerald-950/40 to-slate-900 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center">
                <i data-lucide="unlock" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 uppercase tracking-wider">Shift Anda Aktif</span>
                    <span class="text-xs text-slate-400">Dibuka sejak {{ $activeShift->opened_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="text-lg font-black text-white mt-1">
                    Kasir: {{ $activeShift->user->name }} • Modal Awal: Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openMovement('{{ $activeShift->id }}')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition">
                Kas Masuk/Keluar
            </button>
            <button @click="openCloseModal('{{ $activeShift->id }}', {{ $activeShift->opening_cash + $activeShift->total_cash_sales + $activeShift->total_cash_in - $activeShift->total_cash_out }})" class="px-4 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold transition">
                Tutup Shift Ini
            </button>
        </div>
    </div>
    @endif

    <!-- Shifts Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="font-bold text-sm text-white">Riwayat Shift Kasir</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Kasir / Outlet</th>
                        <th class="py-3.5 px-4">Waktu Buka / Tutup</th>
                        <th class="py-3.5 px-4 text-right">Modal Awal</th>
                        <th class="py-3.5 px-4 text-right">Penjualan Tunai</th>
                        <th class="py-3.5 px-4 text-right">Uang Fisik / Diharapkan</th>
                        <th class="py-3.5 px-4 text-right">Selisih Kas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($shifts as $shift)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white">{{ $shift->user->name ?? 'Kasir' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $shift->location->name ?? 'Outlet Utama' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div>{{ $shift->opened_at->format('d/m/Y H:i') }}</div>
                            <div class="text-[11px] text-slate-500">{{ $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : 'Masih Terbuka' }}</div>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-medium text-slate-200">
                            Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-medium text-emerald-400">
                            Rp {{ number_format($shift->total_cash_sales, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono">
                            @if($shift->status === 'closed')
                                <div class="font-bold text-white">Rp {{ number_format($shift->closing_cash_actual ?? 0, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-500">Exp: Rp {{ number_format($shift->closing_cash_expected ?? 0, 0, ',', '.') }}</div>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono font-bold">
                            @if($shift->status === 'closed')
                                @php $diff = $shift->cash_difference ?? 0; @endphp
                                <span class="{{ $diff == 0 ? 'text-emerald-400' : ($diff > 0 ? 'text-teal-400' : 'text-rose-400') }}">
                                    {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($shift->status === 'open')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Terbuka</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Ditutup</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">Belum ada sesi shift kasir tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
        <div class="p-4 border-t border-slate-800">
            {{ $shifts->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Buka Shift -->
    <div x-show="showOpenModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Buka Shift Kasir Baru</h3>
            <form action="{{ route('pos.shifts.open') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Pilih Outlet</label>
                    <select name="location_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Modal Awal Kasir (Rp)</label>
                    <input type="number" name="opening_cash" value="100000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold font-mono text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Catatan</label>
                    <input type="text" name="notes" placeholder="Catatan shift..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showOpenModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs">Buka Shift</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tutup Shift & Rekonsiliasi -->
    <div x-show="showCloseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Tutup Shift & Rekonsiliasi Kas</h3>
            <form :action="'{{ url('/pos/shifts') }}/' + selectedShiftId + '/close'" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex justify-between items-center">
                    <span class="text-slate-400">Total Diharapkan di Laci:</span>
                    <span class="font-bold text-base font-mono text-emerald-400" x-text="'Rp ' + Number(expectedCash).toLocaleString('id-ID')"></span>
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Hitungan Uang Fisik Aktual (Rp)</label>
                    <input type="number" name="closing_cash_actual" x-model.number="actualCash" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-lg font-bold font-mono text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between items-center text-xs">
                    <span class="text-slate-400">Selisih Kas:</span>
                    <span :class="(actualCash - expectedCash) === 0 ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold'" 
                          x-text="'Rp ' + Number(actualCash - expectedCash).toLocaleString('id-ID')"></span>
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showCloseModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs">Tutup & Rekonsiliasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Kas Masuk / Keluar -->
    <div x-show="showMovementModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-md glass-card rounded-2xl border border-slate-700 p-6 space-y-4">
            <h3 class="font-extrabold text-lg text-white">Catat Kas Masuk / Kas Keluar</h3>
            <form :action="'{{ url('/pos/shifts') }}/' + selectedShiftId + '/cash-movement'" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Tipe Mutasi</label>
                    <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                        <option value="cash_in">Kas Masuk (Tambah Modal/Uang Pecahan)</option>
                        <option value="cash_out">Kas Keluar (Operasional/Beli Barang/Setor)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Nominal (Rp)</label>
                    <input type="number" name="amount" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-base font-bold font-mono text-white">
                </div>
                <div>
                    <label class="block text-slate-400 font-bold uppercase mb-1">Alasan</label>
                    <input type="text" name="reason" placeholder="Alasan kas masuk/keluar..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white">
                </div>
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                    <button type="button" @click="showMovementModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-bold text-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
