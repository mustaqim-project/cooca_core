{{--
    FASE 5: Coffee Shop Product Card Variant (`artisan_brew` theme)
    PRD-21 §3.1: Bean tasting notes, origin badges, grind selector
    Replaces the generic product_card for fnb_cafe sector.
--}}

@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
    $productDesc = $item->description ?: '';

    // Parse tasting notes from product tags/description (comma-separated in tags or description field)
    $rawNotes = $item->tags ?? ($item->meta['tasting_notes'] ?? '');
    if (is_array($rawNotes)) {
        $notes = $rawNotes;
    } else {
        $notes = array_filter(array_map('trim', explode(',', (string) $rawNotes)));
    }

    // Lucide icon mapping for coffee flavor profiles (no emoji)
    $noteIcons = [
        'fruity' => 'cherry', 'citrus' => 'citrus', 'berry' => 'grape',
        'nutty' => 'nut', 'caramel' => 'candy', 'chocolaty' => 'bean',
        'chocolate' => 'bean', 'floral' => 'flower-2', 'herbal' => 'leaf',
        'spicy' => 'flame', 'honey' => 'droplets', 'brown sugar' => 'heart',
        'smoky' => 'flame-kindling', 'earthy' => 'mountain', 'vanilla' => 'bean',
        'tropical' => 'palmtree', 'stone fruit' => 'apple',
    ];

    // Parse origin badge from category or meta
    $originBadge = $item->meta['origin'] ?? ($item->category?->name ?? null);

    // Roast level (0-5, default 3)
    $roastLevel = (int) ($item->meta['roast_level'] ?? 3);
@endphp

<div class="group flex flex-col rounded-[20px] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300"
    style="background: #FFFFFF; border: 1px solid rgba(139, 90, 43, 0.08);">

    {{-- Product Image with Origin Badge Overlay --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-[4/3] overflow-hidden block"
        style="background: #F5F0EA;">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
        @else
            <div class="w-full h-full flex items-center justify-center" style="color: #C88A58;">
                <i data-lucide="coffee" class="w-12 h-12 stroke-1"></i>
            </div>
        @endif

        {{-- Origin Badge (Top-Left) --}}
        @if ($originBadge)
            <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm backdrop-blur-sm"
                style="background: rgba(139, 90, 43, 0.85); color: #FAF7F2;">
                {{ $originBadge }}
            </span>
        @endif

        {{-- Pre-Order Badge (Top-Right) --}}
        @if ($item->is_preorder)
            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold shadow-sm"
                style="background: #C88A58; color: #FFF;">
                Pre-Order
            </span>
        @endif

        {{-- Subtle gradient overlay at bottom --}}
        <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/20 to-transparent"></div>
    </a>

    {{-- Product Content --}}
    <div class="p-4 flex flex-col flex-1">
        {{-- Tasting Notes Pills Row --}}
        @if (!empty($notes))
            <div class="flex flex-wrap gap-1 mb-2.5">
                @foreach (array_slice($notes, 0, 3) as $note)
                    @php
                        $normalizedNote = strtolower(trim($note));
                        $iconName = $noteIcons[$normalizedNote] ?? 'coffee';
                    @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium"
                        style="background: #FAF7F2; color: #8B5A2B; border: 1px solid rgba(200, 138, 88, 0.2);">
                        <i data-lucide="{{ $iconName }}" class="w-2.5 h-2.5"></i>
                        <span>{{ ucfirst($note) }}</span>
                    </span>
                @endforeach
            </div>
        @endif

        {{-- Product Name --}}
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-sm sm:text-base leading-snug line-clamp-2 mb-1.5 transition"
            style="color: #3D2B1F;"
            onmouseover="this.style.color='#8B5A2B'" onmouseout="this.style.color='#3D2B1F'">
            {{ $item->name }}
        </a>

        {{-- Roast Level Mini Indicator --}}
        <div class="flex items-center gap-1.5 mb-3">
            <span class="text-[10px] font-medium uppercase tracking-wider" style="color: #8B5A2B80;">Roast</span>
            <div class="flex items-center gap-0.5">
                @for ($i = 1; $i <= 5; $i++)
                    <div class="w-3 h-1.5 rounded-full transition-all"
                        style="background: {{ $i <= $roastLevel ? '#8B5A2B' : '#8B5A2B20' }};"></div>
                @endfor
            </div>
        </div>

        {{-- Price & Add to Cart Row --}}
        <div class="mt-auto pt-3 flex items-center justify-between gap-2"
            style="border-top: 1px solid rgba(139, 90, 43, 0.08);">
            <div class="flex flex-col">
                <span class="text-[10px] font-medium" style="color: #C88A58;">Harga</span>
                <span class="font-bold text-sm sm:text-base" style="color: #3D2B1F; font-variant-numeric: tabular-nums;">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Tanya Barista' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="p-2.5 rounded-full hover:text-white transition-all duration-200 active:scale-[0.92] min-w-[44px] min-h-[44px] flex items-center justify-center"
                    style="background: rgba(139, 90, 43, 0.08); color: #8B5A2B;"
                    onmouseover="this.style.background='#8B5A2B'; this.style.color='#FFF';"
                    onmouseout="this.style.background='rgba(139, 90, 43, 0.08)'; this.style.color='#8B5A2B';"
                    title="Tambah ke Keranjang">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3 py-1.5 rounded-full text-[11px] font-semibold transition"
                    style="background: rgba(139, 90, 43, 0.08); color: #8B5A2B;">
                    Detail
                </a>
            @endif
        </div>
    </div>
</div>
