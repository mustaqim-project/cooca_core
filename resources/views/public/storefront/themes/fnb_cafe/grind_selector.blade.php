{{--
    FASE 5: Coffee Shop Grind Size Selector (`artisan_brew` theme)
    PRD-21 §3.1.C.2: Grind Size Selector Stepper
    Options: Biji Utuh, Kasar (Cold Brew), Sedang (V60), Halus (Espresso)
    Used in Product Detail Page (PDP) for coffee products.
    All icons use Lucide — no emoji.
--}}

<div x-data="{ selectedGrind: 'whole' }" class="space-y-3">

    <div class="flex items-center gap-2">
        <h4 class="text-sm font-bold" style="color: #3D2B1F; font-family: var(--font-heading);">
            Pilihan Gilingan
        </h4>
        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
            style="background: #C88A5820; color: #8B5A2B;">
            Wajib Dipilih
        </span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">

        {{-- Biji Utuh --}}
        <button type="button" @click="selectedGrind = 'whole'"
            :class="selectedGrind === 'whole' ? 'ring-2 shadow-md scale-[1.02]' : 'hover:shadow-sm'"
            :style="selectedGrind === 'whole'
                ? 'ring-color: #8B5A2B; border-color: #8B5A2B; background: linear-gradient(135deg, #FAF7F2, #F5EDE0);'
                : 'border-color: rgba(139, 90, 43, 0.12); background: #FFFFFF;'"
            class="relative flex flex-col items-center gap-1.5 p-3 rounded-[14px] border-2 transition-all duration-200 cursor-pointer text-center"
            style="border-color: rgba(139, 90, 43, 0.12);">
            <i data-lucide="bean" class="w-5 h-5" style="color: #8B5A2B;"></i>
            <span class="text-xs font-bold leading-tight" style="color: #3D2B1F;">Biji Utuh</span>
            <span class="text-[10px] leading-tight" style="color: #8B5A2B99;">Simpan kesegaran lebih lama</span>
            <div x-show="selectedGrind === 'whole'" x-transition
                class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center shadow-sm"
                style="background: #8B5A2B; color: #FFFFFF;">
                <i data-lucide="check" class="w-3 h-3"></i>
            </div>
        </button>

        {{-- Kasar --}}
        <button type="button" @click="selectedGrind = 'coarse'"
            :class="selectedGrind === 'coarse' ? 'ring-2 shadow-md scale-[1.02]' : 'hover:shadow-sm'"
            :style="selectedGrind === 'coarse'
                ? 'ring-color: #8B5A2B; border-color: #8B5A2B; background: linear-gradient(135deg, #FAF7F2, #F5EDE0);'
                : 'border-color: rgba(139, 90, 43, 0.12); background: #FFFFFF;'"
            class="relative flex flex-col items-center gap-1.5 p-3 rounded-[14px] border-2 transition-all duration-200 cursor-pointer text-center"
            style="border-color: rgba(139, 90, 43, 0.12);">
            <i data-lucide="square" class="w-5 h-5" style="color: #6B4423;"></i>
            <span class="text-xs font-bold leading-tight" style="color: #3D2B1F;">Kasar</span>
            <span class="text-[10px] leading-tight" style="color: #8B5A2B99;">French Press, Cold Brew</span>
            <div x-show="selectedGrind === 'coarse'" x-transition
                class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center shadow-sm"
                style="background: #8B5A2B; color: #FFFFFF;">
                <i data-lucide="check" class="w-3 h-3"></i>
            </div>
        </button>

        {{-- Sedang --}}
        <button type="button" @click="selectedGrind = 'medium'"
            :class="selectedGrind === 'medium' ? 'ring-2 shadow-md scale-[1.02]' : 'hover:shadow-sm'"
            :style="selectedGrind === 'medium'
                ? 'ring-color: #8B5A2B; border-color: #8B5A2B; background: linear-gradient(135deg, #FAF7F2, #F5EDE0);'
                : 'border-color: rgba(139, 90, 43, 0.12); background: #FFFFFF;'"
            class="relative flex flex-col items-center gap-1.5 p-3 rounded-[14px] border-2 transition-all duration-200 cursor-pointer text-center"
            style="border-color: rgba(139, 90, 43, 0.12);">
            <i data-lucide="grid-2x2" class="w-5 h-5" style="color: #8B5A2B;"></i>
            <span class="text-xs font-bold leading-tight" style="color: #3D2B1F;">Sedang</span>
            <span class="text-[10px] leading-tight" style="color: #8B5A2B99;">V60, Chemex, Aeropress</span>
            <div x-show="selectedGrind === 'medium'" x-transition
                class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center shadow-sm"
                style="background: #8B5A2B; color: #FFFFFF;">
                <i data-lucide="check" class="w-3 h-3"></i>
            </div>
        </button>

        {{-- Halus --}}
        <button type="button" @click="selectedGrind = 'fine'"
            :class="selectedGrind === 'fine' ? 'ring-2 shadow-md scale-[1.02]' : 'hover:shadow-sm'"
            :style="selectedGrind === 'fine'
                ? 'ring-color: #8B5A2B; border-color: #8B5A2B; background: linear-gradient(135deg, #FAF7F2, #F5EDE0);'
                : 'border-color: rgba(139, 90, 43, 0.12); background: #FFFFFF;'"
            class="relative flex flex-col items-center gap-1.5 p-3 rounded-[14px] border-2 transition-all duration-200 cursor-pointer text-center"
            style="border-color: rgba(139, 90, 43, 0.12);">
            <i data-lucide="circle-dot" class="w-5 h-5" style="color: #C88A58;"></i>
            <span class="text-xs font-bold leading-tight" style="color: #3D2B1F;">Halus</span>
            <span class="text-[10px] leading-tight" style="color: #8B5A2B99;">Espresso, Moka Pot</span>
            <div x-show="selectedGrind === 'fine'" x-transition
                class="absolute -top-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center shadow-sm"
                style="background: #8B5A2B; color: #FFFFFF;">
                <i data-lucide="check" class="w-3 h-3"></i>
            </div>
        </button>

    </div>

    {{-- Hidden input for form submission --}}
    <input type="hidden" name="grind_size" x-model="selectedGrind">
</div>
