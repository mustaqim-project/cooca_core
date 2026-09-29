{{--
    FASE 5: Coffee Shop Category Chips (`artisan_brew` theme)
    PRD-21 §3.1: Warm earthy aesthetic category navigation
    Replaces generic category grid with coffee-themed pill chips.
--}}

@if ($productCategories->isNotEmpty() && $landingPage->isPageActive('catalog'))
    <section class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="font-heading font-bold text-xl sm:text-2xl" style="color: #3D2B1F;">
                    Menu Kategori
                </h2>
                <p class="text-xs sm:text-sm" style="color: #8B5A2B99;">
                    Pilih kategori favorit Anda
                </p>
            </div>
            <a href="{{ url('/' . $business->slug . '/katalog') }}"
                class="text-xs sm:text-sm font-semibold flex items-center gap-1 hover:underline"
                style="color: #8B5A2B;">
                <span>Lihat Semua</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Horizontal scrollable chips (mobile-friendly) --}}
        <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap scrollbar-hide">
            @php
                // Lucide icon mapping for coffee categories (no emoji)
                $catIcons = [
                    'kopi' => 'coffee', 'coffee' => 'coffee', 'espresso' => 'coffee',
                    'non-coffee' => 'cup-soda', 'teh' => 'cup-soda', 'tea' => 'cup-soda',
                    'makanan' => 'utensils', 'food' => 'utensils', 'pastry' => 'croissant',
                    'minuman' => 'glass-water', 'drink' => 'glass-water', 'juice' => 'glass-water',
                    'biji' => 'bean', 'bean' => 'bean', 'single origin' => 'globe',
                    'merchandise' => 'gift', 'alat' => 'settings', 'equipment' => 'settings',
                    'snack' => 'cookie', 'dessert' => 'cake-slice', 'cake' => 'cake',
                ];
            @endphp

            @foreach ($productCategories->take(8) as $cat)
                @php
                    $catNameLower = strtolower($cat->name);
                    $catIcon = 'coffee';
                    foreach ($catIcons as $key => $icon) {
                        if (str_contains($catNameLower, $key)) {
                            $catIcon = $icon;
                            break;
                        }
                    }
                @endphp
                <a href="{{ url('/' . $business->slug . '/katalog?category=' . $cat->id) }}"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-3 rounded-full text-sm font-medium transition-all duration-200 hover:shadow-md hover:scale-[1.03] hover:-translate-y-0.5 min-h-[44px]"
                    style="background: #FFFFFF; border: 1.5px solid rgba(139, 90, 43, 0.1); color: #3D2B1F;"
                    onmouseover="this.style.borderColor='#8B5A2B'; this.style.background='linear-gradient(135deg, #FAF7F2, #F5EDE0)';"
                    onmouseout="this.style.borderColor='rgba(139, 90, 43, 0.1)'; this.style.background='#FFFFFF';">
                    <i data-lucide="{{ $catIcon }}" class="w-4 h-4" style="color: #8B5A2B;"></i>
                    <span>{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
