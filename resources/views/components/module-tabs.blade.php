@props([
    'module' => null,
])

@php
    $moduleKey = $module;
    if (empty($moduleKey)) {
        $context = \App\Support\Navigation\NavigationRegistry::getContextForCurrentRoute();
        $moduleKey = $context['module_key'];
    }

    $tabs = !empty($moduleKey) ? \App\Support\Navigation\NavigationRegistry::getTabsForModule($moduleKey) : [];
@endphp

@if (!empty($tabs))
    <nav data-module-tabs="{{ $moduleKey }}" aria-label="Navigasi Tab Modul"
        x-data
        x-init="$nextTick(() => {
            const active = $el.querySelector('[data-active-tab=\'true\']');
            if (active) {
                active.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'instant' });
            }
        })"
        {{ $attributes->merge(['class' => 'overflow-x-auto no-scrollbar scrollbar-none pb-1 flex-nowrap overscroll-x-contain touch-pan-x']) }}>
        <div class="inline-flex flex-nowrap p-1 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.07] border border-black/5 dark:border-white/10 text-[13px] font-medium whitespace-nowrap">
            @foreach ($tabs as $tab)
                <a href="{{ $tab['url'] }}"
                    data-active-tab="{{ $tab['is_active'] ? 'true' : 'false' }}"
                    class="shrink-0 min-h-[40px] sm:min-h-[36px] px-3.5 py-1.5 rounded-[10px] flex items-center gap-2 transition-all {{ $tab['is_active'] ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    @if (!empty($tab['icon']))
                        <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4 {{ $tab['is_active'] ? 'text-[#007AFF]' : 'text-black/40 dark:text-white/40' }}"></i>
                    @endif
                    <span>{{ $tab['label'] }}</span>
                    @if (!empty($tab['badge']))
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $tab['is_active'] ? 'bg-black/10 dark:bg-white/20 text-black dark:text-white' : 'bg-black/5 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                            {{ $tab['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>
@endif
