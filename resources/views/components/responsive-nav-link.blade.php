@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-400 text-start text-base font-medium text-primary-700 bg-primary-50 dark:bg-primary-500/10 dark:text-primary-300 focus:outline-none focus:text-primary-800 dark:focus:text-primary-200 focus:bg-primary-100 dark:focus:bg-primary-500/20 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600 focus:outline-none focus:text-slate-800 dark:focus:text-slate-100 focus:bg-slate-50 dark:focus:bg-slate-800/60 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
