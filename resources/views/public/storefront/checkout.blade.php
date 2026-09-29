@extends('public.storefront.layouts.app')

@php
    $checkoutTitle = 'Checkout Pesanan | ' . $business->name;
    $checkoutDesc = 'Selesaikan pemesanan produk dan layanan di ' . $business->name . ' dengan aman. Pembayaran mudah, konfirmasi instan, dan opsi pengiriman terpercaya.';
    $checkoutCanonical = url('/' . $business->slug . '/checkout');
    $checkoutOgImage = $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/seo/cooca-og-default.jpg')));
@endphp

@section('title', $checkoutTitle)
@section('description', $checkoutDesc)
@section('canonical', $checkoutCanonical)
@section('robots', 'noindex, follow')
@section('og_title', 'Checkout & Pembayaran Pesanan - ' . $business->name)
@section('og_description', $checkoutDesc)
@section('og_image', $checkoutOgImage)
@section('og_type', 'website')

@section('content')

    {{-- Checkout Configuration & Alpine Component --}}
    <script>
        window.__coocaCheckoutConfig = {
            checkoutUrl: @js(url('/' . $business->slug . '/checkout')),
            shippingCalculateUrl: @js(url('/' . $business->slug . '/shipping/calculate')),
            csrfToken: @js(csrf_token()),
            fulfillmentType: @js($storeSetting->allow_delivery ? 'delivery' : 'pickup'),
            customerName: @js(auth('customer')->user()?->name ?? ''),
            customerPhone: @js(auth('customer')->user()?->phone ?? ''),
            customerEmail: @js(auth('customer')->user()?->email ?? ''),
            shippingAddress: @js(auth('customer')->user()?->shipping_address ?? ''),
            selectedPickupLocationId: @js(isset($pickupLocations) ? ($pickupLocations->first()?->id ?? '') : ''),
            selectedPaymentMethod: @js($paymentMethods->first()?->id ?? ''),
            selectedShippingRuleId: @js($shippingRules->first()?->id ?? ''),
            shippingRules: @js($shippingRules ?? []),
            hasPickupLocations: @js(isset($pickupLocations) && $pickupLocations->isNotEmpty()),
            savedAddresses: @js($customerAddresses ?? []),
            defaultAddress: @js($customerAddresses->firstWhere('is_default', true) ?: $customerAddresses->first()),
            biteshipServiceFee: @js((float) (\App\Models\SystemSetting::get('biteship_service_fee') ?? config('services.biteship.service_fee', 1000))),
        };

        function storefrontCheckout(config) {
            config = config || window.__coocaCheckoutConfig || {};
            const savedAddrs = config.savedAddresses || [];
            const defaultAddr = config.defaultAddress || (savedAddrs.length > 0 ? savedAddrs[0] : null);

            return {
                fulfillmentType: config.fulfillmentType || 'delivery',
                savedAddresses: savedAddrs,
                selectedAddressId: defaultAddr ? defaultAddr.id : 'new',
                customerName: defaultAddr ? defaultAddr.recipient_name : (config.customerName || ''),
                customerPhone: defaultAddr ? defaultAddr.recipient_phone : (config.customerPhone || ''),
                customerEmail: config.customerEmail || '',
                shippingAddress: defaultAddr ? (defaultAddr.full_address + (defaultAddr.village ? ', ' + defaultAddr.village : '') + (defaultAddr.district ? ', ' + defaultAddr.district : '') + (defaultAddr.city ? ', ' + defaultAddr.city : '') + (defaultAddr.postal_code ? ' ' + defaultAddr.postal_code : '')) : (config.shippingAddress || ''),
                destinationPostalCode: defaultAddr ? (defaultAddr.postal_code || '') : '',
                biteshipAreaId: defaultAddr ? (defaultAddr.biteship_area_id || '') : '',
                latitude: defaultAddr ? defaultAddr.latitude : null,
                longitude: defaultAddr ? defaultAddr.longitude : null,
                saveAddressToBook: true,
                addressLabel: 'Rumah',
                notes: '',
                selectedPickupLocationId: config.selectedPickupLocationId || '',
                selectedPaymentMethod: config.selectedPaymentMethod || '',
                paymentGateway: 'tripay',
                paymentChannel: 'QRIS',
                shippingCost: 0,
                serviceFee: config.biteshipServiceFee || 1000,
                selectedShippingRuleId: config.selectedShippingRuleId || '',
                selectedShippingOptionId: config.selectedShippingRuleId || '',
                selectedCourierCode: '',
                selectedCourierService: '',
                selectedCourierName: '',
                shippingOptions: [],
                shippingLoading: false,
                totalPackageWeight: 250,
                isSubmitting: false,
                errorMessage: '',
                shippingRules: config.shippingRules || [],
                geoLoading: false,
                geoStatus: '',
                areaSearchQuery: '',
                areaSearchResults: [],
                areaSearching: false,
                areaSearchOpen: false,
                map: null,
                marker: null,

                init() {
                    this.fetchShippingRates();
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        this.initLeafletMap();
                    });
                    this.$watch('$store.cart.items', () => {
                        this.fetchShippingRates();
                    });
                },

                initLeafletMap() {
                    if (this.fulfillmentType !== 'delivery') return;
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initLeafletMap(), 250);
                        return;
                    }

                    const mapContainer = document.getElementById('checkout-delivery-map');
                    if (!mapContainer) return;

                    if (this.map) {
                        setTimeout(() => {
                            if (this.map) this.map.invalidateSize();
                        }, 200);
                        return;
                    }

                    const defaultLat = parseFloat(this.latitude) || -6.2088;
                    const defaultLng = parseFloat(this.longitude) || 106.8456;
                    const defaultZoom = this.latitude ? 16 : 13;

                    this.map = L.map('checkout-delivery-map', {
                        zoomControl: true,
                        scrollWheelZoom: false
                    }).setView([defaultLat, defaultLng], defaultZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    const customIcon = L.divIcon({
                        className: 'custom-map-marker',
                        html: `<div style="background-color: var(--theme-primary, #007AFF); width: 34px; height: 34px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,0.35); border: 2.5px solid white;">
                                 <div style="width: 10px; height: 10px; background-color: white; border-radius: 50%; transform: rotate(45deg);"></div>
                               </div>`,
                        iconSize: [34, 34],
                        iconAnchor: [17, 34]
                    });

                    this.marker = L.marker([defaultLat, defaultLng], {
                        draggable: true,
                        icon: customIcon
                    }).addTo(this.map);

                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.latitude = Number(pos.lat.toFixed(7));
                        this.longitude = Number(pos.lng.toFixed(7));
                        this.reverseGeocode(pos.lat, pos.lng, true);
                    });

                    this.map.on('click', (e) => {
                        this.marker.setLatLng(e.latlng);
                        this.latitude = Number(e.latlng.lat.toFixed(7));
                        this.longitude = Number(e.latlng.lng.toFixed(7));
                        this.reverseGeocode(e.latlng.lat, e.latlng.lng, true);
                    });

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 400);
                },

                async reverseGeocode(lat, lng, forceUpdate = true) {
                    this.geoLoading = true;
                    this.geoStatus = 'Mencari detail alamat dari titik pin GPS...';
                    try {
                        const res = await fetch(`/geo/reverse-geocode?lat=${lat}&lng=${lng}`);
                        const data = await res.json();
                        if (data.success) {
                            const formatted = data.display_name || (data.road + (data.village ? ', ' + data.village : '') + (data.district ? ', ' + data.district : '') + (data.city ? ', ' + data.city : ''));
                            if (forceUpdate || !this.shippingAddress) {
                                this.shippingAddress = formatted;
                            }
                            if (data.postal_code) this.destinationPostalCode = data.postal_code;
                            if (data.biteship_area_id) this.biteshipAreaId = data.biteship_area_id;
                            this.geoStatus = 'Titik pengiriman presisi terverifikasi.';
                        } else {
                            this.geoStatus = 'Titik koordinat GPS tersimpan (' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) + ').';
                        }
                    } catch (e) {
                        this.geoStatus = 'Koordinat GPS tersimpan (' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5) + ').';
                    } finally {
                        this.geoLoading = false;
                        this.fetchShippingRates();
                    }
                },

                selectSavedAddress(addr) {
                    if (addr === 'new') {
                        this.selectedAddressId = 'new';
                        this.shippingAddress = '';
                        this.destinationPostalCode = '';
                        this.biteshipAreaId = '';
                        this.latitude = null;
                        this.longitude = null;
                    } else {
                        this.selectedAddressId = addr.id;
                        this.customerName = addr.recipient_name || this.customerName;
                        this.customerPhone = addr.recipient_phone || this.customerPhone;
                        this.shippingAddress = addr.full_address + (addr.village ? ', ' + addr.village : '') + (addr.district ? ', ' + addr.district : '') + (addr.city ? ', ' + addr.city : '') + (addr.postal_code ? ' ' + addr.postal_code : '');
                        this.destinationPostalCode = addr.postal_code || '';
                        this.biteshipAreaId = addr.biteship_area_id || '';
                        this.latitude = addr.latitude ? Number(addr.latitude) : null;
                        this.longitude = addr.longitude ? Number(addr.longitude) : null;

                        if (this.latitude && this.longitude && this.map && this.marker) {
                            this.marker.setLatLng([this.latitude, this.longitude]);
                            this.map.flyTo([this.latitude, this.longitude], 16, { duration: 1.2 });
                        }
                    }
                    this.fetchShippingRates();
                    this.$nextTick(() => {
                        this.initLeafletMap();
                    });
                },

                async getCurrentLocation() {
                    if (!navigator.geolocation) {
                        alert('Browser Anda tidak mendukung layanan Geolocation GPS.');
                        return;
                    }
                    this.geoLoading = true;
                    this.geoStatus = 'Mendeteksi titik koordinat GPS perangkat Anda...';
                    navigator.geolocation.getCurrentPosition(
                        async (pos) => {
                            const lat = Number(pos.coords.latitude.toFixed(7));
                            const lng = Number(pos.coords.longitude.toFixed(7));
                            this.latitude = lat;
                            this.longitude = lng;
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

                selectBiteshipArea(area) {
                    this.biteshipAreaId = area.id || '';
                    this.destinationPostalCode = String(area.postal_code || '');
                    if (area.latitude && area.longitude) {
                        this.latitude = Number(area.latitude);
                        this.longitude = Number(area.longitude);
                        if (this.map && this.marker) {
                            this.marker.setLatLng([this.latitude, this.longitude]);
                            this.map.flyTo([this.latitude, this.longitude], 16, { duration: 1.2 });
                        }
                    } else if (this.map && this.marker) {
                        fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=1&q=${encodeURIComponent(area.label || area.name)}`)
                            .then(r => r.json())
                            .then(geo => {
                                if (geo && geo[0]) {
                                    const lat = Number(parseFloat(geo[0].lat).toFixed(7));
                                    const lng = Number(parseFloat(geo[0].lon).toFixed(7));
                                    this.latitude = lat;
                                    this.longitude = lng;
                                    this.marker.setLatLng([lat, lng]);
                                    this.map.flyTo([lat, lng], 16, { duration: 1.2 });
                                }
                            })
                            .catch(() => {});
                    }

                    if (!this.shippingAddress) {
                        this.shippingAddress = area.name || area.label || '';
                    } else if (!this.shippingAddress.includes(area.postal_code)) {
                        this.shippingAddress += ', ' + (area.name || area.label || '');
                    }
                    this.areaSearchQuery = '';
                    this.areaSearchResults = [];
                    this.areaSearchOpen = false;
                    this.fetchShippingRates();
                },

                async fetchShippingRates() {
                    if (this.fulfillmentType !== 'delivery') {
                        this.shippingCost = 0;
                        this.shippingOptions = [];
                        return;
                    }

                    const cart = this.$store ? this.$store.cart : null;
                    const subtotal = cart ? cart.subtotal() : 0;
                    const items = cart && cart.items ? cart.items.map(i => ({
                        product_id: i.id,
                        quantity: i.quantity,
                        price: i.price,
                        name: i.name
                    })) : [];

                    if (!this.destinationPostalCode && !this.latitude && !this.shippingAddress) {
                        this.calculateLegacyShipping();
                        return;
                    }

                    this.shippingLoading = true;
                    try {
                        const res = await fetch(config.shippingCalculateUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify({
                                subtotal: subtotal,
                                destination_postal_code: this.destinationPostalCode || null,
                                shipping_address: this.shippingAddress || null,
                                latitude: this.latitude,
                                longitude: this.longitude,
                                shipping_rule_id: this.selectedShippingOptionId || this.selectedShippingRuleId || null,
                                items: items
                            })
                        });

                        const json = await res.json();
                        if (json.success && json.data) {
                            const data = json.data;
                            this.shippingOptions = data.options || [];
                            this.totalPackageWeight = data.total_weight_grams || 250;
                            if (data.service_fee !== undefined) {
                                this.serviceFee = parseFloat(data.service_fee);
                            }

                            if (this.shippingOptions.length > 0) {
                                let chosen = this.shippingOptions.find(o => String(o.id) === String(this.selectedShippingOptionId));
                                if (!chosen) {
                                    chosen = this.shippingOptions.find(o => String(o.id) === String(data.applied_rule_id)) || this.shippingOptions[0];
                                }
                                this.selectCourierOption(chosen);
                            } else if (this.shippingRules && this.shippingRules.length > 0) {
                                this.calculateLegacyShipping();
                            } else {
                                this.shippingCost = parseFloat(data.shipping_fee || 0);
                            }
                        } else {
                            this.calculateLegacyShipping();
                        }
                    } catch (e) {
                        console.warn('Error fetching shipping rates:', e);
                        this.calculateLegacyShipping();
                    } finally {
                        this.shippingLoading = false;
                        this.$nextTick(() => {
                            if (typeof lucide !== 'undefined') lucide.createIcons();
                        });
                    }
                },

                selectCourierOption(opt) {
                    if (!opt) return;
                    this.selectedShippingOptionId = opt.id;
                    this.selectedShippingRuleId = opt.id;
                    this.shippingCost = parseFloat(opt.fee || 0);
                    this.selectedCourierCode = opt.courier_code || '';
                    this.selectedCourierService = opt.courier_service_code || '';
                    this.selectedCourierName = (opt.courier_name ? opt.courier_name + ' ' : '') + (opt.courier_service_name || opt.name || '');
                },

                calculateLegacyShipping() {
                    const rule = this.shippingRules.find(r => String(r.id) === String(this.selectedShippingRuleId));
                    if (rule) {
                        this.shippingCost = parseFloat(rule.rate_amount || rule.rate || 0);
                        this.selectedCourierName = rule.name;
                    } else {
                        this.shippingCost = 0;
                    }
                },

                calculateShipping() {
                    this.fetchShippingRates();
                },

                getCourierBadgeClass(code) {
                    code = (code || '').toLowerCase();
                    if (code === 'jne') return 'bg-red-100 text-red-700 dark:bg-red-950/80 dark:text-red-300 border border-red-200 dark:border-red-800/50';
                    if (code === 'sicepat') return 'bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300 border border-red-200 dark:border-red-800/50';
                    if (code === 'jnt') return 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-200 dark:border-rose-800/50';
                    if (code === 'anteraja') return 'bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300 border border-purple-200 dark:border-purple-800/50';
                    if (code === 'gosend') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50';
                    if (code === 'grab') return 'bg-green-100 text-green-800 dark:bg-green-950/80 dark:text-green-300 border border-green-200 dark:border-green-800/50';
                    return 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700';
                },

                grandTotal() {
                    const cart = this.$store ? this.$store.cart : null;
                    const sub = cart ? cart.subtotal() : 0;
                    const ship = this.fulfillmentType === 'delivery' ? this.shippingCost : 0;
                    const fee = this.fulfillmentType === 'delivery' ? this.serviceFee : 0;
                    return Math.max(0, sub + ship + fee);
                },

                async submitOrder() {
                    const cart = this.$store ? this.$store.cart : null;
                    if (!cart || cart.items.length === 0) {
                        alert(window.COOCA_I18N?.cart_empty || 'Keranjang belanja Anda masih kosong.');
                        return;
                    }
                    if (!this.customerName || !this.customerPhone) {
                        alert('Nama dan Nomor WhatsApp wajib diisi.');
                        return;
                    }
                    if (this.fulfillmentType === 'delivery' && !this.shippingAddress) {
                        alert('Alamat pengiriman wajib diisi untuk pesanan antar/delivery.');
                        return;
                    }
                    if (this.fulfillmentType === 'pickup' && !this.selectedPickupLocationId && config.hasPickupLocations) {
                        alert('Silakan pilih cabang / outlet untuk pengambilan pesanan.');
                        return;
                    }

                    this.isSubmitting = true;
                    this.errorMessage = '';

                    const payload = {
                        _token: config.csrfToken,
                        customer_name: this.customerName,
                        customer_phone: this.customerPhone,
                        customer_email: this.customerEmail,
                        shipping_address: this.shippingAddress,
                        destination_postal_code: this.destinationPostalCode || null,
                        destination_latitude: this.latitude,
                        destination_longitude: this.longitude,
                        biteship_area_id: this.biteshipAreaId || null,
                        destination_area_id: this.biteshipAreaId || null,
                        save_to_address_book: this.selectedAddressId === 'new' ? this.saveAddressToBook : false,
                        address_label: this.addressLabel || 'Rumah',
                        notes: this.notes,
                        fulfillment_type: this.fulfillmentType === 'delivery' ? 'merchant_delivery' : 'pickup',
                        pickup_location_id: this.fulfillmentType === 'pickup' ? this.selectedPickupLocationId : null,
                        pos_table_id: null,
                        shipping_rule_id: this.fulfillmentType === 'delivery' ? (this.selectedShippingOptionId || this.selectedShippingRuleId) : null,
                        shipping_fee: this.fulfillmentType === 'delivery' ? this.shippingCost : 0,
                        courier_company: this.fulfillmentType === 'delivery' ? this.selectedCourierCode : null,
                        courier_type: this.fulfillmentType === 'delivery' ? this.selectedCourierService : null,
                        courier_name: this.fulfillmentType === 'delivery' ? this.selectedCourierName : null,
                        biteship_service_fee: this.fulfillmentType === 'delivery' ? this.serviceFee : 0,
                        payment_gateway: 'tripay',
                        payment_channel: 'QRIS',
                        payment_method_id: this.selectedPaymentMethod || null,
                        items: cart.items.map(i => ({
                            product_id: i.id,
                            quantity: i.quantity,
                            notes: i.notes || ''
                        }))
                    };

                    try {
                        const res = await fetch(config.checkoutUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();

                        if (data.success && data.order && data.order.tracking_url) {
                            cart.clear();
                            window.location.href = data.order.tracking_url;
                        } else {
                            this.errorMessage = data.message || 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.';
                            this.isSubmitting = false;
                        }
                    } catch (err) {
                        this.errorMessage = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
                        this.isSubmitting = false;
                    }
                }
            };
        }

        document.addEventListener('alpine:init', () => {
            if (typeof Alpine !== 'undefined' && Alpine.data) {
                Alpine.data('storefrontCheckout', (config) => storefrontCheckout(config));
            }
        });
    </script>

    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" x-data="storefrontCheckout(window.__coocaCheckoutConfig)">

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 text-xs text-neutral-500 mb-2">
                <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
                <span>/</span>
                <a href="{{ url('/' . $business->slug . '/katalog') }}"
                    class="hover:text-theme-primary transition">Katalog</a>
                <span>/</span>
                <span class="text-neutral-900 dark:text-white font-medium">Checkout Pembayaran</span>
            </div>
            <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-neutral-900 dark:text-white">
                Checkout Pesanan
            </h1>
            <p class="text-sm text-neutral-500">Lengkapi detail pengiriman dan selesaikan pembayaran pesanan Anda secara aman.</p>
        </div>

        {{-- Error Notice --}}
        <div x-show="errorMessage" x-cloak
            class="mb-6 p-4 rounded-[12px] bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span x-text="errorMessage"></span>
        </div>

        {{-- Empty Cart Guard --}}
        <div x-show="$store.cart.count() === 0" x-cloak class="py-16 text-center space-y-4">
            <div
                class="w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-400 mx-auto flex items-center justify-center">
                <i data-lucide="shopping-bag" class="w-8 h-8"></i>
            </div>
            <h2 class="font-heading font-bold text-xl text-neutral-800 dark:text-neutral-200">
                Keranjang belanja Anda masih kosong
            </h2>
            <p class="text-sm text-neutral-500 max-w-sm mx-auto">
                Silakan pilih produk favorit Anda dari katalog untuk melanjutkan ke proses pembayaran.
            </p>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-[12px] theme-btn-primary font-semibold text-sm shadow-md min-h-[44px]">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Mulai Belanja</span>
            </a>
        </div>

        {{-- Verified Customer Identity Badge --}}
        @if (auth('customer')->check())
            <div x-show="$store.cart.count() > 0"
                class="mb-6 p-4 rounded-[16px] bg-blue-50/70 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-neutral-900 dark:text-white flex items-center gap-1.5">
                            <span>{{ auth('customer')->user()->name }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">Akun Terverifikasi</span>
                        </div>
                        <div class="text-[11px] text-neutral-500">
                            WhatsApp: {{ auth('customer')->user()->phone }} • Email: {{ auth('customer')->user()->email ?: 'Belum terhubung' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('customer.dashboard') }}"
                        class="px-3 py-1.5 rounded-lg border border-black/10 dark:border-white/10 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/5 transition flex items-center gap-1">
                        <span>Portal Pelanggan</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        @endif

        {{-- Dedicated 2-Column Standalone Checkout --}}
        <div x-show="$store.cart.count() > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

                {{-- ================================================================= --}}
                {{-- LEFT COLUMN: FORM DETAIL PENGIRIMAN & PEMBAYARAN                  --}}
                {{-- ================================================================= --}}
                <div class="lg:col-span-7 space-y-6">

                    {{-- Step 1: Tipe Pesanan / Pemenuhan (Delivery & Pickup only) --}}
                    <div
                        class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                        <div
                            class="flex items-center gap-2 font-heading font-bold text-base text-neutral-900 dark:text-white">
                            <span
                                class="w-6 h-6 rounded-[8px] bg-theme-primary text-white text-xs flex items-center justify-center">1</span>
                            <span>Metode Penerimaan Pesanan</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            @if ($storeSetting->allow_delivery)
                                <button type="button" @click="fulfillmentType = 'delivery'; calculateShipping()"
                                    :class="fulfillmentType === 'delivery' ?
                                        'border-theme-primary bg-theme-primary/5 text-theme-primary font-bold shadow-sm' :
                                        'border-black/10 dark:border-white/10 text-neutral-600 dark:text-neutral-300 hover:border-black/20'"
                                    class="p-4 rounded-[14px] border text-center text-xs flex flex-col items-center gap-2 transition min-h-[72px]">
                                    <i data-lucide="truck" class="w-5 h-5"></i>
                                    <span>Kirim ke Alamat</span>
                                </button>
                            @endif

                            @if ($storeSetting->allow_pickup)
                                <button type="button" @click="fulfillmentType = 'pickup'; calculateShipping()"
                                    :class="fulfillmentType === 'pickup' ?
                                        'border-theme-primary bg-theme-primary/5 text-theme-primary font-bold shadow-sm' :
                                        'border-black/10 dark:border-white/10 text-neutral-600 dark:text-neutral-300 hover:border-black/20'"
                                    class="p-4 rounded-[14px] border text-center text-xs flex flex-col items-center gap-2 transition min-h-[72px]">
                                    <i data-lucide="store" class="w-5 h-5"></i>
                                    <span>Ambil di Toko</span>
                                </button>
                            @endif
                        </div>

                        {{-- Store Pickup Location Selector --}}
                        <div x-show="fulfillmentType === 'pickup'" x-cloak class="pt-2">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Pilih Lokasi Outlet / Cabang Pengambilan <span class="text-red-500">*</span>
                            </label>
                            @if (isset($pickupLocations) && $pickupLocations->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach ($pickupLocations as $pLoc)
                                        <label
                                            class="flex items-start gap-3 p-3 rounded-[12px] border transition-all cursor-pointer text-left"
                                            :class="selectedPickupLocationId === '{{ $pLoc->id }}' ?
                                                'border-theme-primary bg-theme-primary/5 ring-1 ring-theme-primary' :
                                                'border-black/10 dark:border-white/10 hover:border-black/20 bg-neutral-50/50 dark:bg-neutral-900/50'">
                                            <input type="radio" name="pickup_location" value="{{ $pLoc->id }}"
                                                x-model="selectedPickupLocationId"
                                                class="mt-1 text-theme-primary focus:ring-theme-primary">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="font-semibold text-xs text-neutral-900 dark:text-white">{{ $pLoc->name }}</span>
                                                    @if ($pLoc->is_primary)
                                                        <span
                                                            class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Pusat</span>
                                                    @endif
                                                </div>
                                                @if ($pLoc->address)
                                                    <p
                                                        class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-1">
                                                        {{ $pLoc->address }}</p>
                                                @endif
                                                @if ($pLoc->phone)
                                                    <p class="text-[10px] text-neutral-400 dark:text-neutral-500 mt-0.5">
                                                        Telp/WA: {{ $pLoc->phone }}</p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="p-3 bg-neutral-100 dark:bg-neutral-900 rounded-[12px] text-xs text-neutral-600 dark:text-neutral-400">
                                    Pengambilan langsung di toko / outlet utama.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Step 2: Customer Identity & Shipping Address --}}
                    <div
                        class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                        <div
                            class="flex items-center gap-2 font-heading font-bold text-base text-neutral-900 dark:text-white">
                            <span
                                class="w-6 h-6 rounded-[8px] bg-theme-primary text-white text-xs flex items-center justify-center">2</span>
                            <span>Informasi Pembeli &amp; Pengiriman</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="customerName" required placeholder="Contoh: Budi Santoso"
                                    class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                    Nomor WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" x-model="customerPhone" required placeholder="081234567890"
                                    class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                    Email (Opsional untuk bukti digital)
                                </label>
                                <input type="email" x-model="customerEmail" placeholder="nama@email.com"
                                    class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                            </div>

                            {{-- Multi-Address & Geolocation Delivery Section --}}
                            <div x-show="fulfillmentType === 'delivery'" class="sm:col-span-2 space-y-4 pt-1">
                                
                                {{-- Saved Addresses Selector (if available) --}}
                                <template x-if="savedAddresses.length > 0">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <label class="block text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                                Pilih Alamat Tersimpan
                                            </label>
                                            <span class="text-[11px] text-neutral-500" x-text="savedAddresses.length + ' Alamat tersimpan'"></span>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            <template x-for="addr in savedAddresses" :key="addr.id">
                                                <div @click="selectSavedAddress(addr)"
                                                    class="p-3.5 rounded-[14px] border transition-all cursor-pointer text-left relative"
                                                    :class="selectedAddressId === addr.id ?
                                                        'border-theme-primary bg-theme-primary/5 ring-2 ring-theme-primary/30 shadow-sm' :
                                                        'border-black/10 dark:border-white/10 hover:border-black/20 bg-neutral-50/50 dark:bg-neutral-900/50'">
                                                    <div class="flex items-start justify-between gap-2 mb-1.5">
                                                        <div class="flex items-center gap-1.5 flex-wrap">
                                                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-neutral-200 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200"
                                                                x-text="addr.label || 'Alamat'"></span>
                                                            <template x-if="addr.is_default">
                                                                <span class="px-1.5 py-0.5 rounded-[6px] text-[9px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                                    Utama
                                                                </span>
                                                            </template>
                                                        </div>
                                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0 mt-0.5"
                                                            :class="selectedAddressId === addr.id ? 'border-theme-primary bg-theme-primary text-white' : 'border-neutral-300 dark:border-neutral-600'">
                                                            <template x-if="selectedAddressId === addr.id">
                                                                <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <div class="text-xs font-semibold text-neutral-900 dark:text-white truncate"
                                                        x-text="(addr.recipient_name || customerName) + ' • ' + (addr.recipient_phone || customerPhone)"></div>
                                                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 line-clamp-2 leading-relaxed"
                                                        x-text="addr.full_address + (addr.village ? ', ' + addr.village : '') + (addr.district ? ', ' + addr.district : '') + (addr.city ? ', ' + addr.city : '') + (addr.postal_code ? ' ' + addr.postal_code : '')"></p>

                                                    <template x-if="addr.latitude && addr.longitude">
                                                        <div class="mt-2 flex items-center gap-1 text-[10px] font-medium text-emerald-600 dark:text-emerald-400">
                                                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                            <span>GPS Akurat &amp; Biteship Terhubung</span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- Option for new address --}}
                                            <div @click="selectSavedAddress('new')"
                                                class="p-3.5 rounded-[14px] border-2 border-dashed transition-all cursor-pointer flex flex-col items-center justify-center text-center min-h-[90px]"
                                                :class="selectedAddressId === 'new' ?
                                                    'border-theme-primary bg-theme-primary/5 text-theme-primary font-semibold' :
                                                    'border-neutral-300 dark:border-neutral-700 text-neutral-500 hover:border-neutral-400'">
                                                <i data-lucide="plus-circle" class="w-5 h-5 mb-1"></i>
                                                <span class="text-xs">+ Gunakan Alamat Lain / Baru</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Input Manual / GPS Geolocation / Biteship Search for Address --}}
                                <div x-show="selectedAddressId === 'new' || savedAddresses.length === 0" class="space-y-3 pt-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <label class="block text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                            Alamat Lengkap Pengiriman <span class="text-red-500">*</span>
                                        </label>

                                        {{-- 1-Klik GPS Geolocation Button --}}
                                        <button type="button" @click="getCurrentLocation()" :disabled="geoLoading"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800/60 border border-emerald-200 transition active:scale-[0.98] self-start sm:self-auto">
                                            <template x-if="!geoLoading">
                                                <div class="flex items-center gap-1.5">
                                                    <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                                                    <span>Deteksi Lokasi GPS Saya</span>
                                                </div>
                                            </template>
                                            <template x-if="geoLoading">
                                                <div class="flex items-center gap-1.5">
                                                    <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                                                    <span>Mencari Koordinat...</span>
                                                </div>
                                            </template>
                                        </button>
                                    </div>

                                    <template x-if="geoStatus">
                                        <div class="p-2.5 rounded-[10px] text-[11px] leading-snug flex items-center gap-2"
                                            :class="latitude ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50' : 'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800/50'">
                                            <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                                            <span x-text="geoStatus"></span>
                                        </div>
                                    </template>

                                    {{-- Biteship Area Autocomplete Search --}}
                                    <div class="relative">
                                        <div class="relative">
                                            <input type="text" x-model="areaSearchQuery"
                                                @input.debounce.300ms="searchBiteshipAreas()"
                                                @focus="if(areaSearchResults.length > 0) areaSearchOpen = true"
                                                placeholder="Cari Kelurahan / Kecamatan / Kota / Kode Pos via Biteship..."
                                                class="w-full pl-9 pr-9 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-xs focus:ring-2 focus:ring-theme-primary">
                                            <i data-lucide="search" class="w-4 h-4 text-neutral-400 absolute left-3 top-3"></i>
                                            <div x-show="areaSearching" class="absolute right-3 top-3">
                                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-theme-primary"></i>
                                            </div>
                                        </div>

                                        {{-- Autocomplete Dropdown List --}}
                                        <div x-show="areaSearchOpen && areaSearchResults.length > 0"
                                            @click.outside="areaSearchOpen = false"
                                            class="absolute z-30 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-[14px] bg-white dark:bg-neutral-800 border border-black/10 dark:border-white/10 shadow-xl divide-y divide-black/5 dark:divide-white/5">
                                            <template x-for="item in areaSearchResults" :key="item.id">
                                                <button type="button" @click="selectBiteshipArea(item)"
                                                    class="w-full text-left px-3.5 py-2.5 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition flex items-center justify-between gap-2">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="text-xs font-semibold text-neutral-900 dark:text-white"
                                                            x-text="item.name || item.label"></div>
                                                        <div class="text-[10px] text-neutral-500"
                                                            x-text="(item.administrative_division_level_2_name || item.city || '') + ' • Kode Pos: ' + (item.postal_code || '-')"></div>
                                                    </div>
                                                    <span class="text-[10px] font-bold text-theme-primary shrink-0">Pilih</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Interactive Leaflet Delivery Map & Draggable Pin --}}
                                    <div class="space-y-1.5 pt-1">
                                        <div class="flex items-center justify-between text-[11px] text-neutral-600 dark:text-neutral-400">
                                            <span class="flex items-center gap-1 font-semibold text-neutral-800 dark:text-neutral-200">
                                                <i data-lucide="map" class="w-3.5 h-3.5 text-theme-primary"></i>
                                                <span>Titik Presisi Lokasi (Geser Pin / Klik Peta):</span>
                                            </span>
                                            <template x-if="latitude && longitude">
                                                <span class="font-mono text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                                    x-text="Number(latitude).toFixed(4) + ', ' + Number(longitude).toFixed(4)"></span>
                                            </template>
                                        </div>
                                        <div class="relative w-full h-[220px] sm:h-[250px] rounded-[16px] overflow-hidden border border-black/10 dark:border-white/10 shadow-inner bg-neutral-100 dark:bg-neutral-900 z-0">
                                            <div id="checkout-delivery-map" class="w-full h-full"></div>
                                            {{-- Floating Overlay Instructions --}}
                                            <div class="absolute bottom-2.5 left-2.5 right-2.5 sm:right-auto z-[400] pointer-events-none">
                                                <div class="px-3 py-1.5 rounded-[10px] bg-white/95 dark:bg-neutral-900/95 backdrop-blur-md border border-black/10 dark:border-white/10 text-[10.5px] font-semibold text-neutral-800 dark:text-neutral-200 shadow-lg flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                                    <span>Tarik pin marker ke titik pintu / pagar rumah Anda</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Full Address Textarea --}}
                                    <div>
                                        <label class="block text-[11px] font-medium text-neutral-600 dark:text-neutral-400 mb-1">
                                            Detail Alamat (Jalan, RT/RW, Patokan):
                                        </label>
                                        <textarea x-model="shippingAddress" rows="3" required
                                            placeholder="Detail jalan, nomor bangunan, RT/RW, patokan lokasi..."
                                            class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary leading-relaxed"></textarea>
                                    </div>

                                    {{-- Verified GPS Coordinates Badge (Without exposing raw internal IDs) --}}
                                    <div class="flex flex-wrap items-center gap-2 text-[11px]" x-show="latitude">
                                        <template x-if="latitude && longitude">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 font-mono text-[10px]">
                                                <i data-lucide="map-pin" class="w-3 h-3 text-emerald-600"></i>
                                                <span x-text="'GPS: ' + Number(latitude).toFixed(5) + ', ' + Number(longitude).toFixed(5)"></span>
                                            </span>
                                        </template>
                                    </div>

                                    {{-- Save to Address Book Options --}}
                                    <div class="p-3 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/60 border border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                            <input type="checkbox" x-model="saveAddressToBook"
                                                class="rounded border-neutral-300 text-theme-primary focus:ring-theme-primary">
                                            <span>Simpan ke Buku Alamat Saya</span>
                                        </label>

                                        <div x-show="saveAddressToBook" class="flex items-center gap-2">
                                            <span class="text-[11px] text-neutral-500">Label:</span>
                                            <select x-model="addressLabel"
                                                class="px-2 py-1 rounded-[8px] text-xs border border-black/10 dark:border-white/10 bg-white dark:bg-neutral-800">
                                                <option value="Rumah">Rumah</option>
                                                <option value="Kantor">Kantor</option>
                                                <option value="Apartemen">Apartemen</option>
                                                <option value="Toko">Toko</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Interactive Courier & Shipping Rates Selection (Bento Apple HIG) --}}
                            <div x-show="fulfillmentType === 'delivery'" class="sm:col-span-2 space-y-2.5 pt-2">
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-1.5 text-xs font-semibold text-neutral-800 dark:text-neutral-200">
                                        <i data-lucide="truck" class="w-3.5 h-3.5 text-theme-primary"></i>
                                        <span>Pilihan Ekspedisi &amp; Biaya Ongkir:</span>
                                    </label>
                                    
                                    <div class="flex items-center gap-2">
                                        <template x-if="totalPackageWeight > 0">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-[8px] bg-neutral-100 dark:bg-neutral-800 text-[10.5px] font-medium text-neutral-600 dark:text-neutral-300 border border-black/5 dark:border-white/5">
                                                <i data-lucide="scale" class="w-3 h-3 text-theme-primary"></i>
                                                <span>Berat: <strong class="tabular-nums font-semibold" x-text="(totalPackageWeight >= 1000 ? (totalPackageWeight/1000).toFixed(1) + ' kg' : totalPackageWeight + ' gr')"></strong></span>
                                            </span>
                                        </template>
                                        <button type="button" @click="fetchShippingRates()" :disabled="shippingLoading"
                                            class="p-1.5 rounded-[8px] text-neutral-500 hover:text-theme-primary hover:bg-neutral-100 dark:hover:bg-neutral-800 transition text-[11px] flex items-center gap-1 border border-black/5 dark:border-white/5"
                                            title="Hitung Ulang Tarif Kurir">
                                            <i data-lucide="refresh-cw" class="w-3 h-3" :class="{'animate-spin': shippingLoading}"></i>
                                            <span class="hidden sm:inline text-[10.5px]">Cek Ongkir</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Loading State --}}
                                <div x-show="shippingLoading" class="p-4 rounded-[14px] bg-neutral-50 dark:bg-neutral-900/60 border border-black/5 dark:border-white/5 flex items-center justify-center gap-2 text-xs text-neutral-500 animate-pulse">
                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-theme-primary"></i>
                                    <span>Mengkalkulasi pilihan tarif kurir pengiriman...</span>
                                </div>

                                {{-- Bento Courier Tile Grid --}}
                                <div x-show="!shippingLoading && shippingOptions.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <template x-for="opt in shippingOptions" :key="opt.id">
                                        <button type="button" @click="selectCourierOption(opt)"
                                            :class="selectedShippingOptionId === opt.id 
                                                ? 'border-theme-primary bg-theme-primary/[0.04] dark:bg-theme-primary/10 ring-2 ring-theme-primary/30 shadow-sm' 
                                                : 'border-black/10 dark:border-white/10 bg-white dark:bg-neutral-900/50 hover:border-black/20 dark:hover:border-white/20'"
                                            class="relative p-3 rounded-[14px] border text-left transition-all duration-200 flex flex-col justify-between gap-2.5 group min-h-[76px]">
                                            
                                            <div class="flex items-center justify-between w-full">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-extrabold uppercase tracking-wider shrink-0" 
                                                        :class="getCourierBadgeClass(opt.courier_code)" 
                                                        x-text="opt.courier_code || 'KURIR'"></span>
                                                    <span class="font-bold text-xs text-neutral-900 dark:text-white truncate" 
                                                        x-text="opt.courier_service_name || opt.name"></span>
                                                </div>
                                                <div x-show="selectedShippingOptionId === opt.id" class="w-4 h-4 rounded-full bg-theme-primary text-white flex items-center justify-center shrink-0 shadow-sm">
                                                    <i data-lucide="check" class="w-2.5 h-2.5"></i>
                                                </div>
                                            </div>

                                            <div class="flex items-baseline justify-between w-full pt-1.5 border-t border-black/5 dark:border-white/5 text-[11px]">
                                                <span class="text-neutral-500 flex items-center gap-1">
                                                    <i data-lucide="clock" class="w-3 h-3 text-neutral-400"></i>
                                                    <span x-text="opt.duration ? opt.duration + (opt.duration.includes('Hari') || opt.duration.includes('Jam') ? '' : ' Hari') : 'Estimasi standar'"></span>
                                                </span>
                                                <span class="font-bold text-xs text-neutral-900 dark:text-white tabular-nums" 
                                                    :class="{'text-emerald-600 dark:text-emerald-400': opt.is_free}"
                                                    x-text="opt.is_free ? 'Gratis' : 'Rp ' + Number(opt.fee).toLocaleString('id-ID')"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                {{-- Fallback if legacy shippingRules available and no Biteship options --}}
                                <div x-show="!shippingLoading && shippingOptions.length === 0 && shippingRules.length > 0">
                                    <select x-model="selectedShippingRuleId" @change="calculateLegacyShipping()"
                                        class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm">
                                        <template x-for="r in shippingRules" :key="r.id">
                                            <option :value="r.id"
                                                x-text="r.name + ' (Rp ' + parseFloat(r.rate_amount || r.rate || 0).toLocaleString('id-ID') + ')'">
                                            </option>
                                        </template>
                                    </select>
                                </div>

                                {{-- No Couriers / Incomplete Address Hint --}}
                                <div x-show="!shippingLoading && shippingOptions.length === 0 && shippingRules.length === 0" 
                                    class="p-3.5 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/60 border border-black/5 dark:border-white/5 text-xs text-neutral-500 flex items-center gap-2">
                                    <i data-lucide="info" class="w-4 h-4 text-theme-primary shrink-0"></i>
                                    <span>Tentukan alamat atau geser pin lokasi di peta untuk menampilkan pilihan ekspedisi &amp; ongkir otomatis.</span>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">Catatan
                                    Tambahan untuk Toko</label>
                                <input type="text" x-model="notes"
                                    placeholder="Contoh: Jangan terlalu pedas, titipkan di pos satpam..."
                                    class="w-full px-3.5 py-2.5 rounded-[12px] border border-black/10 dark:border-white/10 bg-neutral-50 dark:bg-neutral-900 text-[16px] sm:text-sm focus:ring-2 focus:ring-theme-primary">
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Payment Method (QRIS Cooca Pay via Tripay Exclusive) --}}
                    <div
                        class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                        <div
                            class="flex items-center justify-between">
                            <div class="flex items-center gap-2 font-heading font-bold text-base text-neutral-900 dark:text-white">
                                <span
                                    class="w-6 h-6 rounded-[8px] bg-theme-primary text-white text-xs flex items-center justify-center">3</span>
                                <span>Metode Pembayaran</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 flex items-center gap-1">
                                <i data-lucide="zap" class="w-3 h-3"></i>
                                <span>Verifikasi Instan</span>
                            </span>
                        </div>

                        <div class="p-4 rounded-[16px] bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-[12px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="scan-qr-code" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-sm text-neutral-900 dark:text-white">QRIS Cooca Pay</h4>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 uppercase">Tripay</span>
                                    </div>
                                    <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5 leading-relaxed">
                                        Bayar instan via kode QR resmi Standar Pembayaran Nasional (QRIS). Mendukung semua e-wallet (GoPay, OVO, Dana, ShopeePay, LinkAja) dan Mobile Banking (BCA, Mandiri, BRI, BNI, CIMB, dll).
                                    </p>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-emerald-200/60 dark:border-emerald-800/40 flex items-center justify-between text-[11px] text-emerald-800 dark:text-emerald-300 font-medium">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    Status pembayaran terkonfirmasi otomatis dalam hitungan detik
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- ================================================================= --}}
                {{-- RIGHT COLUMN: CART ITEMS SUMMARY & SUBMISSION                     --}}
                {{-- ================================================================= --}}
                <div class="lg:col-span-5 space-y-6 sticky top-24">

                    <div
                        class="p-6 rounded-[20px] bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-lg space-y-6">
                        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-4">
                            <h2 class="font-heading font-bold text-lg text-neutral-900 dark:text-white">Rincian Belanja
                            </h2>
                            <span class="text-xs font-semibold px-2 py-1 rounded-[8px] theme-badge"
                                x-text="$store.cart.count() + ' Item'"></span>
                        </div>

                        {{-- Items List --}}
                        <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                            <template x-for="item in $store.cart.items" :key="item.id">
                                <div
                                    class="flex items-center gap-3 p-2 rounded-[12px] bg-neutral-50 dark:bg-neutral-900/50">
                                    <div
                                        class="w-12 h-12 rounded-[8px] bg-neutral-200 dark:bg-neutral-800 shrink-0 overflow-hidden">
                                        <template x-if="item.image_url">
                                            <img :src="item.image_url" :alt="item.name"
                                                class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image_url">
                                            <div class="w-full h-full flex items-center justify-center text-neutral-400">
                                                <i data-lucide="package" class="w-5 h-5"></i>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-neutral-900 dark:text-white truncate"
                                            x-text="item.name"></div>
                                        <div class="text-[11px] text-neutral-500"
                                            style="font-variant-numeric: tabular-nums;"
                                            x-text="'Rp ' + item.price.toLocaleString('id-ID')"></div>
                                    </div>

                                    {{-- Quantity Buttons --}}
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="$store.cart.updateQty(item.id, item.quantity - 1)"
                                            class="w-6 h-6 rounded-md bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200 flex items-center justify-center text-xs font-bold hover:bg-neutral-300">
                                            -
                                        </button>
                                        <span class="w-6 text-center text-xs font-bold"
                                            style="font-variant-numeric: tabular-nums;" x-text="item.quantity"></span>
                                        <button type="button" @click="$store.cart.updateQty(item.id, item.quantity + 1)"
                                            class="w-6 h-6 rounded-md bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200 flex items-center justify-center text-xs font-bold hover:bg-neutral-300">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Cost Breakdown --}}
                        <div class="space-y-2 border-t border-black/5 dark:border-white/10 pt-4 text-xs">
                            <div class="flex justify-between text-neutral-600 dark:text-neutral-400">
                                <span>Subtotal Produk</span>
                                <span style="font-variant-numeric: tabular-nums;" class="font-semibold text-neutral-900 dark:text-white"
                                    x-text="'Rp ' + $store.cart.subtotal().toLocaleString('id-ID')"></span>
                            </div>

                            <div class="flex justify-between text-neutral-600 dark:text-neutral-400"
                                x-show="fulfillmentType === 'delivery'">
                                <span class="flex items-center gap-1">
                                    <span>Ongkos Kirim</span>
                                    <span class="text-[10.5px] text-neutral-400 font-normal" x-show="selectedCourierName" x-text="'(' + selectedCourierName + ')'"></span>
                                </span>
                                <span style="font-variant-numeric: tabular-nums;" class="font-semibold text-neutral-900 dark:text-white"
                                    x-text="shippingCost > 0 ? 'Rp ' + shippingCost.toLocaleString('id-ID') : (shippingLoading ? 'Menghitung...' : 'Rp 0')"></span>
                            </div>

                            <div class="flex justify-between items-center text-neutral-600 dark:text-neutral-400"
                                x-show="fulfillmentType === 'delivery' && serviceFee > 0">
                                <span class="flex items-center gap-1.5">
                                    <span>Biaya Layanan Sistem</span>
                                    <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-theme-primary/10 text-theme-primary">Cooca</span>
                                </span>
                                <span style="font-variant-numeric: tabular-nums;" class="font-semibold text-neutral-900 dark:text-white"
                                    x-text="'Rp ' + Number(serviceFee).toLocaleString('id-ID')"></span>
                            </div>

                            <div
                                class="flex justify-between text-neutral-900 dark:text-white font-bold text-base pt-3 border-t border-black/5 dark:border-white/10">
                                <span>Total Bayar</span>
                                <span class="text-theme-primary font-extrabold" style="font-variant-numeric: tabular-nums;"
                                    x-text="'Rp ' + grandTotal().toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        {{-- Confirm Order Button --}}
                        <button type="button" @click="submitOrder()" :disabled="isSubmitting"
                            class="w-full py-4 rounded-[12px] theme-btn-primary font-bold text-sm shadow-xl flex items-center justify-center gap-2 active:scale-[0.97] disabled:opacity-50 min-h-[52px]">
                            <span x-show="!isSubmitting">Konfirmasi &amp; Pesan Sekarang</span>
                            <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                <span>Memproses Pesanan...</span>
                            </span>
                            <i data-lucide="arrow-right" class="w-4 h-4" x-show="!isSubmitting"></i>
                        </button>

                        <p class="text-[11px] text-neutral-400 text-center leading-relaxed">
                            Data transaksi Anda dienkripsi dan diproses secara aman sesuai standar perlindungan konsumen.
                        </p>
                    </div>

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
