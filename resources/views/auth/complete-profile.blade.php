@extends('layouts.public_marketing', ['title' => 'Lengkapi Profil & Lokasi Usaha - Cooca', 'noindex' => true])

@section('content')
    @php
        $initProvince = (string) old('province', optional($primaryLocation)->province ?? '');
        $initCity = (string) old('city', optional($primaryLocation)->city ?? '');
        $initDistrict = (string) old('district', optional($primaryLocation)->district ?? '');
        $initVillage = (string) old('village', optional($primaryLocation)->village ?? '');
        $initPostalCode = (string) old('postal_code', optional($primaryLocation)->postal_code ?? '');
        $initBiteshipId = (string) old('biteship_area_id', optional($primaryLocation)->biteship_area_id ?? '');
        $initAddress = (string) old('address', optional($primaryLocation)->address ?? '');
        $initLat = (string) old('latitude', optional($primaryLocation)->latitude ?? '-6.2088');
        $initLng = (string) old('longitude', optional($primaryLocation)->longitude ?? '106.8456');
        $initLabel = $initVillage ? implode(', ', array_filter([$initVillage, $initDistrict, $initCity, $initProvince])) : '';
    @endphp

    <!-- Leaflet.js Assets for Interactive Business Mapping -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                @include('auth.partials.brand-logo', ['class' => 'mb-5'])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Setup Profil &amp; Lokasi Usaha</h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-lg mx-auto leading-relaxed">
                    Konfirmasi identitas bisnis dan tentukan titik lokasi cabang utama untuk struk kasir, faktur, dan kalkulasi otomatis kurir pengiriman.
                </p>
            </div>

            <!-- Structured Auth Card -->
            <div
                x-data="businessSetupLocation()"
                class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-9 shadow-sm dark:shadow-2xl dark:shadow-black/40 relative overflow-hidden transition-all">
                @if ($errors->any())
                    <div
                        class="mb-6 p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-sm animate-shake">
                        <div class="font-semibold mb-1.5 flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <span>Ada data yang perlu diperbaiki:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.complete.save') }}" class="space-y-6">
                    @csrf

                    {{-- ========================================================== --}}
                    {{-- BAGIAN 1: IDENTITAS PEMILIK & BRAND                        --}}
                    {{-- ========================================================== --}}
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 pb-1 border-b border-black/5 dark:border-white/5">
                            <span class="w-6 h-6 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-xs font-bold flex items-center justify-center">1</span>
                            <h3 class="text-sm font-bold text-black dark:text-white uppercase tracking-wider">Identitas Usaha &amp; Kontak</h3>
                        </div>

                        <div>
                            <label for="name"
                                class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-1.5">
                                Nama Lengkap Pemilik / Penanggung Jawab <span class="text-[#FF3B30]">*</span>
                            </label>
                            <div class="relative">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </div>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                    required
                                    class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[15px] sm:text-sm outline-none"
                                    placeholder="Contoh: Budi Pratama">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="phone"
                                    class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-1.5">
                                    Nomor WhatsApp / HP Aktif <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                                    </div>
                                    <input type="text" name="phone" id="phone"
                                        value="{{ old('phone', $user->phone ?? ($business->phone ?? '')) }}" required
                                        class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[15px] sm:text-sm outline-none"
                                        placeholder="081234567890">
                                </div>
                            </div>

                            <div>
                                <label for="business_name"
                                    class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-1.5">
                                    Nama Usaha / Toko / Brand <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                        <i data-lucide="store" class="w-5 h-5"></i>
                                    </div>
                                    <input type="text" name="business_name" id="business_name"
                                        value="{{ old('business_name', str_starts_with($business->name ?? '', 'Usaha ') ? '' : $business->name ?? '') }}"
                                        required
                                        class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[15px] sm:text-sm outline-none"
                                        placeholder="Contoh: Kopi Kenangan Manis">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- BAGIAN 2: TITIK LOKASI & WILAYAH INDONESIA (LEAFLET.JS)   --}}
                    {{-- ========================================================== --}}
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-[#34C759]/15 text-[#34C759] text-xs font-bold flex items-center justify-center">2</span>
                                <h3 class="text-sm font-bold text-black dark:text-white uppercase tracking-wider">Titik Lokasi Usaha Utama</h3>
                            </div>
                            <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Tingkat Desa / Kelurahan</span>
                        </div>

                        <!-- Autocomplete Pencarian Cepat Wilayah Indonesia -->
                        <div class="relative">
                            <label class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-1.5">
                                Cari Wilayah Cepat (Autocomplete)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                    <i data-lucide="search" class="w-5 h-5"></i>
                                </div>
                                <input type="text"
                                    x-model="searchQuery"
                                    @input.debounce.350ms="searchAreas()"
                                    @focus="if(searchResults.length > 0) showDropdown = true"
                                    class="w-full pl-11 pr-10 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[15px] sm:text-sm outline-none"
                                    placeholder="Ketik Kode Pos (cth: 12190) atau Nama Kelurahan / Desa (cth: Senayan)...">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                                    <template x-if="isSearching">
                                        <i data-lucide="loader-2" class="w-4 h-4 text-[#007AFF] animate-spin"></i>
                                    </template>
                                </div>
                            </div>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-1">
                                Ketik minimal 3 karakter untuk mengisi otomatis seluruh kolom wilayah di bawah, atau isi langsung secara manual.
                            </p>

                            <!-- Dropdown Hasil Pencarian Wilayah -->
                            <div x-show="showDropdown && searchResults.length > 0" x-cloak
                                @click.outside="showDropdown = false"
                                class="absolute z-20 left-0 right-0 mt-1.5 max-h-60 overflow-y-auto bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-[18px] shadow-2xl divide-y divide-black/5 dark:divide-white/5">
                                <template x-for="item in searchResults" :key="item.id || item.label">
                                    <button type="button"
                                        @click="selectArea(item)"
                                        class="w-full px-4 py-3 text-left hover:bg-[#007AFF]/10 dark:hover:bg-[#0A84FF]/15 transition-colors flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0 mt-0.5">
                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13.5px] font-semibold text-black dark:text-white truncate" x-text="item.village ? item.village + ', ' + item.district : item.label"></p>
                                            <p class="text-[12px] text-black/55 dark:text-white/55" x-text="item.city + ', ' + item.province"></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-mono font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 shrink-0" x-text="item.postal_code || '-'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Bento Grid Form Wilayah Administratif (Visible & Editable) -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3.5">
                            <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                                <span class="text-[11.5px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                    <i data-lucide="map-pinned" class="w-4 h-4 text-[#007AFF]"></i>
                                    Data Wilayah Administratif Usaha
                                </span>
                                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Bisa diedit manual</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label for="province" class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                        Provinsi <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="province" id="province" x-model="selectedArea.province" required
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#1E2538] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[14px] outline-none"
                                        placeholder="Contoh: DKI Jakarta">
                                </div>

                                <div>
                                    <label for="city" class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                        Kota / Kabupaten <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="city" id="city" x-model="selectedArea.city" required
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#1E2538] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[14px] outline-none"
                                        placeholder="Contoh: Kota Jakarta Selatan">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label for="district" class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                        Kecamatan <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="district" id="district" x-model="selectedArea.district" required
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#1E2538] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[14px] outline-none"
                                        placeholder="Contoh: Kebayoran Baru">
                                </div>

                                <div>
                                    <label for="village" class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                        Kelurahan / Desa <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="village" id="village" x-model="selectedArea.village" required
                                        class="w-full px-3.5 py-2.5 bg-white dark:bg-[#1E2538] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[14px] outline-none"
                                        placeholder="Contoh: Senayan">
                                </div>
                            </div>

                            <div>
                                <label for="postal_code" class="block font-semibold text-black/80 dark:text-white/85 text-xs mb-1">
                                    Kode Pos <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="postal_code" id="postal_code" x-model="selectedArea.postal_code" required
                                    class="w-full sm:w-1/2 px-3.5 py-2.5 bg-white dark:bg-[#1E2538] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[14px] outline-none font-mono"
                                    placeholder="Contoh: 12190">
                            </div>
                        </div>

                        <!-- Hidden Form Inputs for Geolocation Metadata -->
                        <input type="hidden" name="latitude" :value="latitude">
                        <input type="hidden" name="longitude" :value="longitude">
                        <input type="hidden" name="biteship_area_id" :value="selectedArea.biteship_area_id">

                        <!-- Detail Alamat Jalan / Nomor Bangunan -->
                        <div>
                            <label for="address"
                                class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm mb-1.5">
                                Detail Alamat Jalan / No. Ruko / Patokan <span class="text-[#FF3B30]">*</span>
                            </label>
                            <textarea name="address" id="address" rows="2" required x-model="address"
                                class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-[16px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[15px] sm:text-sm outline-none resize-none"
                                placeholder="Contoh: Jl. Sudirman No. 45, Ruko Blok B-2 (Sebelah Bank BCA)"></textarea>
                        </div>

                        <!-- Peta Interaktif Leaflet.js -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block font-semibold text-black/80 dark:text-white/85 text-xs sm:text-sm">
                                    Titik Koordinat Peta GPS (Geser Pin ke Lokasi Toko) <span class="text-[#FF3B30]">*</span>
                                </label>
                                <button type="button"
                                    @click="useCurrentGps()"
                                    :disabled="isGpsLoading"
                                    class="text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline flex items-center gap-1.5">
                                    <i data-lucide="crosshair" class="w-3.5 h-3.5" :class="{'animate-spin': isGpsLoading}"></i>
                                    <span x-text="isGpsLoading ? 'Mencari GPS...' : 'Gunakan GPS Saya'"></span>
                                </button>
                            </div>

                            <!-- Map Canvas Container (Apple HIG Squircle) -->
                            <div class="relative rounded-[20px] overflow-hidden border border-black/10 dark:border-white/10 shadow-sm">
                                <div id="business-map" class="w-full h-[260px] sm:h-[300px] bg-black/5 dark:bg-white/5 z-0"></div>
                                <div class="absolute bottom-2.5 left-2.5 z-10 px-3 py-1.5 rounded-[12px] bg-white/90 dark:bg-black/80 backdrop-blur-md border border-black/10 dark:border-white/10 text-[11px] font-mono text-black/70 dark:text-white/70 shadow-md">
                                    <span class="font-bold text-[#007AFF] dark:text-[#0A84FF]">GPS:</span>
                                    <span x-text="latitude ? Number(latitude).toFixed(5) : '-'"></span>,
                                    <span x-text="longitude ? Number(longitude).toFixed(5) : '-'"></span>
                                </div>
                            </div>
                            <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                Anda dapat mengklik atau menggeser pin biru di atas peta untuk menyesuaikan titik tepat toko Anda.
                            </p>
                        </div>
                    </div>

                    <!-- Reassurance / Trust Card -->
                    <div
                        class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] text-xs text-black/65 dark:text-white/65 flex items-start gap-2.5">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                        <p class="leading-relaxed">
                            Data lokasi ini akan ditetapkan sebagai <strong>Cabang Utama</strong> usaha Anda. Anda dapat menambah gudang atau cabang baru kapan saja melalui menu Manajemen Gudang.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] hover:from-[#1cd0ff] hover:to-[#006fe6] text-white font-bold text-sm sm:text-[15px] shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99] min-h-[48px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25 cursor-pointer">
                            <span>Konfirmasi &amp; Masuk ke Workspace</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alpine.js Business Setup Location Script -->
    <script>
        function businessSetupLocation() {
            return {
                searchQuery: '',
                isSearching: false,
                searchResults: [],
                showDropdown: false,
                selectedArea: {
                    province: {!! json_encode($initProvince) !!},
                    city: {!! json_encode($initCity) !!},
                    district: {!! json_encode($initDistrict) !!},
                    village: {!! json_encode($initVillage) !!},
                    postal_code: {!! json_encode($initPostalCode) !!},
                    biteship_area_id: {!! json_encode($initBiteshipId) !!},
                    label: {!! json_encode($initLabel) !!}
                },
                address: {!! json_encode($initAddress) !!},
                latitude: {!! json_encode($initLat) !!},
                longitude: {!! json_encode($initLng) !!},
                isGpsLoading: false,
                map: null,
                marker: null,

                init() {
                    this.$nextTick(() => {
                        this.initLeafletMap();
                    });
                },

                initLeafletMap() {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initLeafletMap(), 250);
                        return;
                    }

                    const defaultLat = parseFloat(this.latitude) || -6.2088;
                    const defaultLng = parseFloat(this.longitude) || 106.8456;
                    const defaultZoom = (this.selectedArea.village || this.selectedArea.postal_code) ? 15 : 12;

                    this.map = L.map('business-map', {
                        zoomControl: true,
                        scrollWheelZoom: false
                    }).setView([defaultLat, defaultLng], defaultZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    const customIcon = L.divIcon({
                        className: 'custom-map-marker',
                        html: `<div style="background-color: #007AFF; width: 32px; height: 32px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,122,255,0.45); border: 2.5px solid white;">
                                 <div style="width: 10px; height: 10px; background-color: white; border-radius: 50%; transform: rotate(45deg);"></div>
                               </div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32]
                    });

                    this.marker = L.marker([defaultLat, defaultLng], {
                        draggable: true,
                        icon: customIcon
                    }).addTo(this.map);

                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.latitude = pos.lat.toFixed(7);
                        this.longitude = pos.lng.toFixed(7);
                        this.reverseGeocode(pos.lat, pos.lng, true);
                    });

                    this.map.on('click', (e) => {
                        this.marker.setLatLng(e.latlng);
                        this.latitude = e.latlng.lat.toFixed(7);
                        this.longitude = e.latlng.lng.toFixed(7);
                        this.reverseGeocode(e.latlng.lat, e.latlng.lng, true);
                    });

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 400);
                },

                async searchAreas() {
                    const q = this.searchQuery.trim();
                    if (q.length < 2) {
                        this.searchResults = [];
                        this.showDropdown = false;
                        return;
                    }

                    this.isSearching = true;
                    try {
                        const res = await fetch(`/geo/search-areas?query=${encodeURIComponent(q)}`);
                        const data = await res.json();
                        if (data.success && Array.isArray(data.areas)) {
                            this.searchResults = data.areas;
                            this.showDropdown = this.searchResults.length > 0;
                        }
                    } catch (err) {
                        console.error('Error searching areas:', err);
                    } finally {
                        this.isSearching = false;
                    }
                },

                selectArea(area) {
                    this.selectedArea.province = area.province || '';
                    this.selectedArea.city = area.city || '';
                    this.selectedArea.district = area.district || '';
                    this.selectedArea.village = area.village || '';
                    this.selectedArea.postal_code = area.postal_code || '';
                    this.selectedArea.biteship_area_id = area.id || '';
                    this.selectedArea.label = area.label || '';

                    this.searchQuery = '';
                    this.showDropdown = false;

                    if (area.latitude && area.longitude && this.map && this.marker) {
                        const lat = parseFloat(area.latitude);
                        const lng = parseFloat(area.longitude);
                        this.latitude = lat.toFixed(7);
                        this.longitude = lng.toFixed(7);
                        this.marker.setLatLng([lat, lng]);
                        this.map.flyTo([lat, lng], 15, { duration: 1.2 });
                    } else if (this.map && this.marker) {
                        fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=1&q=${encodeURIComponent(area.label)}`)
                            .then(r => r.json())
                            .then(geo => {
                                if (geo && geo[0]) {
                                    const lat = parseFloat(geo[0].lat);
                                    const lng = parseFloat(geo[0].lon);
                                    this.latitude = lat.toFixed(7);
                                    this.longitude = lng.toFixed(7);
                                    this.marker.setLatLng([lat, lng]);
                                    this.map.flyTo([lat, lng], 15, { duration: 1.2 });
                                }
                            })
                            .catch(() => {});
                    }
                },

                async reverseGeocode(lat, lng, forceOverwrite = false) {
                    try {
                        const res = await fetch(`/geo/reverse-geocode?lat=${lat}&lng=${lng}`);
                        const data = await res.json();
                        if (data.success) {
                            if (forceOverwrite || !this.selectedArea.province) {
                                if (data.province) this.selectedArea.province = data.province;
                            }
                            if (forceOverwrite || !this.selectedArea.city) {
                                if (data.city) this.selectedArea.city = data.city;
                            }
                            if (forceOverwrite || !this.selectedArea.district) {
                                if (data.district) this.selectedArea.district = data.district;
                            }
                            if (forceOverwrite || !this.selectedArea.village) {
                                if (data.village) this.selectedArea.village = data.village;
                            }
                            if (forceOverwrite || !this.selectedArea.postal_code) {
                                if (data.postal_code) this.selectedArea.postal_code = data.postal_code;
                            }
                            if (data.biteship_area_id) {
                                this.selectedArea.biteship_area_id = data.biteship_area_id;
                            }
                            if (data.road && (forceOverwrite || !this.address)) {
                                this.address = data.road;
                            }
                        }
                    } catch (err) {
                        console.error('Reverse geocode error:', err);
                    }
                },

                useCurrentGps() {
                    if (!navigator.geolocation) {
                        alert('Fitur GPS tidak didukung oleh browser Anda.');
                        return;
                    }

                    this.isGpsLoading = true;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const lat = pos.coords.latitude;
                            const lng = pos.coords.longitude;
                            this.latitude = lat.toFixed(7);
                            this.longitude = lng.toFixed(7);
                            if (this.map && this.marker) {
                                this.marker.setLatLng([lat, lng]);
                                this.map.flyTo([lat, lng], 16, { duration: 1.2 });
                            }
                            this.reverseGeocode(lat, lng, true);
                            this.isGpsLoading = false;
                        },
                        (err) => {
                            this.isGpsLoading = false;
                            alert('Gagal mengambil lokasi GPS: ' + (err.message || 'Izin akses lokasi ditolak.'));
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                }
            };
        }
    </script>
@endsection
