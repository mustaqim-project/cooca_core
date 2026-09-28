@extends('layouts.app', ['title' => 'Rekening Penarikan Terverifikasi'])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-28 lg:pb-12" x-data="payoutAccountApp()">

    {{-- TOOLBAR / PAGE HEADER --}}
    <x-module-header
        title="Rekening Penarikan Saldo"
        subtitle="Kelola rekening bank terverifikasi untuk pencairan saldo Cooca Pay (QR Meja & Toko Online)">
        <button type="button" @click="openAddModal()"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Tambah Rekening Bank</span>
        </button>
        <a href="{{ route('finance.settlements.index') }}"
            class="min-h-[44px] sm:min-h-0 h-11 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Payout Hub</span>
        </a>
    </x-module-header>

    {{-- MODULE TABS (SSOT) --}}
    <x-module-tabs module="finance" />

    {{-- ANTI-FRAUD SECURITY BANNER --}}
    <div class="rounded-[16px] bg-[#007AFF]/10 dark:bg-[#007AFF]/15 border border-[#007AFF]/25 p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                    <span>Proteksi Rekening Penarikan (Strict Owner Identity Matching)</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]">Aktif</span>
                </h2>
                <p class="text-xs text-black/70 dark:text-white/70 mt-0.5 leading-relaxed">
                    Demi keamanan dana dan regulasi anti-fraud, nama pemilik rekening penarikan <strong class="text-black dark:text-white">WAJIB SESUAI PERSIS</strong> dengan nama pemilik usaha terdaftar: <strong class="text-[#007AFF] underline">{{ $expectedOwnerName }}</strong>. Penarikan ke rekening pihak ketiga atau karyawan dilarang.
                </p>
            </div>
        </div>
    </div>

    {{-- ACCOUNTS GRID BENTO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($accounts as $acc)
            <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border {{ $acc->is_primary ? 'border-[#007AFF] ring-1.5 ring-[#007AFF]/40' : 'border-black/5 dark:border-white/10' }} p-5 shadow-sm flex flex-col justify-between space-y-4 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 flex items-center justify-center font-bold text-xs text-black dark:text-white font-mono">
                                {{ $acc->bank_code }}
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-black dark:text-white">{{ $acc->bank_name }}</h3>
                                <p class="text-[11px] text-black/50 dark:text-white/50">{{ $acc->location ? $acc->location->name : 'Semua Cabang (Pusat)' }}</p>
                            </div>
                        </div>
                        @if($acc->is_primary)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                <span>Utama</span>
                            </span>
                        @endif
                    </div>

                    <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1 font-mono">
                        <div class="text-[11px] text-black/45 dark:text-white/45 font-sans">Nomor Rekening:</div>
                        <div class="text-base font-extrabold text-black dark:text-white tracking-wider tabular-nums">
                            {{ $acc->account_number }}
                        </div>
                        <div class="text-xs font-semibold text-[#007AFF] uppercase font-sans pt-1 border-t border-black/5 dark:border-white/5">
                            a.n {{ $acc->account_holder_name }}
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-black/5 dark:border-white/10 text-xs">
                    <div class="flex items-center gap-1.5 text-[#34C759] font-medium text-[11px]">
                        <i data-lucide="badge-check" class="w-4 h-4"></i>
                        <span>Terverifikasi</span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if(!$acc->is_primary)
                            <form action="{{ route('finance.payout-accounts.set-primary', $acc->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="h-8 px-2.5 rounded-[8px] text-[11px] font-medium text-[#007AFF] hover:bg-[#007AFF]/10 transition">
                                    Jadikan Utama
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('finance.payout-accounts.destroy', $acc->id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus rekening penarikan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-8 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-base text-black dark:text-white">Belum Ada Rekening Penarikan Terdaftar</h3>
                <p class="text-xs text-black/50 dark:text-white/50 max-w-md mx-auto">
                    Daftarkan rekening bank milik pemilik usaha agar Cooca dapat mentransfer hasil penjualan QR Order dan Toko Online secara otomatis atau saat Anda melakukan penarikan.
                </p>
                <button type="button" @click="openAddModal()"
                    class="h-10 px-5 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition inline-flex items-center gap-2 shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Rekening Sekarang</span>
                </button>
            </div>
        @endforelse
    </div>

    {{-- MODAL TAMBAH REKENING (BENTO SHEET XXL) --}}
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" style="display: none;">
        <div class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden"
            @click.outside="showModal = false">
            <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.01]">
                <div>
                    <h2 class="text-[16px] font-bold text-black dark:text-white">Tambah Rekening Bank Penarikan</h2>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Nama pemilik rekening wajib sama dengan nama pemilik usaha terdaftar</p>
                </div>
                <button type="button" @click="showModal = false" class="w-8 h-8 rounded-full hover:bg-black/5 dark:hover:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('finance.payout-accounts.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Bank Penerbit *</label>
                        <select name="bank_code" x-model="selectedBankCode" @change="updateBankName()" required
                            class="w-full h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">-- Pilih Bank --</option>
                            <option value="BCA">Bank Central Asia (BCA)</option>
                            <option value="MANDIRI">Bank Mandiri</option>
                            <option value="BRI">Bank Rakyat Indonesia (BRI)</option>
                            <option value="BNI">Bank Negara Indonesia (BNI)</option>
                            <option value="JAGO">Bank Jago</option>
                            <option value="SEABANK">SeaBank Indonesia</option>
                            <option value="CIMB">CIMB Niaga</option>
                            <option value="PERMATA">Bank Permata</option>
                            <option value="DANAMON">Bank Danamon</option>
                            <option value="BSI">Bank Syariah Indonesia (BSI)</option>
                        </select>
                        <input type="hidden" name="bank_name" :value="selectedBankName">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Nomor Rekening *</label>
                        <input type="text" name="account_number" required placeholder="Contoh: 8820194821"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-mono font-medium text-black dark:text-white tabular-nums focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-black/70 dark:text-white/70">Nama Pemilik Rekening *</label>
                        <span class="text-[11px] text-[#007AFF] font-medium">Harus sesuai KTP Owner: {{ $expectedOwnerName }}</span>
                    </div>
                    <input type="text" name="account_holder_name" required value="{{ old('account_holder_name', $expectedOwnerName) }}"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs font-bold text-black dark:text-white uppercase focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    @error('account_holder_name')
                        <p class="text-[11px] text-[#FF3B30] font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">Alokasi Cabang (Opsional)</label>
                    <select name="location_id"
                        class="w-full h-11 px-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">Semua Cabang (Rekening Pusat HQ)</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-black/5 dark:border-white/10">
                    <label class="flex items-center gap-2 text-xs text-black/70 dark:text-white/70 cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" class="rounded border-black/20 text-[#007AFF] focus:ring-0">
                        <span>Jadikan sebagai rekening utama penarikan Cooca Pay</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="showModal = false"
                            class="h-10 px-4 rounded-[12px] text-xs font-medium text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/10 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-10 px-5 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition shadow-sm flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Verifikasi &amp; Simpan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function payoutAccountApp() {
        return {
            showModal: false,
            selectedBankCode: '',
            selectedBankName: '',
            bankMap: {
                'BCA': 'PT Bank Central Asia Tbk',
                'MANDIRI': 'PT Bank Mandiri (Persero) Tbk',
                'BRI': 'PT Bank Rakyat Indonesia (Persero) Tbk',
                'BNI': 'PT Bank Negara Indonesia (Persero) Tbk',
                'JAGO': 'PT Bank Jago Tbk',
                'SEABANK': 'PT Bank Seabank Indonesia',
                'CIMB': 'PT Bank CIMB Niaga Tbk',
                'PERMATA': 'PT Bank Permata Tbk',
                'DANAMON': 'PT Bank Danamon Indonesia Tbk',
                'BSI': 'PT Bank Syariah Indonesia Tbk'
            },
            openAddModal() {
                this.showModal = true;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },
            updateBankName() {
                this.selectedBankName = this.bankMap[this.selectedBankCode] || this.selectedBankCode;
            }
        };
    }
</script>
@endsection
