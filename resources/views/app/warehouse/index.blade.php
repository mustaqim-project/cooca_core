@extends('layouts.app', ['title' => 'Manajemen Gudang & Lokasi'])

@section('content')
<div class="space-y-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editData: {},
    openEdit(loc) {
        this.editData = { ...loc };
        this.showEditModal = true;
    }
}">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <i data-lucide="warehouse" class="w-5 h-5 text-cyan-400"></i>
                <span class="text-xs font-bold text-cyan-400 uppercase tracking-widest">Manajemen Gudang</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Gudang & Lokasi Bisnis</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola semua gudang, outlet, dan dapur pusat. Pantau stok & arus barang per lokasi.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('inventory.stocks') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="layers" class="w-4 h-4 text-cyan-400"></i>
                <span>Semua Stok</span>
            </a>
            <a href="{{ route('inventory.transfers.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="arrow-left-right" class="w-4 h-4 text-indigo-400"></i>
                <span>Transfer Stok</span>
            </a>
            <a href="{{ route('purchase-orders.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition flex items-center gap-1.5">
                <i data-lucide="truck" class="w-4 h-4 text-amber-400"></i>
                <span>Purchase Order</span>
            </a>
            @if(\App\Support\Context::hasPermission('inventory.manage'))
            <button @click="showCreateModal = true" class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-lg shadow-cyan-500/20 transition flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Gudang</span>
            </button>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== KPI SUMMARY CARDS ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-transparent pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center">
                    <i data-lucide="warehouse" class="w-4 h-4 text-cyan-400"></i>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Gudang</div>
            </div>
            <div class="text-3xl font-black text-white font-mono">{{ $totalWarehouses }}</div>
            <div class="text-[11px] text-slate-500 mt-1">{{ $activeWarehouses }} aktif</div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center">
                    <i data-lucide="coins" class="w-4 h-4 text-emerald-400"></i>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Nilai Aset</div>
            </div>
            <div class="text-xl font-black text-emerald-400 font-mono">Rp {{ number_format($totalValuation, 0, ',', '.') }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Semua gudang & outlet</div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-400"></i>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Stok Minimum</div>
            </div>
            <div class="text-3xl font-black {{ $totalLowStock > 0 ? 'text-amber-400' : 'text-slate-300' }} font-mono">{{ $totalLowStock }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Produk butuh restock</div>
        </div>

        <div class="glass-card rounded-2xl p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent pointer-events-none"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4 text-indigo-400"></i>
                </div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Mutasi Terkini</div>
            </div>
            <div class="text-3xl font-black text-white font-mono">{{ $recentMovements->count() }}</div>
            <div class="text-[11px] text-slate-500 mt-1">Pergerakan stok terbaru</div>
        </div>
    </div>

    {{-- ===== GUDANG CARDS GRID ===== --}}
    <div>
        <h2 class="text-sm font-bold text-slate-300 uppercase tracking-widest mb-3 flex items-center gap-2">
            <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400"></i>
            Daftar Gudang & Lokasi
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($locations as $loc)
            <div class="glass-card-interactive rounded-2xl border border-slate-800 overflow-hidden group">
                {{-- Card Header --}}
                <div class="p-5 pb-3">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0
                                {{ $loc->type === 'warehouse' ? 'bg-cyan-500/15 border border-cyan-500/30' : '' }}
                                {{ $loc->type === 'outlet' ? 'bg-emerald-500/15 border border-emerald-500/30' : '' }}
                                {{ $loc->type === 'central_kitchen' ? 'bg-orange-500/15 border border-orange-500/30' : '' }}
                            ">
                                @if($loc->type === 'warehouse')
                                    <i data-lucide="warehouse" class="w-5 h-5 text-cyan-400"></i>
                                @elseif($loc->type === 'central_kitchen')
                                    <i data-lucide="utensils" class="w-5 h-5 text-orange-400"></i>
                                @else
                                    <i data-lucide="store" class="w-5 h-5 text-emerald-400"></i>
                                @endif
                            </div>
                            <div>
                                <div class="font-bold text-white text-sm leading-tight">{{ $loc->name }}</div>
                                @if($loc->code)
                                    <div class="text-[10px] font-mono text-slate-500 mt-0.5">{{ $loc->code }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            {{-- Type Badge --}}
                            @if($loc->type === 'warehouse')
                                <span class="text-[9px] px-2 py-0.5 rounded-full font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 uppercase">Gudang</span>
                            @elseif($loc->type === 'central_kitchen')
                                <span class="text-[9px] px-2 py-0.5 rounded-full font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30 uppercase">Dapur</span>
                            @else
                                <span class="text-[9px] px-2 py-0.5 rounded-full font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase">Outlet</span>
                            @endif
                            {{-- Active Status --}}
                            @if($loc->is_primary)
                                <span class="text-[9px] px-2 py-0.5 rounded-full font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase">Utama</span>
                            @endif
                            @if(!$loc->is_active)
                                <span class="text-[9px] px-2 py-0.5 rounded-full font-bold bg-slate-700 text-slate-400 uppercase">Nonaktif</span>
                            @endif
                        </div>
                    </div>

                    @if($loc->address)
                        <p class="text-[11px] text-slate-500 line-clamp-1 mb-3">
                            <i data-lucide="map-pin" class="w-3 h-3 inline mr-1"></i>{{ $loc->address }}
                        </p>
                    @endif

                    {{-- Stats Row --}}
                    <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-800/60">
                        <div class="text-center">
                            <div class="text-sm font-black text-white font-mono">{{ $loc->total_products }}</div>
                            <div class="text-[10px] text-slate-500">Produk</div>
                        </div>
                        <div class="text-center border-x border-slate-800/60">
                            <div class="text-sm font-black text-emerald-400 font-mono">
                                @if($loc->total_valuation >= 1000000)
                                    {{ number_format($loc->total_valuation / 1000000, 1) }}JT
                                @else
                                    {{ number_format($loc->total_valuation / 1000, 0) }}K
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-500">Nilai Aset</div>
                        </div>
                        <div class="text-center">
                            <div class="text-sm font-black {{ $loc->low_stock_count > 0 ? 'text-amber-400' : 'text-slate-300' }} font-mono">{{ $loc->low_stock_count }}</div>
                            <div class="text-[10px] text-slate-500">Min. Stok</div>
                        </div>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div class="px-5 pb-4 pt-2 flex items-center justify-between gap-2 border-t border-slate-800/60">
                    <a href="{{ route('warehouse.show', $loc->id) }}"
                       class="flex-1 text-center py-2 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 text-xs font-bold transition border border-cyan-500/20 flex items-center justify-center gap-1.5">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        Detail Gudang
                    </a>
                    @if(\App\Support\Context::hasPermission('inventory.manage'))
                    <button
                        @click="openEdit({
                            id: '{{ $loc->id }}',
                            name: '{{ addslashes($loc->name) }}',
                            type: '{{ $loc->type }}',
                            code: '{{ $loc->code ?? '' }}',
                            phone: '{{ $loc->phone ?? '' }}',
                            address: '{{ addslashes($loc->address ?? '') }}',
                            is_active: {{ $loc->is_active ? 'true' : 'false' }}
                        })"
                        class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                    </button>
                    @if(!$loc->is_primary)
                    <form action="{{ route('warehouse.destroy', $loc->id) }}" method="POST"
                          onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus gudang {{ addslashes($loc->name) }}? Pastikan tidak ada stok aktif.', 'Hapus Gudang?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                    @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full glass-card rounded-2xl border border-slate-800 p-12 text-center">
                <i data-lucide="warehouse" class="w-12 h-12 text-slate-700 mx-auto mb-3"></i>
                <div class="text-slate-400 font-semibold">Belum ada gudang / lokasi</div>
                <div class="text-slate-600 text-sm mt-1 mb-4">Mulai dengan menambahkan gudang atau outlet pertama Anda.</div>
                @if(\App\Support\Context::hasPermission('inventory.manage'))
                <button @click="showCreateModal = true" class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition">
                    + Tambah Gudang Pertama
                </button>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- ===== PANDUAN ALUR KERJA ===== --}}
    <div class="glass-card rounded-2xl border border-slate-800 p-6">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="book-open" class="w-4 h-4 text-indigo-400"></i>
            <h3 class="font-bold text-slate-200 text-sm">Panduan: Cara Mengelola Barang Masuk ke Gudang</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                <div class="w-10 h-10 rounded-full bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mx-auto mb-2">
                    <span class="text-amber-400 font-black text-sm">1</span>
                </div>
                <div class="text-xs font-bold text-white mb-1">Buat Purchase Order</div>
                <div class="text-[11px] text-slate-500">Buat PO ke supplier dengan daftar item yang dipesan.</div>
                <a href="{{ route('purchase-orders.create', ['type' => 'supplier']) }}" class="mt-2 inline-block text-[11px] text-amber-400 hover:text-amber-300 font-semibold">→ Buat PO</a>
            </div>
            <div class="text-center p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                <div class="w-10 h-10 rounded-full bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center mx-auto mb-2">
                    <span class="text-cyan-400 font-black text-sm">2</span>
                </div>
                <div class="text-xs font-bold text-white mb-1">Konfirmasi PO</div>
                <div class="text-[11px] text-slate-500">Ubah status PO dari Draft ke Dikonfirmasi untuk dikirim ke supplier.</div>
            </div>
            <div class="text-center p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                <div class="w-10 h-10 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mx-auto mb-2">
                    <span class="text-emerald-400 font-black text-sm">3</span>
                </div>
                <div class="text-xs font-bold text-white mb-1">Terima Barang ke Gudang</div>
                <div class="text-[11px] text-slate-500">Klik "Terima Barang" di detail PO. Pilih gudang tujuan dan input qty aktual.</div>
            </div>
            <div class="text-center p-4 rounded-xl bg-slate-900/60 border border-slate-800">
                <div class="w-10 h-10 rounded-full bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center mx-auto mb-2">
                    <span class="text-indigo-400 font-black text-sm">4</span>
                </div>
                <div class="text-xs font-bold text-white mb-1">Stok Otomatis Bertambah</div>
                <div class="text-[11px] text-slate-500">Sistem mencatat mutasi, memperbarui saldo stok, dan mencatat kartu stok.</div>
            </div>
        </div>
    </div>

    {{-- ===== RIWAYAT MUTASI TERKINI ===== --}}
    @if($recentMovements->isNotEmpty())
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">
        <div class="p-4 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="activity" class="w-4 h-4 text-indigo-400"></i>
                <span class="text-sm font-bold text-white">Aktivitas Stok Terkini</span>
            </div>
            <a href="{{ route('inventory.movements') }}" class="text-xs text-slate-400 hover:text-indigo-400 transition font-semibold">Lihat Semua →</a>
        </div>
        <div class="table-responsive">
            <table class="w-full text-left text-xs text-slate-300 min-w-[580px]">
                <thead class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-extrabold tracking-wider border-b border-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4">Produk</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4 text-right">Perubahan</th>
                        <th class="py-3 px-4 text-right">Saldo Akhir</th>
                        <th class="py-3 px-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($recentMovements as $mv)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-3 px-4 font-semibold text-white">{{ $mv->product?->name ?? '-' }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $mv->location?->name ?? '-' }}</td>
                        <td class="py-3 px-4">
                            @php
                                $mvLabels = [
                                    'goods_receipt'  => ['label' => 'Penerimaan', 'color' => 'emerald'],
                                    'pos_sale'       => ['label' => 'Penjualan POS', 'color' => 'teal'],
                                    'adjustment'     => ['label' => 'Penyesuaian', 'color' => 'amber'],
                                    'transfer_in'    => ['label' => 'Transfer Masuk', 'color' => 'indigo'],
                                    'transfer_out'   => ['label' => 'Transfer Keluar', 'color' => 'slate'],
                                    'opname'         => ['label' => 'Opname', 'color' => 'purple'],
                                ];
                                $mvInfo = $mvLabels[$mv->movement_type] ?? ['label' => $mv->movement_type, 'color' => 'slate'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $mvInfo['color'] }}-500/20 text-{{ $mvInfo['color'] }}-400 border border-{{ $mvInfo['color'] }}-500/30">
                                {{ $mvInfo['label'] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold {{ $mv->quantity_change >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $mv->quantity_change >= 0 ? '+' : '' }}{{ number_format($mv->quantity_change, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-300">
                            {{ number_format($mv->balance_after, 2) }}
                        </td>
                        <td class="py-3 px-4 text-slate-500">{{ $mv->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif


    {{-- ===== MODAL: TAMBAH GUDANG ===== --}}
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display:none;">
        <div class="w-full max-w-lg glass-card rounded-2xl border border-slate-700 p-6 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-lg text-white">Tambah Gudang / Lokasi Baru</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Isi informasi gudang atau outlet baru.</p>
                </div>
                <button @click="showCreateModal = false" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('warehouse.store') }}" method="POST" class="space-y-4 text-sm">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Nama Gudang / Outlet <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" required placeholder="Mis: Gudang Pusat, Outlet Sudirman..."
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Tipe Lokasi <span class="text-rose-400">*</span></label>
                        <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition">
                            <option value="warehouse">🏢 Gudang (Warehouse)</option>
                            <option value="outlet">🏪 Outlet / Toko</option>
                            <option value="central_kitchen">🍳 Dapur Pusat (Central Kitchen)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Kode Lokasi</label>
                        <input type="text" name="code" placeholder="Mis: WH-01, OTL-JKT..."
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono focus:outline-none focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Nomor Telepon</label>
                        <input type="text" name="phone" placeholder="+62 21 ..."
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Alamat</label>
                        <textarea name="address" rows="2" placeholder="Alamat lengkap gudang / outlet..."
                                  class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition resize-none"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm transition">Batal</button>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition shadow-lg shadow-cyan-500/20">
                        Simpan Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL: EDIT GUDANG ===== --}}
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display:none;">
        <div class="w-full max-w-lg glass-card rounded-2xl border border-slate-700 p-6 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-lg text-white">Edit Gudang</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Mengubah: ' + editData.name"></p>
                </div>
                <button @click="showEditModal = false" class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('/warehouse') }}/' + editData.id" method="POST" class="space-y-4 text-sm">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Nama Gudang <span class="text-rose-400">*</span></label>
                        <input type="text" name="name" :value="editData.name" required
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Tipe Lokasi</label>
                        <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition">
                            <option value="warehouse" :selected="editData.type === 'warehouse'">🏢 Gudang (Warehouse)</option>
                            <option value="outlet" :selected="editData.type === 'outlet'">🏪 Outlet / Toko</option>
                            <option value="central_kitchen" :selected="editData.type === 'central_kitchen'">🍳 Dapur Pusat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Kode Lokasi</label>
                        <input type="text" name="code" :value="editData.code"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white font-mono focus:outline-none focus:border-cyan-500 transition">
                    </div>
                    <div>
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Telepon</label>
                        <input type="text" name="phone" :value="editData.phone"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-slate-400 font-bold uppercase text-xs mb-1.5">Alamat</label>
                        <textarea name="address" rows="2" :value="editData.address"
                                  class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-cyan-500 transition resize-none"></textarea>
                    </div>
                    <div class="col-span-2 flex items-center gap-3 p-3 rounded-xl bg-slate-900/50 border border-slate-800">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                               :checked="editData.is_active" class="w-4 h-4 accent-cyan-500">
                        <label for="edit_is_active" class="text-sm text-slate-300 font-medium cursor-pointer">Gudang aktif (bisa menerima & mengeluarkan stok)</label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showEditModal = false"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-sm transition">Batal</button>
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition shadow-lg shadow-cyan-500/20">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
