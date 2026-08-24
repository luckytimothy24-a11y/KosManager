@props(['href' => '#', 'type' => 'primary', 'size' => 'md'])

@php
    $colors = [
        'primary' => 'bg-primary-500 hover:bg-primary-600 active:bg-primary-700 text-white shadow-sm shadow-primary-500/25',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200',
        'danger' => 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white shadow-sm shadow-red-600/25',
        'success' => 'bg-green-600 hover:bg-green-700 text-white shadow-sm shadow-green-600/25',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white shadow-sm',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
@endphp

<a href="{{ $href }}" class="inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900 {{ $colors[$type] }} {{ $sizes[$size] }}">
    {{ $slot }}
</a>
