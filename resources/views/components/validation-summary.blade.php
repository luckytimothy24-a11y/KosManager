@props(['title' => 'Periksa kembali isian Anda'])

@if ($errors->any())
    <div x-data="{ open: true }"
         x-show="open"
         class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-500/25 dark:bg-red-500/10 dark:text-red-300"
         role="alert"
         aria-live="assertive"
         aria-atomic="true">
        <div class="flex items-start gap-2.5">
            <i class="ri-error-warning-line mt-0.5 shrink-0 text-base" aria-hidden="true"></i>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold">{{ $title }}</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-4 text-xs leading-relaxed">
                    @foreach (array_unique($errors->all()) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button"
                    @click="open = false"
                    class="-mr-1 shrink-0 rounded-lg p-1 text-red-500 transition hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-500/10"
                    aria-label="Tutup ringkasan kesalahan">
                <i class="ri-close-line" aria-hidden="true"></i>
            </button>
        </div>
    </div>
@endif
