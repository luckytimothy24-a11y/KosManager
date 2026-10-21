@props(['title' => 'Panduan Alur Kerja', 'steps' => []])

<div class="section-tint rounded-2xl p-5 sm:p-6">
    <div class="flex items-center gap-2.5 mb-5">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-soft shrink-0">
            <i class="ri-map-2-line text-white text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-50">{{ $title }}</h3>
            <p class="text-[11px] text-slate-400 dark:text-slate-500">Ikuti urutan ini agar semua fitur terpakai dengan benar</p>
        </div>
    </div>
    <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($steps as $i => $step)
            <li class="relative flex items-start gap-3 p-3.5 rounded-xl bg-white dark:bg-surface-elevated border border-slate-100 dark:border-white/[0.08] shadow-card transition-all duration-300 hover:shadow-card-hover hover:border-slate-200 dark:hover:border-white/[0.12] group">
                <span class="w-7 h-7 rounded-lg bg-primary-600 text-white text-xs font-semibold flex items-center justify-center shrink-0 shadow-sm group-hover:scale-110 transition-transform duration-300">{{ $i + 1 }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 leading-snug">{{ $step['title'] }}</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>
</div>
