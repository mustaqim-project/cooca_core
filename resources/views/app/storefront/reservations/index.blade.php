@extends('layouts.app', ['title' => 'Reservasi & Booking Jadwal - Cooca UMKM'])

@section('content')
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{
        showAssignModal: false,
        selectedReservation: null,
        openAssign(rsv) {
            this.selectedReservation = rsv;
            this.showAssignModal = true;
        }
    }">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div
                    class="flex items-center gap-2 text-xs font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider mb-1">
                    <a href="{{ route('dashboard') }}"
                        class="hover:text-black dark:hover:text-white transition-colors">Workspace</a>
                    <span>/</span>
                    <a href="{{ route('storefront.orders.index') }}"
                        class="hover:text-black dark:hover:text-white transition-colors">Toko Online</a>
                    <span>/</span>
                    <span class="text-black/80 dark:text-white/80">Reservasi Meja & Jasa</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-black dark:text-white flex items-center gap-2.5">
                    <i data-lucide="calendar-check" class="w-6 h-6 text-[#007AFF]"></i>
                    <span>Reservasi &amp; Booking Jadwal</span>
                </h1>
                <p class="text-sm text-black/60 dark:text-white/60 mt-1">
                    Kelola jadwal pemesanan meja makan restoran dan slot waktu layanan jasa pelanggan.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('public.business.landing', $business->slug) }}#layanan" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-black/10 dark:border-white/10 text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-all">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Buka Etalase Publik</span>
                </a>
            </div>
        </div>

        <!-- Feedback Alerts -->
        @if (session('success'))
            <div
                class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-sm flex items-center gap-3 font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div
                class="p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-sm flex items-center gap-3 font-medium">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Apple Bento Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div
                class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-2">
                <div class="flex items-center justify-between text-[#007AFF]">
                    <span class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Jadwal Hari
                        Ini</span>
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div class="text-3xl font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                    {{ $todayCount }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">Reservasi terdaftar untuk hari ini</p>
            </div>

            <div
                class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-2">
                <div class="flex items-center justify-between text-amber-500">
                    <span class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Menunggu
                        Konfirmasi</span>
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div class="text-3xl font-extrabold text-amber-500 tabular-nums tracking-tight">
                    {{ $pendingCount }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">Permintaan reservasi butuh persetujuan</p>
            </div>

            <div
                class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-2">
                <div class="flex items-center justify-between text-[#5856D6]">
                    <span class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Master Meja
                        Aktif</span>
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
                <div class="text-3xl font-extrabold text-[#5856D6] tabular-nums tracking-tight">
                    {{ $tables->count() }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">Total kapasitas meja dine-in restoran</p>
            </div>
        </div>

        <!-- Filters & Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                <a href="{{ route('storefront.reservations.index', ['tab' => 'all']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'all' ? 'bg-black dark:bg-white text-white dark:text-black shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    Semua
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'today']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'today' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    Hari Ini ({{ $todayCount }})
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'pending']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    Menunggu ({{ $pendingCount }})
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'confirmed']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'confirmed' ? 'bg-[#34C759] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    Dikonfirmasi / Seated
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'completed']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'completed' ? 'bg-black dark:bg-white text-white dark:text-black shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    Selesai
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'cancelled']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'cancelled' ? 'bg-red-500 text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    Batal / No-Show
                </a>
            </div>

            <form method="GET" action="{{ route('storefront.reservations.index') }}" class="relative min-w-[240px]">
                <input type="hidden" name="tab" value="{{ $statusTab }}">
                <i data-lucide="search"
                    class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, kode, WhatsApp..."
                    class="w-full h-10 pl-9 pr-3 rounded-full bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </form>
        </div>

        <!-- Reservations Table / Cards -->
        <div
            class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
            @if ($reservations->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <div
                        class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto text-black/30 dark:text-white/30">
                        <i data-lucide="calendar-x" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-black dark:text-white">Belum Ada Reservasi</h3>
                    <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mx-auto">
                        Pelanggan dapat melakukan reservasi meja atau booking jadwal layanan melalui etalase toko online.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-black/50 dark:text-white/50 uppercase font-bold text-[11px] tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Kode &amp; Waktu</th>
                                <th class="py-3.5 px-4">Pelanggan</th>
                                <th class="py-3.5 px-4">Jumlah Tamu</th>
                                <th class="py-3.5 px-4">Alokasi Meja / Jasa</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            @foreach ($reservations as $rsv)
                                <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                                    <td class="py-4 px-4 align-top">
                                        <span
                                            class="font-bold text-black dark:text-white font-mono text-[13px] block">{{ $rsv->reservation_code }}</span>
                                        <span
                                            class="text-black/60 dark:text-white/60 block mt-0.5">{{ $rsv->reservation_date->translatedFormat('d M Y') }}</span>
                                        <span
                                            class="inline-block px-2 py-0.5 rounded bg-[#007AFF]/10 text-[#007AFF] font-bold text-[10.5px] mt-1">
                                            {{ $rsv->time_slot }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        <span
                                            class="font-bold text-black dark:text-white block">{{ $rsv->customer_name }}</span>
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $rsv->customer_phone);
                                            if (str_starts_with($cleanPhone, '0')) {
                                                $cleanPhone = '62' . substr($cleanPhone, 1);
                                            }
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($rsv->customer_name) }},%20konfirmasi%20reservasi%20{{ $rsv->reservation_code }}..."
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-[#25D366] hover:underline text-[11.5px] mt-0.5">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            <span>+{{ $rsv->customer_phone }}</span>
                                        </a>
                                        @if ($rsv->notes)
                                            <p
                                                class="text-[11.5px] text-black/50 dark:text-white/50 italic mt-1 max-w-xs line-clamp-2">
                                                "{{ $rsv->notes }}"</p>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 align-top font-semibold text-black dark:text-white">
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="users"
                                                class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                            <span>{{ $rsv->guest_count }} Orang</span>
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        @if ($rsv->posTable)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#5856D6]/10 text-[#5856D6]">
                                                <i data-lucide="layout-grid" class="w-3 h-3"></i>
                                                <span>Meja #{{ $rsv->posTable->table_number }}
                                                    ({{ $rsv->posTable->name }})</span>
                                            </span>
                                        @elseif($rsv->product)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
                                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                                <span>{{ $rsv->product->name }}</span>
                                            </span>
                                        @else
                                            <button type="button" @click="openAssign({{ Js::from($rsv) }})"
                                                class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-[#007AFF] hover:underline">
                                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                                <span>Pilih Meja</span>
                                            </button>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        @if ($rsv->status === 'confirmed')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Dikonfirmasi</span>
                                        @elseif($rsv->status === 'seated')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6]">Duduk
                                                / Seated</span>
                                        @elseif($rsv->status === 'completed')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">Selesai</span>
                                        @elseif($rsv->status === 'cancelled')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-500/15 text-red-600">Batal</span>
                                        @elseif($rsv->status === 'no_show')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-500/15 text-gray-600">No
                                                Show</span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400 animate-pulse">Menunggu</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 align-top text-right">
                                        <div class="flex items-center justify-end gap-1.5" x-data="{ openMenu: false }">
                                            @if ($rsv->status === 'pending_confirmation')
                                                <form action="{{ route('storefront.reservations.status', $rsv) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit"
                                                        class="px-2.5 py-1 rounded-[8px] bg-[#34C759] hover:bg-[#2EB04E] text-white font-bold text-[11px] transition">
                                                        Konfirmasi
                                                    </button>
                                                </form>
                                            @elseif($rsv->status === 'confirmed')
                                                <form action="{{ route('storefront.reservations.status', $rsv) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="seated">
                                                    <button type="submit"
                                                        class="px-2.5 py-1 rounded-[8px] bg-[#5856D6] hover:bg-[#4745B8] text-white font-bold text-[11px] transition">
                                                        Duduk / Seated
                                                    </button>
                                                </form>
                                            @elseif($rsv->status === 'seated')
                                                <form action="{{ route('storefront.reservations.status', $rsv) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit"
                                                        class="px-2.5 py-1 rounded-[8px] bg-black dark:bg-white text-white dark:text-black font-bold text-[11px] transition">
                                                        Selesai
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Dropdown for other actions --}}
                                            <div class="relative">
                                                <button type="button" @click="openMenu = !openMenu"
                                                    class="p-1 rounded hover:bg-black/5 dark:hover:bg-white/10 text-black/50">
                                                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                                </button>
                                                <div x-show="openMenu" @click.away="openMenu = false" x-transition
                                                    class="absolute right-0 mt-1 w-36 bg-white dark:bg-[#2C2C2E] rounded-[14px] shadow-lg border border-black/10 dark:border-white/10 p-1.5 z-20 text-left text-[11.5px] space-y-0.5">
                                                    <button type="button"
                                                        @click="openMenu = false; openAssign({{ Js::from($rsv) }})"
                                                        class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-black/5 flex items-center gap-2 text-black dark:text-white">
                                                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                                                        <span>Ubah Meja</span>
                                                    </button>
                                                    @if (!in_array($rsv->status, ['cancelled', 'completed', 'no_show'], true))
                                                        <form action="{{ route('storefront.reservations.status', $rsv) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="status" value="cancelled">
                                                            <button type="submit"
                                                                class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-red-50 text-red-600 flex items-center gap-2">
                                                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                                <span>Batalkan</span>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-black/5 dark:border-white/5">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Assign Table -->
        <div x-show="showAssignModal" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
            style="display: none;">
            <div @click.away="showAssignModal = false"
                class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                    <h3 class="text-base font-bold text-black dark:text-white">Alokasikan Meja Restoran</h3>
                    <button type="button" @click="showAssignModal = false" class="text-black/40 hover:text-black">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <template x-if="selectedReservation">
                    <form :action="'/storefront/reservations/' + selectedReservation.id + '/assign-table'" method="POST"
                        class="space-y-4">
                        @csrf
                        <div>
                            <span class="text-[12px] text-black/50 dark:text-white/50 block">Reservasi</span>
                            <p class="font-bold text-sm text-black dark:text-white"
                                x-text="selectedReservation.customer_name + ' (' + selectedReservation.guest_count + ' Tamu)'">
                            </p>
                        </div>

                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Pilih
                                Meja yang Tersedia</label>
                            <select name="pos_table_id" required
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                                <option value="">-- Pilih Nomor Meja --</option>
                                @foreach ($tables as $tbl)
                                    <option value="{{ $tbl->id }}">
                                        Meja #{{ $tbl->table_number }} - {{ $tbl->name }} (Kapasitas:
                                        {{ $tbl->capacity }} Kursi)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="showAssignModal = false"
                                class="px-4 py-2 rounded-full text-xs font-semibold text-black/60 hover:bg-black/5">Batal</button>
                            <button type="submit"
                                class="px-5 py-2 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold transition">Simpan
                                Alokasi</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
@endsection
