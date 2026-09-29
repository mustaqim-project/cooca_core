{{--
    FASE 5: Coffee Shop Bean Tasting Notes Detail (`artisan_brew` theme)
    PRD-21 §3.1.C.1: Bean Tasting Notes & Origin Badges
    Full tasting profile panel for Product Detail Page.
    Reads from product->meta['tasting_notes'], product->meta['origin'], product->meta['altitude'],
    product->meta['process'], product->meta['roast_level']
--}}

@php
    $meta = is_array($product->meta) ? $product->meta : [];

    $tastingNotes = $meta['tasting_notes'] ?? [];
    if (is_string($tastingNotes)) {
        $tastingNotes = array_filter(array_map('trim', explode(',', $tastingNotes)));
    }

    $origin = $meta['origin'] ?? null;
    $altitude = $meta['altitude'] ?? null;
    $process = $meta['process'] ?? null;
    $roastLevel = (int) ($meta['roast_level'] ?? 3);
    $roastLabels = ['', 'Light', 'Medium-Light', 'Medium', 'Medium-Dark', 'Dark'];
    $roastLabel = $roastLabels[$roastLevel] ?? 'Medium';
    $acidity = (int) ($meta['acidity'] ?? 3);
    $body = (int) ($meta['body'] ?? 3);
    $sweetness = (int) ($meta['sweetness'] ?? 3);

    // Lucide icon mapping for coffee flavor profiles (no emoji)
    $noteIcons = [
        'fruity' => 'cherry', 'citrus' => 'citrus', 'berry' => 'grape',
        'nutty' => 'nut', 'caramel' => 'candy', 'chocolaty' => 'bean',
        'chocolate' => 'bean', 'floral' => 'flower-2', 'herbal' => 'leaf',
        'spicy' => 'flame', 'honey' => 'droplets', 'brown sugar' => 'heart',
        'smoky' => 'flame-kindling', 'earthy' => 'mountain', 'vanilla' => 'bean',
        'tropical' => 'palmtree', 'stone fruit' => 'apple', 'wine' => 'wine',
        'jasmine' => 'flower', 'rose' => 'flower-2', 'cinnamon' => 'cylinder',
    ];

    $hasProfile = !empty($tastingNotes) || $origin || $altitude || $process;
@endphp

@if ($hasProfile)
    <div class="rounded-[20px] p-5 sm:p-6 space-y-5"
        style="background: linear-gradient(135deg, #FAF7F2 0%, #F5EDE0 100%); border: 1px solid rgba(139, 90, 43, 0.1);">

        {{-- Section Header --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] flex items-center justify-center shrink-0"
                style="background: rgba(139, 90, 43, 0.12);">
                <i data-lucide="coffee" class="w-5 h-5" style="color: #8B5A2B;"></i>
            </div>
            <div>
                <h3 class="font-heading font-bold text-base" style="color: #3D2B1F;">
                    Profil Rasa & Asal Biji
                </h3>
                <p class="text-[11px]" style="color: #8B5A2B99;">
                    Catatan sensorik kopi spesialti
                </p>
            </div>
        </div>

        {{-- Origin, Altitude, Process Row --}}
        @if ($origin || $altitude || $process)
            <div class="grid grid-cols-3 gap-3">
                @if ($origin)
                    <div class="text-center p-3 rounded-[12px]"
                        style="background: rgba(255,255,255,0.7); border: 1px solid rgba(139, 90, 43, 0.08);">
                        <i data-lucide="globe" class="w-5 h-5 mx-auto mb-1" style="color: #8B5A2B;"></i>
                        <span class="text-[10px] font-medium uppercase tracking-wider block" style="color: #8B5A2B80;">Asal</span>
                        <span class="text-xs font-bold block mt-0.5" style="color: #3D2B1F;">{{ $origin }}</span>
                    </div>
                @endif
                @if ($altitude)
                    <div class="text-center p-3 rounded-[12px]"
                        style="background: rgba(255,255,255,0.7); border: 1px solid rgba(139, 90, 43, 0.08);">
                        <i data-lucide="mountain" class="w-5 h-5 mx-auto mb-1" style="color: #8B5A2B;"></i>
                        <span class="text-[10px] font-medium uppercase tracking-wider block" style="color: #8B5A2B80;">Ketinggian</span>
                        <span class="text-xs font-bold block mt-0.5" style="color: #3D2B1F;">{{ $altitude }}</span>
                    </div>
                @endif
                @if ($process)
                    <div class="text-center p-3 rounded-[12px]"
                        style="background: rgba(255,255,255,0.7); border: 1px solid rgba(139, 90, 43, 0.08);">
                        <i data-lucide="flask-conical" class="w-5 h-5 mx-auto mb-1" style="color: #8B5A2B;"></i>
                        <span class="text-[10px] font-medium uppercase tracking-wider block" style="color: #8B5A2B80;">Proses</span>
                        <span class="text-xs font-bold block mt-0.5" style="color: #3D2B1F;">{{ $process }}</span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Tasting Notes Flavor Wheel --}}
        @if (!empty($tastingNotes))
            <div class="space-y-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider" style="color: #8B5A2B;">
                    Catatan Rasa
                </span>
                <div class="flex flex-wrap gap-2">
                    @foreach ($tastingNotes as $note)
                        @php
                            $normalizedNote = strtolower(trim($note));
                            $iconName = $noteIcons[$normalizedNote] ?? 'coffee';
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-medium shadow-sm"
                            style="background: #FFFFFF; color: #8B5A2B; border: 1px solid rgba(200, 138, 88, 0.25);">
                            <i data-lucide="{{ $iconName }}" class="w-3 h-3"></i>
                            <span>{{ ucfirst(trim($note)) }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Sensory Profile Bars: Roast, Acidity, Body, Sweetness --}}
        <div class="space-y-3 pt-2">
            <span class="text-[11px] font-semibold uppercase tracking-wider" style="color: #8B5A2B;">
                Profil Sensorik
            </span>

            @php
                $sensoryBars = [
                    ['label' => 'Roast Level', 'value' => $roastLevel, 'display' => $roastLabel],
                    ['label' => 'Acidity', 'value' => $acidity, 'display' => $acidity . '/5'],
                    ['label' => 'Body', 'value' => $body, 'display' => $body . '/5'],
                    ['label' => 'Sweetness', 'value' => $sweetness, 'display' => $sweetness . '/5'],
                ];
            @endphp

            @foreach ($sensoryBars as $bar)
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-medium w-24 shrink-0" style="color: #6B5744;">
                        {{ $bar['label'] }}
                    </span>
                    <div class="flex-1 flex items-center gap-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="flex-1 h-2 rounded-full transition-all duration-300"
                                style="background: {{ $i <= $bar['value'] ? '#8B5A2B' : 'rgba(139, 90, 43, 0.12)' }};">
                            </div>
                        @endfor
                    </div>
                    <span class="text-[10px] font-bold w-16 text-right" style="color: #8B5A2B;">
                        {{ $bar['display'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endif
