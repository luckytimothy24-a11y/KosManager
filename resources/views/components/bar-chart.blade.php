@props(['title', 'labels' => [], 'values' => [], 'prefix' => '', 'suffix' => ''])

@php
    $max = count($values) > 0 ? max($values) : 0;
    $hasData = $max > 0;
@endphp

<div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6">
    <div class="flex items-center gap-2 mb-5">
        <div class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center">
            <i class="ri-bar-chart-grouped-line text-primary-600 dark:text-primary-400"></i>
        </div>
        <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ $title }}</h3>
    </div>

    @if(!$hasData)
        <div class="py-8 text-center">
            <i class="ri-line-chart-line text-3xl text-slate-300 dark:text-slate-600"></i>
            <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Belum ada data untuk ditampilkan.</p>
        </div>
    @else
        <div class="flex items-end justify-between gap-2 h-40">
            @foreach($values as $i => $value)
                @php
                    $height = $max > 0 ? max(4, round(($value / $max) * 100)) : 4;
                    $display = $prefix . number_format($value, 0, ',', '.') . $suffix;
                @endphp
                <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end min-w-0" title="{{ $labels[$i] }}: {{ $display }}">
                    <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 truncate max-w-full px-0.5">{{ $value > 0 ? $display : '' }}</span>
                    <div class="w-full rounded-t-md bg-gradient-to-t from-primary-600 to-primary-400 hover:from-primary-700 hover:to-primary-500 transition-colors"
                         style="height: {{ $height }}%"></div>
                    <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500 whitespace-nowrap">{{ $labels[$i] }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
