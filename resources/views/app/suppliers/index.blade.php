@extends('layouts.app', [
    'title' => 'Pemasok & Vendor',
    'headerTitle' => 'Manajemen Pemasok (Suppliers)',
    'headerSubtitle' => 'Kelola direktori pemasok, kontak PIC, dan pengadaan bahan baku'
])

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    editSupplier: { id: '', name: '', contact_person: '', phone: '', email: '', address: '', notes: '' },

    openEditModal(sup) {
        this.editSupplier = { ...sup };
        this.showEditModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Search Form -->
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex-1 flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama pemasok, kontak, atau email..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
            </div>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="text-xs text-slate-400 hover:text-white">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('materials.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold flex items-center gap-2 transition-all">
                <i data-lucide="boxes" class="w-4 h-4 text-slate-400"></i>
                <span>Katalog Bahan</span>
            </a>
            @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                <button @click="showAddModal = true"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Pemasok</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Suppliers Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Nama Pemasok</th>
                        <th class="py-3.5 px-4 font-semibold">Kontak PIC</th>
                        <th class="py-3.5 px-4 font-semibold">Telepon / WhatsApp</th>
                        <th class="py-3.5 px-4 font-semibold">Email</th>
                        <th class="py-3.5 px-4 font-semibold">Alamat</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Bahan Baku</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($suppliers as $supplier)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-white text-sm">{{ $supplier->name }}</div>
                            @if($supplier->notes)
                                <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $supplier->notes }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-300">
                            {{ $supplier->contact_person ?? '-' }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-300">
                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}" class="hover:text-emerald-400 flex items-center gap-1.5">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>{{ $supplier->phone }}</span>
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-300">
                            @if($supplier->email)
                                <a href="mailto:{{ $supplier->email }}" class="hover:text-emerald-400 flex items-center gap-1.5">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>{{ $supplier->email }}</span>
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-400 max-w-xs truncate">
                            {{ $supplier->address ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
                                {{ $supplier->materials_count }} item
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(\App\Support\Context::hasPermission('master_data.suppliers.manage'))
                                <button @click="openEditModal({
                                    id: '{{ $supplier->id }}',
                                    name: '{{ addslashes($supplier->name) }}',
                                    contact_person: '{{ addslashes($supplier->contact_person ?? '') }}',
                                    phone: '{{ addslashes($supplier->phone ?? '') }}',
                                    email: '{{ addslashes($supplier->email ?? '') }}',
                                    address: '{{ addslashes($supplier->address ?? '') }}',
                                    notes: '{{ addslashes($supplier->notes ?? '') }}'
                                })" class="p-1.5 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-white transition-colors" title="Edit Pemasok">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier->id) }}" onsubmit="return AppAlert.confirmSubmit(event, this, 'Hapus pemasok {{ addslashes($supplier->name) }}?', 'Hapus Pemasok?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Hapus Pemasok">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            <i data-lucide="truck" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p class="text-sm font-medium">Belum ada data pemasok.</p>
                            <p class="text-xs text-slate-400 mt-1">Tambahkan pemasok untuk mengelola sumber pasokan bahan baku dan PO.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/40">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Tambah Pemasok -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card w-full max-w-lg rounded-2xl p-6 border border-slate-700 space-y-4" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="truck" class="w-5 h-5 text-emerald-400"></i>
                    <span>Tambah Pemasok Baru</span>
                </h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Pemasok / Vendor *</label>
                    <input type="text" name="name" required placeholder="PT Sumber Pangan Sejahtera" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Kontak (PIC)</label>
                        <input type="text" name="contact_person" placeholder="Budi Santoso" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812-3456-7890" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" placeholder="sales@sumberpangan.com" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Lengkap</label>
                    <textarea name="address" rows="2" placeholder="Jl. Pergudangan No. 12, Jakarta Barat" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Catatan Khusus</label>
                    <input type="text" name="notes" placeholder="Term pembayaran 14 hari, minimum order 50kg" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold">Simpan Pemasok</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pemasok -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card w-full max-w-lg rounded-2xl p-6 border border-slate-700 space-y-4" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-5 h-5 text-emerald-400"></i>
                    <span>Edit Data Pemasok</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/suppliers/' + editSupplier.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Pemasok / Vendor *</label>
                    <input type="text" name="name" x-model="editSupplier.name" required class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Kontak (PIC)</label>
                        <input type="text" name="contact_person" x-model="editSupplier.contact_person" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" x-model="editSupplier.phone" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email</label>
                    <input type="email" name="email" x-model="editSupplier.email" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Lengkap</label>
                    <textarea name="address" rows="2" x-model="editSupplier.address" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Catatan Khusus</label>
                    <input type="text" name="notes" x-model="editSupplier.notes" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-xs text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
