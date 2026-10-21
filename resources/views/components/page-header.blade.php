@props([
    'title' => '',
    'description' => null,
    'icon' => 'ri-apps-2-line',
])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-3 min-w-0">
        <div class="w-10 h-10 shrink-0 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-soft flex items-center justify-center">
            <i class="{{ $icon }} text-lg"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-sm sm:text-base font-bold tracking-tight text-slate-900 dark:text-slate-50 leading-tight truncate">{{ $title }}</h1>
            @if($description)
                <p class="text-[10px] sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed hidden sm:block truncate">{{ $description }}</p>
            @endif
        </div>
    </div>
    @if(isset($slot) && trim($slot) !== '')
        <div class="shrink-0 flex sm:flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
