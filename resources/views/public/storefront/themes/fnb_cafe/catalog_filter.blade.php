{{--
    FASE 5: Coffee Shop Catalog Filter Bar (`artisan_brew` theme)
    PRD-21 §3.1: Warm artisan aesthetic catalog toolbar
    Adds coffee-specific filter chips (Origin, Roast Level, Bean Type)
    alongside the standard category/search/sort filters.
--}}

<div class="rounded-[20px] p-4 sm:p-5 shadow-sm space-y-4"
    style="background: #FFFFFF; border: 1px solid rgba(139, 90, 43, 0.08);">

    {{-- Standard Search + Category + Sort Form --}}
    <form method="GET" action="{{ url('/' . $business->slug . '/katalog') }}"
        class="grid grid-cols-1 sm:grid-cols-12 gap-3 sm:gap-4 items-center">

        {{-- Keyword Search --}}
        <div class="sm:col-span-5 relative">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2" style="color: #C88A58;"></i>
            <input type="text" name="q" value="{{ $search }}"
                placeholder="Cari kopi, blend, atau origin..."
                class="w-full pl-10 pr-4 py-2.5 rounded-full text-[16px] sm:text-sm focus:outline-none focus:ring-2 transition"
                style="border: 1.5px solid rgba(139, 90, 43, 0.12); background: #FAF7F2; color: #3D2B1F; --tw-ring-color: #8B5A2B;">
        </div>

        {{-- Category Filter --}}
        <div class="sm:col-span-3">
            <select name="category" onchange="this.form.submit()"
                class="w-full px-3 py-2.5 rounded-full text-[16px] sm:text-sm focus:outline-none focus:ring-2 transition appearance-none cursor-pointer"
                style="border: 1.5px solid rgba(139, 90, 43, 0.12); background: #FAF7F2; color: #3D2B1F; --tw-ring-color: #8B5A2B;">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($categoryId ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Sorting --}}
        <div class="sm:col-span-3">
            <select name="sort" onchange="this.form.submit()"
                class="w-full px-3 py-2.5 rounded-full text-[16px] sm:text-sm focus:outline-none focus:ring-2 transition appearance-none cursor-pointer"
                style="border: 1.5px solid rgba(139, 90, 43, 0.12); background: #FAF7F2; color: #3D2B1F; --tw-ring-color: #8B5A2B;">
                <option value="popular" {{ ($sort ?? '') === 'popular' ? 'selected' : '' }}>Populer</option>
                <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                <option value="price_asc" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                <option value="price_desc" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
            </select>
        </div>

        {{-- Search Submit --}}
        <div class="sm:col-span-1">
            <button type="submit"
                class="w-full py-2.5 rounded-full text-sm font-semibold text-white transition-all hover:-translate-y-0.5 active:scale-[0.97] min-h-[44px]"
                style="background: linear-gradient(135deg, #8B5A2B, #6B4423);">
                <i data-lucide="search" class="w-4 h-4 mx-auto"></i>
            </button>
        </div>
    </form>

    {{-- Coffee-Specific Quick Filter Chips --}}
    <div class="flex flex-wrap items-center gap-2 pt-1"
        style="border-top: 1px solid rgba(139, 90, 43, 0.06);">
        <span class="text-[10px] font-semibold uppercase tracking-wider mr-1" style="color: #8B5A2B80;">
            Quick Filter:
        </span>

        @php
            $coffeeChips = [
                ['label' => 'Espresso Based', 'q' => 'espresso', 'icon' => 'coffee'],
                ['label' => 'Cold Brew', 'q' => 'cold brew', 'icon' => 'snowflake'],
                ['label' => 'Single Origin', 'q' => 'single origin', 'icon' => 'globe'],
                ['label' => 'Susu & Latte', 'q' => 'latte', 'icon' => 'milk'],
                ['label' => 'Non-Coffee', 'q' => 'non coffee', 'icon' => 'cup-soda'],
                ['label' => 'Pastry & Snack', 'q' => 'pastry', 'icon' => 'croissant'],
            ];
        @endphp

        @foreach ($coffeeChips as $chip)
            <a href="{{ url('/' . $business->slug . '/katalog?q=' . urlencode($chip['q'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium transition-all duration-200 hover:scale-105 hover:shadow-sm"
                style="background: {{ ($search ?? '') === $chip['q'] ? '#8B5A2B' : '#FAF7F2' }};
                       color: {{ ($search ?? '') === $chip['q'] ? '#FFFFFF' : '#8B5A2B' }};
                       border: 1px solid {{ ($search ?? '') === $chip['q'] ? '#8B5A2B' : 'rgba(200, 138, 88, 0.2)' }};">
                <i data-lucide="{{ $chip['icon'] }}" class="w-3 h-3"></i>
                <span>{{ $chip['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
