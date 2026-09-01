@extends('layouts.app', [
    'title' => 'Klien & Pelanggan',
    'headerTitle' => 'Manajemen Pelanggan (Customers)',
    'headerSubtitle' => 'Kelola direktori klien, profil perusahaan, kontak penagihan, dan termin pembayaran'
])

@section('content')
<div class="space-y-6" x-data="{
    showAddModal: false,
    showEditModal: false,
    editCustomer: { id: '', slug: '', name: '', company_name: '', code: '', email: '', phone: '', billing_address: '', shipping_address: '', tax_identification_number: '', payment_terms_days: 30, notes: '' },
    
    openEditModal(c) {
        this.editCustomer = { ...c };
        this.showEditModal = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <!-- Search Form -->
        <form method="GET" action="{{ route('customers.index') }}" class="flex-1 flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama pelanggan, perusahaan, nomor telp, email..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-xs text-white">
            </div>
            @if(request('search'))
                <a href="{{ route('customers.index') }}" class="text-xs text-slate-400 hover:text-white">Reset</a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <button @click="showAddModal = true" 
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 transition-all">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pelanggan</span>
            </button>
        </div>
    </div>

    <!-- Customers Table Card -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 bg-slate-900/50">
                        <th class="py-3.5 px-4 font-semibold">Nama & Perusahaan</th>
                        <th class="py-3.5 px-4 font-semibold">ID Klien</th>
                        <th class="py-3.5 px-4 font-semibold">Kontak & Email</th>
                        <th class="py-3.5 px-4 font-semibold">Alamat Penagihan</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Termin Bayar</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Transaksi</th>
                        <th class="py-3.5 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-bold text-white text-sm">{{ $customer->name }}</div>
                            @if($customer->company_name)
                                <div class="text-xs text-emerald-400 font-medium flex items-center gap-1 mt-0.5">
                                    <i data-lucide="building" class="w-3 h-3"></i>
                                    <span>{{ $customer->company_name }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-300">
                            {{ $customer->code ?? '-' }}
                        </td>
                        <td class="py-3 px-4">
                            @if($customer->phone)
                                <div class="font-mono text-slate-200 flex items-center gap-1.5">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-400"></i>
                                    <span>{{ $customer->phone }}</span>
                                </div>
                            @endif
                            @if($customer->email)
                                <div class="text-slate-400 flex items-center gap-1.5 mt-0.5">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-500"></i>
                                    <span>{{ $customer->email }}</span>
                                </div>
                            @endif
                            @if(! $customer->phone && ! $customer->email)
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-400 max-w-xs truncate">
                            {{ $customer->billing_address ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-center font-mono">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                Net {{ $customer->payment_terms_days }} hari
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-[11px] text-slate-300 font-semibold" title="Total PO">
                                    {{ $customer->purchase_orders_count }} PO
                                </span>
                                <span class="text-slate-600">•</span>
                                <span class="text-[11px] text-emerald-400 font-semibold" title="Total Faktur">
                                    {{ $customer->invoices_count }} Faktur
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="openEditModal({
                                    id: '{{ $customer->id }}',
                                    slug: '{{ $customer->slug }}',
                                    name: '{{ addslashes($customer->name) }}',
                                    company_name: '{{ addslashes($customer->company_name ?? '') }}',
                                    code: '{{ addslashes($customer->code ?? '') }}',
                                    email: '{{ addslashes($customer->email ?? '') }}',
                                    phone: '{{ addslashes($customer->phone ?? '') }}',
                                    billing_address: '{{ addslashes($customer->billing_address ?? '') }}',
                                    shipping_address: '{{ addslashes($customer->shipping_address ?? '') }}',
                                    tax_identification_number: '{{ addslashes($customer->tax_identification_number ?? '') }}',
                                    payment_terms_days: {{ (int) $customer->payment_terms_days }},
                                    notes: '{{ addslashes($customer->notes ?? '') }}'
                                })" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition-colors" title="Edit Pelanggan">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('customers.destroy', $customer->slug) }}" onsubmit="return confirm('Hapus data pelanggan {{ $customer->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition-colors" title="Hapus Pelanggan">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p class="text-sm font-medium">Belum ada data pelanggan.</p>
                            <p class="text-xs text-slate-400 mt-1">Tambahkan pelanggan untuk mulai mencatat pesanan (PO) dan menerbitkan faktur penjualan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/40">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Tambah Pelanggan -->
    <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card w-full max-w-xl rounded-2xl p-6 border border-slate-700 space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="showAddModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5 text-emerald-400"></i>
                    <span>Tambah Pelanggan Baru</span>
                </h3>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Pelanggan (PIC) *</label>
                        <input type="text" name="name" required placeholder="Ahmad Zaki" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Perusahaan / Instansi</label>
                        <input type="text" name="company_name" placeholder="PT Maju Bersama Sentosa" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">ID / Kode Klien</label>
                        <input type="text" name="code" placeholder="CUST-001" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" placeholder="0812-8888-9999" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Termin Bayar (Hari)</label>
                        <input type="number" name="payment_terms_days" value="30" min="0" max="365" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Alamat Email Penagihan</label>
                        <input type="email" name="email" placeholder="finance@majubersama.com" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">NPWP / Pajak</label>
                        <input type="text" name="tax_identification_number" placeholder="01.234.567.8-901.000" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Alamat Penagihan (Billing Address)</label>
                    <textarea name="billing_address" rows="2" placeholder="Gedung Cyber 2 Lt. 10, Jl. HR Rasuna Said, Jakarta Selatan" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Alamat Pengiriman (Shipping Address)</label>
                    <textarea name="shipping_address" rows="2" placeholder="Gudang Logistik Kawasan Industri Pulogadung" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Catatan Klien</label>
                    <input type="text" name="notes" placeholder="Diskon khusus pesanan repeat order > 100 unit" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Pelanggan -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="glass-card w-full max-w-xl rounded-2xl p-6 border border-slate-700 space-y-4 max-h-[90vh] overflow-y-auto" @click.outside="showEditModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-5 h-5 text-emerald-400"></i>
                    <span>Edit Data Pelanggan</span>
                </h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/customers/' + editCustomer.slug" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Pelanggan (PIC) *</label>
                        <input type="text" name="name" x-model="editCustomer.name" required class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Perusahaan / Instansi</label>
                        <input type="text" name="company_name" x-model="editCustomer.company_name" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">ID / Kode Klien</label>
                        <input type="text" name="code" x-model="editCustomer.code" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">No. Telepon / WhatsApp</label>
                        <input type="text" name="phone" x-model="editCustomer.phone" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Termin Bayar (Hari)</label>
                        <input type="number" name="payment_terms_days" x-model="editCustomer.payment_terms_days" min="0" max="365" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Alamat Email Penagihan</label>
                        <input type="email" name="email" x-model="editCustomer.email" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">NPWP / Pajak</label>
                        <input type="text" name="tax_identification_number" x-model="editCustomer.tax_identification_number" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Alamat Penagihan (Billing Address)</label>
                    <textarea name="billing_address" rows="2" x-model="editCustomer.billing_address" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Alamat Pengiriman (Shipping Address)</label>
                    <textarea name="shipping_address" rows="2" x-model="editCustomer.shipping_address" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Catatan Klien</label>
                    <input type="text" name="notes" x-model="editCustomer.notes" class="w-full px-3.5 py-2 bg-slate-900 border border-slate-800 focus:border-emerald-500 rounded-xl text-white">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl text-slate-400 hover:text-white">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold shadow-lg shadow-emerald-500/20">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
