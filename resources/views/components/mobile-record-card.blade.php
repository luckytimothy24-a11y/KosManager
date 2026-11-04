@props([
    'title' => null,
    'subtitle' => null,
    'meta' => [],
])

<article {{ $attributes->merge(['class' => 'card-surface p-4']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            @if($title)
                <p class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">{{ $title }}</p>
            @endif
            @if($subtitle)
                <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($badge) && trim($badge) !== '')
            <div class="shrink-0">{{ $badge }}</div>
        @endif
    </div>

    @if(!empty($meta))
        <dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2 text-xs">
            @foreach($meta as $label => $value)
                <div class="min-w-0">
                    <dt class="text-slate-400 dark:text-slate-500">{{ $label }}</dt>
                    <dd class="mt-0.5 break-words font-semibold text-slate-700 dark:text-slate-200">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    @if(isset($actions) && trim($actions) !== '')
        <div class="mt-3 flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-3 dark:border-white/[0.06]">
            {{ $actions }}
        </div>
    @endif
</article>
