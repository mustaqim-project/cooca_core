@extends('layouts.app', ['title' => 'Integrasi Logistik & Pengiriman Biteship - Cooca'])

@section('content')
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-28 sm:pb-32 lg:pb-10"
        x-data="{
            isTestingRate: false,
            testPostalCode: '',
            testWeight: 250,
            testValue: 50000,
            testResult: null,
            testError: null,
            detectingGps: false,
            originLat: '{{ $storeSetting?->origin_latitude ?? '' }}',
            originLng: '{{ $storeSetting?->origin_longitude ?? '' }}',
            originPostalCode: '{{ $storeSetting?->origin_postal_code ?? '' }}',
            originAreaId: '{{ $storeSetting?->origin_area_id ?? '' }}',
            areaQuery: '',
            areaResults: [],
            isSearchingArea: false,
            selectedAreaLabel: '{{ $storeSetting?->origin_area_id ? "ID: " . $storeSetting->origin_area_id : "" }}',

            async searchArea() {
                if (!this.areaQuery || this.areaQuery.trim().length < 2) {
                    this.areaResults = [];
                    return;
                }
                this.isSearchingArea = true;
                try {
                    const res = await fetch('{{ route('storefront.shipping.search_areas') }}?query=' + encodeURIComponent(this.areaQuery));
                    const data = await res.json();
                    this.areaResults = data.areas || [];
                } catch (e) {
                    console.error(e);
                } finally {
                    this.isSearchingArea = false;
                }
            },

            selectArea(area) {
                this.originAreaId = area.id;
                this.selectedAreaLabel = area.name;
                if (area.postal_code) {
                    this.originPostalCode = area.postal_code.toString();
                }
                this.areaResults = [];
                this.areaQuery = '';
            },

            detectLocation() {
                if (!navigator.geolocation) {
                    alert('Fitur GPS tidak didukung oleh browser Anda.');
                    return;
                }
                this.detectingGps = true;
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.originLat = position.coords.latitude.toFixed(7);
                        this.originLng = position.coords.longitude.toFixed(7);
                        this.detectingGps = false;
                    },
                    (err) => {
                        alert('Gagal mengambil koordinat: ' + err.message);
                        this.detectingGps = false;
                    },
                    { timeout: 10000 }
                );
            },

            async runRateTest() {
                if (!this.testPostalCode || this.testPostalCode.trim().length < 4) {
                    this.testError = 'Masukkan kode pos tujuan yang valid (minimal 4-5 digit).';
                    return;
                }
                this.isTestingRate = true;
                this.testError = null;
                this.testResult = null;

                try {
                    const response = await fetch('{{ route('storefront.shipping.test_rate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            destination_postal_code: this.testPostalCode,
                            weight_grams: this.testWeight,
                            item_value: this.testValue
                        })
                    });

                    const data = await response.json();
                    if (data.success && data.pricing && data.pricing.length > 0) {
                        this.testResult = data.pricing;
                    } else {
                        this.testError = data.message || 'Tidak ada kurir yang menjangkau rute tersebut.';
                    }
                } catch (err) {
                    this.testError = 'Gagal menghubungi server Biteship: ' + err.message;
                } finally {
                    this.isTestingRate = false;
                }
            }
        }">

        {{-- UNIFIED STOREFRONT HUB NAVIGATION --}}
        @include('app.storefront.partials.navigation', ['title' => 'Pengiriman & Logistik Biteship'])

        {{-- Action & Overview Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
            <div>
                <p class="text-[13px] text-black/60 dark:text-white/60 max-w-2xl">
                    Koneksi resmi logistik <strong>Biteship.com</strong>. Tarif ongkir dihitung otomatis secara real-time langsung ke berbagai ekspedisi nasional dan instan (JNE, SiCepat, J&T, GoSend, GrabExpress).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    API Biteship Terhubung
                </span>
            </div>
        </div>

        <!-- Bento Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Delivery Mode Status Card -->
            <div class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[20px] p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-black/50 dark:text-white/50">Layanan Antar (Delivery)</span>
                    <span class="p-2 rounded-xl {{ $storeSetting?->allow_delivery ?? true ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                        <i data-lucide="package" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-lg font-bold text-black dark:text-white">
                        {{ $storeSetting?->allow_delivery ?? true ? 'Aktif & Menerima Pesanan' : 'Dinonaktifkan' }}
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                        {{ $storeSetting?->allow_delivery ?? true ? 'Pelanggan dapat memilih opsi kurir pengiriman saat checkout.' : 'Toko hanya menerima opsi Ambil Sendiri (Pickup).' }}
                    </p>
                </div>
            </div>

            <!-- Origin Store Status -->
            <div class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[20px] p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-black/50 dark:text-white/50">Lokasi Asal Penjemputan</span>
                    <span class="p-2 rounded-xl {{ !empty($storeSetting?->origin_postal_code) ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }}">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-lg font-bold text-black dark:text-white">
                        {{ !empty($storeSetting?->origin_postal_code) ? 'Kode Pos ' . $storeSetting->origin_postal_code : 'Belum Dikonfigurasi' }}
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-1 truncate">
                        {{ $storeSetting?->origin_address ?? 'Wajib diisi agar tarif pengiriman dapat dihitung akurat.' }}
                    </p>
                </div>
            </div>

            <!-- Active Couriers Count -->
            <div class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[20px] p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-black/50 dark:text-white/50">Ekspedisi Logistik Aktif</span>
                    <span class="p-2 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-bold text-black dark:text-white tabular-nums">
                        {{ count($enabledCouriers) }} <span class="text-sm font-normal text-black/40 dark:text-white/40">/ {{ count($availableCouriers) }} kurir</span>
                    </div>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-1">
                        JNE, SiCepat, J&T, AnterAja, GoSend, Grab
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Form Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Origin Address & Couriers Form (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                <form action="{{ route('storefront.shipping.origin.save') }}" method="POST"
                    class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[24px] p-6 shadow-xs space-y-6">
                    @csrf

                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="building" class="w-5 h-5 text-[#007AFF]"></i>
                            <h2 class="text-base font-bold text-black dark:text-white">Alamat Asal Penjemputan Toko (Origin)</h2>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60">
                            Lokasi fisik toko tempat kurir logistik (JNE, SiCepat, GoSend, dll.) akan mengambil paket pesanan pelanggan.
                        </p>
                    </div>

                    {{-- Biteship Locations API Status Badge --}}
                    @if(!empty($storeSetting?->origin_location_id))
                        <div class="flex items-center justify-between p-3.5 rounded-[16px] bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                <span class="truncate">Tersimpan di <strong>Biteship Locations API</strong></span>
                            </div>
                            <span class="font-mono text-[11px] px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 font-semibold">{{ $storeSetting->origin_location_id }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2.5 p-3.5 rounded-[16px] bg-blue-500/10 border border-blue-500/20 text-blue-700 dark:text-blue-400 text-xs">
                            <i data-lucide="info" class="w-4 h-4 text-blue-500 shrink-0"></i>
                            <span>Alamat ini akan otomatis didaftarkan dan mendapatkan <strong>Biteship Location ID</strong> saat Anda menyimpan formulir.</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama PIC Pengirim Toko <span class="text-rose-500">*</span></label>
                            <input type="text" name="origin_contact_name" required
                                value="{{ old('origin_contact_name', $storeSetting?->origin_contact_name ?? $business->name) }}"
                                placeholder="Contoh: Admin Pengiriman Cooca"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nomor WhatsApp / Telepon Toko <span class="text-rose-500">*</span></label>
                            <input type="tel" name="origin_contact_phone" required
                                value="{{ old('origin_contact_phone', $storeSetting?->origin_contact_phone ?? $business->phone) }}"
                                placeholder="081234567890"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    {{-- Biteship Maps Search Area Autocomplete Widget --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70">
                                Cari Area Administratif (Biteship Maps API)
                            </label>
                            <span class="text-[11px] text-black/40 dark:text-white/40">Kecamatan, Kelurahan, Kota</span>
                        </div>
                        <div class="relative">
                            <div class="relative flex items-center">
                                <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 pointer-events-none"></i>
                                <input type="text" x-model="areaQuery" @input.debounce.300ms="searchArea()"
                                    placeholder="Ketik untuk mencari area: misal Cilandak, Kebayoran, Sukajadi, Wonokromo..."
                                    class="w-full h-11 pl-9 pr-24 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <button type="button" @click="searchArea()" :disabled="isSearchingArea"
                                    class="absolute right-1.5 px-3 py-1.5 rounded-[8px] bg-black/10 dark:bg-white/10 hover:bg-black/15 text-xs font-medium text-black dark:text-white cursor-pointer transition-colors">
                                    <span x-text="isSearchingArea ? 'Mencari...' : 'Cari Area'"></span>
                                </button>
                            </div>

                            <!-- Dropdown Search Results -->
                            <div x-show="areaResults.length > 0" @click.away="areaResults = []"
                                class="absolute z-30 left-0 right-0 mt-1.5 max-h-56 overflow-y-auto rounded-[16px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-xl p-1.5 space-y-1">
                                <template x-for="item in areaResults" :key="item.id">
                                    <button type="button" @click="selectArea(item)"
                                        class="w-full text-left p-2.5 rounded-[10px] hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer flex flex-col gap-0.5">
                                        <span class="text-xs font-bold text-black dark:text-white" x-text="item.name"></span>
                                        <span class="text-[11px] text-black/50 dark:text-white/50 flex items-center gap-2">
                                            <span>Area ID: <code class="font-mono text-[10px]" x-text="item.id"></code></span>
                                            <span x-show="item.postal_code" class="text-black/40">• Kode Pos: <span x-text="item.postal_code"></span></span>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Selected Area Badge -->
                        <div x-show="selectedAreaLabel" class="flex items-center gap-2 text-xs text-black/60 dark:text-white/60 pt-0.5">
                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500"></i>
                            <span>Area Terpilih: <strong class="text-black dark:text-white" x-text="selectedAreaLabel"></strong></span>
                        </div>
                        <input type="hidden" name="origin_area_id" :value="originAreaId">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Alamat Lengkap Toko / Gudang <span class="text-rose-500">*</span></label>
                        <textarea name="origin_address" rows="3" required
                            placeholder="Jalan, Nomor Bangunan, RT/RW, Kelurahan, Kecamatan, Kota"
                            class="w-full p-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">{{ old('origin_address', $storeSetting?->origin_address ?? $business->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Kode Pos Toko <span class="text-rose-500">*</span></label>
                            <input type="text" name="origin_postal_code" required maxlength="10"
                                x-model="originPostalCode"
                                placeholder="Contoh: 12440"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Latitude GPS</label>
                            <input type="text" name="origin_latitude" x-model="originLat"
                                placeholder="-6.2253114"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70">Longitude GPS</label>
                                <button type="button" @click="detectLocation()" :disabled="detectingGps"
                                    class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 cursor-pointer">
                                    <i data-lucide="navigation" class="w-3 h-3"></i>
                                    <span x-text="detectingGps ? 'Mendeteksi...' : 'GPS Otomatis'"></span>
                                </button>
                            </div>
                            <input type="text" name="origin_longitude" x-model="originLng"
                                placeholder="106.7993735"
                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-sm text-black dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <hr class="border-black/5 dark:border-white/10 my-4">

                    <!-- Courier Selection Checkboxes -->
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="truck" class="w-5 h-5 text-[#007AFF]"></i>
                            <h2 class="text-base font-bold text-black dark:text-white">Pilihan Kurir Ekspedisi yang Diaktifkan</h2>
                        </div>
                        <p class="text-xs text-black/60 dark:text-white/60 mb-4">
                            Centang jasa pengiriman yang ingin Anda sediakan untuk pembeli di halaman etalase online.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach ($availableCouriers as $courier)
                                <label class="relative flex items-start gap-3 p-3.5 rounded-[16px] border border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] hover:bg-black/[0.04] dark:hover:bg-white/[0.05] transition-all cursor-pointer">
                                    <input type="checkbox" name="biteship_enabled_couriers[]" value="{{ $courier['code'] }}"
                                        {{ in_array($courier['code'], $enabledCouriers) ? 'checked' : '' }}
                                        class="mt-1 w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF] border-black/20 dark:border-white/20">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-black dark:text-white uppercase">{{ $courier['code'] }}</span>
                                            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $courier['category'] === 'instant' ? 'bg-amber-500/10 text-amber-600' : 'bg-blue-500/10 text-blue-600' }}">
                                                {{ ucfirst($courier['category']) }}
                                            </span>
                                        </div>
                                        <p class="text-[12px] font-semibold text-black/80 dark:text-white/80 mt-0.5">{{ $courier['name'] }}</p>
                                        <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5 truncate">{{ $courier['service_types'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-bold tracking-wide transition-all shadow-sm flex items-center gap-2 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Konfigurasi Biteship</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right: Live Test Rate Simulator (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border border-black/5 dark:border-white/10 rounded-[24px] p-5 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calculator" class="w-5 h-5 text-[#007AFF]"></i>
                        <h3 class="text-sm font-bold text-black dark:text-white">Simulator Ongkir Live</h3>
                    </div>
                    <p class="text-xs text-black/60 dark:text-white/60">
                        Uji coba perhitungan ongkos kirim langsung dari alamat toko Anda ke kode pos tujuan pembeli.
                    </p>

                    <div class="space-y-3 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Kode Pos Tujuan Uji Coba</label>
                            <input type="text" x-model="testPostalCode" placeholder="Contoh: 12310 (Jakarta Selatan)"
                                class="w-full h-10 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Berat (Gram)</label>
                                <input type="number" x-model="testWeight" min="10" step="50"
                                    class="w-full h-10 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nilai Barang</label>
                                <input type="number" x-model="testValue" min="1000" step="10000"
                                    class="w-full h-10 px-3 rounded-[10px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            </div>
                        </div>

                        <button type="button" @click="runRateTest()" :disabled="isTestingRate"
                            class="w-full h-10 rounded-[12px] bg-black dark:bg-white text-white dark:text-black hover:bg-black/80 dark:hover:bg-white/90 text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <i data-lucide="search" class="w-3.5 h-3.5" x-show="!isTestingRate"></i>
                            <span x-show="!isTestingRate">Hitung Tarif Kurir</span>
                            <span x-show="isTestingRate" class="flex items-center gap-1.5">
                                <span class="w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full animate-spin"></span>
                                Menghubungi Biteship...
                            </span>
                        </button>
                    </div>

                    <!-- Error Alert -->
                    <div x-show="testError" x-cloak
                        class="p-3 rounded-[12px] bg-rose-500/10 border border-rose-500/20 text-xs text-rose-600 dark:text-rose-400">
                        <div class="flex items-start gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span x-text="testError"></span>
                        </div>
                    </div>

                    <!-- Test Results List -->
                    <div x-show="testResult && testResult.length > 0" x-cloak class="space-y-2 pt-2">
                        <div class="flex items-center justify-between text-[11px] font-semibold text-black/50 dark:text-white/50">
                            <span>Layanan Kurir</span>
                            <span>Tarif Resmi</span>
                        </div>
                        <div class="space-y-1.5 max-h-72 overflow-y-auto pr-1">
                            <template x-for="item in testResult" :key="item.id">
                                <div class="p-2.5 rounded-[12px] border border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-black dark:text-white truncate" x-text="item.description"></div>
                                        <div class="text-[11px] text-black/50 dark:text-white/50" x-text="item.duration"></div>
                                    </div>
                                    <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tabular-nums shrink-0"
                                        x-text="'Rp ' + Number(item.price).toLocaleString('id-ID')">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Guidance Card -->
                <div class="bg-blue-500/5 dark:bg-blue-500/10 border border-blue-500/15 rounded-[20px] p-4 text-xs text-blue-900 dark:text-blue-200 space-y-2">
                    <div class="flex items-center gap-2 font-bold">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Penenang Jiwa Operasional</span>
                    </div>
                    <p class="leading-relaxed text-[11.5px] text-blue-900/80 dark:text-blue-200/80">
                        Tenang: Seluruh perhitungan ongkir etalase dilakukan secara otomatis dan transparan. Anda tidak perlu lagi repot menghitung jarak atau menetapkan tarif manual antar kota.
                    </p>
                </div>
            </div>

        </div>
    </div>
@endsection
