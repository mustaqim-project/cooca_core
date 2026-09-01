@extends('layouts.app', [
    'title' => 'Tenaga Kerja & Mesin',
    'headerTitle' => 'Biaya Tenaga Kerja & Depresiasi Mesin',
    'headerSubtitle' => 'Konversi tarif upah ke tarif per jam efektif dan kalkulasi biaya mesin per jam operasi'
])

@section('content')
<div class="space-y-8" x-data="{ showLaborModal: false, showMachineModal: false }">

    <!-- Labor Rates Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Tarif Tenaga Kerja Langsung (Direct Labor)</h2>
                    <p class="text-xs text-slate-400">Dikonversi otomatis ke jam produktif (Productive Hours Utilization %)</p>
                </div>
            </div>

            <button @click="showLaborModal = true" 
                    class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Tarif Upah</span>
            </button>
        </div>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                            <th class="py-3.5 px-4 font-semibold">Nama Posisi / Peran</th>
                            <th class="py-3.5 px-4 font-semibold">Basis Pembayaran</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Nominal Tarif Dasar</th>
                            <th class="py-3.5 px-4 font-semibold text-center">Utilisasi Kerja</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Setara per Jam</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($laborRates as $lr)
                        @php
                            $hourly = $lr->hourly_rate;
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="py-3.5 px-4 font-medium text-white">
                                <div class="font-bold">{{ $lr->name }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $lr->is_subcontractor ? 'Subkontraktor / Outsourcing' : 'Karyawan Internal' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono text-[10px] uppercase font-semibold">
                                    {{ $lr->basis }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                                {{ $business->currency_symbol }} {{ number_format((float)$lr->rate_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-300">
                                {{ $lr->utilization_percentage }}% ({{ $lr->working_hours_per_day }} jam/hari)
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-emerald-400">
                                {{ $business->currency_symbol }} {{ number_format((float)$hourly, 0, ',', '.') }}/jam
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form method="POST" action="{{ route('labor-rates.destroy', $lr->id) }}" onsubmit="return confirm('Hapus tarif tenaga kerja ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-red-400 rounded transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500">
                                Belum ada tarif tenaga kerja terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Machines Section -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white">Mesin & Peralatan Produksi</h2>
                    <p class="text-xs text-slate-400">Kalkulasi depresiasi garis lurus, konsumsi listrik kWh, dan biaya perawatan per jam</p>
                </div>
            </div>

            <button @click="showMachineModal = true" 
                    class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold shadow-lg shadow-blue-500/20 flex items-center gap-1.5 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Mesin Baru</span>
            </button>
        </div>

        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                            <th class="py-3.5 px-4 font-semibold">Nama Mesin / Peralatan</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Harga Perolehan</th>
                            <th class="py-3.5 px-4 font-semibold text-center">Umur Manfaat (Jam)</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Depresiasi / Jam</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Total Biaya Mesin / Jam</th>
                            <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($machines as $m)
                        @php
                            $deprec = ($m->purchase_price - $m->salvage_value) / max(1, $m->useful_life_hours);
                            $power = $m->power_kw * $m->electricity_cost_per_kwh;
                            $totalHourly = $deprec + $power + $m->maintenance_cost_per_hour;
                        @endphp
                        <tr class="hover:bg-slate-900/40 transition-colors">
                            <td class="py-3.5 px-4 font-medium text-white">
                                <div class="font-bold">{{ $m->name }}</div>
                                <div class="text-[10px] text-slate-400">Daya: {{ $m->power_kw }} kW</div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-white">
                                {{ $business->currency_symbol }} {{ number_format((float)$m->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-slate-300">
                                {{ number_format((float)$m->useful_life_hours, 0, ',', '.') }} jam
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-300">
                                {{ $business->currency_symbol }} {{ number_format((float)$deprec, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-extrabold text-blue-400">
                                {{ $business->currency_symbol }} {{ number_format((float)$totalHourly, 0, ',', '.') }}/jam
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form method="POST" action="{{ route('machines.destroy', $m->id) }}" onsubmit="return confirm('Hapus mesin/peralatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-red-400 rounded transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">
                                Belum ada mesin atau peralatan produksi terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Tarif Upah -->
    <div x-show="showLaborModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4" @click.outside="showLaborModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah Tarif Tenaga Kerja</h3>
                <button @click="showLaborModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" action="{{ route('labor-rates.store') }}" class="space-y-3.5 text-xs">
                @csrf
                
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Peran / Posisi *</label>
                    <input type="text" name="name" required placeholder="Contoh: Koki Masak / Operator Jahit"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Basis Tarif *</label>
                        <select name="basis" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                            <option value="hourly">Per Jam (Hourly)</option>
                            <option value="daily">Per Hari (Daily)</option>
                            <option value="monthly">Bulanan (Monthly)</option>
                            <option value="per_unit">Per Potong/Unit</option>
                            <option value="per_task">Per Tugas / Task</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nominal Upah (Rp) *</label>
                        <input type="number" name="rate_amount" required min="1" step="1000" placeholder="3500000"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800">
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Hari/Bln</label>
                        <input type="number" name="working_days_per_month" value="22" min="1" max="31"
                               class="w-full px-2 py-1 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Jam/Hari</label>
                        <input type="number" name="working_hours_per_day" value="8" min="1" max="24"
                               class="w-full px-2 py-1 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Utilisasi %</label>
                        <input type="number" name="utilization_percentage" value="80" min="10" max="100"
                               class="w-full px-2 py-1 bg-slate-900 border border-slate-800 rounded-lg text-white font-mono">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showLaborModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold">Simpan Tarif</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Mesin Baru -->
    <div x-show="showMachineModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card max-w-md w-full p-6 rounded-2xl space-y-4" @click.outside="showMachineModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="text-base font-bold text-white">Tambah Mesin / Peralatan</h3>
                <button @click="showMachineModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form method="POST" action="{{ route('machines.store') }}" class="space-y-3.5 text-xs">
                @csrf
                
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Mesin / Alat *</label>
                    <input type="text" name="name" required placeholder="Contoh: Mesin Espresso 2 Group / Oven Deck"
                           class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Harga Beli (Rp) *</label>
                        <input type="number" name="purchase_price" required min="1" step="10000" placeholder="45000000"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Umur Manfaat (Jam) *</label>
                        <input type="number" name="useful_life_hours" required min="100" placeholder="10000"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Daya Listrik (kW)</label>
                        <input type="number" name="power_kw" value="1.5" step="0.1" min="0"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tarif Listrik / kWh</label>
                        <input type="number" name="electricity_cost_per_kwh" value="1500" min="0"
                               class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="showMachineModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold">Simpan Mesin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
