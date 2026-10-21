{{--
    Bubble pesan ala Mamikos: milik sendiri kanan (oranye), lawan kiri (putih).
    Props: mine (bool), message, category (nullable), time, read (nullable bool).
--}}
@props([
    'mine' => false,
    'message' => '',
    'category' => null,
    'time' => null,
    'read' => null, // true = dibaca, false = terkirim, null = sembunyikan status
])

<div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
    <div class="max-w-[78%] {{ $mine ? 'rounded-br-md bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20' : 'rounded-bl-md border border-slate-100 bg-white text-slate-800 shadow-sm dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-200' }} rounded-[20px] px-4 py-3">
        @if($category)
            <p class="mb-1.5 flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest {{ $mine ? 'text-white/80' : 'text-primary-600 dark:text-primary-400' }}">
                <i class="ri-price-tag-3-line"></i> {{ \App\Models\Message::CATEGORIES[$category] ?? $category }}
            </p>
        @endif
        <p class="whitespace-pre-line text-[13px] font-medium leading-relaxed">{{ $message }}</p>
        <p class="mt-2 flex items-center gap-1 text-[10px] {{ $mine ? 'text-white/70' : 'text-slate-400 dark:text-slate-500' }}">
            <i class="ri-time-line"></i> {{ $time }}
            @if(!is_null($read))
                @if($read)
                    <span class="inline-flex items-center gap-0.5 text-emerald-500">· <i class="ri-check-double-line"></i> dibaca</span>
                @else
                    <span class="inline-flex items-center gap-0.5">· <i class="ri-check-line"></i> terkirim</span>
                @endif
            @endif
        </p>
    </div>
</div>
