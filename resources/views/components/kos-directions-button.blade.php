@props([
    'kos' => null,
    'variant' => 'primary',
    'label' => null,
    'size' => 'md',
])

@php
    $url = $kos?->googleMapsDirectionsUrl();
    $sizes = [
        'sm' => 'min-h-9 px-3 py-1.5 text-xs rounded-lg',
        'md' => 'min-h-11 px-4 py-2.5 text-sm rounded-xl',
        'lg' => 'min-h-12 px-5 py-3 text-sm rounded-xl',
    ];
    $styles = [
        'primary' => 'text-white bg-primary-500 hover:bg-primary-600 active:bg-primary-700 shadow-sm shadow-primary-500/30',
        'secondary' => 'text-primary-600 dark:text-primary-300 border border-primary-200 dark:border-primary-500/40 hover:bg-primary-50 dark:hover:bg-primary-500/10',
        'ghost' => 'text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800',
    ];
@endphp

@if($url)
    @php
        $text = $label ?: 'Arah ke Kos';
        $accessible = 'Buka petunjuk arah menuju ' . $kos->name . ' di Google Maps';
    @endphp
    <a href="{{ $url }}"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="{{ $accessible }}"
       title="{{ $accessible }}"
       class="inline-flex items-center justify-center gap-2 font-semibold transition active:scale-95 focus:outline-none focus-visible:ring-4 focus-visible:ring-primary-500/50 {{ $styles[$variant] ?? $styles['primary'] }} {{ $sizes[$size] ?? $sizes['md'] }}">
        <i class="ri-navigation-line" aria-hidden="true"></i>
        <span>{{ $text }}</span>
    </a>
@endif