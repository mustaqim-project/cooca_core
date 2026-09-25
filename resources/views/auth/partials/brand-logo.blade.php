@php
    $siteLogoLightSetting = \App\Models\SystemSetting::get('site_logo_light');
    $siteLogoDarkSetting = \App\Models\SystemSetting::get('site_logo_dark');

    $siteLogoLightUrl =
        \App\Domain\Storage\AdminStorage::publicUrl($siteLogoLightSetting) ??
        asset('assets/image/cooca-logo-landscape.png');
    $siteLogoDarkUrl =
        \App\Domain\Storage\AdminStorage::publicUrl($siteLogoDarkSetting) ??
        asset('assets/image/1785229034_logo_dark.png');
@endphp

<a href="{{ route('landing') }}" class="inline-block transition-transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40 rounded-xl {{ $class ?? 'mb-5' }}" aria-label="COOCA Beranda">
    <img src="{{ $siteLogoLightUrl }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto block dark:hidden">
    <img src="{{ $siteLogoDarkUrl }}" alt="COOCA" class="h-9 sm:h-10 w-auto object-contain mx-auto hidden dark:block">
</a>
