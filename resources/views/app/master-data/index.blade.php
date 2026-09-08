@extends('layouts.app', [
    'title' => $title,
    'headerTitle' => $title,
    'headerSubtitle' => $subtitle,
])

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    activeTab: 'units',
    editUnit: { id: '', code: '', name: '', category: 'quantity' },
    openEditUnit(unit) {
        this.editUnit = { ...unit };
        this.showEditModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5 text-emerald-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">{{ $title }}</h2>
                    <p class="text-xs text-slate-400">{{ $subtitle }}</p>
                </div>
            </div>
        </div>
        @if(in_array($type, ['material-categories', 'product-categories', 'units'], true) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
            <button type="button" @click="showAddModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Satuan</span>
            </button>
        @endif
    </div>

    @if($type === 'units')
        <div class="flex items-center gap-2 border-b border-slate-800 pb-3">
            <button type="button" @click="activeTab = 'units'" :class="activeTab === 'units' ? 'bg-emerald-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i data-lucide="scale" class="w-4 h-4"></i><span>Daftar Satuan</span>
            </button>
            <button type="button" @click="activeTab = 'conversions'" :class="activeTab === 'conversions' ? 'bg-emerald-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i data-lucide="shuffle" class="w-4 h-4"></i><span>Konversi</span>
            </button>
        </div>
    @endif

    <div x-show="activeTab === 'units'" class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">{{ $type === 'units' ? 'Kode' : 'Nama' }}</th>
                        @if($type === 'units')
                            <th class="py-3.5 px-4 font-semibold">Nama Satuan</th>
                            <th class="py-3.5 px-4 font-semibold">Kategori</th>
                            <th class="py-3.5 px-4 font-semibold">Sumber</th>
                        @else
                            <th class="py-3.5 px-4 font-semibold">Deskripsi</th>
                        @endif
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-800/30">
                            @if($type === 'units')
                                <td class="py-3 px-4 font-mono font-bold text-emerald-400">{{ $item->code }}</td>
                                <td class="py-3 px-4 font-semibold text-white">{{ $item->name }}</td>
                                <td class="py-3 px-4 text-slate-300 capitalize">{{ $item->category }}</td>
                                <td class="py-3 px-4 {{ $item->business_id ? 'text-emerald-400' : 'text-sky-400' }}">{{ $item->business_id ? 'Bisnis ini' : 'Sistem' }}</td>
                            @else
                                <td class="py-3 px-4 font-semibold text-white">{{ $item->name }}</td>
                                <td class="py-3 px-4 text-slate-300">{{ $item->description ?: '-' }}</td>
                            @endif
                            <td class="py-3 px-4 text-right">
                                @if($item->business_id && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
                                    <div class="flex items-center justify-end gap-1">
                                        @if($type === 'units')
                                            <button type="button" title="Edit satuan" @click="openEditUnit(@js(['id' => $item->id, 'code' => $item->code, 'name' => $item->name, 'category' => $item->category]))" class="p-1.5 text-slate-400 hover:text-emerald-400"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                                        @endif
                                        <form method="POST" action="{{ route($type . '.destroy', $item->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus data ini?', 'Hapus Data?', 'danger')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-rose-400"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-500">{{ $emptyLabel }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($type === 'units')
        <div x-show="activeTab === 'conversions'" class="space-y-5" style="display: none">
            @if(\App\Support\Context::hasPermission('master_data.units.manage'))
                <div class="glass-card rounded-2xl p-5">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-white">Tambah Konversi Satuan</h3>
                        <p class="text-xs text-slate-400 mt-1">Tentukan berapa nilai satu satuan asal jika dinyatakan dalam satuan tujuan.</p>
                    </div>
                    <form method="POST" action="{{ route('unit-conversions.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        @csrf
                        <select name="from_unit_id" required class="px-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                            <option value="">Satuan asal</option>
                            @foreach($items as $unit)<option value="{{ $unit->id }}">{{ $unit->code }} — {{ $unit->name }}</option>@endforeach
                        </select>
                        <select name="to_unit_id" required class="px-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                            <option value="">Satuan tujuan</option>
                            @foreach($items as $unit)<option value="{{ $unit->id }}">{{ $unit->code }} — {{ $unit->name }}</option>@endforeach
                        </select>
                        <input type="number" name="factor" required min="0.000001" step="0.000001" placeholder="Faktor, contoh: 1000" class="px-3 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center justify-center gap-2"><i data-lucide="save" class="w-4 h-4"></i><span>Simpan Konversi</span></button>
                    </form>
                </div>
            @endif

            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead><tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50"><th class="py-3.5 px-4">Dari</th><th class="py-3.5 px-4">Ke</th><th class="py-3.5 px-4">Faktor</th><th class="py-3.5 px-4 text-right">Aksi</th></tr></thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($unitConversions as $conversion)
                                <tr class="hover:bg-slate-800/30"><td class="py-3 px-4 font-semibold text-white">{{ $conversion->fromUnit?->code }} — {{ $conversion->fromUnit?->name }}</td><td class="py-3 px-4 font-semibold text-white">{{ $conversion->toUnit?->code }} — {{ $conversion->toUnit?->name }}</td><td class="py-3 px-4 font-mono text-emerald-400">{{ number_format((float) $conversion->factor, 6, '.', '') }}</td><td class="py-3 px-4 text-right"><form method="POST" action="{{ route('unit-conversions.destroy', $conversion->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus konversi ini?', 'Hapus Konversi?', 'danger')">@csrf @method('DELETE')<button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-rose-400"><i data-lucide="trash-2" class="w-4 h-4"></i></button></form></td></tr>
                            @empty
                                <tr><td colspan="4" class="py-12 text-center text-slate-500">Belum ada konversi satuan bisnis.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if(in_array($type, ['material-categories', 'product-categories', 'units'], true) && \App\Support\Context::hasPermission("master_data.{$type}.manage"))
        <div x-show="showAddModal" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div @click.outside="showAddModal = false" class="glass-card w-full max-w-lg rounded-2xl p-6 border border-slate-700">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-bold text-white">Tambah {{ $title }}</h3>
                    <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <form method="POST" action="{{ route($type . '.store') }}" class="space-y-4">
                    @csrf
                    @if($type === 'units')
                        <input name="code" required maxlength="30" placeholder="Kode satuan, contoh: sak" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        <input name="name" required maxlength="100" placeholder="Nama lengkap satuan" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        <select name="category" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                            <option value="quantity">Kuantitas</option><option value="weight">Berat</option><option value="volume">Volume</option><option value="length">Panjang</option><option value="time">Waktu</option><option value="area">Luas</option><option value="custom">Kustom</option>
                        </select>
                    @else
                        <input name="name" required maxlength="150" placeholder="{{ $nameLabel }}" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        <textarea name="description" maxlength="500" rows="3" placeholder="Deskripsi (opsional)" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white"></textarea>
                    @endif
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 text-xs text-slate-400">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($type === 'units' && \App\Support\Context::hasPermission('master_data.units.manage'))
        <div x-show="showEditModal" style="display: none" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div @click.outside="showEditModal = false" class="glass-card w-full max-w-lg rounded-2xl p-6 border border-slate-700">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-bold text-white">Edit Satuan Bisnis</h3>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
                </div>
                <form :action="'/units/' + editUnit.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input name="code" x-model="editUnit.code" required maxlength="30" placeholder="Kode satuan" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                    <input name="name" x-model="editUnit.name" required maxlength="100" placeholder="Nama lengkap satuan" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                    <select name="category" x-model="editUnit.category" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white">
                        <option value="quantity">Kuantitas</option><option value="weight">Berat</option><option value="volume">Volume</option><option value="length">Panjang</option><option value="time">Waktu</option><option value="area">Luas</option><option value="custom">Kustom</option>
                    </select>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs text-slate-400">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
