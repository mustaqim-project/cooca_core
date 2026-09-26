@props([
    'items' => [],
])

@php
    $canSeeDashboard = \App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view');
    $homeRoute = $canSeeDashboard ? route('dashboard') : route('portal');
    $homeLabel = $canSeeDashboard ? 'Dashboard' : 'Portal';

    if (empty($items)) {
        $context = \App\Support\Navigation\NavigationRegistry::getContextForCurrentRoute();
        $items = $context['breadcrumbs'] ?? [];
    }

    // Automatically adapt root breadcrumbs (Dashboard -> Portal) for non-dashboard users
    if (! $canSeeDashboard && ! empty($items)) {
        foreach ($items as &$crumb) {
            if (isset($crumb['url']) && ($crumb['url'] === route('dashboard') || str_ends_with((string) $crumb['url'], '/dashboard'))) {
                $crumb['url'] = $homeRoute;
                if (($crumb['label'] ?? '') === 'Dashboard' || ($crumb['label'] ?? '') === 'Beranda') {
                    $crumb['label'] = $homeLabel;
                }
            }
        }
        unset($crumb);
    }
@endphp

@if (!empty($items))
    <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
        @foreach ($items as $index => $item)
            @if ($loop->last || empty($item['url']))
                <span class="text-black dark:text-white font-semibold truncate max-w-[200px] sm:max-w-none">{{ $item['label'] }}</span>
            @else
                <a href="{{ $item['url'] }}" class="hover:text-[#007AFF] transition-colors truncate max-w-[150px] sm:max-w-none">{{ $item['label'] }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40 shrink-0"></i>
            @endif
        @endforeach
    </nav>
@endif
