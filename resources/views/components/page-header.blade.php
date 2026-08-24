@props(['title' => '', 'description' => ''])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <div class="leading-tight">
        <h1 class="text-lg font-bold text-slate-900 dark:text-white">{{ $title }}</h1>
        @if($description)
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    @if(isset($slot) && trim($slot) !== '')
        <div class="shrink-0">{{ $slot }}</div>
    @endif
</div>
