@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                <i class="ri-arrow-left-line"></i> Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->appends(request()->query())->previousPageUrl() }}" rel="prev"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                <i class="ri-arrow-left-line"></i> Sebelumnya
            </a>
        @endif

        <span class="text-xs text-slate-400 dark:text-slate-500">Halaman {{ $paginator->currentPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->appends(request()->query())->nextPageUrl() }}" rel="next"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                Berikutnya <i class="ri-arrow-right-line"></i>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                Berikutnya <i class="ri-arrow-right-line"></i>
            </span>
        @endif
    </nav>
@endif
