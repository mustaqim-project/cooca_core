@extends('layouts.customer', ['title' => 'Pengaturan Profil & Buku Alamat'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6"
    x-data="{
        showAddressModal: false,
        isEditing: false,
        addressFormUrl: '{{ route('customer.addresses.store') }}',
        addressFormMethod: 'POST',
        modalTitle: 'Tambah Alamat Pengiriman Baru',
        geoLoading: false,
        geoStatus: '',
        areaSearchQuery: '',
        areaSearchResults: [],
        areaSearching: false,
        areaSearchOpen: false,
        map: null,
        marker: null,
        form: {
            id: '',
            label: 'Rumah',
            recipient_name: '{{ $customer->name }}',
            recipient_phone: '{{ $customer->phone }}',
            full_address: '',
            village: '',
            district: '',
            city: '',
            province: '',
            postal_code: '',
            biteship_area_id: '',
            latitude: '',
            longitude: '',
            notes: '',
            is_default: false
        },
        openAddModal() {
            this.isEditing = false;
            this.addressFormUrl = '{{ route('customer.addresses.store') }}';
            this.addressFormMethod = 'POST';
            this.modalTitle = 'Tambah Alamat Pengiriman Baru';
            this.geoStatus = '';
            this.areaSearchQuery = '';
            this.areaSearchResults = [];
            this.form = {
                id: '',
                label: 'Rumah',
                recipient_name: '{{ $customer->name }}',
                recipient_phone: '{{ $customer->phone }}',
                full_address: '',
                village: '',
                district: '',
                city: '',
                province: '',
                postal_code: '',
                biteship_area_id: '',
                latitude: '',
                longitude: '',
                notes: '',
                is_default: {{ $addresses->isEmpty() ? 'true' : 'false' }}
            };
            this.showAddressModal = true;
            this.$nextTick(() => {
                setTimeout(() => this.initLeafletMap(), 100);
            });
        },
        openEditModal(addr) {
            this.isEditing = true;
            this.addressFormUrl = '/customer/addresses/' + addr.id;
            this.addressFormMethod = 'PUT';
            this.modalTitle = 'Ubah Alamat Pengiriman';
            this.geoStatus = '';
            this.areaSearchQuery = '';
            this.areaSearchResults = [];
            this.form = {
                id: addr.id,
                label: addr.label || 'Rumah',
                recipient_name: addr.recipient_name || '{{ $customer->name }}',
                recipient_phone: addr.recipient_phone || '{{ $customer->phone }}',
                full_address: addr.full_address || '',
                village: addr.village || '',
                district: addr.district || '',
                city: addr.city || '',
                province: addr.province || '',
                postal_code: addr.postal_code || '',
                biteship_area_id: addr.biteship_area_id || '',
                latitude: addr.latitude || '',
                longitude: addr.longitude || '',
                notes: addr.notes || '',
                is_default: Boolean(addr.is_default)
            };
            this.showAddressModal = true;
            this.$nextTick(() => {
                setTimeout(() => this.initLeafletMap(), 100);
            });
        },
        initLeafletMap() {
            if (typeof L === 'undefined') {
                setTimeout(() => this.initLeafletMap(), 250);
                return;
            }
            const mapContainer = document.getElementById('profile-address-map');
            if (!mapContainer) return;

            const lat = parseFloat(this.form.latitude) || -6.2088;
            const lng = parseFloat(this.form.longitude) || 106.8456;
            const zoom = (this.form.latitude && this.form.longitude) ? 16 : 13;

            if (this.map) {
                this.map.invalidateSize();
                this.map.setView([lat, lng], zoom);
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                }
                setTimeout(() => {
                    if (this.map) this.map.invalidateSize();
                }, 200);
                return;
            }

            this.map = L.map('profile-address-map', {
                zoomControl: true,
                scrollWheelZoom: false
            }).setView([lat, lng], zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);

            const customIcon = L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="background-color: #007AFF; width: 34px; height: 34px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,0.35); border: 2.5px solid white;">
                         <div style="width: 10px; height: 10px; background-color: white; border-radius: 50%; transform: rotate(45deg);"></div>
                       </div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 34]
            });

            this.marker = L.marker([lat, lng], {
                draggable: true,
                icon: customIcon
            }).addTo(this.map);

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.form.latitude = Number(pos.lat.toFixed(7));
                this.form.longitude = Number(pos.lng.toFixed(7));
                this.reverseGeocode(pos.lat, pos.lng, true);
            });

            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                this.form.latitude = Number(e.latlng.lat.toFixed(7));
                this.form.longitude = Number(e.latlng.lng.toFixed(7));
                this.reverseGeocode(e.latlng.lat, e.latlng.lng, true);
            });

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 300);
        },
        async reverseGeocode(lat, lng, forceUpdate = true) {
            this.geoLoading = true;
            this.geoStatus = 'Mencari detail alamat dari titik pin GPS...';
            try {
                const res = await fetch(`/geo/reverse-geocode?lat=${lat}&lng=${lng}`);
                const data = await res.json();
                if (data.success) {
                    if (forceUpdate || !this.form.full_address) {
                        this.form.full_address = data.display_name || (data.road + (data.village ? ', ' + data.village : ''));
                    }
                    if (data.village) this.form.village = data.village;
                    if (data.district) this.form.district = data.district;
                    if (data.city) this.form.city = data.city;
                    if (data.province) this.form.province = data.province;
                    if (data.postal_code) this.form.postal_code = data.postal_code;
                    if (data.biteship_area_id) this.form.biteship_area_id = data.biteship_area_id;

                    this.geoStatus = 'Titik lokasi pin & Biteship API berhasil disinkronkan!';
                } else {
                    this.geoStatus = 'Koordinat GPS tersimpan (' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) + ').';
                }
            } catch (e) {
                this.geoStatus = 'Koordinat GPS tersimpan (' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) + ').';
            } finally {
                this.geoLoading = false;
            }
        },
        async getCurrentLocation() {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung layanan Geolocation GPS.');
                return;
            }
            this.geoLoading = true;
            this.geoStatus = 'Mendeteksi titik koordinat GPS...';
            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    const lat = Number(pos.coords.latitude.toFixed(7));
                    const lng = Number(pos.coords.longitude.toFixed(7));
                    this.form.latitude = lat;
                    this.form.longitude = lng;
                    if (this.map && this.marker) {
                        this.marker.setLatLng([lat, lng]);
                        this.map.flyTo([lat, lng], 16, { duration: 1.2 });
                    }
                    await this.reverseGeocode(lat, lng, true);
                },
                (err) => {
                    this.geoLoading = false;
                    this.geoStatus = 'Gagal mengakses GPS: ' + (err.message || 'Izin ditolak');
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },
        async searchBiteshipAreas() {
            const q = this.areaSearchQuery.trim();
            if (q.length < 2) {
                this.areaSearchResults = [];
                this.areaSearchOpen = false;
                return;
            }
            this.areaSearching = true;
            try {
                const res = await fetch(`/geo/search-areas?query=${encodeURIComponent(q)}`);
                const data = await res.json();
                if (data.success && Array.isArray(data.areas)) {
                    this.areaSearchResults = data.areas;
                    this.areaSearchOpen = true;
                }
            } catch (err) {
                console.error('Error searching areas:', err);
            } finally {
                this.areaSearching = false;
            }
        },
        selectArea(area) {
            this.form.biteship_area_id = area.id || '';
            this.form.village = area.village || area.administrative_division_level_4_name || '';
            this.form.district = area.district || area.administrative_division_level_3_name || '';
            this.form.city = area.city || area.administrative_division_level_2_name || '';
            this.form.province = area.province || area.administrative_division_level_1_name || '';
            this.form.postal_code = String(area.postal_code || '');
            if (area.latitude && area.longitude) {
                this.form.latitude = Number(area.latitude);
                this.form.longitude = Number(area.longitude);
                if (this.map && this.marker) {
                    this.marker.setLatLng([this.form.latitude, this.form.longitude]);
                    this.map.flyTo([this.form.latitude, this.form.longitude], 16, { duration: 1.2 });
                }
            } else if (this.map && this.marker) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=1&q=${encodeURIComponent(area.label || area.name)}`)
                    .then(r => r.json())
                    .then(geo => {
                        if (geo && geo[0]) {
                            const lat = Number(parseFloat(geo[0].lat).toFixed(7));
                            const lng = Number(parseFloat(geo[0].lon).toFixed(7));
                            this.form.latitude = lat;
                            this.form.longitude = lng;
                            this.marker.setLatLng([lat, lng]);
                            this.map.flyTo([lat, lng], 16, { duration: 1.2 });
                        }
                    })
                    .catch(() => {});
            }
            this.areaSearchQuery = '';
            this.areaSearchResults = [];
            this.areaSearchOpen = false;
        }
    }">

    {{-- Header --}}
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Pengaturan Profil &amp; Alamat</h1>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400">Kelola identitas akun, multi-alamat pengiriman dengan presisi GPS &amp; Biteship, serta kata sandi Anda.</p>
    </div>

    {{-- Feedback Alerts --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-center gap-2.5">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm">
            <div class="font-semibold mb-1 flex items-center gap-1.5">
                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                <span>Ada data yang perlu diperiksa:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 1. Profile & Contact Card --}}
    <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors">
        <form action="{{ route('customer.profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center gap-2 pb-1 border-b border-slate-200/80 dark:border-white/10">
                <span class="w-6 h-6 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-xs font-bold flex items-center justify-center">1</span>
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Data Diri &amp; Kontak</h2>
            </div>

            <div>
                <label for="profile_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                    Nama Lengkap <span class="text-[#FF3B30]">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <input type="text" name="name" id="profile_name" value="{{ old('name', $customer->name) }}" required
                        class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="profile_phone" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                        Nomor WhatsApp <span class="text-[#FF3B30]">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <input type="tel" name="phone" id="profile_phone" value="{{ old('phone', $customer->phone) }}" required inputmode="tel" autocomplete="tel"
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="profile_email" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Email Akun
                        </label>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500">Opsional</span>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" name="email" id="profile_email" value="{{ old('email', $customer->email) }}"
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs sm:text-sm shadow-sm transition active:scale-[0.99] cursor-pointer">
                    <span>Simpan Kontak</span>
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- 2. Multi-Address Book Section (Bento Apple HIG) --}}
    <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors space-y-5">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-xs font-bold flex items-center justify-center">2</span>
                <div>
                    <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Buku Alamat Pengiriman</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Dukungan banyak alamat (Rumah, Kantor, Toko) dengan koordinat GPS &amp; Biteship</p>
                </div>
            </div>
            <button type="button" @click="openAddModal()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#007AFF] hover:bg-[#006fe6] text-white text-xs font-semibold shadow-sm transition active:scale-[0.98] cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Alamat</span>
            </button>
        </div>

        {{-- Address Cards List --}}
        @if($addresses->isNotEmpty())
            <div class="grid grid-cols-1 gap-3.5">
                @foreach ($addresses as $addr)
                    <div class="p-4 sm:p-5 rounded-2xl border transition-all relative {{ $addr->is_default ? 'border-[#007AFF]/50 bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.06] shadow-sm' : 'border-slate-200 dark:border-white/10 bg-slate-50/60 dark:bg-white/[0.02]' }}">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-slate-300">
                                    {{ $addr->label }}
                                </span>
                                @if($addr->is_default)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 flex items-center gap-1">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        <span>Alamat Utama</span>
                                    </span>
                                @endif
                                @if($addr->biteship_area_id)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        Biteship Verified
                                    </span>
                                @endif
                                @if($addr->hasCoordinates())
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 flex items-center gap-0.5">
                                        <i data-lucide="navigation" class="w-2.5 h-2.5"></i>
                                        <span>GPS</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 pt-1 sm:pt-0">
                                @if(!$addr->is_default)
                                    <form action="{{ route('customer.addresses.default', $addr->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-[#007AFF] dark:text-[#0A84FF] hover:underline font-semibold cursor-pointer">
                                            Jadikan Utama
                                        </button>
                                    </form>
                                    <span class="text-slate-300 dark:text-white/20">•</span>
                                @endif
                                <button type="button" @click='openEditModal(@json($addr))'
                                    class="text-xs text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium cursor-pointer">
                                    Ubah
                                </button>
                                <span class="text-slate-300 dark:text-white/20">•</span>
                                <form action="{{ route('customer.addresses.destroy', $addr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="space-y-1 text-xs sm:text-sm text-slate-700 dark:text-slate-300">
                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span>{{ $addr->recipient_name }}</span>
                                <span class="text-slate-400 font-normal">({{ $addr->recipient_phone }})</span>
                            </div>
                            <p class="leading-relaxed text-slate-600 dark:text-slate-300">
                                {{ $addr->full_address }}
                            </p>
                            @if($addr->formatted_area)
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $addr->formatted_area }}
                                </p>
                            @endif
                            @if($addr->notes)
                                <div class="text-[11px] text-amber-600 dark:text-amber-400 italic pt-0.5">
                                    Catatan: {{ $addr->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center rounded-2xl border border-dashed border-slate-200 dark:border-white/10 space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-white/5 text-slate-400 mx-auto flex items-center justify-center">
                    <i data-lucide="map-pin" class="w-6 h-6"></i>
                </div>
                <div class="space-y-1">
                    <div class="font-semibold text-sm text-slate-800 dark:text-slate-200">Belum ada alamat pengiriman tersimpan</div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">Tambahkan alamat rumah atau kantor Anda untuk mempermudah pemesanan instan dengan kalkulasi kurir akurat.</p>
                </div>
                <button type="button" @click="openAddModal()"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#007AFF] hover:bg-[#006fe6] text-white text-xs font-semibold shadow-sm transition active:scale-[0.98] cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Alamat Sekarang</span>
                </button>
            </div>
        @endif
    </div>

    {{-- 3. Security & Password Card --}}
    <div class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-colors" x-data="{ changePass: false }">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 dark:border-white/10">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-xs font-bold flex items-center justify-center">3</span>
                <h2 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Keamanan &amp; Kata Sandi</h2>
            </div>
            <button type="button" @click="changePass = !changePass"
                class="text-xs sm:text-sm font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer"
                x-text="changePass ? 'Tutup' : 'Ubah Kata Sandi'">
            </button>
        </div>

        <form action="{{ route('customer.profile.update') }}" method="POST" x-show="changePass" x-cloak class="space-y-4 pt-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ $customer->name }}">
            <input type="hidden" name="phone" value="{{ $customer->phone }}">
            <input type="hidden" name="email" value="{{ $customer->email }}">

            <div>
                <label for="current_password" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                    Kata Sandi Saat Ini
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input type="password" name="current_password" id="current_password" required placeholder="Masukkan kata sandi saat ini"
                        class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white transition-all outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label for="new_password" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                        Kata Sandi Baru
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                        </div>
                        <input type="password" name="new_password" id="new_password" required minlength="6" placeholder="Min. 6 karakter"
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white transition-all outline-none">
                    </div>
                </div>
                <div>
                    <label for="new_password_confirmation" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                        Ulangi Kata Sandi Baru
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required minlength="6" placeholder="Ulangi kata sandi baru"
                            class="w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white transition-all outline-none">
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#007AFF] hover:bg-[#006fe6] text-white font-bold text-xs sm:text-sm shadow-sm transition active:scale-[0.99] cursor-pointer">
                <span>Update Kata Sandi</span>
                <i data-lucide="check" class="w-4 h-4"></i>
            </button>
        </form>
    </div>

    {{-- Interactive Address Modal (Apple Sheet Style) --}}
    <div x-show="showAddressModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div @click.away="showAddressModal = false"
            class="relative w-full max-w-lg bg-white dark:bg-[#151B2B] rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-white/10 my-8 space-y-5">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-white" x-text="modalTitle"></h3>
                </div>
                <button type="button" @click="showAddressModal = false"
                    class="w-8 h-8 rounded-full bg-slate-100 dark:bg-white/10 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Address Form --}}
            <form :action="addressFormUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                {{-- Label Selection --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                        Label Alamat
                    </label>
                    <div class="flex items-center gap-2 flex-wrap">
                        <template x-for="lbl in ['Rumah', 'Kantor', 'Apartemen', 'Toko', 'Gudang', 'Lainnya']" :key="lbl">
                            <button type="button" @click="form.label = lbl"
                                :class="form.label === lbl ? 'bg-[#007AFF] text-white font-bold' : 'bg-slate-100 dark:bg-white/5 text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                                x-text="lbl">
                            </button>
                        </template>
                        <input type="hidden" name="label" :value="form.label">
                    </div>
                </div>

                {{-- Recipient Information --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
                            Nama Penerima <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_name" x-model="form.recipient_name" required
                            placeholder="Nama Lengkap"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
                            Nomor WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="recipient_phone" x-model="form.recipient_phone" required
                            placeholder="081234567890"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                    </div>
                </div>

                {{-- 1-Click Geolocation GPS Trigger & Biteship Area Search --}}
                <div class="p-3.5 rounded-2xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <span class="text-xs font-bold text-blue-900 dark:text-blue-200 flex items-center gap-1.5">
                            <i data-lucide="navigation" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Presisi Lokasi &amp; Biteship API</span>
                        </span>
                        <button type="button" @click="getCurrentLocation()" :disabled="geoLoading"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#007AFF] hover:bg-[#006fe6] text-white text-xs font-semibold shadow-sm transition active:scale-[0.98] cursor-pointer disabled:opacity-50">
                            <template x-if="!geoLoading">
                                <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                            </template>
                            <template x-if="geoLoading">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                            </template>
                            <span>Gunakan Lokasi GPS Saya</span>
                        </button>
                    </div>

                    <div x-show="geoStatus" x-cloak class="text-[11px] text-blue-700 dark:text-blue-300 font-medium" x-text="geoStatus"></div>

                    {{-- Biteship Area Autocomplete Search --}}
                    <div class="relative">
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            Cari Kecamatan / Kota / Kode Pos (Biteship Autocomplete):
                        </label>
                        <div class="relative">
                            <input type="text" x-model="areaSearchQuery" @input.debounce.350ms="searchBiteshipAreas()"
                                placeholder="Ketik minimal 3 huruf (cth: Kebayoran, Dago, 12420)..."
                                class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1E2638] text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            </div>
                            <div x-show="areaSearching" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                            </div>
                        </div>

                        {{-- Autocomplete Dropdown List --}}
                        <div x-show="areaSearchOpen && areaSearchResults.length > 0" x-cloak
                            class="absolute z-20 left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-xl bg-white dark:bg-[#1E2638] border border-slate-200 dark:border-white/10 shadow-xl divide-y divide-slate-100 dark:divide-white/5">
                            <template x-for="item in areaSearchResults" :key="item.id || item.name">
                                <button type="button" @click="selectArea(item)"
                                    class="w-full px-3 py-2 text-left text-xs hover:bg-blue-50 dark:hover:bg-white/5 transition flex flex-col cursor-pointer">
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="item.name || item.label"></span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500" x-text="(item.district ? item.district + ', ' : '') + (item.city || '') + ' (' + (item.postal_code || '') + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Interactive Leaflet Address Map & Draggable Marker --}}
                    <div class="space-y-1.5 pt-1">
                        <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400">
                            <span class="flex items-center gap-1 font-semibold text-slate-800 dark:text-slate-200">
                                <i data-lucide="map" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                                <span>Titik Presisi Peta (Geser Pin / Klik Lokasi):</span>
                            </span>
                            <template x-if="form.latitude && form.longitude">
                                <span class="font-mono text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                    x-text="Number(form.latitude).toFixed(4) + ', ' + Number(form.longitude).toFixed(4)"></span>
                            </template>
                        </div>
                        <div class="relative w-full h-[200px] sm:h-[220px] rounded-2xl overflow-hidden border border-slate-200 dark:border-white/10 shadow-inner bg-slate-100 dark:bg-slate-900 z-0">
                            <div id="profile-address-map" class="w-full h-full"></div>
                            <div class="absolute bottom-2.5 left-2.5 right-2.5 sm:right-auto z-[400] pointer-events-none">
                                <div class="px-2.5 py-1 rounded-lg bg-white/95 dark:bg-[#151B2B]/95 backdrop-blur-md border border-slate-200 dark:border-white/10 text-[10px] font-semibold text-slate-800 dark:text-slate-200 shadow-lg flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    <span>Geser pin marker ke lokasi tujuan Anda</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Full Address Textarea --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
                        Alamat Lengkap (Nama Jalan, No. Rumah, RT/RW, Patokan) <span class="text-red-500">*</span>
                    </label>
                    <textarea name="full_address" x-model="form.full_address" rows="2" required
                        placeholder="Contoh: Jl. Kemang Raya No. 45, RT 02/RW 05 (Pagar Hitam samping minimarket)"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs sm:text-sm text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/20 resize-none"></textarea>
                </div>

                {{-- Area Fields Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-300 mb-0.5">Kelurahan</label>
                        <input type="text" name="village" x-model="form.village" placeholder="Kelurahan"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs text-slate-900 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-300 mb-0.5">Kecamatan</label>
                        <input type="text" name="district" x-model="form.district" placeholder="Kecamatan"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs text-slate-900 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-300 mb-0.5">Kota/Kab</label>
                        <input type="text" name="city" x-model="form.city" placeholder="Kota"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs text-slate-900 dark:text-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-600 dark:text-slate-300 mb-0.5">Kode Pos</label>
                        <input type="text" name="postal_code" x-model="form.postal_code" placeholder="12420"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs text-slate-900 dark:text-white outline-none">
                    </div>
                </div>

                {{-- Hidden System Coordinates & Area ID --}}
                <input type="hidden" name="biteship_area_id" :value="form.biteship_area_id">
                <input type="hidden" name="latitude" :value="form.latitude">
                <input type="hidden" name="longitude" :value="form.longitude">
                <input type="hidden" name="province" :value="form.province">

                {{-- Notes --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">
                        Catatan Kurir (Opsional)
                    </label>
                    <input type="text" name="notes" x-model="form.notes"
                        placeholder="Contoh: Titipkan di pos satpam atau telpon sebelum sampai"
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-[#1E2638] text-xs text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                </div>

                {{-- Default Checkbox --}}
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_default" value="1" id="modal_is_default" x-model="form.is_default"
                        class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF] border-slate-300 dark:border-white/20">
                    <label for="modal_is_default" class="text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                        Jadikan sebagai alamat pengiriman utama (default)
                    </label>
                </div>

                {{-- Modal Action CTA --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-white/10">
                    <button type="button" @click="showAddressModal = false"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-[#007AFF] hover:bg-[#006fe6] text-white text-xs font-bold shadow-md transition active:scale-[0.98] cursor-pointer">
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-container {
            font-family: inherit;
            z-index: 10;
        }
        .custom-map-marker {
            background: transparent;
            border: none;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

