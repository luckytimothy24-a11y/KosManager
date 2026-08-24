@props([
    'icon' => 'ri-inbox-line',
    'title' => 'Belum ada data',
    'description' => null,
])

<div class="py-12 flex flex-col items-center justify-center text-center px-4">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
        <i class="{{ $icon }} text-2xl text-slate-400 dark:text-slate-500"></i>
    </div>
    <p class="mt-4 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
    @if($description)
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 max-w-xs">{{ $description }}</p>
    @endif
    @isset($slot)
        <div class="mt-5">{{ $slot }}</div>
    @endisset
</div>
