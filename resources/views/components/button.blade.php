@props(['href' => null, 'variant' => null, 'type' => 'primary', 'size' => 'md', 'as' => null])

@php
    $variant = $variant ?? $type;
    $colors = [
        'primary' => 'bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white shadow-soft',
        'secondary' => 'bg-surface-card dark:bg-white/[0.04] border border-slate-200/60 dark:border-white/[0.08] hover:bg-slate-100 dark:hover:bg-white/[0.06] text-slate-600 dark:text-slate-300',
        'danger' => 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-soft',
        'success' => 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-soft',
        'warning' => 'bg-warn-500 hover:bg-warn-600 text-white shadow-soft',
        'ghost' => 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/[0.06]',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg font-semibold',
        'md' => 'px-4 py-2 text-sm rounded-xl font-semibold',
        'lg' => 'px-5 py-2.5 text-sm rounded-xl font-semibold',
    ];
    $base = 'inline-flex items-center justify-center gap-2 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-surface-dark ' . ($colors[$variant] ?? $colors['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    $isButton = $as === 'button' || $href === null;
@endphp

@if($isButton)
<button {{ $attributes->merge(['type' => 'button', 'class' => $base]) }}>
    {{ $slot }}
</button>
@else
<a href="{{ $href }}" {{ $attributes->merge(['class' => $base]) }}>
    {{ $slot }}
</a>
@endif
