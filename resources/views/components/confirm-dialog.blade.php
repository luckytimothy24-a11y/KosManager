@props([
    'title' => 'Konfirmasi Tindakan',
    'description' => null,
    'confirmText' => 'Ya, Lanjutkan',
    'confirmClass' => 'bg-red-600 hover:bg-red-700 text-white',
    'triggerClass' => '',
])

<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true" aria-haspopup="dialog" {{ $attributes->merge(['class' => $triggerClass]) }}>
        {{ $slot }}
    </button>

    <div x-show="open" x-cloak
         x-on:keydown.escape.window="open = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog" aria-modal="true" aria-label="{{ $title }}">
        <div x-show="open"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="open = false"></div>

        <div class="min-h-full flex items-center justify-center p-4">
            <div x-show="open"
                 class="relative w-full max-w-sm bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 overflow-hidden"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ str_contains($confirmClass, 'red') ? 'bg-red-100 dark:bg-red-500/10' : 'bg-primary-50 dark:bg-primary-500/10' }}">
                            <i class="{{ str_contains($confirmClass, 'red') ? 'ri-error-warning-line text-red-600 dark:text-red-400' : 'ri-question-line text-primary-600 dark:text-primary-400' }} text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
                            @if($description)
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $description }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5">{{ $content }}</div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                            Batal
                        </button>
                        {{ $actions }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
