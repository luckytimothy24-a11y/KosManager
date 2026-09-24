@props(['title' => '', 'description' => ''])

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
    <div class="flex gap-3 min-w-0">
        <div class="hidden sm:grid w-10 h-10 place-items-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg shadow-orange-500/25 shrink-0">
            <i class="ri-sparkling-2-line text-lg"></i>
        </div>
        <div class="min-w-0 leading-tight">
            <h1 class="text-[17px] sm:text-lg font-black tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
            @if($description)
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $description }}</p>
            @endif
        </div>
    </div>
    @if(isset($slot) && trim($slot) !== '')
        <div class="shrink-0 flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
