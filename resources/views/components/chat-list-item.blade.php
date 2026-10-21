{{--
    Satu item daftar percakapan ala Mamikos: avatar inisial, nama,
    konteks kos, snippet pesan terakhir, jam, badge unread.
--}}
@props([
    'href',
    'name' => '-',
    'context' => null,   // mis. nama kos
    'snippet' => '',
    'time' => null,
    'unread' => 0,
    'badge' => null,     // label kecil mis. "Calon Penghuni"
    'active' => false,
])

<a href="{{ $href }}"
   @if($active) aria-current="true" @endif
   class="group flex items-center gap-3 px-5 py-3.5 transition {{ $active ? 'bg-primary-50/70 dark:bg-surface-elevated/70' : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/50' }}">
    <span class="relative grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-slate-900 text-white text-sm font-bold shadow-sm dark:bg-white dark:text-slate-900">
        {{ mb_substr($name, 0, 1) }}
        @if($unread > 0)
            <span class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white dark:ring-slate-900">{{ $unread }}</span>
        @endif
    </span>
    <span class="min-w-0 flex-1">
        <span class="flex items-center gap-2">
            <span class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">{{ $name }}</span>
            @if($badge)
                <span class="inline-flex shrink-0 items-center rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">{{ $badge }}</span>
            @endif
        </span>
        @if($context)
            <span class="mt-0.5 block truncate text-xs text-slate-400 dark:text-slate-500">{{ $context }}</span>
        @endif
        <span class="mt-0.5 block truncate text-[13px] text-slate-500 dark:text-slate-400">{{ $snippet }}</span>
    </span>
    @if($time)
        <span class="shrink-0 text-[11px] text-slate-400 dark:text-slate-500">{{ $time }}</span>
    @endif
</a>
