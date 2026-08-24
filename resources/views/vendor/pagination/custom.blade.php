@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Menampilkan
            <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span>&ndash;<span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
            dari <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span> data
        </p>

        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                    <i class="ri-arrow-left-s-line"></i>
                </span>
            @else
                <a href="{{ $paginator->appends(request()->query())->previousPageUrl() }}" rel="prev"
                   aria-label="Halaman sebelumnya"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <i class="ri-arrow-left-s-line"></i>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                {{-- "Three dots" separator --}}
                @if (is_string($element))
                    <span class="px-1.5 text-xs text-slate-400 dark:text-slate-500">{{ $element }}</span>
                @endif

                {{-- Array of page links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page"
                                  class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg text-sm font-semibold bg-primary-500 text-white shadow-sm shadow-primary-500/30">{{ $page }}</span>
                        @else
                            <a href="{{ $paginator->appends(request()->query())->url($page) }}"
                               class="inline-flex items-center justify-center min-w-[2.25rem] h-9 px-2 rounded-lg text-sm font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->appends(request()->query())->nextPageUrl() }}" rel="next"
                   aria-label="Halaman berikutnya"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-200 transition">
                    <i class="ri-arrow-right-s-line"></i>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm text-slate-300 dark:text-slate-600 cursor-not-allowed" aria-hidden="true">
                    <i class="ri-arrow-right-s-line"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
