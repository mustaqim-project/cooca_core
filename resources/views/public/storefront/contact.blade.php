@extends('public.storefront.layouts.app')

@php
    $contactTitle = 'Kontak & Lokasi Cabang | ' . $business->name;
    $contactDesc = 'Hubungi tim layanan pelanggan ' . $business->name . '. Temukan informasi alamat lengkap, jam operasional toko, nomor WhatsApp resmi, dan lokasi cabang terdekat.';
    $contactCanonical = url('/' . $business->slug . '/kontak');
    $contactOgImage = $landingPage->og_image_url ?: ($landingPage->hero_image_url ?: ($business->logo_url ?: asset('assets/seo/cooca-og-default.jpg')));
@endphp

@section('title', $contactTitle)
@section('description', $contactDesc)
@section('canonical', $contactCanonical)
@section('og_title', 'Kontak, Lokasi & Layanan Pelanggan - ' . $business->name)
@section('og_description', $contactDesc)
@section('og_image', $contactOgImage)
@section('og_type', 'website')

@push('seo')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "ContactPage",
  "name": "{{ addslashes($contactTitle) }}",
  "description": "{{ addslashes($contactDesc) }}",
  "url": "{{ $contactCanonical }}",
  "mainEntity": {
    "@type": "Organization",
    "name": "{{ addslashes($business->name) }}",
    "telephone": "{{ $landingPage->whatsapp_number ?: ($business->phone ?: '') }}",
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "{{ addslashes($landingPage->contact_address ?: ($business->address ?: 'Indonesia')) }}",
      "addressCountry": "ID"
    }
  }
}
</script>
@endpush

@section('content')
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 space-y-12">

        {{-- Breadcrumb & Title --}}
        <div class="max-w-2xl space-y-3">
            <div class="flex items-center gap-2 text-xs text-neutral-500">
                <a href="{{ url('/' . $business->slug) }}" class="hover:text-theme-primary transition">Beranda</a>
                <span>/</span>
                <span class="text-neutral-900 dark:text-white font-medium">Kontak & Cabang</span>
            </div>
            <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold theme-badge">Hubungi Kami</div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-4xl text-neutral-900 dark:text-white">
                Lokasi, Jam Buka & Layanan Pelanggan
            </h1>
            <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300">
                Kunjungi gerai fisik kami atau hubungi tim customer support untuk pertanyaan, konsultasi, dan informasi
                kemitraan.
            </p>
        </div>

        {{-- 2-Column Info & Interactive Map --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            {{-- Left: Contact Cards & Operational Hours --}}
            <div class="lg:col-span-6 space-y-6">

                {{-- Contact Channels --}}
                <div
                    class="p-6 rounded-theme bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                    <h2 class="font-heading font-bold text-lg text-neutral-900 dark:text-white">Saluran Komunikasi Resmi
                    </h2>

                    <div class="space-y-3">
                        {{-- WhatsApp --}}
                        @if ($hasWhatsapp)
                            <a href="{{ $landingPage->getWhatsAppUrl() }}" target="_blank" rel="noopener"
                                class="flex items-center gap-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-200 hover:bg-emerald-100 transition group">
                                <div
                                    class="w-10 h-10 rounded-full bg-[#25D366] text-white flex items-center justify-center shrink-0">
                                    <i data-lucide="message-circle" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400">WhatsApp CS
                                    </div>
                                    <div class="font-bold text-sm font-mono truncate">
                                        {{ $landingPage->whatsapp_number ?: $business->phone }}</div>
                                </div>
                                <i data-lucide="arrow-up-right"
                                    class="w-4 h-4 text-emerald-600 group-hover:translate-x-0.5 transition"></i>
                            </a>
                        @endif

                        {{-- Telephone --}}
                        @if ($landingPage->custom_phone || $business->phone)
                            <div
                                class="flex items-center gap-4 p-3.5 rounded-xl bg-neutral-50 dark:bg-neutral-900/50 border border-black/5 dark:border-white/10">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="phone" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-neutral-500 font-medium">Telepon Gerai</div>
                                    <div class="font-bold text-sm text-neutral-900 dark:text-white font-mono">
                                        {{ $landingPage->custom_phone ?: $business->phone }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Email --}}
                        @if ($landingPage->custom_email || $business->email)
                            <div
                                class="flex items-center gap-4 p-3.5 rounded-xl bg-neutral-50 dark:bg-neutral-900/50 border border-black/5 dark:border-white/10">
                                <div
                                    class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="mail" class="w-5 h-5"></i>
                                </div>
                                <div class="truncate">
                                    <div class="text-xs text-neutral-500 font-medium">Email Korespondensi</div>
                                    <div class="font-bold text-sm text-neutral-900 dark:text-white truncate">
                                        {{ $landingPage->custom_email ?: $business->email }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Address --}}
                        <div
                            class="flex items-start gap-4 p-3.5 rounded-xl bg-neutral-50 dark:bg-neutral-900/50 border border-black/5 dark:border-white/10">
                            <div
                                class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="map-pin" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="text-xs text-neutral-500 font-medium">Alamat Lengkap</div>
                                <div class="text-sm font-medium text-neutral-900 dark:text-white leading-relaxed">
                                    {{ $landingPage->custom_address ?: ($business->address ?: 'Indonesia') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Operational Hours Schedule --}}
                @php
                    $opHours = (array) ($landingPage->operational_hours ?? []);
                @endphp
                @if (!empty($opHours))
                    <div
                        class="p-6 rounded-theme bg-white dark:bg-neutral-800/80 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
                        <h2 class="font-heading font-bold text-lg text-neutral-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="clock" class="w-5 h-5 text-theme-primary"></i>
                            <span>Jadwal Jam Operasional</span>
                        </h2>

                        <div class="divide-y divide-black/5 dark:divide-white/10 text-xs sm:text-sm">
                            @foreach ($opHours as $day => $time)
                                <div class="py-2.5 flex items-center justify-between">
                                    <span
                                        class="font-medium text-neutral-700 dark:text-neutral-300 capitalize">{{ $day }}</span>
                                    <span class="font-mono text-neutral-900 dark:text-white font-semibold">
                                        {{ is_array($time) ? ($time['open'] ?? '08:00') . ' - ' . ($time['close'] ?? '21:00') : (string) $time }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- Right: Google Maps Embed / Location Viewer --}}
            <div class="lg:col-span-6 space-y-4">
                <div
                    class="rounded-theme overflow-hidden border border-black/5 dark:border-white/10 shadow-lg bg-neutral-100 dark:bg-neutral-800 aspect-[4/3] sm:aspect-square relative">
                    @if ($landingPage->google_maps_embed_url)
                        <iframe src="{{ $landingPage->google_maps_embed_url }}" width="100%" height="100%"
                            style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-full"></iframe>
                    @else
                        {{-- Default Map Embed Fallback using address query --}}
                        @php
                            $mapQuery = urlencode(
                                $landingPage->custom_address ?: ($business->address ?: $business->name . ' Indonesia'),
                            );
                        @endphp
                        <iframe src="https://maps.google.com/maps?q={{ $mapQuery }}&t=&z=15&ie=UTF8&iwloc=&output=embed"
                            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                            class="w-full h-full"></iframe>
                    @endif
                </div>

                <div class="flex items-center justify-between text-xs text-neutral-500 px-1">
                    <span>Peta navigasi langsung ke lokasi toko fisik kami.</span>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($landingPage->custom_address ?: ($business->address ?: $business->name)) }}"
                        target="_blank" rel="noopener"
                        class="font-semibold text-theme-primary hover:underline flex items-center gap-1">
                        <span>Buka di Google Maps</span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>
@endsection
