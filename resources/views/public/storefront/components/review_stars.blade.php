@props([
    'rating' => 4.9,
    'count' => 128,
    'showCount' => true,
    'size' => 'sm', // 'xs', 'sm', 'md'
])

@php
    $starSizeClass = match($size) {
        'xs' => 'w-3 h-3',
        'md' => 'w-4.5 h-4.5',
        default => 'w-3.5 h-3.5',
    };
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
@endphp

<div class="inline-flex items-center gap-1.5">
    <div class="flex items-center text-amber-400">
        @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $fullStars)
                <svg class="{{ $starSizeClass }} fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @elseif ($i == $fullStars + 1 && $hasHalfStar)
                <svg class="{{ $starSizeClass }}" viewBox="0 0 20 20">
                    <defs>
                        <linearGradient id="half-star-grad-{{ $size }}">
                            <stop offset="50%" stop-color="#FBBF24" />
                            <stop offset="50%" stop-color="#E5E7EB" />
                        </linearGradient>
                    </defs>
                    <path fill="url(#half-star-grad-{{ $size }})" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @else
                <svg class="{{ $starSizeClass }} text-neutral-300 dark:text-neutral-700 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @endif
        @endfor
    </div>

    <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200 font-mono tabular-nums">
        {{ number_format($rating, 1) }}
    </span>

    @if ($showCount)
        <span class="text-[11px] text-neutral-400 dark:text-neutral-500">
            ({{ $count }} {{ __('storefront.product_detail.reviews') }})
        </span>
    @endif
</div>
