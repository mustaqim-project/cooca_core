@extends('layouts.app', ['title' => 'Aturan Ongkir & Kurir Toko - Cooca'])

@section('content')
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-28 lg:pb-10" x-data="{
        showAddModal: false,
        editMode: false,
        editRule: {
            id: '',
            name: '',
            rule_type: 'flat',
            rate_amount: 0,
            min_distance_km: '',
            max_distance_km: '',
            min_order_for_free: '',
            sort_order: 0
        },
        openEdit(rule) {
            this.editMode = true;
            this.editRule = {
                id: rule.id,
                name: rule.name,
                rule_type: rule.rule_type,
                rate_amount: rule.rate_amount,
                min_distance_km: rule.min_distance_km || '',
                max_distance_km: rule.max_distance_km || '',
                min_order_for_free: rule.min_order_for_free || '',
                sort_order: rule.sort_order || 0
            };
            this.showAddModal = true;
        },
        openAdd() {
            this.editMode = false;
            this.editRule = {
                id: '',
                name: '',
                rule_type: 'flat',
                rate_amount: 10000,
                min_distance_km: '',
                max_distance_km: '',
                min_order_for_free: '',
                sort_order: 0
            };
            this.showAddModal = true;
        }
    }">
        {{-- UNIFIED STOREFRONT HUB NAVIGATION --}}
        @include('app.storefront.partials.navigation', ['title' => 'Aturan Ongkir & Kurir Toko'])

        {{-- Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
            <p class="text-[13px] text-black/60 dark:text-white/60 max-w-2xl">
                Atur tarif ongkos kirim mandiri, pembagian zona radius jarak (KM), dan promo bebas ongkir otomatis untuk pelanggan etalase online Anda.
            </p>
            <button type="button" @click="openAdd()"
                class="h-9 sm:h-10 px-4 sm:px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12.5px] font-bold tracking-wide transition-all shadow-xs hover:shadow-sm flex items-center gap-2 self-start sm:self-auto shrink-0 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Aturan Ongkir</span>
            </button>
        </div>

        <!-- Bento Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Delivery Mode Status Card -->
            <div
                class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-black/50 dark:text-white/50">Layanan Antar (Delivery)</span>
                    <span
                        class="p-2 rounded-xl {{ $storeSetting?->allow_delivery ?? true ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-lg font-bold text-black dark:text-white">
                        {{ $storeSetting?->allow_delivery ?? true ? 'Aktif & Menerima Pesanan' : 'Dinonaktifkan' }}
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                        {{ $storeSetting?->allow_delivery ?? true ? 'Pelanggan dapat memilih opsi pengiriman ke alamat.' : 'Pelanggan hanya dapat memilih opsi Ambil di Toko (Pickup).' }}
                    </p>
                </div>
            </div>

            <!-- Active Rules Count -->
            <div
                class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-black/50 dark:text-white/50">Total Aturan Aktif</span>
                    <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-black dark:text-white tabular-nums">
                        {{ $rules->where('is_active', true)->count() }} <span
                            class="text-sm font-normal text-black/40 dark:text-white/40">/ {{ $rules->count() }}
                            aturan</span>
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                        Aturan aktif akan otomatis dihitung saat checkout toko.
                    </p>
                </div>
            </div>

            <!-- Free Delivery Threshold -->
            @php
                $freeRule = $rules->firstWhere('rule_type', 'free_threshold');
            @endphp
            <div
                class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-black/50 dark:text-white/50">Bebas Ongkir Otomatis</span>
                    <span class="p-2 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3">
                    @if ($freeRule && $freeRule->is_active)
                        <div class="text-lg font-bold text-purple-600 dark:text-purple-400 tabular-nums">
                            Min. Rp {{ number_format((float) $freeRule->min_order_for_free, 0, ',', '.') }}
                        </div>
                        <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                            Otomatis Rp 0 jika subtotal pesanan mencapai batas ini.
                        </p>
                    @else
                        <div class="text-sm font-bold text-black/60 dark:text-white/60">
                            Belum Dikonfigurasi
                        </div>
                        <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                            Buat aturan baru bertipe "Ambang Belanja Gratis" untuk promo.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Shipping Rules Table Card -->
        <div
            class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-black dark:text-white">Daftar Tarif & Aturan Pengiriman</h2>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Sistem akan mengevaluasi aturan yang paling
                        menguntungkan pelanggan secara otomatis.</p>
                </div>
                <span
                    class="text-xs font-mono tabular-nums px-2.5 py-1 rounded-lg bg-black/[0.03] dark:bg-white/[0.05] text-black/60 dark:text-white/60">
                    {{ $rules->count() }} Data
                </span>
            </div>

            @if ($rules->isEmpty())
                <div class="py-16 px-4 text-center">
                    <div
                        class="w-16 h-16 rounded-2xl bg-black/[0.03] dark:bg-white/[0.05] flex items-center justify-center mx-auto mb-4 text-black/40 dark:text-white/40">
                        <i data-lucide="truck" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-base font-bold text-black dark:text-white">Belum Ada Aturan Ongkir</h3>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-1 max-w-sm mx-auto">
                        Anda belum membuat aturan ongkos kirim. Secara default pengiriman akan menggunakan estimasi Rp 0
                        atau konfirmasi manual kurir toko.
                    </p>
                    <button type="button" @click="openAdd()"
                        class="mt-4 inline-flex items-center gap-2 h-9 px-4 rounded-xl bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold transition-all shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>Buat Aturan Ongkir Pertama</span>
                    </button>
                </div>
            @else
                {{-- DESKTOP TABLE VIEW (>= md) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-black/5 dark:border-white/5 text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider bg-black/[0.01] dark:bg-white/[0.01]">
                                <th class="py-3 px-5">Nama Aturan</th>
                                <th class="py-3 px-5">Tipe Logika</th>
                                <th class="py-3 px-5">Kriteria & Batasan</th>
                                <th class="py-3 px-5 text-right">Tarif Ongkir</th>
                                <th class="py-3 px-5 text-center">Status</th>
                                <th class="py-3 px-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5 text-xs">
                            @foreach ($rules as $rule)
                                <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                                    <td class="py-3.5 px-5 font-semibold text-black dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <span>{{ $rule->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        @if ($rule->rule_type === 'flat')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium text-[11px]">
                                                <i data-lucide="tag" class="w-3 h-3"></i>
                                                <span>Tarif Flat</span>
                                            </span>
                                        @elseif ($rule->rule_type === 'distance_tier')
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 font-medium text-[11px]">
                                                <i data-lucide="navigation" class="w-3 h-3"></i>
                                                <span>Radius Jarak (KM)</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-medium text-[11px]">
                                                <i data-lucide="gift" class="w-3 h-3"></i>
                                                <span>Bebas Ongkir (Threshold)</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-black/60 dark:text-white/60">
                                        @if ($rule->rule_type === 'distance_tier')
                                            <span class="tabular-nums">Radius {{ $rule->min_distance_km ?? 0 }} - {{ $rule->max_distance_km ?? '∞' }} KM</span>
                                        @elseif ($rule->rule_type === 'free_threshold')
                                            <span class="tabular-nums">Min. Belanja Rp {{ number_format((float) $rule->min_order_for_free, 0, ',', '.') }}</span>
                                        @else
                                            Berlaku untuk semua jarak antar
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-right font-mono font-bold text-black dark:text-white tabular-nums">
                                        @if ($rule->rate_amount <= 0 && $rule->rule_type === 'free_threshold')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">GRATIS (Rp 0)</span>
                                        @else
                                            Rp {{ number_format((float) $rule->rate_amount, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-5 text-center">
                                        <form action="{{ route('storefront.shipping.toggle', $rule) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors {{ $rule->is_active ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40 hover:bg-black/[0.08]' }}">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full {{ $rule->is_active ? 'bg-emerald-500' : 'bg-black/30 dark:bg-white/30' }}"></span>
                                                <span>{{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3.5 px-5 text-right space-x-1">
                                        <button type="button" @click='openEdit(@json($rule))'
                                            class="p-1.5 rounded-lg hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black/60 dark:text-white/60 transition-colors">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <form action="{{ route('storefront.shipping.destroy', $rule) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan ongkir ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 rounded-lg hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 transition-colors">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARD LIST (< md) --}}
                <div class="block md:hidden divide-y divide-black/5 dark:divide-white/5">
                    @foreach ($rules as $rule)
                        <div class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-sm text-black dark:text-white">{{ $rule->name }}</h4>
                                    <div class="mt-1">
                                        @if ($rule->rule_type === 'flat')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium text-[11px]">
                                                <i data-lucide="tag" class="w-3 h-3"></i>
                                                <span>Tarif Flat</span>
                                            </span>
                                        @elseif ($rule->rule_type === 'distance_tier')
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 font-medium text-[11px]">
                                                <i data-lucide="navigation" class="w-3 h-3"></i>
                                                <span>Radius Jarak (KM)</span>
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-medium text-[11px]">
                                                <i data-lucide="gift" class="w-3 h-3"></i>
                                                <span>Bebas Ongkir</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <form action="{{ route('storefront.shipping.toggle', $rule) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-colors {{ $rule->is_active ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $rule->is_active ? 'bg-emerald-500' : 'bg-black/30 dark:bg-white/30' }}"></span>
                                        <span>{{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </form>
                            </div>

                            <div
                                class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                    <span>Batasan</span>
                                    <span class="font-medium text-black dark:text-white tabular-nums">
                                        @if ($rule->rule_type === 'distance_tier')
                                            Radius {{ $rule->min_distance_km ?? 0 }} - {{ $rule->max_distance_km ?? '∞' }} KM
                                        @elseif ($rule->rule_type === 'free_threshold')
                                            Min. Belanja Rp {{ number_format((float) $rule->min_order_for_free, 0, ',', '.') }}
                                        @else
                                            Semua Jarak Antar
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-black/60 dark:text-white/60">Tarif Ongkir</span>
                                    <span class="font-bold text-black dark:text-white font-mono tabular-nums">
                                        @if ($rule->rate_amount <= 0 && $rule->rule_type === 'free_threshold')
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">GRATIS (Rp 0)</span>
                                        @else
                                            Rp {{ number_format((float) $rule->rate_amount, 0, ',', '.') }}
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button type="button" @click='openEdit(@json($rule))'
                                    class="h-8 px-3 rounded-lg border border-black/10 dark:border-white/10 hover:bg-black/5 text-xs font-semibold text-black/70 dark:text-white/70 flex items-center gap-1.5 transition">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('storefront.shipping.destroy', $rule) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus aturan ongkir ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-8 px-3 rounded-lg hover:bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-semibold flex items-center gap-1.5 transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Modal Form (Add / Edit Rule) -->
        <div x-show="showAddModal" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/40 backdrop-blur-sm transition-opacity"
            @keydown.escape.window="showAddModal = false">
            <div class="bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-t-[28px] sm:rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6"
                @click.away="showAddModal = false">
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-4">
                    <h3 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                        <i data-lucide="truck" class="w-5 h-5 text-[#007AFF]"></i>
                        <span x-text="editMode ? 'Edit Aturan Ongkir' : 'Tambah Aturan Ongkir'"></span>
                    </h3>
                    <button type="button" @click="showAddModal = false"
                        class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form
                    :action="editMode ? '{{ url('/storefront/shipping') }}/' + editRule.id :
                        '{{ route('storefront.shipping.store') }}'"
                    method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label
                            class="block text-xs font-semibold text-black/60 dark:text-white/60 uppercase tracking-wider mb-1.5">Nama
                            Aturan Ongkir</label>
                        <input type="text" name="name" x-model="editRule.name" required
                            placeholder="Contoh: Kurir Toko Flat, Radius Dekat (0-3km)"
                            class="w-full h-11 px-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label
                            class="block text-xs font-semibold text-black/60 dark:text-white/60 uppercase tracking-wider mb-1.5">Tipe
                            Perhitungan</label>
                        <select name="rule_type" x-model="editRule.rule_type"
                            class="w-full h-11 px-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="flat">Tarif Flat (Biaya Tetap per Pesanan)</option>
                            <option value="distance_tier">Berdasarkan Radius Jarak (KM)</option>
                            <option value="free_threshold">Ambang Belanja Bebas Ongkir (Promo)</option>
                        </select>
                    </div>

                    <!-- Distance Tier Inputs -->
                    <div x-show="editRule.rule_type === 'distance_tier'" class="grid grid-cols-2 gap-3" x-transition>
                        <div>
                            <label
                                class="block text-xs font-semibold text-black/60 dark:text-white/60 uppercase tracking-wider mb-1.5">Jarak
                                Min (KM)</label>
                            <input type="number" step="0.1" min="0" name="min_distance_km"
                                x-model="editRule.min_distance_km" placeholder="0"
                                class="w-full h-11 px-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-semibold text-black/60 dark:text-white/60 uppercase tracking-wider mb-1.5">Jarak
                                Max (KM)</label>
                            <input type="number" step="0.1" min="0" name="max_distance_km"
                                x-model="editRule.max_distance_km" placeholder="5"
                                class="w-full h-11 px-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                        </div>
                    </div>

                    <!-- Free Threshold Input -->
                    <div x-show="editRule.rule_type === 'free_threshold'" x-transition>
                        <label
                            class="block text-xs font-semibold text-black/60 dark:text-white/60 uppercase tracking-wider mb-1.5">Minimal
                            Total Belanja untuk Bebas Ongkir (Rp)</label>
                        <input type="number" min="0" name="min_order_for_free"
                            x-model="editRule.min_order_for_free" placeholder="Contoh: 100000"
                            class="w-full h-11 px-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                    </div>

                    <!-- Rate Amount -->
                    <div>
                        <label
                            class="block text-xs font-semibold text-black/60 dark:text-white/60 uppercase tracking-wider mb-1.5">
                            <span
                                x-text="editRule.rule_type === 'free_threshold' ? 'Tarif Ongkir Standar Jika Kurang dari Batas (Rp)' : 'Tarif Ongkos Kirim (Rp)'"></span>
                        </label>
                        <input type="number" min="0" name="rate_amount" x-model="editRule.rate_amount" required
                            placeholder="10000"
                            class="w-full h-11 px-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs font-medium text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] tabular-nums">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-black/5 dark:border-white/10">
                        <button type="button" @click="showAddModal = false"
                            class="h-11 px-5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-xs font-semibold text-black/80 dark:text-white/80 transition-all">
                            Batal
                        </button>
                        <button type="submit"
                            class="h-11 px-6 rounded-xl bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold tracking-wide transition-all shadow-sm">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
