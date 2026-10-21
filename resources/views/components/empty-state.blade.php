@props([
    'title' => 'Belum ada data',
    'description' => null,
    'illustration' => 'empty', // empty|search|booking|payment|notification
    'icon' => null,
])

@php
    $illustrations = [
        'empty' => '<svg viewBox="0 0 180 140" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="20" y="30" width="140" height="90" rx="14" fill="currentColor" opacity="0.05"/><rect x="20" y="30" width="140" height="90" rx="14" stroke="currentColor" stroke-width="1.2" opacity="0.1"/><path d="M50 70h80M50 84h55M50 98h35" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.1"/><circle cx="145" cy="42" r="16" fill="currentColor" opacity="0.06"/><path d="M140 42h10M145 37v10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.15"/></svg>',
        'search' => '<svg viewBox="0 0 180 140" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="82" cy="68" r="30" fill="currentColor" opacity="0.05"/><circle cx="82" cy="68" r="30" stroke="currentColor" stroke-width="1.2" opacity="0.1"/><path d="M103 89l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.15"/><path d="M70 68h24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.1"/></svg>',
        'booking' => '<svg viewBox="0 0 180 140" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="30" y="25" width="120" height="90" rx="12" fill="currentColor" opacity="0.04"/><rect x="30" y="25" width="120" height="24" rx="12" fill="currentColor" opacity="0.08"/><circle cx="54" cy="37" r="3" fill="currentColor" opacity="0.12"/><circle cx="66" cy="37" r="3" fill="currentColor" opacity="0.12"/><circle cx="78" cy="37" r="3" fill="currentColor" opacity="0.12"/><path d="M50 68h35M50 80h50M50 92h25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.1"/><path d="M115 72l8 8 16-16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.12"/></svg>',
        'payment' => '<svg viewBox="0 0 180 140" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="25" y="35" width="130" height="75" rx="12" fill="currentColor" opacity="0.04"/><rect x="25" y="35" width="130" height="18" rx="12" fill="currentColor" opacity="0.07"/><rect x="42" y="64" width="55" height="7" rx="3.5" fill="currentColor" opacity="0.05"/><rect x="42" y="78" width="38" height="7" rx="3.5" fill="currentColor" opacity="0.05"/><circle cx="135" cy="85" r="13" fill="currentColor" opacity="0.05"/><path d="M130 85h10M135 80v10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.14"/></svg>',
    ];
    $svg = $illustrations[$illustration] ?? $illustrations['empty'];
@endphp

<div class="py-12 sm:py-16 flex flex-col items-center justify-center text-center px-6">
    @if($icon)
        <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-500/10 text-primary-500 dark:text-primary-400 flex items-center justify-center mb-4">
            <i class="{{ $icon }} text-2xl"></i>
        </div>
    @else
        <div class="w-36 h-28 text-primary-400 dark:text-primary-500/60 opacity-80">
            {!! $svg !!}
        </div>
    @endif
    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $title }}</p>
    @if($description)
        <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500 max-w-xs leading-relaxed">{{ $description }}</p>
    @endif
    @isset($slot)
        <div class="mt-5">{{ $slot }}</div>
    @endisset
</div>
