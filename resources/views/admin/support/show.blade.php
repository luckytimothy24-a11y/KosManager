<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.support.index') }}" class="grid h-9 w-9 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-white/[0.08] dark:bg-surface-elevated dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Kembali">
                <i class="ri-arrow-left-line"></i>
            </a>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white">
                <i class="ri-customer-service-2-line text-lg"></i>
            </div>
            <div class="min-w-0">
                <h1 class="truncate text-[15px] font-bold leading-none text-slate-900 dark:text-slate-50">{{ $tenant->name }}</h1>
                <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"><i class="ri-shield-check-line text-primary-500"></i> Bantuan & CS KosManager</p>
            </div>
            <span class="ml-auto hidden items-center gap-1 rounded-full bg-primary-600 px-2.5 py-1 text-[11px] font-bold text-white sm:inline-flex"><i class="ri-user-star-line"></i> CS</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <x-alert />

        <x-chat-shell listTitle="Pesan CS" mobile="panel">
            <x-slot name="list">
                <div class="px-5 py-4 text-xs text-slate-400 dark:text-slate-500">Gunakan halaman daftar untuk berpindah percakapan.</div>
            </x-slot>
            <x-slot name="panel">
                <div class="flex min-h-0 flex-1 flex-col">
                    <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-primary-50 via-white to-white px-5 py-3.5 dark:border-white/[0.08] dark:from-primary-950/10 dark:via-surface-elevated dark:to-surface-elevated">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20">
                            <i class="ri-user-3-line"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-sm font-bold text-slate-900 dark:text-slate-50">{{ $tenant->name }}</h2>
                            <p class="mt-0.5 truncate text-xs font-medium text-slate-500 dark:text-slate-400">{{ $tenant->email }}</p>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 space-y-3 overflow-y-auto bg-slate-50/40 px-5 py-5 dark:bg-surface-elevated/20" aria-live="polite"
                         x-data="{ scrollBottom() { this.$el.scrollTop = this.$el.scrollHeight; } }" x-init="scrollBottom()">
                        @forelse($messages as $msg)
                            @php
                                $mine = (int) $msg->sender_id !== (int) $tenant->id;
                            @endphp
                            <div>
                                <p class="mb-1 text-[10px] font-bold uppercase tracking-widest {{ $mine ? 'text-right text-primary-500' : 'text-slate-400 dark:text-slate-500' }}">
                                    {{ $mine ? 'CS KosManager' : $tenant->name }}
                                </p>
                                <x-chat-bubble
                                    :mine="$mine"
                                    :message="$msg->message"
                                    :time="$msg->created_at->translatedFormat('d M Y, H:i')"
                                    :read="$mine ? !is_null($msg->read_at) : null" />
                            </div>
                        @empty
                            <div class="flex h-full flex-col items-center justify-center gap-3 py-10 text-center">
                                <p class="text-sm font-bold text-slate-900 dark:text-slate-50">Belum ada pesan</p>
                            </div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('admin.support.store', $tenant->id) }}" novalidate
                          class="shrink-0 border-t border-slate-100 bg-white px-5 py-4 dark:border-white/[0.08] dark:bg-surface-elevated"
                          x-data="{ submitting: false, clientError: '', message: '' }"
                          x-on:submit="
                            clientError = '';
                            const val = (message || '').trim();
                            if (!val) {
                              $event.preventDefault();
                              clientError = 'Tulis balasan terlebih dahulu sebelum mengirim.';
                              $refs.ta.focus();
                              return;
                            }
                            if (submitting) {
                              $event.preventDefault();
                              return;
                            }
                            submitting = true;
                            setTimeout(() => { submitting = false; }, 15000);
                          "
                          x-on:pageshow.window="submitting = false; clientError = '';"
                          x-on:reset="submitting = false; clientError = ''; message = ''">
                        @csrf

                        <label for="message" class="sr-only">Tulis balasan untuk tenant</label>
                        <div class="relative">
                            <textarea id="message" name="message" x-ref="ta" x-model="message" rows="3" required maxlength="2000" placeholder="Tulis balasan sebagai CS KosManager..."
                                      class="w-full resize-none rounded-2xl border bg-slate-50 px-4 py-3.5 pr-12 text-sm text-slate-900 placeholder-slate-400 transition focus:bg-white focus:ring-4 dark:bg-surface-elevated dark:text-slate-50 dark:placeholder-slate-500"
                                      :class="clientError ? 'border-red-300 focus:border-red-400 focus:ring-red-500/10' : 'border-slate-200 dark:border-white/[0.08] focus:border-primary-300 focus:ring-primary-500/10'"></textarea>
                            <button type="submit" :disabled="submitting" aria-label="Kirim balasan CS"
                                    class="absolute right-3 bottom-3 grid h-8 w-8 place-items-center rounded-xl bg-primary-600 text-white shadow-sm transition hover:bg-primary-700 disabled:opacity-60">
                                <i class="ri-send-plane-2-line"></i>
                            </button>
                        </div>
                        <p x-show="clientError" x-cloak role="alert" class="mt-2 text-xs font-medium text-red-500" x-text="clientError"></p>
                        @error('message') <p role="alert" class="mt-2 text-xs font-medium text-red-500">{{ $message }}</p> @enderror

                        <button type="submit" :disabled="submitting"
                                class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3.5 text-sm font-bold text-white shadow-sm shadow-primary-500/30 transition hover:bg-primary-700 disabled:opacity-60">
                            <span class="flex items-center gap-2" x-show="!submitting"><i class="ri-send-plane-2-line text-base"></i> Kirim Balasan CS</span>
                            <span class="flex items-center gap-2" x-show="submitting" x-cloak><i class="ri-loader-4-line animate-spin"></i> Mengirim...</span>
                        </button>
                        <p class="mt-2 text-center text-meta"><i class="ri-shield-check-line mr-1"></i> Balasan dikirim atas nama tim CS KosManager</p>
                    </form>
                </div>
            </x-slot>
        </x-chat-shell>
    </div>
</x-app-layout>
