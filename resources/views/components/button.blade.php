@props(['href' => '#', 'type' => 'primary', 'size' => 'md'])

@php
    $colors = [
        'primary' => 'bg-primary-500 hover:bg-primary-600 text-white',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-700 dark:text-slate-200',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'success' => 'bg-green-600 hover:bg-green-700 text-white',
        'warning' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
@endphp

<a href="{{ $href }}" class="inline-flex items-center gap-2 rounded-lg font-medium transition-colors {{ $colors[$type] }} {{ $sizes[$size] }}">
    {{ $slot }}
</a>
